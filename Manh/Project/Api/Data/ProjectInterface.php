<?php

namespace Mageplus\Project\Api\Data;

interface ProjectInterface
{
    /**
     * String constants for property names
     */
    public const TABLE_NAME = 'mageplus_project';
    public const PROJECT_ID = "project_id";
    public const PROJECT_NAME = "project_name";
    public const STATUS = "status";
    public const STREET = "street";
    public const CITY = "city";
    public const COUNTRY_ID = "country_id";
    public const REGION = "region";
    public const REGION_ID = "region_id";
    public const SHORT_DESCRIPTION = "short_description";
    public const DESCRIPTION = "description";
    public const STORES = "stores";
    public const URL_KEY = "url_key";

    public const COMPLETION_YEAR = "completion_year";

    public const ADDITION_SKU = "addition_sku";

    public const REDIRECT_URL = "redirect_url";

    public const POSITION = "position";

    public const PRODUCT_IDS = 'product_ids';
    public const TAG_IDS = 'tag_ids';

    public const REQUIRE_FIELDS = [
        self::PROJECT_NAME,
        self::COUNTRY_ID,
    ];

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
     * Getter for ProjectName.
     *
     * @return string|null
     */
    public function getProjectName(): ?string;

    /**
     * Setter for ProjectName.
     *
     * @param string|null $projectName
     *
     * @return void
     */
    public function setProjectName(?string $projectName): void;

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
     * Getter for Street.
     *
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * Setter for Street.
     *
     * @param string|null $street
     *
     * @return void
     */
    public function setStreet(?string $street): void;

    /**
     * Getter for City.
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Setter for City.
     *
     * @param string|null $city
     *
     * @return void
     */
    public function setCity(?string $city): void;

    /**
     * Getter for CountryId.
     *
     * @return string|null
     */
    public function getCountryId(): ?string;

    /**
     * Setter for CountryId.
     *
     * @param string|null $countryId
     *
     * @return void
     */
    public function setCountryId(?string $countryId): void;

    /**
     * Getter for Region.
     *
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * Setter for Region.
     *
     * @param string|null $region
     *
     * @return void
     */
    public function setRegion(?string $region): void;

    /**
     * Getter for RegionId.
     *
     * @return int|null
     */
    public function getRegionId(): ?int;

    /**
     * Setter for RegionId.
     *
     * @param int|null $regionId
     *
     * @return void
     */
    public function setRegionId(?int $regionId): void;

    /**
     * Getter for ShortDescription.
     *
     * @return string|null
     */
    public function getShortDescription(): ?string;

    /**
     * Setter for ShortDescription.
     *
     * @param string|null $shortDescription
     *
     * @return void
     */
    public function setShortDescription(?string $shortDescription): void;

    /**
     * Getter for Description.
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Setter for Description.
     *
     * @param string|null $description
     *
     * @return void
     */
    public function setDescription(?string $description): void;

    /**
     * Getter for Stores.
     *
     * @return string|null
     */
    public function getStores(): ?string;

    /**
     * Setter for Stores.
     *
     * @param string|null $stores
     *
     * @return void
     */
    public function setStores(?string $stores): void;

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

    /**
     * Getter for CompletionYear.
     *
     * @return string|null
     */
    public function getCompletionYear(): ?string;

    /**
     * Setter for CompletionYear.
     *
     * @param string|null $completionYear
     *
     * @return void
     */
    public function setCompletionYear(?string $completionYear): void;

    /**
     * Getter for Addition SKU.
     *
     * @return string|null
     */
    public function getAdditionSku(): ?string;

    /**
     * Setter for Addition SKU.
     *
     * @param string|null $additionSku
     *
     * @return void
     */
    public function setAdditionSku(?string $additionSku): void;

    /**
     * Getter for RedirectUrl.
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string;

    /**
     * Setter for RedirectUrl.
     *
     * @param string|null $redirectUrl
     *
     * @return void
     */
    public function setRedirectUrl(?string $redirectUrl): void;

    /**
     * Getter for Position.
     *
     * @return int|null
     */
    public function getPosition(): ?int;

    /**
     * Setter for Position.
     *
     * @param int|null $position
     *
     * @return void
     */
    public function setPosition(?int $position): void;


    /**
     * @return int[]
     */
    public function getProductIds();

    /**
     * @param int[] $productIds
     * @return ProjectInterface
     */
    public function setProductIds(array $productIds = []);

    /**
     * @return int[]
     */
    public function getTagIds();

    /**
     * @param int[] $tagIds
     * @return ProjectInterface
     */
    public function setTagIds(array $tagIds = []);
}
