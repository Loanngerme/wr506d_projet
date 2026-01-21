<?php

namespace App\Controller\Api;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Director;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/movies', name: 'api_movies_')]
class MovieApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Paramètres de pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 15)));
        $offset = ($page - 1) * $limit;

        // Paramètres de filtrage
        $onlineFilter = $request->query->get('online');
        $titleFilter = $request->query->get('title');
        $searchQuery = $request->query->get('search'); // Recherche globale
        $authorFilter = $request->query->get('author'); // Filtre par auteur (ID)
        $dateFrom = $request->query->get('date_from'); // Date de début (releaseDate)
        $dateTo = $request->query->get('date_to'); // Date de fin (releaseDate)

        // Construire la requête avec QueryBuilder pour supporter les filtres
        $qb = $em->getRepository(Movie::class)->createQueryBuilder('m')
            ->leftJoin('m.actors', 'a')
            ->leftJoin('m.categories', 'c')
            ->leftJoin('m.author', 'u')
            ->groupBy('m.id');

        // Filtre par statut online
        if ($onlineFilter !== null) {
            $isOnline = filter_var($onlineFilter, FILTER_VALIDATE_BOOLEAN);
            $qb->andWhere('m.online = :online')
               ->setParameter('online', $isOnline);
        }

        // Filtre par titre (recherche partielle insensible à la casse)
        if ($titleFilter !== null && $titleFilter !== '') {
            $qb->andWhere('LOWER(m.name) LIKE LOWER(:title)')
               ->setParameter('title', '%' . $titleFilter . '%');
        }

        // Recherche globale (dans titre, description, acteurs, catégories)
        if ($searchQuery !== null && $searchQuery !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(m.name) LIKE LOWER(:search)',
                    'LOWER(m.description) LIKE LOWER(:search)',
                    'LOWER(a.firstname) LIKE LOWER(:search)',
                    'LOWER(a.lastname) LIKE LOWER(:search)',
                    'LOWER(c.name) LIKE LOWER(:search)'
                )
            )->setParameter('search', '%' . $searchQuery . '%');
        }

        // Filtre par auteur
        if ($authorFilter !== null && $authorFilter !== '') {
            $qb->andWhere('m.author = :author')
               ->setParameter('author', $authorFilter);
        }

        // Filtre par date de sortie (date de début)
        if ($dateFrom !== null && $dateFrom !== '') {
            try {
                $dateFromObj = new \DateTime($dateFrom);
                $qb->andWhere('m.releaseDate >= :dateFrom')
                   ->setParameter('dateFrom', $dateFromObj);
            } catch (\Exception $e) {
                // Ignorer si la date n'est pas valide
            }
        }

        // Filtre par date de sortie (date de fin)
        if ($dateTo !== null && $dateTo !== '') {
            try {
                $dateToObj = new \DateTime($dateTo);
                $qb->andWhere('m.releaseDate <= :dateTo')
                   ->setParameter('dateTo', $dateToObj);
            } catch (\Exception $e) {
                // Ignorer si la date n'est pas valide
            }
        }

        // Compter le total avec les filtres appliqués
        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT m.id)');
        $countQb->resetDQLPart('groupBy'); // Remove groupBy for counting
        $totalMovies = (int) $countQb->getQuery()->getSingleScalarResult();

        // Appliquer la pagination et l'ordre
        $qb->orderBy('m.id', 'ASC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);

        $movies = $qb->getQuery()->getResult();

        $data = array_map(function (Movie $movie) {
            return $this->movieToArray($movie);
        }, $movies);

        return $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $totalMovies,
                'total_pages' => (int) ceil($totalMovies / $limit),
                'has_next' => $page < ceil($totalMovies / $limit),
                'has_previous' => $page > 1
            ]
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id, EntityManagerInterface $em): JsonResponse
    {
        $movie = $em->getRepository(Movie::class)->find($id);

        if (!$movie) {
            return $this->json([
                'success' => false,
                'error' => 'Movie not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $this->movieToArray($movie, true)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return $this->json([
                'success' => false,
                'error' => 'name is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $movie = new Movie();
        $movie->setName($data['name']);
        $movie->setDescription($data['description'] ?? null);
        $movie->setDuration($data['duration'] ?? null);
        $movie->setOnline($data['online'] ?? false);
        $movie->setNbEntries($data['nbEntries'] ?? null);
        $movie->setUrl($data['url'] ?? null);
        $movie->setBudget($data['budget'] ?? null);

        if (isset($data['releaseDate'])) {
            $movie->setReleaseDate(new \DateTime($data['releaseDate']));
        }
        if (isset($data['image'])) {
            $movie->setImage($data['image']);
        }
        if (isset($data['director'])) {
            $director = $em->getRepository(Director::class)->find($data['director']);
            if ($director) {
                $movie->setDirector($director);
            }
        }

        // Gestion des acteurs
        if (isset($data['actors']) && is_array($data['actors'])) {
            foreach ($data['actors'] as $actorId) {
                $actor = $em->getRepository(Actor::class)->find($actorId);
                if ($actor) {
                    $movie->addActor($actor);
                }
            }
        }

        // Gestion des catégories
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {
                $category = $em->getRepository(Category::class)->find($categoryId);
                if ($category) {
                    $movie->addCategory($category);
                }
            }
        }

        $em->persist($movie);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Movie created successfully',
            'data' => $this->movieToArray($movie, true)
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $movie = $em->getRepository(Movie::class)->find($id);

        if (!$movie) {
            return $this->json([
                'success' => false,
                'error' => 'Movie not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $movie->setName($data['name']);
        }
        if (isset($data['description'])) {
            $movie->setDescription($data['description']);
        }
        if (isset($data['duration'])) {
            $movie->setDuration($data['duration']);
        }
        if (isset($data['online'])) {
            $movie->setOnline((bool) $data['online']);
        }
        if (isset($data['nbEntries'])) {
            $movie->setNbEntries($data['nbEntries']);
        }
        if (isset($data['url'])) {
            $movie->setUrl($data['url']);
        }
        if (isset($data['budget'])) {
            $movie->setBudget($data['budget']);
        }
        if (isset($data['director'])) {
            $director = $em->getRepository(Director::class)->find($data['director']);
            $movie->setDirector($director);
        }
        if (isset($data['releaseDate'])) {
            $movie->setReleaseDate(new \DateTime($data['releaseDate']));
        }
        if (isset($data['image'])) {
            $movie->setImage($data['image']);
        }

        // Mise à jour des acteurs
        if (isset($data['actors']) && is_array($data['actors'])) {
            // Retirer tous les acteurs existants
            foreach ($movie->getActors() as $actor) {
                $movie->removeActor($actor);
            }
            // Ajouter les nouveaux
            foreach ($data['actors'] as $actorId) {
                $actor = $em->getRepository(Actor::class)->find($actorId);
                if ($actor) {
                    $movie->addActor($actor);
                }
            }
        }

        // Mise à jour des catégories
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($movie->getCategories() as $category) {
                $movie->removeCategory($category);
            }
            foreach ($data['categories'] as $categoryId) {
                $category = $em->getRepository(Category::class)->find($categoryId);
                if ($category) {
                    $movie->addCategory($category);
                }
            }
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Movie updated successfully',
            'data' => $this->movieToArray($movie, true)
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $movie = $em->getRepository(Movie::class)->find($id);

        if (!$movie) {
            return $this->json([
                'success' => false,
                'error' => 'Movie not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $em->remove($movie);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Movie deleted successfully'
        ]);
    }

    private function movieToArray(Movie $movie, bool $detailed = false): array
    {
        $data = [
            'id' => $movie->getId(),
            'name' => $movie->getName(),
            'description' => $movie->getDescription(),
            'duration' => $movie->getDuration(),
            'releaseDate' => $movie->getReleaseDate()?->format('Y-m-d'),
            'image' => $movie->getImage(),
            'online' => $movie->isOnline(),
            'nbEntries' => $movie->getNbEntries(),
            'url' => $movie->getUrl(),
            'budget' => $movie->getBudget(),
            'createdAt' => $movie->getCreatedAt()?->format('Y-m-d H:i:s')
        ];

        // Ajouter le réalisateur s'il existe
        if ($movie->getDirector()) {
            $data['director'] = [
                'id' => $movie->getDirector()->getId(),
                'firstname' => $movie->getDirector()->getFirstname(),
                'lastname' => $movie->getDirector()->getLastname()
            ];
        } else {
            $data['director'] = null;
        }

        // Ajouter l'auteur s'il existe
        if ($movie->getAuthor()) {
            $data['author'] = [
                'id' => $movie->getAuthor()->getId(),
                'email' => $movie->getAuthor()->getEmail(),
                'firstname' => $movie->getAuthor()->getFirstname(),
                'lastname' => $movie->getAuthor()->getLastname()
            ];
        } else {
            $data['author'] = null;
        }

        if ($detailed) {
            $data['actors'] = $movie->getActors()->map(fn($actor) => [
                'id' => $actor->getId(),
                'firstname' => $actor->getFirstname(),
                'lastname' => $actor->getLastname()
            ])->toArray();

            $data['categories'] = $movie->getCategories()->map(fn($category) => [
                'id' => $category->getId(),
                'name' => $category->getName()
            ])->toArray();
        }

        return $data;
    }
}
