<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Project Account for Magento 2
 */

namespace Mageplus\Project\Controller\Adminhtml\Project;

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
            $this->projectRepository->save($project);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deactivated.', $collectionSize));
    }
}
