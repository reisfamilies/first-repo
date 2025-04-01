<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Project Account for Magento 2
 */

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Magento\Framework\Exception\CouldNotDeleteException;
use Mageplus\Project\Api\Data\ProjectInterface;

class Delete extends AbstractProject
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $projectId = $this->getRequest()->getParam(ProjectInterface::PROJECT_ID);
        if (!$projectId) {
            $this->messageManager->addErrorMessage(__('We can\'t find account to delete.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $this->projectRepository->deleteById($projectId);
            $this->messageManager->addSuccessMessage(__('Project was successfully removed'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
