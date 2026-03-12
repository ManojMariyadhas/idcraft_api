<?php

namespace App\Service;

use App\Entity\School;
use App\Entity\SchoolClass;
use App\Entity\Student;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvImportService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function importStudents(UploadedFile $file, SchoolClass $class, School $school): array
    {
        $handle = fopen($file->getPathname(), 'r');
        if ($handle === false) {
            throw new ApiException('Unable to read CSV file', 400);
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $lineNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($lineNumber === 1 && $this->isHeaderRow($row)) {
                continue;
            }

            $row = array_map('trim', $row);
            if (count($row) < 3) {
                $errors[] = "Line {$lineNumber}: invalid column count";
                continue;
            }

            [$admissionNo, $name, $phone] = $row;
            if ($admissionNo === '' || $name === '' || $phone === '') {
                $errors[] = "Line {$lineNumber}: missing required fields";
                continue;
            }

            $existing = $this->entityManager->getRepository(Student::class)->findOneBy([
                'school' => $school,
                'admissionNo' => $admissionNo,
            ]);

            if ($existing) {
                $skipped++;
                continue;
            }

            $student = (new Student())
                ->setSchool($school)
                ->setClass($class)
                ->setAdmissionNo($admissionNo)
                ->setName($name)
                ->setPhone($phone);

            $this->entityManager->persist($student);
            $imported++;
        }

        fclose($handle);
        $this->entityManager->flush();

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function isHeaderRow(array $row): bool
    {
        $header = array_map(static fn ($value) => strtolower(trim((string) $value)), $row);

        return in_array('admission_no', $header, true)
            && in_array('name', $header, true)
            && in_array('phone', $header, true);
    }
}
