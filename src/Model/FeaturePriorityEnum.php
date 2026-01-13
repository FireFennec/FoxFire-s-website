<?php

namespace App\Model;

enum FeaturePriorityEnum: string
{
    case NICE_TO_HAVE = 'Nice to have';
    case ANYTIME = 'Anytime';
    case SOON = 'Soon';
    case HIGH_PRIORITY = 'High priority';
    case ASAP = 'ASAP';

    public function priorityColor(): string
    {
        return match ($this) {
            self::NICE_TO_HAVE  => 'text-slate-500 hover:text-slate-400',
            self::ANYTIME => 'text-gray-200 hover:text-gray-100',
            self::SOON => 'text-yellow-500 hover:text-yellow-400',
            self::HIGH_PRIORITY => 'text-orange-500 hover:text-orange-400',
            self::ASAP => 'text-red-500 hover:text-red-400',
        };
    }
}
