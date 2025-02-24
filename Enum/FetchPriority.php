<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Enum;

enum FetchPriority : string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
