<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * State
 * @ORM\Entity
 * @ORM\Table(name="User_State")
 */
class State
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Account", inversedBy="state")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $idUser;

    /**
     * @ORM\Column(type="point", nullable=true)
     *
     * @var App\Model\Object\Point
     */
    private $geoLocation;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $accuracyLocation;

    /**
     * @ORM\Column(name="in_alert", type="boolean")
     */
    private $inAlert=FALSE;

    /**
     * @ORM\Column(name="online", type="boolean")
     */
    private $online=FALSE;

    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'inAlert' => $this->inAlert
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGeoLocation()
    {
        return $this->geoLocation;
    }

    public function setGeoLocation($geoLocation): self
    {
        $this->geoLocation = $geoLocation;

        return $this;
    }

    public function getAccuracyLocation(): ?string
    {
        return $this->accuracyLocation;
    }

    public function setAccuracyLocation(?string $accuracyLocation): self
    {
        $this->accuracyLocation = $accuracyLocation;

        return $this;
    }

    public function getInAlert(): ?bool
    {
        return $this->inAlert;
    }

    public function setInAlert(bool $inAlert): self
    {
        $this->inAlert = $inAlert;

        return $this;
    }

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

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