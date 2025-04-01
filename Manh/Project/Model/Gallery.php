<?php

namespace Mageplus\Project\Model;

use Magento\Framework\Model\AbstractModel;
use Mageplus\Project\Api\Data\GalleryInterface;
use Mageplus\Project\Model\ResourceModel\Gallery as ResourceModel;

class Gallery extends AbstractModel implements GalleryInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_project_gallery_model';

    /**
     * Getter for EntityId.
     *
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        return $this->getData(self::ENTITY_ID) === null ? null
            : (int)$this->getData(self::ENTITY_ID);
    }

    /**
     * Setter for EntityId.
     *
     * @param int|null $entityId
     *
     * @return void
     */
    public function setEntityId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
    }

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
     * Getter for ImageName.
     *
     * @return string|null
     */
    public function getImageName(): ?string
    {
        return $this->getData(self::IMAGE_NAME);
    }

    /**
     * Setter for ImageName.
     *
     * @param string|null $imageName
     *
     * @return void
     */
    public function setImageName(?string $imageName): void
    {
        $this->setData(self::IMAGE_NAME, $imageName);
    }

    /**
     * Getter for IsBase.
     *
     * @return bool|null
     */
    public function getIsBase(): ?bool
    {
        return $this->getData(self::IS_BASE) === null ? null
            : (bool)$this->getData(self::IS_BASE);
    }

    /**
     * Setter for IsBase.
     *
     * @param bool|null $isBase
     *
     * @return void
     */
    public function setIsBase(?bool $isBase): void
    {
        $this->setData(self::IS_BASE, $isBase);
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
