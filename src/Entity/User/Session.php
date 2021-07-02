<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * Session
 * @ORM\Entity
 * @ORM\Table(name="User_Session")
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Account")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $idUser;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $fcm;

    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'fcm' => $this->fcm
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFcm(): ?string
    {
        return $this->fcm;
    }

    public function setFcm(?string $fcm): self
    {
        $this->fcm = $fcm;

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