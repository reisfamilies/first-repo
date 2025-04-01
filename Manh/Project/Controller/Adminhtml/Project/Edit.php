<?php

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\Project\Api\Data\ProjectInterface;

class Edit extends AbstractProject
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $projectId = (int)$this->getRequest()->getParam(ProjectInterface::PROJECT_ID);

        try {
            /**
             * @var ProjectInterface $model
             */
            if ($projectId) {
                $model = $this->projectRepository->getById($projectId);
            } else {
                $model = $this->projectRepository->getNew();
            }
            $this->getProjectRegistry()->set($model);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This project no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $projectName = $model->getProjectName() ?: '';

        $text = $projectName ? __('Edit "%1"', $projectName) : __('New Project');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);

        return $resultPage;
    }
}
