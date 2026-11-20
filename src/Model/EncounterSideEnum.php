<?php

declare(strict_types=1);

namespace App\Model;

enum EncounterSideEnum: string
{
    case ALLY = 'ally';
    case ENEMY = 'enemy';
}
