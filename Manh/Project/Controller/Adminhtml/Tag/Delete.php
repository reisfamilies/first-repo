<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Project Account for Magento 2
 */

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Magento\Framework\Exception\CouldNotDeleteException;
use Mageplus\Project\Api\Data\TagInterface;

class Delete extends AbstractTag
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $tagId = $this->getRequest()->getParam(TagInterface::TAG_ID);
        if (!$tagId) {
            $this->messageManager->addErrorMessage(__('We can\'t find account to delete.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $this->tagRepository->deleteById($tagId);
            $this->messageManager->addSuccessMessage(__('Tag was successfully removed'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
