<?php

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Mageplus\Project\Model\Backend\Project\Registry as ProjectRegistry;
use Mageplus\Project\Model\Repository\ProjectRepository;

abstract class AbstractProject extends Action
{
    public const ADMIN_RESOURCE = 'Mageplus_Project::project_management';

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @var ProjectRegistry
     */
    private $projectRegistry;

    public function __construct(
        Context           $context,
        ForwardFactory    $resultForwardFactory,
        RedirectFactory   $resultRedirectFactory,
        ProjectRepository $projectRepository,
        ProjectRegistry   $projectRegistry
    )
    {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->projectRepository = $projectRepository;
        $this->projectRegistry = $projectRegistry;
    }

    public function getProjectRegistry(): ProjectRegistry
    {
        return $this->projectRegistry;
    }

    /**
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mageplus_Project::project_listing');
        $resultPage->getConfig()->getTitle()->prepend(__('Projects'));

        return $resultPage;
    }
}
