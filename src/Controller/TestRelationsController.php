<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestRelationsController extends AbstractController
{
    #[Route('/test/complete', name: 'test_complete', methods: ['GET'])]
    public function createComplete(EntityManagerInterface $entityManager): JsonResponse
    {
        // Créer une catégorie
        $category = new Category();
        $category->setName('Action');
        $entityManager->persist($category);

        // Créer un acteur
        $actor = new Actor();
        $actor->setLastname('Nolan');
        $actor->setFirstname('Christopher');
        $actor->setDob(new \DateTime('1970-07-30'));
        $actor->setBio('British-American film director, producer, and screenwriter.');
        $entityManager->persist($actor);

        // Créer un film avec les relations
        $movie = new Movie();
        $movie->setName('The Dark Knight');
        $movie->setDescription('When the menace known as the Joker wreaks havoc on Gotham.');
        $movie->setDuration(152);
        $movie->setReleaseDate(new \DateTime('2008-07-18'));
        $movie->setImage('dark-knight.jpg');

        // Ajouter les relations
        $movie->addCategory($category);
        $movie->addActor($actor);

        $entityManager->persist($movie);
        $entityManager->flush();

        return $this->json([
            'message' => 'Complete test created successfully!',
            'movie' => [
                'id' => $movie->getId(),
                'name' => $movie->getName(),
                'duration' => $movie->getDuration(),
                'categories' => $movie->getCategories()->map(fn($cat) => $cat->getName())->toArray(),
                'actors' => $movie->getActors()->map(fn($act) => $act->getFirstname() . ' ' . $act->getLastname())->toArray(),
            ],
        ]);
    }

    #[Route('/test/add-relation/{movieId}/{actorId}', name: 'test_add_actor', methods: ['GET'])]
    public function addActorToMovie(int $movieId, int $actorId, EntityManagerInterface $entityManager): JsonResponse
    {
        $movie = $entityManager->getRepository(Movie::class)->find($movieId);
        $actor = $entityManager->getRepository(Actor::class)->find($actorId);

        if (!$movie || !$actor) {
            return $this->json(['error' => 'Movie or Actor not found'], 404);
        }

        $movie->addActor($actor);
        $entityManager->flush();

        return $this->json([
            'message' => 'Actor added to movie successfully!',
            'movie' => $movie->getName(),
            'actor' => $actor->getFirstname() . ' ' . $actor->getLastname(),
        ]);
    }
}
