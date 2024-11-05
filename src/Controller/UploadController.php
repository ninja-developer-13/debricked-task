<?php
// src/Controller/UploadController.php
namespace App\Controller;

use App\Service\DebrickedApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadController
 *
 * Controller responsible for handling file uploads and initiating scans through the Debricked API.
 */
class UploadController extends AbstractController
{
    private DebrickedApiService $debrickedApiService;
    

    /**
     * UploadController constructor.
     *
     * @param DebrickedApiService $debrickedApiService Service for handling Debricked API interactions
     */
    public function __construct(DebrickedApiService $debrickedApiService)
    {
        $this->debrickedApiService = $debrickedApiService;
    }

    /**
     * Handles file upload and initiates scanning through the Debricked API.
     *
     * @Route("/upload", name="upload", methods={"POST"})
     *
     * @param Request $request HTTP request containing the uploaded file and other parameters.
     *
     * @return JsonResponse HTTP response indicating the success or failure of the upload.
     *
     * @throws \Exception if an error occurs during the upload or API communication.
     */
    public function upload(Request $request): JsonResponse
    {
        $files = $request->files->get('fileData');
        if (empty($files)) {
            return new JsonResponse(['error' => 'No files uploaded'], 400);
        }

        $repositoryName = $request->get('repositoryName');
        $commitName = $request->get('commitName');
        $email = $request->get('email');

        // Validate required parameters
        if (!$repositoryName) {
            return new JsonResponse(['error' => 'repositoryName is required'], 400);
        } elseif (!$commitName) {
            return new JsonResponse(['error' => 'commitName is required'], 400);
        } elseif (!$email) {
            return new JsonResponse(['error' => 'email is required'], 400);
        }

        try {
            // Upload files to Debricked and store in the database
            $this->debrickedApiService->uploadFiles($files, $repositoryName, $commitName, $email);
            return new JsonResponse(['message' => 'Files uploaded successfully, scan started.'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
