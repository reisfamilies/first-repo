<?php

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Magento\Backend\Model\View\Result\Forward;

class NewAction extends AbstractTag
{
    public function execute()
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
