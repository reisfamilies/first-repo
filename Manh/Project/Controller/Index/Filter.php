<?php

namespace Mageplus\Project\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Mageplus\Project\Block\Project;

class Filter extends Action
{
    /**
     * Default customer account page
     *
     * @return Page|(Page&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();

        /** @var Project $block */
        $block = $this->_view->getLayout()->getBlock('mp_filter');
        $block->setProjectCollectionPrepared(false);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Project List'));

        return $resultPage;
    }
}
