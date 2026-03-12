<?php

namespace App\Controller\School;

use App\Controller\ApiController;
use App\Repository\SchoolClassRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ClassController extends ApiController
{
    #[Route('/api/school/classes', name: 'school_classes', methods: ['GET'])]
    public function list(SchoolClassRepository $classRepository): JsonResponse
    {
        $school = $this->currentSchool();
        $classes = $classRepository->findBy(['school' => $school], ['className' => 'ASC', 'division' => 'ASC']);

        return $this->json($classes, 200, [], ['groups' => ['class:read']]);
    }
}
