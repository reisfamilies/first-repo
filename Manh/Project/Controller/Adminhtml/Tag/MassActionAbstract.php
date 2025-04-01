<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Project Account for Magento 2
 */

namespace Mageplus\Project\Controller\Adminhtml\Tag;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Mageplus\Project\Api\TagRepositoryInterface;
use Mageplus\Project\Model\ResourceModel\Tag\CollectionFactory;

abstract class MassActionAbstract extends Action
{
    public const ADMIN_RESOURCE = 'Mageplus_Project::project_management';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * MassActionAbstract Constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param TagRepositoryInterface $tagRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context                $context,
        Filter                 $filter,
        TagRepositoryInterface $tagRepository,
        CollectionFactory      $collectionFactory
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->tagRepository = $tagRepository;
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
