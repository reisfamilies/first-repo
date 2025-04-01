<?php

namespace Mageplus\Project\Model;

use Magento\Framework\Model\AbstractModel;
use Mageplus\Project\Api\Data\ProjectInterface;
use Mageplus\Project\Model\ResourceModel\Project as ResourceModel;

class Project extends AbstractModel implements ProjectInterface
{

    const CACHE_TAG = 'mageplus_project_model';
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_project_model';

    protected $_cacheTag = 'mageplus_project_model';

    /**
     * Getter for ProjectId.
     *
     * @return int|null
     */
    public function getProjectId(): ?int
    {
        return $this->getData(self::PROJECT_ID) === null ? null
            : (int)$this->getData(self::PROJECT_ID);
    }

    /**
     * Setter for ProjectId.
     *
     * @param int|null $projectId
     *
     * @return void
     */
    public function setProjectId(?int $projectId): void
    {
        $this->setData(self::PROJECT_ID, $projectId);
    }

    /**
     * Getter for ProjectName.
     *
     * @return string|null
     */
    public function getProjectName(): ?string
    {
        return $this->getData(self::PROJECT_NAME);
    }

    /**
     * Setter for ProjectName.
     *
     * @param string|null $projectName
     *
     * @return void
     */
    public function setProjectName(?string $projectName): void
    {
        $this->setData(self::PROJECT_NAME, $projectName);
    }

    /**
     * Getter for Status.
     *
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->getData(self::STATUS) === null ? null
            : (int)$this->getData(self::STATUS);
    }

    /**
     * Setter for Status.
     *
     * @param int|null $status
     *
     * @return void
     */
    public function setStatus(?int $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * Getter for Street.
     *
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->getData(self::STREET);
    }

    /**
     * Setter for Street.
     *
     * @param string|null $street
     *
     * @return void
     */
    public function setStreet(?string $street): void
    {
        $this->setData(self::STREET, $street);
    }

    /**
     * Getter for City.
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->getData(self::CITY);
    }

    /**
     * Setter for City.
     *
     * @param string|null $city
     *
     * @return void
     */
    public function setCity(?string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * Getter for CountryId.
     *
     * @return string|null
     */
    public function getCountryId(): ?string
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * Setter for CountryId.
     *
     * @param string|null $countryId
     *
     * @return void
     */
    public function setCountryId(?string $countryId): void
    {
        $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * Getter for Region.
     *
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->getData(self::REGION);
    }

    /**
     * Setter for Region.
     *
     * @param string|null $region
     *
     * @return void
     */
    public function setRegion(?string $region): void
    {
        $this->setData(self::REGION, $region);
    }

    /**
     * Getter for RegionId.
     *
     * @return int|null
     */
    public function getRegionId(): ?int
    {
        return $this->getData(self::REGION_ID) === null ? null
            : (int)$this->getData(self::REGION_ID);
    }

    /**
     * Setter for RegionId.
     *
     * @param int|null $regionId
     *
     * @return void
     */
    public function setRegionId(?int $regionId): void
    {
        $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * Getter for ShortDescription.
     *
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * Setter for ShortDescription.
     *
     * @param string|null $shortDescription
     *
     * @return void
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->setData(self::SHORT_DESCRIPTION, $shortDescription);
    }

    /**
     * Getter for Description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Setter for Description.
     *
     * @param string|null $description
     *
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Getter for Stores.
     *
     * @return string|null
     */
    public function getStores(): ?string
    {
        return $this->getData(self::STORES);
    }

    /**
     * Setter for Stores.
     *
     * @param string|null $stores
     *
     * @return void
     */
    public function setStores(?string $stores): void
    {
        $this->setData(self::STORES, $stores);
    }

    /**
     * Getter for UrlKey.
     *
     * @return string|null
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Setter for UrlKey.
     *
     * @param string|null $urlKey
     *
     * @return void
     */
    public function setUrlKey(?string $urlKey): void
    {
        $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * Getter for CompletionYear.
     *
     * @return string|null
     */
    public function getCompletionYear(): ?string
    {
        return $this->getData(self::COMPLETION_YEAR);
    }

    /**
     * Setter for CompletionYear.
     *
     * @param string|null $completionYear
     *
     * @return void
     */
    public function setCompletionYear(?string $completionYear): void
    {
        $this->setData(self::COMPLETION_YEAR, $completionYear);
    }

    /**
     * Getter for Addition SKU.
     *
     * @return string|null
     */
    public function getAdditionSku(): ?string
    {
        return $this->getData(self::ADDITION_SKU);
    }

    /**
     * Setter for Addition SKU.
     *
     * @param string|null $additionSku
     *
     * @return void
     */
    public function setAdditionSku(?string $additionSku): void
    {
        $this->setData(self::ADDITION_SKU, $additionSku);
    }

    /**
     * Getter for RedirectUrl.
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->getData(self::REDIRECT_URL);
    }

    /**
     * Setter for RedirectUrl.
     *
     * @param string|null $redirectUrl
     *
     * @return void
     */
    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->setData(self::REDIRECT_URL, $redirectUrl);
    }

    /**
     * Getter for Position.
     *
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Setter for Position.
     *
     * @param int|null $position
     *
     * @return void
     */
    public function setPosition(?int $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    /**
     * @return array
     */
    public function getProductIds(): array
    {
        if ($this->getData(ProjectInterface::PRODUCT_IDS) === null) {
            $productIds = $this->getResource()->getProjectProductIds($this);
            $this->setData(ProjectInterface::PRODUCT_IDS, $productIds);
            $this->setOrigData(ProjectInterface::PRODUCT_IDS, $productIds);
        }
        return $this->getData(ProjectInterface::PRODUCT_IDS);
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function setProductIds(array $productIds = [])
    {
        $this->setData(ProjectInterface::PRODUCT_IDS, $productIds);
        return $this;
    }

    /**
     * @return array
     */
    public function getTagIds(): array
    {
        if ($this->getData(ProjectInterface::TAG_IDS) === null) {
            $tagIds = $this->getResource()->getProjectTagIds($this);
            $this->setData(ProjectInterface::TAG_IDS, $tagIds);
            $this->setOrigData(ProjectInterface::TAG_IDS, $tagIds);
        }
        return $this->getData(ProjectInterface::TAG_IDS);
    }

    /**
     * @param array $tagIds
     * @return $this
     */
    public function setTagIds(array $tagIds = [])
    {
        $this->setData(ProjectInterface::TAG_IDS, $tagIds);
        return $this;
    }

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
    }
}
