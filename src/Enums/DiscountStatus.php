<?php

namespace Coupone\DiscountManager\Enums;

enum DiscountStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case EXPIRED = 'expired';
    case DEPLETED = 'depleted';
    case SCHEDULED = 'scheduled';
    case PAUSED = 'paused';
    case ARCHIVED = 'archived';
} 