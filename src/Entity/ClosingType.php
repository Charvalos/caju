<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClosingTypeRepository")
 */
class ClosingType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Closing", mappedBy="closingType")
     */
    private $closings;

    public function __construct()
    {
        $this->closings = new ArrayCollection();
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

    /**
     * @return Collection|Closing[]
     */
    public function getClosings(): Collection
    {
        return $this->closings;
    }

    public function addClosing(Closing $closing): self
    {
        if (!$this->closings->contains($closing)) {
            $this->closings[] = $closing;
            $closing->setClosingType($this);
        }

        return $this;
    }

    public function removeClosing(Closing $closing): self
    {
        if ($this->closings->contains($closing)) {
            $this->closings->removeElement($closing);
            // set the owning side to null (unless already changed)
            if ($closing->getClosingType() === $this) {
                $closing->setClosingType(null);
            }
        }

        return $this;
    }
}
