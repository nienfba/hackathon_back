<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\NullLogger;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;


/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     attributes={"order"={"publicationDate": "DESC"}},
 * )
 * @ApiFilter(RangeFilter::class, properties={"latitude", "longitude"})
 * @ApiFilter(SearchFilter::class, properties={"member": "exact", "categories": "exact", "icon" : "exact", "title": "ipartial", "description": "ipartial"})
 * @ApiFilter(DateFilter::class, properties={"publicationDate"})
 * @ApiFilter(OrderFilter::class, properties={"id", "categories", "member", "publicationDate", "icon"}, arguments={"orderParameterName"="order"})
 * @ORM\Entity(repositoryClass="App\Repository\InfoRepository")
 */
class Info
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"read"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"read"})
     */
    private $longitude;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Groups({"read"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Media", fetch="EAGER", inversedBy="infos", cascade={"persist"}, orphanRemoval=false)
     */
    private $media;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @Groups({"read"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="infos", fetch="EAGER")
     */
    private $member;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="info")
     */
    private $messages;
    
    /**
     * @Groups({"read"})
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="infos", cascade={"persist"}, orphanRemoval=false)
     */
    private $categories;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    public function __construct()
    {
        // API PLATFORM GROUPS
        // https://api-platform.com/docs/core/serialization/
        
        $this->media = new ArrayCollection();
        $this->publicationDate = new \datetime();
        $this->endDate = (new \datetime())->add(new \DateInterval('P1Y'));
        $this->messages = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }


    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        if ($latitude==0){
            $this->latitude = rand(42.8 * pow(10, 3), 44 * pow(10, 3)) / pow(10, 3);
        }
        else{
            $this->latitude = $latitude;
        }

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        if ($longitude==0){
            $this->longitude = $this->longitude = rand(4 * pow(10, 3), 6 * pow(10, 3)) / pow(10, 3);
        }
        else
        {
            $this->longitude = $longitude;
        }

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

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->contains($medium)) {
            $this->media->removeElement($medium);
        }

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

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setInfo($this);
        }

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
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getInfo() === $this) {
                $message->setInfo(null);
            }
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }




}
