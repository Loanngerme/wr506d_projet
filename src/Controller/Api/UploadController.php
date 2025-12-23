<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/v1/upload', name: 'api_upload_')]
class UploadController extends AbstractController
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    private const UPLOAD_DIR = 'uploads';

    #[Route('', name: 'file', methods: ['POST'])]
    public function uploadFile(
        Request $request,
        SluggerInterface $slugger,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $file = $request->files->get('file');

        if (!$file) {
            return $this->json([
                'success' => false,
                'error' => 'No file provided'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return $this->json([
                'success' => false,
                'error' => 'File size exceeds maximum allowed size of 5MB'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate file extension
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->guessExtension() ?? $file->getClientOriginalExtension());

        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return $this->json([
                'success' => false,
                'error' => 'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_EXTENSIONS)
            ], Response::HTTP_BAD_REQUEST);
        }

        // Generate unique filename
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

        // Get upload directory
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/' . self::UPLOAD_DIR;

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move file
        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to upload file'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $fileUrl = '/uploads/' . $newFilename;

        return $this->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'filename' => $newFilename,
                'original_filename' => $file->getClientOriginalName(),
                'url' => $fileUrl,
                'size' => $file->getSize(),
                'extension' => $extension
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/image', name: 'image', methods: ['POST'])]
    public function uploadImage(
        Request $request,
        SluggerInterface $slugger,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Authentication required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $file = $request->files->get('image');

        if (!$file) {
            return $this->json([
                'success' => false,
                'error' => 'No image provided'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate it's an image
        $extension = strtolower($file->guessExtension() ?? $file->getClientOriginalExtension());
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $imageExtensions)) {
            return $this->json([
                'success' => false,
                'error' => 'File must be an image. Allowed types: ' . implode(', ', $imageExtensions)
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return $this->json([
                'success' => false,
                'error' => 'Image size exceeds maximum allowed size of 5MB'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Generate unique filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

        // Get upload directory
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/' . self::UPLOAD_DIR . '/images';

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move file
        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to upload image'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $imageUrl = '/uploads/images/' . $newFilename;

        return $this->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => [
                'filename' => $newFilename,
                'original_filename' => $file->getClientOriginalName(),
                'url' => $imageUrl,
                'size' => $file->getSize(),
                'extension' => $extension
            ]
        ], Response::HTTP_CREATED);
    }
}
