<?php

namespace App\Entity\Alert;

use App\Entity\User\Account;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 * @ORM\Entity
 * @ORM\Table(name="Alert_Contact", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="id_user_alias", columns={"id_user","alias"}),
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
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
    private $idUser;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $alias;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $phone;

    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'id' => $this->id,
            'alias' => $this->alias,
            'phone' => $this->phone,
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

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
}