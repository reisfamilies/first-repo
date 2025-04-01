<?php

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Mageplus\Project\Model\Source\Project\Status;

class MassDisable extends MassActionAbstract
{
    /**
     * @param AbstractDb $collection
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function doAction(AbstractDb $collection)
    {
        $collectionSize = $collection->getSize();
        foreach ($collection as $project) {
            $project->setStatus(Status::STATUS_INACTIVE);
            $this->tagRepository->save($project);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deactivated.', $collectionSize));
    }
}
