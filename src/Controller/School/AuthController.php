<?php

namespace App\Controller\School;

use App\Controller\ApiController;
use App\Entity\School;
use App\Exception\ApiException;
use App\Repository\SchoolRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends ApiController
{
    #[Route('/api/school/login', name: 'school_login', methods: ['POST'])]
    public function login(
        Request $request,
        SchoolRepository $schoolRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $schoolCode = trim((string) ($payload['school_code'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($schoolCode === '' || $password === '') {
            throw new ApiException('School code and password are required', 422);
        }

        /** @var School|null $school */
        $school = $schoolRepository->findOneBy(['schoolCode' => $schoolCode]);
        if (!$school || !$passwordHasher->isPasswordValid($school, $password)) {
            throw new ApiException('Invalid credentials', 401);
        }

        $token = $jwtManager->create($school);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $school->getId(),
                'name' => $school->getName(),
                'school_code' => $school->getSchoolCode(),
                'role' => 'ROLE_SCHOOL',
            ],
        ]);
    }
}
