<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 */
class Email
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
    private $emailFrom;

    /**
     * @ORM\Column(type="array")
     */
    private $emailTo = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailSubject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $emailContent;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $emailType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailFrom(): ?string
    {
        return $this->emailFrom;
    }

    public function setEmailFrom(string $emailFrom): self
    {
        $this->emailFrom = $emailFrom;

        return $this;
    }

    public function getEmailTo(): ?array
    {
        return $this->emailTo;
    }

    public function setEmailTo(array $emailTo): self
    {
        $this->emailTo = $emailTo;

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(?string $emailSubject): self
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    public function getEmailContent(): ?string
    {
        return $this->emailContent;
    }

    public function setEmailContent(?string $emailContent): self
    {
        $this->emailContent = $emailContent;

        return $this;
    }

    public function getEmailType(): ?string
    {
        return $this->emailType;
    }

    public function setEmailType(?string $emailType): self
    {
        $this->emailType = $emailType;

        return $this;
    }
}
