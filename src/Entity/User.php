<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(
    fields: ['username'],
    message: 'This username is already taken.'
)]
#[UniqueEntity(
    fields: ['email'],
    message: 'This email address is already registered.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank(message: 'Username cannot be empty.')]
    #[Assert\Length(
        min: 5,
        max: 30,
        minMessage: 'Username must contain at least 5 characters.',
        maxMessage: 'Username cannot exceed 30 characters.'
    )]
    private ?string $username = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Email cannot be empty.')]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    private Collection $assignedRoles;

    public function __construct()
    {
        $this->assignedRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        if ($this->username === null || $this->username === '') {
            throw new \LogicException('User identifier cannot be empty.');
        }

        return $this->username;
    }

    /**
     * Returns the user's security roles.
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = [];

        foreach ($this->assignedRoles as $assignedRole) {
            $roleName = $assignedRole->getName();

            if ($roleName !== null) {
                $roles[] = $roleName;
            }
        }

        // Every authenticated user receives the basic role.
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes
     * by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getAssignedRoles(): Collection
    {
        return $this->assignedRoles;
    }

    public function addAssignedRole(Role $assignedRole): static
    {
        if (!$this->assignedRoles->contains($assignedRole)) {
            $this->assignedRoles->add($assignedRole);
        }

        return $this;
    }

    public function removeAssignedRole(Role $assignedRole): static
    {
        $this->assignedRoles->removeElement($assignedRole);

        return $this;
    }
}
