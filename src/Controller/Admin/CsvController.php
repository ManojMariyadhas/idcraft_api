<?php

namespace App\Controller\Admin;

use App\Controller\ApiController;
use App\Exception\ApiException;
use App\Repository\SchoolClassRepository;
use App\Service\CsvImportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsvController extends ApiController
{
    #[Route('/api/admin/upload-csv', name: 'admin_upload_csv', methods: ['POST'])]
    public function upload(
        Request $request,
        SchoolClassRepository $classRepository,
        CsvImportService $csvImportService,
        ValidatorInterface $validator
    ): JsonResponse {
        $classId = (int) $request->request->get('class_id', 0);
        $file = $request->files->get('file');

        if ($classId <= 0) {
            throw new ApiException('class_id is required', 422);
        }

        if (!$file) {
            throw new ApiException('CSV file is required', 422);
        }

        $violations = $validator->validate($file, new File([
            'maxSize' => '2M',
            'mimeTypes' => ['text/plain', 'text/csv', 'application/vnd.ms-excel'],
            'mimeTypesMessage' => 'Please upload a valid CSV file',
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            throw new ApiException('Validation failed', 422, $errors);
        }

        $class = $classRepository->find($classId);
        if (!$class) {
            throw new ApiException('Class not found', 404);
        }

        $result = $csvImportService->importStudents($file, $class, $class->getSchool());

        return $this->json([
            'status' => 'success',
            'message' => 'CSV import completed',
            'result' => $result,
        ]);
    }
}
