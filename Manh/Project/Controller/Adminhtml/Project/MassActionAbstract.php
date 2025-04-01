<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Project Account for Magento 2
 */

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Mageplus\Project\Api\ProjectRepositoryInterface;
use Mageplus\Project\Model\ResourceModel\Project\CollectionFactory;

abstract class MassActionAbstract extends Action
{
    public const ADMIN_RESOURCE = 'Mageplus_Project::project_management';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProjectRepositoryInterface
     */
    protected $projectRepository;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * MassActionAbstract Constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param ProjectRepositoryInterface $projectRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context                    $context,
        Filter                     $filter,
        ProjectRepositoryInterface $projectRepository,
        CollectionFactory          $collectionFactory
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->projectRepository = $projectRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $this->doAction($collection);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param AbstractDb $collection
     * @return void
     */
    abstract protected function doAction(AbstractDb $collection);
}
