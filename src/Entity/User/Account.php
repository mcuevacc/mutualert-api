<?php

namespace App\Entity\User;

use App\Entity\Alert\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Account
 * @ORM\Entity
 * @ORM\Table(name="User_Account")
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true))
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $password;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive=TRUE;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Profile", mappedBy="idUser")
     */
    private $profile;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\State", mappedBy="idUser")
     */
    private $state;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Config", mappedBy="idUser")
     */
    private $config;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Alert\Contact", mappedBy="idUser")
     */
    private $contacts;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }
    
    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'id' => $this->id,
            'profile' => $this->profile->asArray(['apepat','apemat','nombres','email'])
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        // unset the owning side of the relation if necessary
        if ($profile === null && $this->profile !== null) {
            $this->profile->setIdUser(null);
        }

        // set the owning side of the relation if necessary
        if ($profile !== null && $profile->getIdUser() !== $this) {
            $profile->setIdUser($this);
        }

        $this->profile = $profile;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        // unset the owning side of the relation if necessary
        if ($state === null && $this->state !== null) {
            $this->state->setIdUser(null);
        }

        // set the owning side of the relation if necessary
        if ($state !== null && $state->getIdUser() !== $this) {
            $state->setIdUser($this);
        }

        $this->state = $state;

        return $this;
    }

    public function getConfig(): ?Config
    {
        return $this->config;
    }

    public function setConfig(?Config $config): self
    {
        // unset the owning side of the relation if necessary
        if ($config === null && $this->config !== null) {
            $this->config->setIdUser(null);
        }

        // set the owning side of the relation if necessary
        if ($config !== null && $config->getIdUser() !== $this) {
            $config->setIdUser($this);
        }

        $this->config = $config;

        return $this;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setIdUser($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getIdUser() === $this) {
                $contact->setIdUser(null);
            }
        }

        return $this;
    }
}