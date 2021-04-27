<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * Code
 * @ORM\Entity
 * @ORM\Table(name="User_Code")
 */
class Code
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     */
    private $f;

    public function asArray($filtro=NULL): ?array
    {
        $response = [
            'id' => $this->id,
            'username' => $this->username,
            'code' => $this->code,
            'f' => $this->f
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getF(): ?\DateTimeInterface
    {
        return $this->f;
    }

    public function setF(\DateTimeInterface $f): self
    {
        $this->f = $f;

        return $this;
    }
}