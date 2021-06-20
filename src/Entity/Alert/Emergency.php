<?php

namespace App\Entity\Alert;

use App\Entity\User\Account;
use Doctrine\ORM\Mapping as ORM;
use App\Service\Util\Constante;

/**
 * Emergency
 * @ORM\Entity
 * @ORM\Table(name="Alert_Emergency")
 */
class Emergency
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\Account")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", nullable=false)
     */
    private $idUser;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive=TRUE;

    /**
    * @ORM\Column(type="datetime", nullable=false)
     */
    private $startedAt;

    /**
    * @ORM\Column(type="datetime", nullable=true)
     */
    private $finishedAt;

    /**
    * @ORM\Column(type="json")
    */
    private $aUserAlert=[];

    /**
    * @ORM\Column(type="json")
    */
    private $aLocation=[];

    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'id' => $this->id,
            'apepat' => $this->idUser->getProfile()->getApepat(),
            'apemat' => $this->idUser->getProfile()->getApemat(),
            'nombres' => $this->idUser->getProfile()->getNombres(),
            'avatar' => $this->idUser->getProfile()->getAvatar(),
            'phone' => $this->idUser->getConfig()->getShowPhone() ? $this->idUser->getUsername() : null,
            'isActive' => $this->isActive,
            'startedAt' => $this->startedAt,
            'finishedAt' => $this->finishedAt,
            'aUserAlert' => $this->aUserAlert,
            'aLocation' => $this->aLocation
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getAUserAlert(): ?array
    {
        return $this->aUserAlert;
    }

    public function setAUserAlert(array $aUserAlert): self
    {
        $this->aUserAlert = $aUserAlert;

        return $this;
    }

    public function getALocation(): ?array
    {
        return $this->aLocation;
    }

    public function setALocation(array $aLocation): self
    {
        $this->aLocation = $aLocation;

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