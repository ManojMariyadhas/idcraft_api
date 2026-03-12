<?php

namespace App\Controller\Admin;

use App\Controller\ApiController;
use App\Repository\SchoolClassRepository;
use App\Repository\SchoolRepository;
use App\Repository\StudentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends ApiController
{
    #[Route('/api/admin/reports', name: 'admin_reports', methods: ['GET'])]
    public function reports(
        SchoolRepository $schoolRepository,
        SchoolClassRepository $classRepository,
        StudentRepository $studentRepository
    ): JsonResponse {
        $schoolCount = $schoolRepository->count([]);
        $classCount = $classRepository->count([]);
        $studentCount = $studentRepository->count([]);

        $bySchool = $studentRepository->createQueryBuilder('student')
            ->select('school.id AS school_id, school.name AS school_name, COUNT(student.id) AS students')
            ->join('student.school', 'school')
            ->groupBy('school.id')
            ->orderBy('students', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return $this->json([
            'stats' => [
                'schools' => $schoolCount,
                'classes' => $classCount,
                'students' => $studentCount,
            ],
            'by_school' => $bySchool,
        ]);
    }
}
