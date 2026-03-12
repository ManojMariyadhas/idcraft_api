<?php

namespace App\Service;

use App\Exception\ApiException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService
{
    public function __construct(private string $uploadDir, private int $uploadMaxSize)
    {
    }

    public function uploadStudentPhoto(UploadedFile $file): string
    {
        if ($file->getSize() > $this->uploadMaxSize) {
            throw new ApiException('File too large', 422);
        }

        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowed, true)) {
            throw new ApiException('Invalid file type', 422);
        }

        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
            throw new ApiException('Unable to create upload directory', 500);
        }

        $filename = bin2hex(random_bytes(16)).'.'.$extension;
        $file->move($this->uploadDir, $filename);

        return $filename;
    }
}
