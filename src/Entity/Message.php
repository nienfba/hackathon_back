<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Info", inversedBy="messages")
     */
    private $info;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Author;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    private $file;

    public function __construct()
    {
        $this->publicationDate = new \datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getInfo(): ?Info
    {
        return $this->info;
    }

    public function setInfo(?Info $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getAuthor(): ?Member
    {
        return $this->Author;
    }

    public function setAuthor(?Member $Author): self
    {
        $this->Author = $Author;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

}
