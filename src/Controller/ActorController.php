<?php

namespace App\Controller;

use App\Entity\Actor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ActorController extends AbstractController
{
    #[Route('/actor/create', name: 'actor_create', methods: ['GET'])]
    public function create(EntityManagerInterface $entityManager): JsonResponse
    {
        $actor = new Actor();
        $actor->setLastname('DiCaprio');
        $actor->setFirstname('Leonardo');
        $actor->setDob(new \DateTime('1974-11-11'));
        $actor->setBio('Leonardo Wilhelm DiCaprio is an American actor and film producer.');
        $actor->setPhoto('dicaprio.jpg');

        $entityManager->persist($actor);
        $entityManager->flush();

        return $this->json([
            'message' => 'Actor created successfully!',
            'id' => $actor->getId(),
            'name' => $actor->getFirstname() . ' ' . $actor->getLastname(),
            'dob' => $actor->getDob()?->format('Y-m-d'),
            'createdAt' => $actor->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/actor/list', name: 'actor_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $actors = $entityManager->getRepository(Actor::class)->findAll();

        $data = array_map(function (Actor $actor) {
            return [
                'id' => $actor->getId(),
                'lastname' => $actor->getLastname(),
                'firstname' => $actor->getFirstname(),
                'dob' => $actor->getDob()?->format('Y-m-d'),
                'dod' => $actor->getDod()?->format('Y-m-d'),
                'bio' => $actor->getBio(),
                'photo' => $actor->getPhoto(),
                'createdAt' => $actor->getCreatedAt()?->format('Y-m-d H:i:s'),
                'movies' => $actor->getMovies()->map(fn($movie) => [
                    'id' => $movie->getId(),
                    'name' => $movie->getName(),
                ])->toArray(),
            ];
        }, $actors);

        return $this->json($data);
    }
}
