<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Enum;

enum FetchPriority : string
{
    case Auto = 'auto';
    case Low = 'low';
    case High = 'high';
}
