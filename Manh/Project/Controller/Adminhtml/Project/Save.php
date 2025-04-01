<?php

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\Project\Api\Data\ProjectInterface;
use Mageplus\Project\Model\Backend\Project\Registry as ProjectRegistry;
use Mageplus\Project\Model\Project;
use Mageplus\Project\Model\Repository\ProjectRepository;

class Save extends AbstractProject
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * Save Constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ProjectRepository $projectRepository
     * @param ProjectRegistry $projectRegistry
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        Context           $context,
        ForwardFactory    $resultForwardFactory,
        RedirectFactory   $resultRedirectFactory,
        ProjectRepository $projectRepository,
        ProjectRegistry   $projectRegistry,
        RegionFactory     $regionFactory
    )
    {
        parent::__construct($context, $resultForwardFactory, $resultRedirectFactory, $projectRepository, $projectRegistry);
        $this->regionFactory = $regionFactory;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $projectId = (int)$this->getRequest()->getParam(ProjectInterface::PROJECT_ID);
        $returnToEdit = false;

        try {
            if ($projectId) {
                /** @var  Project $model */
                $model = $this->projectRepository->getById($projectId);
            } else {
                $model = $this->projectRepository->getNew();
            }

            $data = $this->getRequest()->getPostValue();
            if (isset($data['stores']) && !array_filter($data['stores'])) {
                $data['stores'] = ',0,';
            }
            if (isset($data['stores']) && is_array($data['stores'])) {
                $data['stores'] = ',' . implode(',', array_filter($data['stores'])) . ',';
            }
            if (!empty($data['region_id'])) {
                $region = $this->regionFactory->create()->load($data['region_id']);
                if (!empty($region) && !empty($region->getName())) {
                    $data['region'] = $region->getName();
                }
            }

            $model->addData($data);
            $model->setRegionId($model->getRegionId() ?: null);
            $model->setProjectId($this->getRequest()->getParam('project_id', null) ?: null);
            $this->fillProductData($model);
            $this->fillTagData($model);
            $this->projectRepository->save($model);
            $projectId = $model->getProjectId();
            $this->messageManager->addSuccessMessage(__('You have saved the project.'));
            $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This project no longer exists.'));
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $returnToEdit = true;
        }

        if ($returnToEdit && $projectId) {
            return $resultRedirect->setPath('*/*/edit', ['project_id' => $projectId]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param ProjectInterface $model
     * @return $this
     */
    private function fillProductData($model)
    {
        $productsData = $this->getRequest()->getPost('project_product_container', []);
        $productIds = [];
        foreach ($productsData as $recordData) {
            $productIds[] = $recordData['entity_id'];
        }
        $model->setProductIds($productIds);

        return $this;
    }

    private function fillTagData($model)
    {
        $tagsData = $this->getRequest()->getPost('project_tag_container', []);
        $tagIds = [];
        foreach ($tagsData as $recordData) {
            $tagIds[] = $recordData['tag_id'];
        }
        $model->setTagIds($tagIds);

        return $this;
    }
}
