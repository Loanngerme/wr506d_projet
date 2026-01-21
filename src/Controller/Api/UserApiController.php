<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/users', name: 'api_users_')]
class UserApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        $qb = $em->getRepository(User::class)->createQueryBuilder('u');
        $totalUsers = (int) $qb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

        $users = $em->getRepository(User::class)->findBy([], ['id' => 'ASC'], $limit, $offset);

        $data = array_map(fn(User $user) => $this->userToArray($user), $users);

        return $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $totalUsers,
                'total_pages' => (int) ceil($totalUsers / $limit),
                'has_next' => $page < ceil($totalUsers / $limit),
                'has_previous' => $page > 1
            ]
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id, EntityManagerInterface $em, #[CurrentUser] ?User $currentUser): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Seul l'admin ou le propriétaire peut voir les détails
        if (!$currentUser || ($currentUser->getId() !== $user->getId() && !$currentUser->hasRole('ROLE_ADMIN'))) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'success' => true,
            'data' => $this->userToArray($user, true)
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $currentUser,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Seul l'admin ou le propriétaire peut modifier
        if (!$currentUser || ($currentUser->getId() !== $user->getId() && !$currentUser->hasRole('ROLE_ADMIN'))) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        // Seul l'admin peut modifier les rôles
        if (isset($data['roles']) && $currentUser->hasRole('ROLE_ADMIN')) {
            $user->setRoles($data['roles']);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $this->userToArray($user, true)
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    #[Route('/{id}/roles', name: 'update_roles', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateRoles(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['roles']) || !is_array($data['roles'])) {
            return $this->json([
                'success' => false,
                'error' => 'Roles must be an array'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setRoles($data['roles']);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'User roles updated successfully',
            'data' => $this->userToArray($user, true)
        ]);
    }

    private function userToArray(User $user, bool $detailed = false): array
    {
        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        if ($detailed) {
            $data['movies_count'] = $user->getMovies()->count();
            $data['comments_count'] = $user->getComments()->count();
        }

        return $data;
    }
}
