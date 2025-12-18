<?php

namespace App\Model;

enum FeaturesStatusEnum: string
{
    case NEW_FEATURE = 'New feature';
    case UPDATE_FEATURE = 'Update feature';
    case COMPLETED_FEATURE = 'Completed feature';
}
