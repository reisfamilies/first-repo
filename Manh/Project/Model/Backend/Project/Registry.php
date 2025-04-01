<?php

namespace Mageplus\Project\Model\Backend\Project;

use Mageplus\Project\Api\Data\ProjectInterface;

class Registry
{
    /**
     * @var ProjectInterface|null
     */
    private $project;

    public function set(ProjectInterface $project): void
    {
        $this->project = $project;
    }

    public function get(): ProjectInterface
    {
        return $this->project;
    }
}
