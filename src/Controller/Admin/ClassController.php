<?php

namespace App\Controller\Admin;

use App\Controller\ApiController;
use App\Entity\SchoolClass;
use App\Exception\ApiException;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClassController extends ApiController
{
    #[Route('/api/admin/classes', name: 'admin_create_class', methods: ['POST'])]
    public function create(
        Request $request,
        SchoolRepository $schoolRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];

        $schoolId = (int) ($payload['school_id'] ?? 0);
        $className = (string) ($payload['class'] ?? '');
        $division = (string) ($payload['division'] ?? '');

        if ($schoolId <= 0) {
            throw new ApiException('school_id is required', 422);
        }

        $school = $schoolRepository->find($schoolId);
        if (!$school) {
            throw new ApiException('School not found', 404);
        }

        $class = (new SchoolClass())
            ->setSchool($school)
            ->setClassName($className)
            ->setDivision($division);

        $this->validateEntity($class, $validator);

        $entityManager->persist($class);
        $entityManager->flush();

        return $this->json($class, 201, [], ['groups' => ['class:read']]);
    }
}
