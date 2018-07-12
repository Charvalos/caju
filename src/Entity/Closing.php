<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClosingRepository")
 */
class Closing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $closingDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobOffer", mappedBy="closing")
     */
    private $jobOffer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClosingType", inversedBy="closings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $closingType;

    public function __construct()
    {
        $this->jobOffer = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getClosingDate(): ?\DateTimeInterface
    {
        return $this->closingDate;
    }

    public function setClosingDate(\DateTimeInterface $closingDate): self
    {
        $this->closingDate = $closingDate;

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
            $jobOffer->setClosing($this);
        }

        return $this;
    }

    public function removeJobOffer(JobOffer $jobOffer): self
    {
        if ($this->jobOffer->contains($jobOffer)) {
            $this->jobOffer->removeElement($jobOffer);
            // set the owning side to null (unless already changed)
            if ($jobOffer->getClosing() === $this) {
                $jobOffer->setClosing(null);
            }
        }

        return $this;
    }

    public function getClosingType(): ?ClosingType
    {
        return $this->closingType;
    }

    public function setClosingType(?ClosingType $closingType): self
    {
        $this->closingType = $closingType;

        return $this;
    }
}
