<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "medias", fetch: "EAGER")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Album::class, fetch: "EAGER", cascade: ['remove'])]
    private ?Album $album = null;

    #[ORM\Column]
    private string $path;

    #[ORM\Column]
    private string $title;

    private ?UploadedFile $file = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): void
    {
        $this->album = $album;
    }

    /**
     * Remove the associated file from the filesystem when the entity is deleted
     *
     * @return void
     */
    #[ORM\PreRemove]
    public function removeFileOnDelete(): void
    {
        if (!$this->path) {
            return;
        }

        $projectDir = \dirname(__DIR__, 2);
        $fullPath   = $projectDir . '/public/' . ltrim($this->path, '/');

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
