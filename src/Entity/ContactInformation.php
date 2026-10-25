<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContactInformationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Stores contact details and social platform profiles.
 */
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

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Assert\Regex(
        pattern: '/^\+?[0-9][0-9 ()-]*$/',
        message: 'Please enter a valid phone number.'
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email cannot be empty.')]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $discord = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z][a-zA-Z0-9_]{4,31}$/',
        message: 'Please enter a valid Telegram username.'
    )]
    private ?string $telegram = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $x = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $xLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $barq = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $barqLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $steam = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $steamLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $battleNet = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $github = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $githubLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $spotify = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: 'Please enter a valid HTTPS URL.',
        protocols: ['https'],
        requireTld: true
    )]
    private ?string $spotifyLink = null;

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

    public function getGithub(): ?string
    {
        return $this->github;
    }

    public function setGithub(?string $github): static
    {
        $this->github = $github;

        return $this;
    }

    public function getGithubLink(): ?string
    {
        return $this->githubLink;
    }

    public function setGithubLink(?string $githubLink): static
    {
        $this->githubLink = $githubLink;

        return $this;
    }

    public function getSpotify(): ?string
    {
        return $this->spotify;
    }

    public function setSpotify(?string $spotify): static
    {
        $this->spotify = $spotify;

        return $this;
    }

    public function getSpotifyLink(): ?string
    {
        return $this->spotifyLink;
    }

    public function setSpotifyLink(?string $spotifyLink): static
    {
        $this->spotifyLink = $spotifyLink;

        return $this;
    }

    /**
     * Validates that social profile URLs belong to their expected platforms.
     *
     * Adds a violation to the corresponding property when the host is invalid.
     */
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
            'githubLink' => [
                'url' => $this->githubLink,
                'hosts' => [
                    'github.com',
                    'www.github.com',
                ],
            ],
            'spotifyLink' => [
                'url' => $this->spotifyLink,
                'hosts' => [
                    'open.spotify.com',
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
