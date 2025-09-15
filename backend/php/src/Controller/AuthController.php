<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth', name: 'app_auth_')]
final class AuthController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private JWTTokenManagerInterface $jwt,
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) return new JsonResponse(['detail'=>'Invalid JSON'], 400);

        $email = (string)($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');
        $first = (string)($data['first_name'] ?? '');
        $last  = (string)($data['last_name'] ?? '');

        if ($email === '' || $password === '' || $first === '' || $last === '') {
            return new JsonResponse(['detail' => 'Missing fields'], 422);
        }
        if ($this->em->getRepository(User::class)->findOneBy(['email'=>$email])) {
            return new JsonResponse(['detail' => 'Email already used'], 409);
        }

        $u = (new User())
            ->setEmail($email)
            ->setFirstName($first)
            ->setLastName($last);
        $u->setPassword($this->hasher->hashPassword($u, $password));

        $this->em->persist($u);
        $this->em->flush();

        $token = $this->jwt->create($u);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'first_name' => $u->getFirstName(),
                'last_name'  => $u->getLastName(),
                'email'      => $u->getEmail(),
            ],
        ], 201);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) return new JsonResponse(['detail'=>'Invalid JSON'], 400);

        $email = (string)($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');

        $u = $this->em->getRepository(User::class)->findOneBy(['email'=>$email]);
        if (!$u || !$this->hasher->isPasswordValid($u, $password)) {
            return new JsonResponse(['detail' => 'Invalid credentials'], 401);
        }

        $token = $this->jwt->create($u);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'first_name' => $u->getFirstName(),
                'last_name'  => $u->getLastName(),
                'email'      => $u->getEmail(),
            ],
        ]);
    }
}
