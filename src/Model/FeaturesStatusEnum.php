<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Defines the available development statuses for website features.
 */
enum FeaturesStatusEnum: string
{
    case NEW_FEATURE = 'New feature';
    case UPDATE_FEATURE = 'Update feature';
    case COMPLETED_FEATURE = 'Completed feature';
}
