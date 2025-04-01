<?php

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Magento\Framework\Data\Collection\AbstractDb;

class MassDelete extends MassActionAbstract
{
    protected function doAction(AbstractDb $collection)
    {
        $collectionSize = $collection->getSize();
        foreach ($collection as $project) {
            $this->tagRepository->delete($project);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
    }
}
