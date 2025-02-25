<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Enum\Preload;

enum AsType : string
{
    case Audio = 'audio';
    case Document = 'document';
    case Embed = 'embed';
    case Fetch = 'fetch';
    case Font = 'font';
    case Image = 'image';
    case Object = 'object';
    case Script = 'script';
    case Style = 'style';
    case Track = 'track';
    case Video = 'video';
    case Worker = 'worker';
}
