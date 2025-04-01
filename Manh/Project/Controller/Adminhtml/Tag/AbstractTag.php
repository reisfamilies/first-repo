<?php

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Mageplus\Project\Model\Backend\Tag\Registry as TagRegistry;
use Mageplus\Project\Model\Repository\TagRepository;

abstract class AbstractTag extends Action
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
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var TagRegistry
     */
    private $tagRegistry;

    /**
     * AbstractTag Constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param TagRepository $tagRepository
     * @param TagRegistry $tagRegistry
     */
    public function __construct(
        Context         $context,
        ForwardFactory  $resultForwardFactory,
        RedirectFactory $resultRedirectFactory,
        TagRepository   $tagRepository,
        TagRegistry     $tagRegistry
    )
    {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->tagRepository = $tagRepository;
        $this->tagRegistry = $tagRegistry;
    }

    public function getTagRegistry(): TagRegistry
    {
        return $this->tagRegistry;
    }

    /**
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mageplus_Project::tag_listing');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Tag'));

        return $resultPage;
    }
}
