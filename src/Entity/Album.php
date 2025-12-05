<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\Column]
    private string $name;

    #[ORM\OneToMany(
        targetEntity: Media::class,
        mappedBy: 'album',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $media;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
