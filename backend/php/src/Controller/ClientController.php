<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/clients')]
final class ClientController extends AbstractController
{
    public function __construct(private EM $em, private ValidatorInterface $validator) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $req): JsonResponse {
        $user = $this->getUser(); if (!$user) return new JsonResponse(['detail'=>'Unauthorized'],401);
        $page  = max(1, (int)$req->query->get('page', 1));
        $limit = max(1, min(100, (int)$req->query->get('limit', 20)));
        $q     = trim((string)$req->query->get('search',''));

        $qb = $this->em->getRepository(Client::class)->createQueryBuilder('c')
            ->andWhere('c.user = :u')->setParameter('u',$user)
            ->orderBy('c.id','DESC')
            ->setFirstResult(($page-1)*$limit)->setMaxResults($limit);

        if ($q !== '') {
            $qb->andWhere('LOWER(c.email) LIKE :q OR LOWER(c.firstName) LIKE :q OR LOWER(c.lastName) LIKE :q')
               ->setParameter('q','%'.mb_strtolower($q).'%');
        }

        $items = array_map(fn(Client $c)=>[
            'id'=>$c->getId(),'first_name'=>$c->getFirstName(),'last_name'=>$c->getLastName(),
            'email'=>$c->getEmail(),'phone'=>$c->getPhone(),'address'=>$c->getAddress()
        ], $qb->getQuery()->getResult());

        return new JsonResponse(['items'=>$items]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $req): JsonResponse {
        $user = $this->getUser(); if (!$user) return new JsonResponse(['detail'=>'Unauthorized'],401);
        $p = json_decode($req->getContent(), true) ?? [];
        $c = (new Client())->setUser($user)
            ->setFirstName($p['first_name'] ?? '')
            ->setLastName($p['last_name'] ?? '')
            ->setEmail($p['email'] ?? '')
            ->setPhone($p['phone'] ?? '')
            ->setAddress($p['address'] ?? '');

        $errors = $this->validator->validate($c);
        if (count($errors)) return new JsonResponse(['detail' => (string)$errors], 422);

        $this->em->persist($c); $this->em->flush();
        return new JsonResponse(['id'=>$c->getId()], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Client $c, Request $req): JsonResponse {
        if ($c->getUser() !== $this->getUser()) return new JsonResponse(['detail'=>'Forbidden'],403);
        $p = json_decode($req->getContent(), true) ?? [];
        $c->setFirstName($p['first_name'] ?? $c->getFirstName())
          ->setLastName($p['last_name'] ?? $c->getLastName())
          ->setEmail($p['email'] ?? $c->getEmail())
          ->setPhone($p['phone'] ?? $c->getPhone())
          ->setAddress($p['address'] ?? $c->getAddress());

        $errors = $this->validator->validate($c);
        if (count($errors)) return new JsonResponse(['detail' => (string)$errors], 422);

        $this->em->flush();
        return new JsonResponse(null, 204);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Client $c): JsonResponse {
        if ($c->getUser() !== $this->getUser()) return new JsonResponse(['detail'=>'Forbidden'],403);
        $this->em->remove($c); $this->em->flush();
        return new JsonResponse(null, 204);
    }
}