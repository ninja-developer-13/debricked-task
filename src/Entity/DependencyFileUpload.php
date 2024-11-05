<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dependency_file_upload")
 */
/**
 *  Doctrine annotations like @ORM\Entity and @ORM\Table(name="dependency_file_upload") 
 *  indicate that this class is an entity mapped to a database table named dependency_file_upload.
 */
class DependencyFileUpload
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * The $id property is an auto-incrementing primary key.
     */
    private $id; // Primary key

    /**
     * @ORM\Column(type="string")
     */
    private $fileName;

    /**
     * @ORM\Column(type="string")
     */
    private $filePath;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $commitName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $repositoryUrl;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $repositoryName;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $ciUploadId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fileRelativePath;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $branchName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $defaultBranchName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $releaseName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $productName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $uploadDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    // Getters and Setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getCommitName(): ?string
    {
        return $this->commitName;
    }

    public function setCommitName(?string $commitName): self
    {
        $this->commitName = $commitName;
        return $this;
    }

    public function getRepositoryUrl(): ?string
    {
        return $this->repositoryUrl;
    }

    public function setRepositoryUrl(?string $repositoryUrl): self
    {
        $this->repositoryUrl = $repositoryUrl;
        return $this;
    }

    public function getRepositoryName(): ?string
    {
        return $this->repositoryName;
    }

    public function setRepositoryName(?string $repositoryName): self
    {
        $this->repositoryName = $repositoryName;
        return $this;
    }

    public function getCiUploadId(): ?string
    {
        return $this->ciUploadId;
    }

    public function setCiUploadId(?string $ciUploadId): self
    {
        $this->ciUploadId = $ciUploadId;
        return $this;
    }

    public function getFileRelativePath(): ?string
    {
        return $this->fileRelativePath;
    }

    public function setFileRelativePath(?string $fileRelativePath): self
    {
        $this->fileRelativePath = $fileRelativePath;
        return $this;
    }

    public function getBranchName(): ?string
    {
        return $this->branchName;
    }

    public function setBranchName(?string $branchName): self
    {
        $this->branchName = $branchName;
        return $this;
    }

    public function getDefaultBranchName(): ?string
    {
        return $this->defaultBranchName;
    }

    public function setDefaultBranchName(?string $defaultBranchName): self
    {
        $this->defaultBranchName = $defaultBranchName;
        return $this;
    }

    public function getReleaseName(): ?string
    {
        return $this->releaseName;
    }

    public function setReleaseName(?string $releaseName): self
    {
        $this->releaseName = $releaseName;
        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }

    public function getUploadDate(): ?\DateTimeInterface
    {
        return $this->uploadDate;
    }

    public function setUploadDate(?\DateTimeInterface $uploadDate): self
    {
        $this->uploadDate = $uploadDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }
}
