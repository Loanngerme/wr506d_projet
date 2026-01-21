<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/create', name: 'category_create', methods: ['GET'])]
    public function create(EntityManagerInterface $entityManager): JsonResponse
    {
        $category = new Category();
        $category->setName('Science Fiction');

        $entityManager->persist($category);
        $entityManager->flush();

        return $this->json([
            'message' => 'Category created successfully!',
            'id' => $category->getId(),
            'name' => $category->getName(),
            'createdAt' => $category->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/category/list', name: 'category_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();

        $data = array_map(function (Category $category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'createdAt' => $category->getCreatedAt()?->format('Y-m-d H:i:s'),
                'movies' => $category->getMovies()->map(fn($movie) => [
                    'id' => $movie->getId(),
                    'name' => $movie->getName(),
                ])->toArray(),
            ];
        }, $categories);

        return $this->json($data);
    }
}
