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
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClosingType", inversedBy="closings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $closingType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobOffer", mappedBy="closing")
     */
    private $jobOffer;

    public function __construct()
    {
        $this->jobOffer = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): self
    {
        $this->jobOffer = $jobOffer;

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
}
