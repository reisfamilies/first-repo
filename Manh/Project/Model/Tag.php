<?php

namespace Mageplus\Project\Model;

use Magento\Framework\Model\AbstractModel;
use Mageplus\Project\Api\Data\TagInterface;
use Mageplus\Project\Model\ResourceModel\Tag as ResourceModel;

class Tag extends AbstractModel implements TagInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_tag_model';

    protected $_cacheTag = 'mageplus_tag_model';

    /**
     * Getter for TagId.
     *
     * @return int|null
     */
    public function getTagId(): ?int
    {
        return $this->getData(self::TAG_ID) === null ? null
            : (int)$this->getData(self::TAG_ID);
    }

    /**
     * Setter for TagId.
     *
     * @param int|null $tagId
     *
     * @return void
     */
    public function setTagId(?int $tagId): void
    {
        $this->setData(self::TAG_ID, $tagId);
    }

    /**
     * Getter for TagName.
     *
     * @return string|null
     */
    public function getTagName(): ?string
    {
        return $this->getData(self::TAG_NAME);
    }

    /**
     * Setter for TagName.
     *
     * @param string|null $tagName
     *
     * @return void
     */
    public function setTagName(?string $tagName): void
    {
        $this->setData(self::TAG_NAME, $tagName);
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
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
