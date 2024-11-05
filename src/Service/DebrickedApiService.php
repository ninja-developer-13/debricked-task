<?php
// src/Service/DebrickedApiService.php
namespace App\Service;

use App\Entity\DependencyFileUpload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\RuleEngine;
/**
 * Class DebrickedApiService
 *
 * This service provides methods for authenticating with and uploading files to the Debricked API.
 */
class DebrickedApiService
{
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;
    private string $apiUrl;
    private ?string $jwtToken = null;
    private ?\DateTime $tokenExpiration = null;
    private string $loginUrl;
    private string $loginUsername;
    private string $loginPassword;
    private string $projectDir;
    private string $params;
    private RuleEngine $ruleEngine;

    /**
     * DebrickedApiService constructor.
     *
     * @param HttpClientInterface $httpClient     HTTP client for making requests
     * @param EntityManagerInterface $entityManager Entity manager for interacting with the database
     * @param string $apiUrl                      Base URL of the Debricked API
     * @param string $loginUsername               Username for Debricked API authentication
     * @param string $loginPassword               Password for Debricked API authentication
     * @param string $loginUrl                    Login URL for Debricked API authentication
     * @param ParameterBagInterface $params       Parameter bag for retrieving application parameters
     */
    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        string $apiUrl,
        string $loginUsername,
        string $loginPassword,
        string $loginUrl,
        ParameterBagInterface $params,
        RuleEngine $ruleEngine
    ) {
        $this->projectDir = $params->get('kernel.project_dir');
        $this->loginUrl = $loginUrl;
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->apiUrl = $apiUrl;
        $this->loginUsername = $loginUsername;
        $this->ruleEngine = $ruleEngine;
        $this->loginPassword = $loginPassword;
    }

    /**
     * Retrieves a JWT token from the Debricked API.
     *
     * @return string The JWT token
     * @throws \Exception If unable to retrieve the token
     */
    private function getJwtToken(): string
    {
        //If all these conditions are met, the method returns $this->jwtToken, reusing the existing token instead of requesting a new one.
        if ($this->jwtToken && $this->tokenExpiration && $this->tokenExpiration > new \DateTime()) {
            return $this->jwtToken;
        }
        return $this->retrieveJwtToken();
    }

    /**
     * Authenticates with the Debricked API and retrieves a new JWT token.
     *
     * @return string The JWT token
     * @throws \Exception If authentication fails
     */
    private function retrieveJwtToken(): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->loginUrl, [
                'body' => [
                    '_username' => $this->loginUsername,
                    '_password' => $this->loginPassword,
                ],
            ]);

            $data = $response->toArray();
            $this->jwtToken = $data['token'] ?? '';
            //This method helps manage token expiration, preventing unauthorized access attempts with expired tokens.
            $this->tokenExpiration = (new \DateTime())->add(new \DateInterval('PT1H'));
            return $this->jwtToken;
        } catch (ClientExceptionInterface $e) {
            throw new \Exception('Error Retrieving JWT: ' . $e->getMessage());
        }
    }

    /**
     * Uploads a file to the Debricked API and returns the CI upload ID.
     *
     * @param UploadedFile $file           The file to upload
     * @param string $repositoryName       The repository name for the file
     * @param string $commitName           The commit name for the file
     * @param string $email                The email
     * @throws \Exception If the upload fails
     */
    public function uploadFiles(UploadedFile $file, string $repositoryName, string $commitName, string $email): void
    {
        $data = [];
        $targetDirectory = $this->projectDir . '/public/uploads';
        $fileName = uniqid() . '.' . $file->guessExtension();
        $file->move($targetDirectory, $fileName);
        $filePath = $targetDirectory . '/' . $fileName;

        $dependencyFileUpload = new DependencyFileUpload();
        $dependencyFileUpload->setFilename($file->getClientOriginalName());
        $dependencyFileUpload->setFilePath($filePath);
        $dependencyFileUpload->setRepositoryName($repositoryName);
        $dependencyFileUpload->setCommitName($commitName);
        $dependencyFileUpload->setUploadDate(new \DateTime());
        $dependencyFileUpload->setStatus('Upload is in progress');

        $this->entityManager->persist($dependencyFileUpload);

        $formDataFields = [
            'fileData' => DataPart::fromPath($filePath, $file->getClientOriginalName()),
            'fileRelativePath' => $file->getClientOriginalName(),
            'repositoryName' => $repositoryName,
            'commitName' => $commitName,
        ];

        $formData = new FormDataPart($formDataFields);
        $headers = $formData->getPreparedHeaders()->toArray();

        $response = $this->httpClient->request('POST', $this->apiUrl . '/uploads/dependencies/files', [
            'headers' => array_merge(['Authorization' => 'Bearer ' . $this->getJwtToken()], $headers),
            'body' => $formData->bodyToIterable(),
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            $scanResult = 'Upload fails for some reason';
            $dependencyFileUpload->setStatus($scanResult);
            $this->entityManager->flush();
            $this->ruleEngine->sendEmail($scanResult, $email);
            throw new \Exception('Failed to upload file: ' . $file->getClientOriginalName());
        }

        $data = $response->toArray();
        $ciUploadId = $data['ciUploadId'] ?? null;
        if ($ciUploadId) {
            $scanResult = 'Filed Uploaded';
            $dependencyFileUpload->setStatus($scanResult);
            $dependencyFileUpload->setCiUploadId($ciUploadId);
            $this->entityManager->flush();
            // Finalize the upload to initiate the scan
            $this->ruleEngine->sendEmail($scanResult, $email);
            $this->finalizeUpload($ciUploadId, $repositoryName, $commitName, $email);
        }
    }

    /**
     * Finalizes the file upload process on the Debricked API.
     *
     * @param string $ciUploadId       The CI upload ID of the file
     * @param string $repositoryName   The repository name
     * @param string $commitName       The commit name
     * @throws \Exception If finalization fails
     */
    public function finalizeUpload(string $ciUploadId, string $repositoryName, string $commitName, string $email): array
    {
        $response = $this->httpClient->request('POST', $this->apiUrl . '/finishes/dependencies/files/uploads', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getJwtToken(),
            ],
            'json' => [
                'ciUploadId' => $ciUploadId,
                'repositoryName' => $repositoryName,
                'commitName' => $commitName,
            ],
        ]);
        $data = $response->toArray();
        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $scanResult = "Scan started";
            // Handle the scan results and notify user
            $this->ruleEngine->sendEmail($scanResult, $email);
            return $data;
        } else {
            throw new \Exception("Error scanning file");
        }
    }
}
