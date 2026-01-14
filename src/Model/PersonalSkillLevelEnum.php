<?php

namespace App\Model;

enum PersonalSkillLevelEnum: string
{
    case AMATEUR = 'Amateur (1/6)';
    case BEGGER = 'Begger (2/6)';
    case ADVANCED = 'Advanced (3/6)';
    case EXPERIENCED = 'Experienced (4/6)';
    case PROFESSIONAL = 'Professional (5/6)';
    case MASTER = 'Master (6/6)';
}
