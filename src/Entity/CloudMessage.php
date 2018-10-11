<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CloudMessageRepository")
 */
class CloudMessage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idMember;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $log;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSubscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;


    public function __construct () 
    {
        $this->dateSubscription = new \DateTime;    
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdMember(): ?int
    {
        return $this->idMember;
    }

    public function setIdMember(?int $idMember): self
    {
        $this->idMember = $idMember;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function getDateSubscription(): ?\DateTimeInterface
    {
        return $this->dateSubscription;
    }

    public function setDateSubscription(?\DateTimeInterface $dateSubscription): self
    {
        $this->dateSubscription = $dateSubscription;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
