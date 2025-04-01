<?php

namespace Mageplus\Project\Api\Data;

interface TagInterface
{
    /**
     * String constants for property names
     */
    public const TABLE_NAME = 'mageplus_tag';
    public const TAG_ID = "tag_id";
    public const TAG_NAME = "tag_name";
    public const STATUS = "status";
    public const URL_KEY = "url_key";

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
     * Getter for TagName.
     *
     * @return string|null
     */
    public function getTagName(): ?string;

    /**
     * Setter for TagName.
     *
     * @param string|null $tagName
     *
     * @return void
     */
    public function setTagName(?string $tagName): void;

    /**
     * Getter for Status.
     *
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * Setter for Status.
     *
     * @param int|null $status
     *
     * @return void
     */
    public function setStatus(?int $status): void;

    /**
     * Getter for UrlKey.
     *
     * @return string|null
     */
    public function getUrlKey(): ?string;

    /**
     * Setter for UrlKey.
     *
     * @param string|null $urlKey
     *
     * @return void
     */
    public function setUrlKey(?string $urlKey): void;
}
