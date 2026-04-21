<?php

namespace App\Entity;

use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeatureRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebsiteFeatureRepository::class)]
class WebsiteFeature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(enumType: FeaturesStatusEnum::class)]
    private ?FeaturesStatusEnum $status = null;

    #[ORM\Column(enumType: FeaturePriorityEnum::class)]
    private ?FeaturePriorityEnum $priority = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deadlineAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?FeaturesStatusEnum
    {
        return $this->status;
    }

    public function setStatus(FeaturesStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusString(): string
    {
        return $this->status->value;
    }

    public function getPriority(): ?FeaturePriorityEnum
    {
        return $this->priority;
    }

    public function setPriority(FeaturePriorityEnum $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriorityColor(): string
    {
        return $this->priority->priorityColor();
    }

    public function getPriorityString(): string
    {
        return $this->priority->value;
    }

    public function getDeadlineAt(): ?\DateTimeImmutable
    {
        return $this->deadlineAt;
    }

    public function setDeadlineAt(?\DateTimeImmutable $deadlineAt): static
    {
        $this->deadlineAt = $deadlineAt;

        return $this;
    }
}
