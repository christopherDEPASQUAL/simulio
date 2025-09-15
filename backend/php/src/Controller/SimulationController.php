<?php

declare(strict_types=1);

// src/Controller/SimulationController.php
namespace App\Controller;

use App\Entity\Client;
use App\Entity\Simulation;
use App\Service\SimulatorClient;
use App\Service\PdfExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;

final class SimulationController extends AbstractController
{
    public function __construct(
        private readonly SimulatorClient $simulator,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/api/simulations', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['detail'=>'Unauthorized'], 401);

        $payload = json_decode($request->getContent(), true) ?: [];

        // client_id ou client inline
        $client = null;
        if (!empty($payload['client_id'])) {
            $client = $this->em->getRepository(Client::class)
                ->findOneBy(['id' => (int)$payload['client_id'], 'user' => $user]);
            if (!$client) return new JsonResponse(['detail' => 'client_id not found'], 404);
        } elseif (!empty($payload['client'])) {
            $c = $payload['client'];
            $client = $this->em->getRepository(Client::class)->findOneBy([
                'email' => $c['email'] ?? null,
                'user'  => $user,
            ]) ?? (new Client())->setUser($user);
            $client->setFirstName($c['first_name'] ?? '')
                   ->setLastName($c['last_name'] ?? '')
                   ->setEmail($c['email'] ?? '')
                   ->setPhone($c['phone'] ?? '')
                   ->setAddress($c['address'] ?? '');
            $this->em->persist($client);
        }

        // appel simulateur
        try {
            $result = $this->simulator->simulate($payload);
        } catch (\Throwable $e) {
            return new JsonResponse(['detail'=>'Simulator error','error'=>$e->getMessage()], 502);
        }

        $simulation = (new Simulation())
            ->setUser($user)
            ->setClient($client)
            ->setInputJson($payload)
            ->setResultJson($result)
            ->setMonthlyPaymentEur((float) ($result['monthly_payment_eur'] ?? $result['result']['monthly_payment_eur'] ?? 0));

        $this->em->persist($simulation);
        $this->em->flush();

        return new JsonResponse(['id'=>$simulation->getId(),'result'=>$result], 201);
    }

    #[Route('/api/simulations', methods: ['GET'])]
    public function history(Request $req): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['detail'=>'Unauthorized'], 401);

        $page = max(1, (int) $req->query->get('page', 1));
        $limit = max(1, min(100, (int) $req->query->get('limit', 20)));

        $items = $this->em->getRepository(Simulation::class)->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            $limit,
            ($page-1)*$limit
        );

        $rows = array_map(static fn (Simulation $s) => [
            'id' => $s->getId(),
            'created_at' => $s->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'client_email' => $s->getClient()?->getEmail(),
            'monthly_payment_eur' => (float)$s->getMonthlyPaymentEur(),
        ], $items);

        return new JsonResponse(['items' => $rows]);
    }

    #[Route('/api/simulations/{id}/pdf', methods: ['GET'])]
    public function pdf(Simulation $simulation, PdfExporter $pdf): Response
    {
        if ($simulation->getUser() !== $this->getUser()) {
            return new Response('', 403);
        }
        try {
            $binary = $pdf->make($simulation);
            return new Response($binary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="simulation-'.$simulation->getId().'.pdf"',
            ]);
        } catch (\Throwable $e) {
            // log si tu veux: $this->container->get('logger')->error('PDF error: '.$e->getMessage(), ['exception'=>$e]);
            return new JsonResponse(['detail' => 'PDF generation failed'], 500);
        }
    }

    #[Route('/api/simulations/{id}', methods: ['DELETE'])]
    public function delete(Simulation $simulation): JsonResponse
    {
        if ($simulation->getUser() !== $this->getUser()) {
            return new JsonResponse(['detail'=>'Forbidden'], 403);
        }
        $this->em->remove($simulation); $this->em->flush();
        return new JsonResponse(null, 204);
    }
}
