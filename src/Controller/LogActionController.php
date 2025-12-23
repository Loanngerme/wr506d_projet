<?php

namespace App\Controller;

use App\Entity\LogAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LogActionController extends AbstractController
{
    #[Route('/logs', name: 'logs_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $logs = $entityManager->getRepository(LogAction::class)->findBy([], ['createdAt' => 'DESC']);

        $data = array_map(function(LogAction $log) {
            return [
                'id' => $log->getId(),
                'entityType' => $log->getEntityType(),
                'entityId' => $log->getEntityId(),
                'action' => $log->getAction(),
                'createdAt' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
                'metadata' => $log->getMetadata(),
            ];
        }, $logs);

        return $this->json($data);
    }
}
