<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 * @Vich\Uploadable()
 */
class Document
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
    private $name;

    /**
     * @Vich\UploadableField(mapping="user_document", fileNameProperty="name", originalName="originalName")
     * @Assert\File(
     *     mimeTypes={"application/pdf"},
     *     mimeTypesMessage="Seul les documents au format PDF sont autorisés"
     * )
     * @Assert\NotBlank(
     *     message="Veuillez sélectionner un fichier"
     * )
     * @var File
     */
    private $file;

    /**
     * @ORM\Column(type="string")
     */
    private $mimeType;

    /**
     * @ORM\Column(type="string")
     */
    private $originalName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="documents")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updateAt;

    public function __construct()
    {
        $this->Postulation = new ArrayCollection();
        $this->jobOffer = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $name): self
    {
        $this->originalName = $name;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if(null !== $file)
            $this->updateAt = new \DateTimeImmutable();
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setIsPublic(bool $allow): self
    {
        $this->isPublic = $allow;

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }
}
