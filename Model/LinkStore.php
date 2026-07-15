<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Model;

class LinkStore
{
    /** @var \SamJUK\FetchPriority\Api\LinkInterface[] $_data */
    private array $_data = [];

    /**
     * @param \SamJUK\FetchPriority\Api\LinkInterface $link
     * @return statics
     */
    public function add(\SamJUK\FetchPriority\Api\LinkInterface $link) : static
    {
        $key = hash('sha256', json_encode($link->getAttrs()));
        $this->_data[$key] = $link;
        return $this;
    }

    /**
     * @return \SamJUK\FetchPriority\Api\LinkInterface[]
     */
    public function get() : array
    {
        return array_values($this->_data);
    }
}
