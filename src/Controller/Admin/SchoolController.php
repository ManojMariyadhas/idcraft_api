<?php

namespace App\Controller\Admin;

use App\Controller\ApiController;
use App\Entity\School;
use App\Exception\ApiException;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SchoolController extends ApiController
{
    #[Route('/api/admin/schools', name: 'admin_create_school', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];

        $school = (new School())
            ->setName((string) ($payload['name'] ?? ''))
            ->setMobile((string) ($payload['mobile'] ?? ''))
            ->setSchoolCode((string) ($payload['school_code'] ?? ''));

        $password = (string) ($payload['password'] ?? '');
        if ($password === '') {
            throw new ApiException('Password is required', 422);
        }

        $school->setPassword($passwordHasher->hashPassword($school, $password));
        $this->validateEntity($school, $validator);

        $entityManager->persist($school);
        $entityManager->flush();

        return $this->json($school, 201, [], ['groups' => ['school:read']]);
    }

    #[Route('/api/admin/schools', name: 'admin_list_schools', methods: ['GET'])]
    public function list(SchoolRepository $schoolRepository): JsonResponse
    {
        $schools = $schoolRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->json($schools, 200, [], ['groups' => ['school:read']]);
    }
}
