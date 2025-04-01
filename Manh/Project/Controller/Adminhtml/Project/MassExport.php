<?php

namespace Mageplus\Project\Controller\Adminhtml\Project;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Mageplus\Project\Api\ProjectRepositoryInterface;
use Mageplus\Project\Model\CountryDataProvider;
use Mageplus\Project\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Mageplus\Project\Model\ResourceModel\Project\CollectionFactory;

class MassExport extends MassActionAbstract
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CountryDataProvider
     */
    private $countryDataProvider;

    /**
     * MassExport Constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param ProjectRepositoryInterface $projectRepository
     * @param CollectionFactory $collectionFactory
     * @param GalleryCollection $galleryCollection
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CountryDataProvider $countryDataProvider
     * @throws FileSystemException
     */
    public function __construct(
        Context                    $context,
        Filter                     $filter,
        FileFactory                $fileFactory,
        Filesystem                 $filesystem,
        ProjectRepositoryInterface $projectRepository,
        CollectionFactory          $collectionFactory,
        GalleryCollection          $galleryCollection,
        ProductCollectionFactory   $productCollectionFactory,
        CountryDataProvider        $countryDataProvider
    )
    {
        parent::__construct($context, $filter, $projectRepository, $collectionFactory);
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->galleryCollection = $galleryCollection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->countryDataProvider = $countryDataProvider;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $this->doAction($collection);
    }

    protected function doAction(AbstractDb $collection)
    {
        $name = date('mdY-His');
        $filepath = 'export/project-data-' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = [
            'project_id',
            'project_name',
            'completion_year',
            'status',
            'street',
            'country',
            'region',
            'stores',
            'redirect_url',
            'image_name',
            'product_skus',
            'position'
        ];

        foreach ($columns as $column) {
            $header[] = $column;
        }

        $stream->writeCsv($header);
        try {
            foreach ($collection as $project) {
                $itemRow = [];
                $itemRow['project_id'] = $project->getData('project_id');
                $itemRow['project_name'] = $project->getData('project_name');
                $itemRow['completion_year'] = $project->getData('completion_year');
                $itemRow['status'] = $project->getData('status');
                $itemRow['street'] = $project->getData('street');
                $itemRow['country'] = $this->countryDataProvider->getCountryName($project->getCountryId());
                $itemRow['region'] = $project->getData('region');
                $itemRow['stores'] = $project->getData('stores');
                $itemRow['redirect_url'] = $project->getData('redirect_url');
                $itemRow['image_name'] = $this->getImagesByProjectId($project->getProjectId());
                $itemRow['product_skus'] = $this->getProductSkusByProject($project);
                $itemRow['position'] = $project->getData('position');
                $stream->writeCsv($itemRow);
            }

            $stream->unlock();
            $stream->close();
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 0;
            $csvFilename = 'project-' . $name . '.csv';

            return $this->_fileFactory->create($csvFilename, $content, DirectoryList::VAR_DIR);
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_redirect('project/index/');
            return false;
        }

    }


    public function getImagesByProjectId($projectId)
    {
        $galleryImages = $this->galleryCollection->getImagesByProjectId($projectId);
        $imagesName = [];
        foreach ($galleryImages as $image) {
            $imagesName[] = $image->getData('image_name');
        }

        return implode(',', $imagesName);
    }

    public function getProductSkusByProject($project)
    {
        $productSkus = [];
        $products = '';
        $productCollection = $this->productCollectionFactory->create();
        if (!empty($project->getProductIds())) {
            $productCollection->addFieldToFilter('entity_id', ['in' => $project->getProductIds()]);
            $products = $productCollection->getItems();
        }

        if (!empty($products)) {
            foreach ($products as $product) {
                $productSkus[] = $product->getSku();
            }

            if (!empty($project->getAdditionalSkus())) {
                foreach (explode(',', $project->getAdditionalSkus()) as $sku) {
                    $productSkus[] = $sku;
                }
            }
        }

        return implode(',', $productSkus);
    }
}
