<?php

namespace App\Controller;

use App\Entity\School;
use App\Exception\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    protected function validateEntity(object $entity, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($entity);
        if (count($violations) === 0) {
            return;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        throw new ApiException('Validation failed', 422, $errors);
    }

    protected function currentSchool(): School
    {
        $user = $this->getUser();
        if (!$user instanceof School) {
            throw new ApiException('School account required', 403);
        }

        return $user;
    }
}
