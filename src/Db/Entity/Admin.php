<?php

namespace Phntm\Lib\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "admins")]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    public string $username;

    #[ORM\Column(type: "string")]
    public string $password;

    #[ORM\Column(type: "string")]
    public string $encryptionKey;

    public function __construct(
        string $username,
        string $password,
        string $encryptionKey
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->encryptionKey = $encryptionKey;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
