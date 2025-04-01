<?php

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\Project\Api\Data\TagInterface;

class Edit extends AbstractTag
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $tagId = (int)$this->getRequest()->getParam(TagInterface::TAG_ID);

        try {
            /**
             * @var TagInterface $model
             */
            if ($tagId) {
                $model = $this->tagRepository->getById($tagId);
            } else {
                $model = $this->tagRepository->getNew();
            }
            $this->getTagRegistry()->set($model);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This tag no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $tagName = $model->getTagName() ?: '';

        $text = $tagName ? __('Edit "%1"', $tagName) : __('New Tag');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);

        return $resultPage;
    }
}
