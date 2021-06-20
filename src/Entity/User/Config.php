<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use App\Service\Util\Constante;

/**
 * Config
 * @ORM\Entity
 * @ORM\Table(name="User_Config")
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Account", inversedBy="config")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     */
    private $idUser;

    /**
     * @ORM\Column(name="show_phone", type="boolean")
     */
    private $showPhone=FALSE;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $alertRadio = Constante::ALERT_RADIO;

    /**
     * @ORM\Column(name="alert_other", type="boolean")
     */
    private $alertOther=TRUE;

    /**
     * @ORM\Column(name="notify_other", type="boolean")
     */
    private $notifyOther=TRUE;

    /**
     * @ORM\Column(name="notify_self_contact", type="boolean")
     */
    private $notifySelfContact=TRUE;

    /**
     * @ORM\Column(name="notify_my_contact", type="boolean")
     */
    private $notifyMyContact=TRUE;

    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'showPhone' => $this->showPhone,
            'alertRadio' => $this->alertRadio,
            'alertOther' => $this->alertOther,
            'notifyOther' => $this->notifyOther,
            'notifySelfContact' => $this->notifySelfContact,
            'notifyMyContact' => $this->notifyMyContact,
        ];

        if($filtro)
            $response = array_intersect_key($response, array_flip($filtro));

        return $response;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlertRadio(): ?string
    {
        return $this->alertRadio;
    }

    public function setAlertRadio(?string $alertRadio): self
    {
        $this->alertRadio = $alertRadio;

        return $this;
    }

    public function getAlertOther(): ?bool
    {
        return $this->alertOther;
    }

    public function setAlertOther(bool $alertOther): self
    {
        $this->alertOther = $alertOther;

        return $this;
    }

    public function getNotifyOther(): ?bool
    {
        return $this->notifyOther;
    }

    public function setNotifyOther(bool $notifyOther): self
    {
        $this->notifyOther = $notifyOther;

        return $this;
    }

    public function getNotifySelfContact(): ?bool
    {
        return $this->notifySelfContact;
    }

    public function setNotifySelfContact(bool $notifySelfContact): self
    {
        $this->notifySelfContact = $notifySelfContact;

        return $this;
    }

    public function getNotifyMyContact(): ?bool
    {
        return $this->notifyMyContact;
    }

    public function setNotifyMyContact(bool $notifyMyContact): self
    {
        $this->notifyMyContact = $notifyMyContact;

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

    public function getShowPhone(): ?bool
    {
        return $this->showPhone;
    }

    public function setShowPhone(bool $showPhone): self
    {
        $this->showPhone = $showPhone;

        return $this;
    }
}