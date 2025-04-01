<?php

namespace Mageplus\Project\Api\Data;

interface ProjectTagInterface
{
    /**
     * String constants for property names
     */
    public const TABLE_NAME = "mageplus_project_tag";
    public const TAG_ID = "tag_id";
    public const PROJECT_ID = "project_id";

    /**
     * Getter for TagId.
     *
     * @return int|null
     */
    public function getTagId(): ?int;

    /**
     * Setter for TagId.
     *
     * @param int|null $tagId
     *
     * @return void
     */
    public function setTagId(?int $tagId): void;

    /**
     * Getter for ProjectId.
     *
     * @return int|null
     */
    public function getProjectId(): ?int;

    /**
     * Setter for ProjectId.
     *
     * @param int|null $projectId
     *
     * @return void
     */
    public function setProjectId(?int $projectId): void;
}
