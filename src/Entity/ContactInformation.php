<?php

namespace App\Entity;

use App\Repository\ContactInformationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactInformationRepository::class)]
class ContactInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email cannot be empty.')]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discord = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $x = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(
        message: 'Please enter a valid URL.',
        protocols: ['http', 'https'],
        requireTld: true
    )]
    private ?string $xLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $barq = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(
        message: 'Please enter a valid URL.',
        protocols: ['http', 'https'],
        requireTld: true
    )]
    private ?string $barqLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $steam = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(
        message: 'Please enter a valid URL.',
        protocols: ['http', 'https'],
        requireTld: true
    )]
    private ?string $steamLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $battleNet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDiscord(): ?string
    {
        return $this->discord;
    }

    public function setDiscord(?string $discord): static
    {
        $this->discord = $discord;

        return $this;
    }

    public function getTelegram(): ?string
    {
        return $this->telegram;
    }

    public function setTelegram(?string $telegram): static
    {
        $this->telegram = $telegram;

        return $this;
    }

    public function getX(): ?string
    {
        return $this->x;
    }

    public function setX(?string $x): static
    {
        $this->x = $x;

        return $this;
    }

    public function getXLink(): ?string
    {
        return $this->xLink;
    }

    public function setXLink(?string $xLink): static
    {
        $this->xLink = $xLink;

        return $this;
    }

    public function getBarq(): ?string
    {
        return $this->barq;
    }

    public function setBarq(?string $barq): static
    {
        $this->barq = $barq;

        return $this;
    }

    public function getBarqLink(): ?string
    {
        return $this->barqLink;
    }

    public function setBarqLink(?string $barqLink): static
    {
        $this->barqLink = $barqLink;

        return $this;
    }

    public function getSteam(): ?string
    {
        return $this->steam;
    }

    public function setSteam(?string $steam): static
    {
        $this->steam = $steam;

        return $this;
    }

    public function getSteamLink(): ?string
    {
        return $this->steamLink;
    }

    public function setSteamLink(?string $steamLink): static
    {
        $this->steamLink = $steamLink;

        return $this;
    }

    public function getBattleNet(): ?string
    {
        return $this->battleNet;
    }

    public function setBattleNet(?string $battleNet): static
    {
        $this->battleNet = $battleNet;

        return $this;
    }

}
