<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Api;

interface LinkInterface
{
    /**
     * @return array<string, string>
     */
    public function getAttrs();
}
