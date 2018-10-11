<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 * )
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @UniqueEntity(fields="username", message="Ce pseudo existe déja !!")
 * @UniqueEntity(fields="email", message="Email déja inscrit")
 */
class Member implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min= 4, minMessage="Votre mot de passe doit faire minimum 4 caractéres")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Info", mappedBy="member")
     */
    private $infos;

    /**
    *  @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas tapé le même mot de passe")
    */
    public $confirm_password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cle;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="Author", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationDate;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    private $file;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $extraCss;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $extraJs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PrivateGroupMember", mappedBy="member", orphanRemoval=true)
     */
    private $privateGroups;

    public function __construct()
    {
       $this->infos = new ArrayCollection();
       $this->messages = new ArrayCollection();
       
       // by default ""
       $this->extraCss = "";
       $this->extraJs  = "";
       $this->privateGroups = new ArrayCollection();
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }
    
    /**
     * pratique pour faker...  
     */
    public function setPasswordHash(?string $password): self
    {
        $passwordHash   = password_hash($password, PASSWORD_BCRYPT);
        $this->password = $passwordHash;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    //IL FAUT RENVOYER UN TABLEAU...
    public function getRoles()
    {
        return  [ $this->role ];
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
        return $this;
    }

    // METHODES SUPPLEMENTAIRES POUR IMPLEMENTER UserInterface
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }
    
    public function eraseCredentials()
    {
            
    }
        
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized, array('allowed_classes' => false));
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
        if(!$this->infos->contains($info)) {
            $this->infos[] = $info;
            $info->setMember($this);
        }
        return $this;
    }

    public function removeInfo(Info $info): self
    {
        if($this->infos->contains($info)) {
            $this->infos->removeElement($info);
            // set the owning side to null (unless already changed)
            if($info->getMember() === $this) {
                $info->setMember(null);
            }
        }
        return $this;
    }

    public function getCle(): ?string
    {
        return $this->cle;
    }

    public function setCle(?string $cle): self
    {
        $this->cle = $cle;
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
            $message->setAuthor($this);
        }

        return $this;
    }
    
    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(?\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

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
    }

    public function getExtraCss(): ?string
    {
        return $this->extraCss;
    }

    public function setExtraCss(?string $extraCss): self
    {
        $this->extraCss = $extraCss;

        return $this;
    }

    public function getExtraJs(): ?string
    {
        return $this->extraJs;
    }

    public function setExtraJs(?string $extraJs): self
    {
        $this->extraJs = $extraJs;

        return $this;
    }

    /**
     * @return Collection|PrivateGroupMember[]
     */
    public function getPrivateGroups(): Collection
    {
        return $this->privateGroups;
    }

    public function addPrivateGroup(PrivateGroupMember $privateGroup): self
    {
        if (!$this->privateGroups->contains($privateGroup)) {
            $this->privateGroups[] = $privateGroup;
            $privateGroup->setMember($this);
        }

        return $this;
    }

    public function removePrivateGroup(PrivateGroupMember $privateGroup): self
    {
        if ($this->privateGroups->contains($privateGroup)) {
            $this->privateGroups->removeElement($privateGroup);
            // set the owning side to null (unless already changed)
            if ($privateGroup->getMember() === $this) {
                $privateGroup->setMember(null);
            }
        }

        return $this;
    }

}