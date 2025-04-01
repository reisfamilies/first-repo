<?php

namespace Mageplus\Project\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mageplus\Project\Model\ImageProcessor;

class Gallery extends AbstractDb
{
    public const TABLE_NAME = 'mageplus_project_gallery';

    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_project_gallery_resource_model';

    private $imageProcessor;

    public function __construct(
        Context        $context,
        ImageProcessor $imageProcessor,
                       $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->imageProcessor = $imageProcessor;
    }

    public function removeProjectGallery($projectId)
    {
        if (!empty($projectId)) {
            $this->getConnection()->delete($this->getMainTable(), ['project_id = ?' => $projectId]);
        }

        return $this;
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('mageplus_project_gallery', 'entity_id');
        $this->_useIsObjectNew = true;
    }

    protected function _afterSave(AbstractModel $object)
    {
        $data = $object->getData();

        if (isset($data['image_name']) && !empty($data['is_object_new'])) {
            $this->imageProcessor->processImage(
                $data['image_name'],
                ImageProcessor::GALLERY_IMAGE_TYPE,
                $object->getProjectId()
            );
        }
    }

    protected function _afterDelete(AbstractModel $object)
    {
        $data = $object->getData();

        if (isset($data['image_name'])) {
            $this->imageProcessor->setBasePaths(
                ImageProcessor::GALLERY_IMAGE_TYPE,
                $object->getProjectId()
            );
            $this->imageProcessor->deleteImage($data['image_name']);
        }
    }
}
