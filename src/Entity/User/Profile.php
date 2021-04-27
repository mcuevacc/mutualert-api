<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profile
 * @ORM\Entity
 * @ORM\Table(name="User_Profile")
 */
class Profile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Account", inversedBy="profile")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $idUser;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $apepat;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $apemat;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $nombres;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $avatar;

    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'apepat' => $this->apepat,
            'apemat' => $this->apemat,
            'nombres' => $this->nombres,
            'email' => $this->email,
            'avatar' => $this->avatar
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getFullName(): ?string
    {
        return $this->nombres.' '.$this->apepat.' '. $this->apemat;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApepat(): ?string
    {
        return $this->apepat;
    }

    public function setApepat(string $apepat): self
    {
        $this->apepat = $apepat;

        return $this;
    }

    public function getApemat(): ?string
    {
        return $this->apemat;
    }

    public function setApemat(?string $apemat): self
    {
        $this->apemat = $apemat;

        return $this;
    }

    public function getNombres(): ?string
    {
        return $this->nombres;
    }

    public function setNombres(string $nombres): self
    {
        $this->nombres = $nombres;

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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

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