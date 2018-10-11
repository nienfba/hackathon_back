<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PrivateGroupRepository")
 */
class PrivateGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PrivateGroupMember", mappedBy="privateGroup", orphanRemoval=true)
     */
    private $privateGroupMembers;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->privateGroupMembers = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|PrivateGroupMember[]
     */
    public function getPrivateGroupMembers(): Collection
    {
        return $this->privateGroupMembers;
    }

    public function addPrivateGroupMember(PrivateGroupMember $privateGroupMember): self
    {
        if (!$this->privateGroupMembers->contains($privateGroupMember)) {
            $this->privateGroupMembers[] = $privateGroupMember;
            $privateGroupMember->setPrivateGroup($this);
        }

        return $this;
    }

    public function removePrivateGroupMember(PrivateGroupMember $privateGroupMember): self
    {
        if ($this->privateGroupMembers->contains($privateGroupMember)) {
            $this->privateGroupMembers->removeElement($privateGroupMember);
            // set the owning side to null (unless already changed)
            if ($privateGroupMember->getPrivateGroup() === $this) {
                $privateGroupMember->setPrivateGroup(null);
            }
        }

        return $this;
    }
}
