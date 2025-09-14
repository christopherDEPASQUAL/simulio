<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ClientDto;
use App\Dto\SimulationRequest;
use App\Entity\Client;
use App\Entity\Simulation;
use App\Service\SimulatorClient;
use App\Service\PdfExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SimulationController
{
    public function __construct(
        private readonly SimulatorClient $simulator,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/api/simulations', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            /** @var array<string,mixed> $payload */
            $payload = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return new JsonResponse(['detail' => 'Invalid JSON body'], 400);
        }

        $dto = SimulationRequest::fromArray($payload);
        $violations = $this->validator->validate($dto);
        if (\count($violations) > 0) {
            $errs = [];
            foreach ($violations as $v) {
                $errs[] = ['field' => (string)$v->getPropertyPath(), 'message' => (string)$v->getMessage()];
            }
            return new JsonResponse(['detail' => $errs], 422);
        }

        // Upsert client if provided
        $client = null;
        if ($dto->client instanceof ClientDto) {
            $clientRepo = $this->em->getRepository(Client::class);
            $client = $clientRepo->findOneBy(['email' => $dto->client->email]) ?? new Client();
            $client
                ->setFirstName($dto->client->first_name)
                ->setLastName($dto->client->last_name)
                ->setEmail($dto->client->email)
                ->setPhone($dto->client->phone)
                ->setAddress($dto->client->address);
            $this->em->persist($client);
        }

        // Call FastAPI
        $pythonInput = $dto->toPythonPayload();
        try {
            $result = $this->simulator->simulate($pythonInput);
        } catch (\Throwable $e) {
            return new JsonResponse(['detail' => 'Simulator error', 'error' => $e->getMessage()], 502);
        }

        // Persist simulation
        $s = (new Simulation())
            ->setClient($client)
            ->setInputJson($pythonInput)
            ->setResultJson($result)
            ->setMonthlyPaymentEur(number_format((float)($result['monthly_payment_eur'] ?? 0), 2, '.', ''));
        $this->em->persist($s);
        $this->em->flush();

        return new JsonResponse([
            'id' => $s->getId(),
            'result' => $result,
        ], 201);
    }

    #[Route('/api/simulations', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page  = max(1, (int)$request->query->get('page', 1));
        $limit = max(1, min(100, (int)$request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        $repo = $this->em->getRepository(Simulation::class);
        $qb = $repo->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        $out = array_map(static function (Simulation $s): array {
            return [
                'id' => $s->getId(),
                'client_email' => $s->getClient()?->getEmail(),
                'created_at' => $s->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'monthly_payment_eur' => (float)$s->getMonthlyPaymentEur(),
            ];
        }, $items);

        return new JsonResponse([
            'page' => $page,
            'limit' => $limit,
            'items' => $out,
        ]);
    }

    #[Route('/api/simulations/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $sim = $this->em->find(Simulation::class, $id);
        if (!$sim) {
            return new JsonResponse(['detail' => 'Not found'], 404);
        }
        return new JsonResponse([
            'id' => $sim->getId(),
            'client' => $sim->getClient() ? [
                'first_name' => $sim->getClient()->getFirstName(),
                'last_name'  => $sim->getClient()->getLastName(),
                'email'      => $sim->getClient()->getEmail(),
            ] : null,
            'input'  => $sim->getInputJson(),
            'result' => $sim->getResultJson(),
            'created_at' => $sim->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ]);
    }

    #[Route('/api/simulations/{id}/pdf', methods: ['GET'])]
    public function pdf(int $id, PdfExporter $pdf): Response
    {
        $sim = $this->em->find(Simulation::class, $id);
        if (!$sim) {
            return new JsonResponse(['detail' => 'Not found'], 404);
        }

        $bytes = $pdf->renderSimulation($sim);
        $filename = sprintf('simulation-%d.pdf', $sim->getId());

        return new Response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store',
        ]);
    }
}
