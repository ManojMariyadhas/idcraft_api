<?php

namespace App\Controller\Admin;

use App\Controller\ApiController;
use App\Entity\Admin;
use App\Exception\ApiException;
use App\Repository\AdminRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends ApiController
{
    #[Route('/api/admin/login', name: 'admin_login', methods: ['POST'])]
    public function login(
        Request $request,
        AdminRepository $adminRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($username === '' || $password === '') {
            throw new ApiException('Username and password are required', 422);
        }

        /** @var Admin|null $admin */
        $admin = $adminRepository->findOneBy(['username' => $username]);
        if (!$admin || !$passwordHasher->isPasswordValid($admin, $password)) {
            throw new ApiException('Invalid credentials', 401);
        }

        $token = $jwtManager->create($admin);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $admin->getId(),
                'username' => $admin->getUsername(),
                'role' => 'ROLE_ADMIN',
            ],
        ]);
    }
}
