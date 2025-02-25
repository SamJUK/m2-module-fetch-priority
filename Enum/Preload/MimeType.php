<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Enum\Preload;

enum MimeType : string
{
    case ImagePNG = 'image/png';
    case ImageJPG = 'image/jpg';
    case ImageJPEG = 'image/jpeg';
    case ImageGIF = 'image/gif';
    case ImageBMP = 'image/bmp';
    case ImageSVG = 'image/svg+xml';
    case AudioMP3 = 'audio/mpeg';
    case VideoMOV = 'video/quicktime';
    case Application_PDF = 'application/pdf';
    case TextCSS = 'text/css';
    case TextJS = 'text/javascript';
    case TextJSON = 'text/json';
}
