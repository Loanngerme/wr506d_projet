<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This method will never be executed because the CustomAuthenticator
        // will intercept the request before it reaches here
        return new JsonResponse(['error' => 'Should not reach here'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse([
                'error' => 'Email and password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse([
                'error' => 'User already exists'
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($data['email']);

        // Ajouter firstname et lastname s'ils sont fournis
        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }
        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'User created successfully',
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname()
        ], Response::HTTP_CREATED);
    }
}
