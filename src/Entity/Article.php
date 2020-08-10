<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $mainPhoto;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateBeginning;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"title"})
     */
    private $slug;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMainPhoto(): ?string
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(?string $mainPhoto): self
    {
        $this->mainPhoto = $mainPhoto;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getDateBeginning(): ?\DateTimeInterface
    {
        return $this->dateBeginning;
    }

    public function setDateBeginning(\DateTimeInterface $dateBeginning): self
    {
        $this->dateBeginning = $dateBeginning;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }
}
