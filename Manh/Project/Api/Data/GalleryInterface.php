<?php

namespace Mageplus\Project\Api\Data;

interface GalleryInterface
{
    /**
     * String constants for property names
     */
    public const TABLE_NAME = 'mageplus_project_gallery';
    public const ENTITY_ID = "entity_id";
    public const PROJECT_ID = "project_id";
    public const IMAGE_NAME = "image_name";
    public const IS_BASE = "is_base";

    /**
     * Getter for EntityId.
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Setter for EntityId.
     *
     * @param int|null $entityId
     *
     * @return void
     */
    public function setEntityId($entityId);

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

    /**
     * Getter for ImageName.
     *
     * @return string|null
     */
    public function getImageName(): ?string;

    /**
     * Setter for ImageName.
     *
     * @param string|null $imageName
     *
     * @return void
     */
    public function setImageName(?string $imageName): void;

    /**
     * Getter for IsBase.
     *
     * @return bool|null
     */
    public function getIsBase(): ?bool;

    /**
     * Setter for IsBase.
     *
     * @param bool|null $isBase
     *
     * @return void
     */
    public function setIsBase(?bool $isBase): void;
}
