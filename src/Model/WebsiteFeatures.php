<?php

namespace App\Model;

class WebsiteFeatures
{
    public function __construct(
        private int $id,
        private string $title,
        private string $description,
        private FeaturesStatusEnum $type,
        private TaskPriorityEnum $priority,
    ){
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getType(): FeaturesStatusEnum
    {
        return $this->type;
    }

    public function setType(FeaturesStatusEnum $type): void
    {
        $this->type = $type;
    }

    public function getTypeString(): string
    {
        return $this->type->value;
    }

    public function getPriority(): TaskPriorityEnum
    {
        return $this->priority;
    }

    public function setPriority(TaskPriorityEnum $priority): void
    {
        $this->priority = $priority;
    }

    public function getPriorityString(): string
    {
        return $this->priority->value;
    }

    public function getPriorityColor(): string
    {
        return $this->priority->priorityColor();
    }
}
