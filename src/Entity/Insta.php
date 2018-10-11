<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ApiFilter(SearchFilter::class, properties={"insta_type": "exact", "id": "exact", "user_username": "exact", "insta_id": "exact", "tags":"partial"})
 * @ApiFilter(RangeFilter::class, properties={"latitude", "longitude"})
 * @ApiFilter(ExistsFilter::class, properties={"insta_id", "responseJson"})
 * @ORM\Entity(repositoryClass="App\Repository\InstaRepository")
 */
class Insta
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $caption;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $user_username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $insta_type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $insta_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $low_resolution;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $standard_resolution;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $thumbnail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $created_time;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $responseJson;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $likes;


    public function __construct ()
    {
        $this->created_time = date("Y-m-d H:i:s");    
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getUserUsername(): ?string
    {
        return $this->user_username;
    }

    public function setUserUsername(?string $user_username): self
    {
        $this->user_username = $user_username;

        return $this;
    }

    public function getInstaType(): ?string
    {
        return $this->insta_type;
    }

    public function setInstaType(?string $insta_type): self
    {
        $this->insta_type = $insta_type;

        return $this;
    }

    public function getInstaId(): ?string
    {
        return $this->insta_id;
    }

    public function setInstaId(?string $insta_id): self
    {
        $this->insta_id = $insta_id;

        return $this;
    }

    public function getLowResolution(): ?string
    {
        return $this->low_resolution;
    }

    public function setLowResolution(?string $low_resolution): self
    {
        $this->low_resolution = $low_resolution;

        return $this;
    }

    public function getStandardResolution(): ?string
    {
        return $this->standard_resolution;
    }

    public function setStandardResolution(?string $standard_resolution): self
    {
        $this->standard_resolution = $standard_resolution;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getCreatedTime(): ?string
    {
        return $this->created_time;
    }

    public function setCreatedTime(?string $created_time): self
    {
        $this->created_time = $created_time;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getResponseJson(): ?string
    {
        return $this->responseJson;
    }

    public function setResponseJson(?string $responseJson): self
    {
        $this->responseJson = $responseJson;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(?int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

}
