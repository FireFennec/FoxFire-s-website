<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContactInformationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ContactInformationRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'This email address is already in use.'
)]
class ContactInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Optional phone number.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Assert\Regex(
        pattern: '/^\+?[0-9][0-9 ()-]*$/',
        message: 'Please enter a valid phone number.'
    )]
    private ?string $phoneNumber = null;

    // Required and unique email address.
    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email cannot be empty.')]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    // Optional Discord username.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $discord = null;

    // Optional Telegram username.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z][a-zA-Z0-9_]{4,31}$/',
        message: 'Please enter a valid Telegram username.'
    )]
    private ?string $telegram = null;

    // X profile display name.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $x = null;

    // X profile URL.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $xLink = null;

    // Barq profile display name.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $barq = null;

    // Barq profile URL.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $barqLink = null;

    // Steam profile display name.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $steamLink = null;

    // Steam profile display name.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $steam = null;

    // Optional Battle.net BattleTag.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
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

    // Validate that profile URLs belong to the expected platforms.
    #[Assert\Callback]
    public function validatePlatformLinks(
        ExecutionContextInterface $context
    ): void {
        $platforms = [
            'xLink' => [
                'url' => $this->xLink,
                'hosts' => [
                    'x.com',
                    'www.x.com',
                    'twitter.com',
                    'www.twitter.com',
                ],
            ],
            'barqLink' => [
                'url' => $this->barqLink,
                'hosts' => [
                    'barq.app',
                    'www.barq.app',
                ],
            ],
            'steamLink' => [
                'url' => $this->steamLink,
                'hosts' => [
                    'steamcommunity.com',
                    'www.steamcommunity.com',
                ],
            ],
        ];

        foreach ($platforms as $property => $platform) {
            if ($platform['url'] === null || $platform['url'] === '') {
                continue;
            }

            $host = parse_url($platform['url'], PHP_URL_HOST);

            if (
                !is_string($host)
                || !in_array(strtolower($host), $platform['hosts'], true)
            ) {
                $context
                    ->buildViolation(
                        'Please enter a URL for the correct platform.'
                    )
                    ->atPath($property)
                    ->addViolation();
            }
        }
    }
}
