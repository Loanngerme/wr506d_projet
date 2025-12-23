<?php

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    #[Route('/movie/create', name: 'movie_create', methods: ['GET'])]
    public function create(EntityManagerInterface $entityManager): JsonResponse
    {
        $movie = new Movie();
        $movie->setName('Inception');
        $movie->setDescription('A thief who steals corporate secrets through the use of dream-sharing technology.');
        $movie->setDuration(148);
        $movie->setReleaseDate(new \DateTime('2010-07-16'));
        $movie->setImage('inception.jpg');

        $entityManager->persist($movie);
        $entityManager->flush();

        return $this->json([
            'message' => 'Movie created successfully!',
            'id' => $movie->getId(),
            'name' => $movie->getName(),
            'duration' => $movie->getDuration(),
            'releaseDate' => $movie->getReleaseDate()?->format('Y-m-d'),
        ]);
    }

    #[Route('/movie/{id}/update', name: 'movie_update', methods: ['GET'])]
    public function update(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $movie = $entityManager->getRepository(Movie::class)->find($id);

        if (!$movie) {
            return $this->json(['error' => 'Movie not found'], 404);
        }

        $movie->setName($movie->getName() . ' - Updated');
        $movie->setDuration($movie->getDuration() + 10);

        $entityManager->flush();

        return $this->json([
            'message' => 'Movie updated successfully!',
            'id' => $movie->getId(),
            'name' => $movie->getName(),
            'duration' => $movie->getDuration(),
        ]);
    }

    #[Route('/movie/list', name: 'movie_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $movies = $entityManager->getRepository(Movie::class)->findAll();

        $data = array_map(function(Movie $movie) {
            return [
                'id' => $movie->getId(),
                'name' => $movie->getName(),
                'description' => $movie->getDescription(),
                'duration' => $movie->getDuration(),
                'releaseDate' => $movie->getReleaseDate()?->format('Y-m-d'),
                'image' => $movie->getImage(),
                'createdAt' => $movie->getCreatedAt()?->format('Y-m-d H:i:s'),
                'categories' => $movie->getCategories()->map(fn($cat) => $cat->getName())->toArray(),
                'actors' => $movie->getActors()->map(fn($actor) => $actor->getFirstname() . ' ' . $actor->getLastname())->toArray(),
            ];
        }, $movies);

        return $this->json($data);
    }
}
