<?php

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\Project\Api\Data\TagInterface;
use Mageplus\Project\Model\Tag;

class Save extends AbstractTag
{
    /**
     * @return ResultInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $tagId = (int)$this->getRequest()->getParam(TagInterface::TAG_ID);
        $returnToEdit = false;

        try {
            if ($tagId) {
                /** @var  Tag $model */
                $model = $this->tagRepository->getById($tagId);
            } else {
                $model = $this->tagRepository->getNew();
            }

            $data = $this->getRequest()->getPostValue();
            $model->addData($data);
            $this->tagRepository->save($model);
            $tagId = $model->getTagId();
            $this->messageManager->addSuccessMessage(__('You have saved the tag.'));
            $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This tag no longer exists.'));
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $returnToEdit = true;
        }

        if ($returnToEdit && $tagId) {
            return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
