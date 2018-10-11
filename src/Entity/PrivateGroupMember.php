<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PrivateGroupMemberRepository")
 */
class PrivateGroupMember
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PrivateGroup", inversedBy="privateGroupMembers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $privateGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="privateGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $member;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $joinStatus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrivateGroup(): ?PrivateGroup
    {
        return $this->privateGroup;
    }

    public function setPrivateGroup(?PrivateGroup $privateGroup): self
    {
        $this->privateGroup = $privateGroup;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getJoinStatus(): ?string
    {
        return $this->joinStatus;
    }

    public function setJoinStatus(?string $joinStatus): self
    {
        $this->joinStatus = $joinStatus;

        return $this;
    }
}
