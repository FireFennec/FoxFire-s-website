<?php

namespace App\Model;

class ContactInformations
{
    public function __construct(
        private int $id,
        private int $phoneNumber,
        private string $email,
        private string $discord,
        private string $telegram,
        private string $x,
        private string $xAD,
        private string $barq,
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

    public function getPhoneNumber(): int
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(int $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getDiscord(): string
    {
        return $this->discord;
    }

    public function setDiscord(string $discord): void
    {
        $this->discord = $discord;
    }

    public function getTelegram(): string
    {
        return $this->telegram;
    }

    public function setTelegram(string $telegram): void
    {
        $this->telegram = $telegram;
    }

    public function getX(): string
    {
        return $this->x;
    }

    public function setX(string $x): void
    {
        $this->x = $x;
    }

    public function getXAD(): string
    {
        return $this->xAD;
    }

    public function setXAD(string $xAD): void
    {
        $this->xAD = $xAD;
    }

    public function getBarq(): string
    {
        return $this->barq;
    }

    public function setBarq(string $barq): void
    {
        $this->barq = $barq;
    }
}
