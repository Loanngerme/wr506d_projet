<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\Movie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/comments', name: 'api_comments_')]
class CommentApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;
        $movieId = $request->query->get('movie');

        $qb = $em->getRepository(Comment::class)->createQueryBuilder('c');

        if ($movieId) {
            $qb->andWhere('c.movie = :movieId')
               ->setParameter('movieId', $movieId);
        }

        $totalComments = (int) $qb->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        $qb->select('c')
           ->orderBy('c.createdAt', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);

        $comments = $qb->getQuery()->getResult();

        $data = array_map(fn(Comment $comment) => $this->commentToArray($comment), $comments);

        return $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $totalComments,
                'total_pages' => (int) ceil($totalComments / $limit),
                'has_next' => $page < ceil($totalComments / $limit),
                'has_previous' => $page > 1
            ]
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id, EntityManagerInterface $em): JsonResponse
    {
        $comment = $em->getRepository(Comment::class)->find($id);

        if (!$comment) {
            return $this->json([
                'success' => false,
                'error' => 'Comment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $this->commentToArray($comment, true)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return $this->json([
                'success' => false,
                'error' => 'Content is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['movie_id'])) {
            return $this->json([
                'success' => false,
                'error' => 'Movie ID is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $movie = $em->getRepository(Movie::class)->find($data['movie_id']);
        if (!$movie) {
            return $this->json([
                'success' => false,
                'error' => 'Movie not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setMovie($movie);
        $comment->setAuthor($user);

        $errors = $validator->validate($comment);
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

        $em->persist($comment);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => $this->commentToArray($comment, true)
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $comment = $em->getRepository(Comment::class)->find($id);

        if (!$comment) {
            return $this->json([
                'success' => false,
                'error' => 'Comment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Only author or admin can update
        if ($comment->getAuthor()->getId() !== $user->getId() && !$user->hasRole('ROLE_ADMIN')) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $comment->setContent($data['content']);
        }

        $errors = $validator->validate($comment);
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

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $this->commentToArray($comment, true)
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $comment = $em->getRepository(Comment::class)->find($id);

        if (!$comment) {
            return $this->json([
                'success' => false,
                'error' => 'Comment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Only author or admin can delete
        if ($comment->getAuthor()->getId() !== $user->getId() && !$user->hasRole('ROLE_ADMIN')) {
            return $this->json([
                'success' => false,
                'error' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        $em->remove($comment);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    private function commentToArray(Comment $comment, bool $detailed = false): array
    {
        $data = [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $comment->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'author' => [
                'id' => $comment->getAuthor()->getId(),
                'email' => $comment->getAuthor()->getEmail(),
                'firstname' => $comment->getAuthor()->getFirstname(),
                'lastname' => $comment->getAuthor()->getLastname()
            ]
        ];

        if ($detailed) {
            $data['movie'] = [
                'id' => $comment->getMovie()->getId(),
                'name' => $comment->getMovie()->getName()
            ];
        } else {
            $data['movie_id'] = $comment->getMovie()->getId();
        }

        return $data;
    }
}
