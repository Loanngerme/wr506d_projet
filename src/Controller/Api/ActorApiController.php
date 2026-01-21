<?php

namespace App\Controller\Api;

use App\Entity\Actor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/actors', name: 'api_actors_')]
class ActorApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Paramètres de pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        // Récupérer le nombre total d'acteurs
        $totalActors = $em->getRepository(Actor::class)->count([]);

        // Récupérer les acteurs avec pagination
        $actors = $em->getRepository(Actor::class)->findBy(
            [],
            ['id' => 'ASC'],
            $limit,
            $offset
        );

        $data = array_map(function (Actor $actor) {
            return $this->actorToArray($actor);
        }, $actors);

        return $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $totalActors,
                'total_pages' => (int) ceil($totalActors / $limit),
                'has_next' => $page < ceil($totalActors / $limit),
                'has_previous' => $page > 1
            ]
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id, EntityManagerInterface $em): JsonResponse
    {
        $actor = $em->getRepository(Actor::class)->find($id);

        if (!$actor) {
            return $this->json([
                'success' => false,
                'error' => 'Actor not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $this->actorToArray($actor, true)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['lastname'])) {
            return $this->json([
                'success' => false,
                'error' => 'lastname is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $actor = new Actor();
        $actor->setLastname($data['lastname']);
        $actor->setFirstname($data['firstname'] ?? null);

        if (isset($data['dob'])) {
            $actor->setDob(new \DateTime($data['dob']));
        }
        if (isset($data['dod'])) {
            $actor->setDod(new \DateTime($data['dod']));
        }
        if (isset($data['bio'])) {
            $actor->setBio($data['bio']);
        }
        if (isset($data['photo'])) {
            $actor->setPhoto($data['photo']);
        }

        $em->persist($actor);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Actor created successfully',
            'data' => $this->actorToArray($actor)
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $actor = $em->getRepository(Actor::class)->find($id);

        if (!$actor) {
            return $this->json([
                'success' => false,
                'error' => 'Actor not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['lastname'])) {
            $actor->setLastname($data['lastname']);
        }
        if (isset($data['firstname'])) {
            $actor->setFirstname($data['firstname']);
        }
        if (isset($data['dob'])) {
            $actor->setDob(new \DateTime($data['dob']));
        }
        if (isset($data['dod'])) {
            $actor->setDod(new \DateTime($data['dod']));
        }
        if (isset($data['bio'])) {
            $actor->setBio($data['bio']);
        }
        if (isset($data['photo'])) {
            $actor->setPhoto($data['photo']);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Actor updated successfully',
            'data' => $this->actorToArray($actor)
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $actor = $em->getRepository(Actor::class)->find($id);

        if (!$actor) {
            return $this->json([
                'success' => false,
                'error' => 'Actor not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $em->remove($actor);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Actor deleted successfully'
        ]);
    }

    private function actorToArray(Actor $actor, bool $detailed = false): array
    {
        $data = [
            'id' => $actor->getId(),
            'lastname' => $actor->getLastname(),
            'firstname' => $actor->getFirstname(),
            'dob' => $actor->getDob()?->format('Y-m-d'),
            'dod' => $actor->getDod()?->format('Y-m-d'),
            'bio' => $actor->getBio(),
            'photo' => $actor->getPhoto(),
            'createdAt' => $actor->getCreatedAt()?->format('Y-m-d H:i:s')
        ];

        if ($detailed) {
            $data['movies'] = $actor->getMovies()->map(fn($movie) => [
                'id' => $movie->getId(),
                'name' => $movie->getName()
            ])->toArray();
        }

        return $data;
    }
}
