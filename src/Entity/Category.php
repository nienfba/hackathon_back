<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Info", mappedBy="categories",cascade={"persist"},orphanRemoval=false)
     */
    private $infos;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $urlName;

    public function __construct()
    {
        $this->infos = new ArrayCollection();
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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Info[]
     */
    public function getInfos(): Collection
    {
        return $this->infos;
    }

    public function addInfo(Info $info): self
    {
        if (!$this->infos->contains($info)) {
            $this->infos[] = $info;
            $info->addCategory($this);
        }

        return $this;
    }

    public function removeInfo(Info $info): self
    {
        if ($this->infos->contains($info)) {
            $this->infos->removeElement($info);
            $info->removeCategory($this);
        }

        return $this;
    }

    public function getUrlName(): ?string
    {
        return $this->urlName;
    }

    public function setUrlName(string $urlName): self
    {
        $this->urlName = $urlName;

        return $this;
    }
}
