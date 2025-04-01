<?php

namespace Mageplus\Project\Model\Backend\Tag;

use Mageplus\Project\Api\Data\TagInterface;

class Registry
{
    /**
     * @var TagInterface|null
     */
    private $tag;

    public function set(TagInterface $tag): void
    {
        $this->tag = $tag;
    }

    public function get(): TagInterface
    {
        return $this->tag;
    }
}
