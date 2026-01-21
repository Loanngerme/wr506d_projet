<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/me', name: 'api_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
