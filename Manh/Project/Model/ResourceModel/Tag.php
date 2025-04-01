<?php

namespace Mageplus\Project\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mageplus\Project\Api\Data\TagInterface;

class Tag extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mageplus_tag_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('mageplus_tag', TagInterface::TAG_ID);
        $this->_useIsObjectNew = true;
    }
}
