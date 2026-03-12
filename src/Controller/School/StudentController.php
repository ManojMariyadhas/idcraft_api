<?php

namespace App\Controller\School;

use App\Controller\ApiController;
use App\Entity\Student;
use App\Exception\ApiException;
use App\Repository\SchoolClassRepository;
use App\Repository\StudentRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StudentController extends ApiController
{
    #[Route('/api/school/class/{id}/students', name: 'school_class_students', methods: ['GET'])]
    public function listByClass(
        int $id,
        SchoolClassRepository $classRepository,
        StudentRepository $studentRepository
    ): JsonResponse {
        $school = $this->currentSchool();
        $class = $classRepository->find($id);

        if (!$class || $class->getSchool()->getId() !== $school->getId()) {
            throw new ApiException('Class not found', 404);
        }

        $students = $studentRepository->findBy(['class' => $class], ['name' => 'ASC']);

        return $this->json($students, 200, [], ['groups' => ['student:read']]);
    }

    #[Route('/api/school/student', name: 'school_create_student', methods: ['POST'])]
    public function create(
        Request $request,
        SchoolClassRepository $classRepository,
        StudentRepository $studentRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $school = $this->currentSchool();
        $payload = json_decode($request->getContent(), true) ?? [];

        $classId = (int) ($payload['class_id'] ?? 0);
        $class = $classRepository->find($classId);
        if (!$class || $class->getSchool()->getId() !== $school->getId()) {
            throw new ApiException('Class not found', 404);
        }

        $admissionNo = (string) ($payload['admission_no'] ?? '');
        $existing = $studentRepository->findOneBy(['school' => $school, 'admissionNo' => $admissionNo]);
        if ($admissionNo !== '' && $existing) {
            throw new ApiException('Admission number already exists', 409);
        }

        $student = (new Student())
            ->setSchool($school)
            ->setClass($class)
            ->setAdmissionNo($admissionNo)
            ->setName((string) ($payload['name'] ?? ''))
            ->setPhone((string) ($payload['phone'] ?? ''))
            ->setParentName($payload['parent_name'] ?? null)
            ->setAddress($payload['address'] ?? null);

        $this->validateEntity($student, $validator);

        $entityManager->persist($student);
        $entityManager->flush();

        return $this->json($student, 201, [], ['groups' => ['student:read']]);
    }

    #[Route('/api/school/student/{id}', name: 'school_update_student', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        SchoolClassRepository $classRepository,
        StudentRepository $studentRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $school = $this->currentSchool();
        $student = $studentRepository->find($id);

        if (!$student || $student->getSchool()->getId() !== $school->getId()) {
            throw new ApiException('Student not found', 404);
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        if (isset($payload['class_id'])) {
            $classId = (int) $payload['class_id'];
            $class = $classRepository->find($classId);
            if (!$class || $class->getSchool()->getId() !== $school->getId()) {
                throw new ApiException('Class not found', 404);
            }
            $student->setClass($class);
        }

        if (isset($payload['admission_no'])) {
            $admissionNo = (string) $payload['admission_no'];
            $existing = $studentRepository->findOneBy(['school' => $school, 'admissionNo' => $admissionNo]);
            if ($existing && $existing->getId() !== $student->getId()) {
                throw new ApiException('Admission number already exists', 409);
            }
            $student->setAdmissionNo($admissionNo);
        }

        if (isset($payload['name'])) {
            $student->setName((string) $payload['name']);
        }
        if (isset($payload['phone'])) {
            $student->setPhone((string) $payload['phone']);
        }
        if (array_key_exists('parent_name', $payload)) {
            $student->setParentName($payload['parent_name']);
        }
        if (array_key_exists('address', $payload)) {
            $student->setAddress($payload['address']);
        }

        $this->validateEntity($student, $validator);
        $entityManager->flush();

        return $this->json($student, 200, [], ['groups' => ['student:read']]);
    }

    #[Route('/api/school/student/photo', name: 'school_student_photo', methods: ['POST'])]
    public function uploadPhoto(
        Request $request,
        StudentRepository $studentRepository,
        FileUploadService $fileUploadService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $school = $this->currentSchool();
        $studentId = (int) $request->request->get('student_id', 0);
        $file = $request->files->get('photo');

        if ($studentId <= 0 || !$file) {
            throw new ApiException('student_id and photo are required', 422);
        }

        $student = $studentRepository->find($studentId);
        if (!$student || $student->getSchool()->getId() !== $school->getId()) {
            throw new ApiException('Student not found', 404);
        }

        $filename = $fileUploadService->uploadStudentPhoto($file);
        $student->setPhoto($filename);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'photo' => $filename,
        ]);
    }
}
