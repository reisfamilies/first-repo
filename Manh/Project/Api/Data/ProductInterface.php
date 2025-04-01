<?php

namespace Mageplus\Project\Api\Data;

interface ProductInterface
{
    /**
     * String constants for property names
     */
    public const TABLE_NAME = 'mageplus_project_product';

    public const PRODUCT_ID = "product_id";
    public const PROJECT_ID = "project_id";

    /**
     * Getter for ProductId.
     *
     * @return int|null
     */
    public function getProductId(): ?int;

    /**
     * Setter for ProductId.
     *
     * @param int|null $productId
     *
     * @return void
     */
    public function setProductId(?int $productId): void;

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
