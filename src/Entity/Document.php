<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
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
     * @ORM\Column(type="string", length=4)
     */
    private $extension;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="documents")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Postulation", inversedBy="documents")
     */
    private $Postulation;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\JobOffer", inversedBy="documents")
     */
    private $jobOffer;

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

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

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

    /**
     * @return Collection|Postulation[]
     */
    public function getPostulation(): Collection
    {
        return $this->Postulation;
    }

    public function addPostulation(Postulation $postulation): self
    {
        if (!$this->Postulation->contains($postulation)) {
            $this->Postulation[] = $postulation;
        }

        return $this;
    }

    public function removePostulation(Postulation $postulation): self
    {
        if ($this->Postulation->contains($postulation)) {
            $this->Postulation->removeElement($postulation);
        }

        return $this;
    }

    /**
     * @return Collection|JobOffer[]
     */
    public function getJobOffer(): Collection
    {
        return $this->jobOffer;
    }

    public function addJobOffer(JobOffer $jobOffer): self
    {
        if (!$this->jobOffer->contains($jobOffer)) {
            $this->jobOffer[] = $jobOffer;
        }

        return $this;
    }

    public function removeJobOffer(JobOffer $jobOffer): self
    {
        if ($this->jobOffer->contains($jobOffer)) {
            $this->jobOffer->removeElement($jobOffer);
        }

        return $this;
    }
}
