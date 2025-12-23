<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validation des champs requis
        if (!isset($data['email'])) {
            return $this->json([
                'success' => false,
                'error' => 'Email is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['password'])) {
            return $this->json([
                'success' => false,
                'error' => 'Password is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json([
                'success' => false,
                'error' => 'An account with this email already exists'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Créer le nouvel utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        // Assigner ROLE_USER par défaut (déjà fait dans __construct de User)

        // Valider l'entité
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json([
                'success' => false,
                'error' => implode(', ', $errorMessages)
            ], Response::HTTP_BAD_REQUEST);
        }

        // Persister l'utilisateur
        $em->persist($user);
        $em->flush();

        // Générer un token JWT pour l'utilisateur
        $token = $jwtManager->create($user);

        return $this->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                    'roles' => $user->getRoles(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
                ],
                'token' => $token
            ]
        ], Response::HTTP_CREATED);
    }
}
