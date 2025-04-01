<?php

namespace Mageplus\VivaTicket\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Mageplus\VivaTicket\Helper\VivaTicketAbstractHelper as VivaHelper;
use Mageplus\VivaTicket\Model\ManagerFactory;
use Psr\Log\LoggerInterface;

class VivaProduct extends Product implements HttpPostActionInterface
{
    /**
     * Mass actions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var VivaHelper
     */
    private $vivaHelper;

    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    /**
     * VivaProduct Constructor
     *
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param VivaHelper $vivaHelper
     * @param ManagerFactory $managerFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context                    $context,
        Builder                    $productBuilder,
        Filter                     $filter,
        CollectionFactory          $collectionFactory,
        VivaHelper                 $vivaHelper,
        ManagerFactory             $managerFactory,
        ProductRepositoryInterface $productRepository = null,
        LoggerInterface            $logger = null
    )
    {
        parent::__construct($context, $productBuilder);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository ?:
            ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        $this->logger = $logger ?:
            ObjectManager::getInstance()->create(LoggerInterface::class);
        $this->vivaHelper = $vivaHelper;
        $this->managerFactory = $managerFactory;
    }

    /**
     * Mass Sync Products Action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->vivaHelper->isEnabled()) {
            $this->messageManager->addErrorMessage(
                __('Viva Integration is disable now. Please try again!.')
            );

            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product/index');
        }

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collection->addMediaGalleryData();
        if (is_array($collection->getAllIds()) && count($collection->getAllIds()) > 1) {
            $this->messageManager->addErrorMessage(
                __('This function limit 1 product one time. Please try again!.')
            );

            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product/index');
        }
        $productSynced = 0;
        $productSyncedError = 0;
        $isVivaProduct = true;
        $manager = $this->managerFactory->create();
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            try {
                $productRepository = $this->productRepository->getById($product->getId());
                $isVivaProduct = $productRepository->getCustomAttribute('is_viva_ticket') ? $productRepository->getCustomAttribute('is_viva_ticket')->getValue() : false;
                $isComboProduct = $productRepository->getCustomAttribute('is_package_combo') ? $productRepository->getCustomAttribute('is_package_combo')->getValue() : false;
                $isB2bProduct = $productRepository->getCustomAttribute('is_b2b_viva_ticket') ? $productRepository->getCustomAttribute('is_b2b_viva_ticket')->getValue() : false;
                if (!$isVivaProduct) {
                    break;
                }

                if (!$isB2bProduct && !$isComboProduct) {
                    $manager->setProductSkuToSync($productRepository->getSku());
                    $manager->scheduleNow('vivaticket_product_sync_by_sku');
                } else if ($isB2bProduct && !$isComboProduct) {
                    $manager->setB2bProductSkuToSync($productRepository->getSku());
                    $manager->scheduleNow('vivaticket_b2b_product_sync_by_sku');
                } else if (!$isB2bProduct && $isComboProduct) {
                    $manager->setComboProductSkuToSync($productRepository->getSku());
                    $manager->scheduleNow('vivaticket_combo_product_sync_by_sku');
                }

                $productSynced++;
            } catch (LocalizedException $exception) {
                $this->logger->error(date("Y-m-d h:i:s") . ": Mass Action Sync Product execute: " . $exception->getMessage());
                $this->logger->error($exception->getLogMessage());
                $productSyncedError++;
                continue;
            }
        }

        if (!$isVivaProduct) {
            $this->messageManager->addErrorMessage(
                __('This product isn\'t viva product. Please try again!.')
            );

            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product/index');
        }

        if ($productSynced) {
            $this->messageManager->addSuccessMessage(
                __('This product has been started the cron job sync', $productSynced)
            );
        }

        if ($productSyncedError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been synced. Please see server logs for more details.',
                    $productSyncedError
                )
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product/index');
    }
}
