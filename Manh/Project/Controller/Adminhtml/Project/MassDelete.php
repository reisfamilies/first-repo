<?php

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Magento\Framework\Data\Collection\AbstractDb;

class MassDelete extends MassActionAbstract
{
    protected function doAction(AbstractDb $collection)
    {
        $collectionSize = $collection->getSize();
        foreach ($collection as $project) {
            /**
             * @TODO be shure, that all customer of project will be blocked
             */
            $this->projectRepository->delete($project);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
    }
}
