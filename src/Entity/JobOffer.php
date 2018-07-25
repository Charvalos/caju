<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JobOfferRepository")
 */
class JobOffer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $renewalDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Postulation", mappedBy="jobOffer")
     */
    private $postulations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="jobOffers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Document", mappedBy="jobOffers")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\File(
     *      mimeTypes={"application/pdf"},
     *      mimeTypesMessage="Seul les documents au format PDF sont acceptés"
     * )
     */
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Closing", inversedBy="jobOffer")
     */
    private $closing;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OfferType", inversedBy="jobOffers")
     */
    private $offerType;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", mappedBy="jobOffers")
     */
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="jobOffer")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="jobOffers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $city;

    public function __construct()
    {
        $this->postulations = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId()
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

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getRenewalDate(): ?\DateTimeInterface
    {
        return $this->renewalDate;
    }

    public function setRenewalDate(?\DateTimeInterface $renewalDate): self
    {
        $this->renewalDate = $renewalDate;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection|Postulation[]
     */
    public function getPostulations(): Collection
    {
        return $this->postulations;
    }

    public function addPostulation(Postulation $postulation): self
    {
        if (!$this->postulations->contains($postulation)) {
            $this->postulations[] = $postulation;
            $postulation->setJobOffer($this);
        }

        return $this;
    }

    public function removePostulation(Postulation $postulation): self
    {
        if ($this->postulations->contains($postulation)) {
            $this->postulations->removeElement($postulation);
            // set the owning side to null (unless already changed)
            if ($postulation->getJobOffer() === $this) {
                $postulation->setJobOffer(null);
            }
        }

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
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->addJobOffer($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            $document->removeJobOffer($this);
        }

        return $this;
    }

    public function getClosing(): ?Closing
    {
        return $this->closing;
    }

    public function setClosing(?Closing $closing): self
    {
        $this->closing = $closing;

        return $this;
    }

    public function getOfferType(): ?OfferType
    {
        return $this->offerType;
    }

    public function setOfferType(?OfferType $offerType): self
    {
        $this->offerType = $offerType;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addJobOffer($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $category->removeJobOffer($this);
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }
}
