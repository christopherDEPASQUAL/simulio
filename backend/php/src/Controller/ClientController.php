<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/clients')]
final class ClientController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['detail' => 'Unauthorized'], 401);
        }

        $items = $em->getRepository(Client::class)
            ->findBy(['user' => $user], ['id' => 'DESC']);

        $rows = array_map(static fn (Client $c) => [
            'id'         => $c->getId(),
            'first_name' => $c->getFirstName(),
            'last_name'  => $c->getLastName(),
            'email'      => $c->getEmail(),
            'phone'      => $c->getPhone(),
            'address'    => $c->getAddress(),
            'user_id'    => $c->getUser()?->getId(),
            'created_at' => $c->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $items);

        return new JsonResponse(['items' => $rows]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $req, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['detail' => 'Unauthorized'], 401);
        }

        $data = json_decode($req->getContent(), true) ?: [];

        $client = (new Client())
            ->setUser($user)
            ->setFirstName($data['first_name'] ?? '')
            ->setLastName($data['last_name'] ?? '')
            ->setEmail($data['email'] ?? '')
            ->setPhone($data['phone'] ?? '')
            ->setAddress($data['address'] ?? '');

        $em->persist($client);
        $em->flush();

        return new JsonResponse([
            'id'         => $client->getId(),
            'first_name' => $client->getFirstName(),
            'last_name'  => $client->getLastName(),
            'email'      => $client->getEmail(),
            'phone'      => $client->getPhone(),
            'address'    => $client->getAddress(),
            'user_id'    => $client->getUser()?->getId(),
            'created_at' => $client->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], 201);
    }
}
