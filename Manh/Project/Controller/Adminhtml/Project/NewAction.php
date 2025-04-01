<?php

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Magento\Backend\Model\View\Result\Forward;

class NewAction extends AbstractProject
{
    public function execute()
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
