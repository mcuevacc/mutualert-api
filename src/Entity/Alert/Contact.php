<?php

namespace App\Entity\Alert;

use App\Entity\User\Account;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 * @ORM\Entity
 * @ORM\Table(name="Alert_Contact", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="id_user_phone", columns={"id_user","phone"})
 * })
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\Account", inversedBy="contacts")
     */
    private $idUser;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $fullname;

    /**
     * @ORM\Column(type="string", length=20, unique=true))
     */
    private $phone;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\Account")
     * @ORM\JoinColumn(name="id_contact", referencedColumnName="id", nullable=true)
     */
    private $idContact;

    public function asArray($filtro=NULL): ?array
    {
        if($this->idContact){
            $fullname = $this->idContact->getProfile()->getFullName();
            $phone = $this->idContact->getUsername();
        }

        $response = [
            'id' => $this->id,
            'fullname' => $fullname ?? $this->fullname,
            'phone' => $phone ?? $this->phone,
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getIdUser(): ?Account
    {
        return $this->idUser;
    }

    public function setIdUser(?Account $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getIdContact(): ?Account
    {
        return $this->idContact;
    }

    public function setIdContact(?Account $idContact): self
    {
        $this->idContact = $idContact;

        return $this;
    }
}