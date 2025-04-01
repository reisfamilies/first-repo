<?php

namespace Mageplus\Project\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplus\Project\Model\GalleryFactory;
use Mageplus\Project\Model\Repository\ProjectRepository;
use Mageplus\Project\Model\ResourceModel\Gallery as GalleryResource;
use Mageplus\Project\Model\ResourceModel\Product as ProductResource;
use Psr\Log\LoggerInterface as Logger;

class Project extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Csv
     */
    protected $fileCsv;
    private array $product;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var GalleryFactory
     */
    private $galleryFactory;

    /**
     * @var GalleryResource
     */
    private $galleryResource;

    /**
     * @var CountryCollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * Project Constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Logger $logger
     * @param ResourceConnection $resource
     * @param ProjectRepository $projectRepository
     * @param ProductResource $productResource
     * @param GalleryFactory $galleryFactory
     * @param GalleryResource $galleryResource
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     */
    public function __construct(
        Context                  $context,
        PageFactory              $resultPageFactory,
        Logger                   $logger,
        ResourceConnection       $resource,
        ProjectRepository        $projectRepository,
        ProductResource          $productResource,
        GalleryFactory           $galleryFactory,
        GalleryResource          $galleryResource,
        CountryCollectionFactory $countryCollectionFactory,
        RegionCollectionFactory  $regionCollectionFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileCsv = new Csv(new File());
        $this->logger = $logger;
        $this->resource = $resource;
        $this->projectRepository = $projectRepository;
        $this->productResource = $productResource;
        $this->galleryFactory = $galleryFactory;
        $this->galleryResource = $galleryResource;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * import data from csv file to model
     * @return Page
     * @throws Exception
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import Project'));
        $file = $this->getRequest()->getFiles('project_file');
        if ($file) {
            $mimes = ['application/vnd.ms-excel', 'application/octet-stream', 'text/plain', 'text/csv', 'text/tsv'];
            if (in_array($file['type'], $mimes)) {
                $csvData = $this->fileCsv->getData($file['tmp_name']);
                if (!empty($csvData)) {
                    $results = $this->import($csvData);
                    if (count($results) > 0) {
                        foreach ($results as $item) {
                            $this->messageManager->addErrorMessage($item);
                        }
                    } else {
                        $this->messageManager->addSuccessMessage(__('Import project Successfully'));
                    }
                }
            } else {
                $this->messageManager->addError(__('Your file is not csv type'));
            }
            $this->_redirect('*/*/project', ['_current' => true]);
        }
        return $resultPage;
    }

    /**
     * @param $csvData
     * @return array
     */
    public function import($csvData)
    {
        if (!empty($csvData) && is_array($csvData) && count($csvData) > 1) {
            $header = $csvData[0];
            $project_id = array_search('project_id', $header);
            $projectName = array_search('project_name', $header);
            $completionYear = array_search('completion_year', $header);
            $status = array_search('status', $header);
            $street = array_search('street', $header);
            $city = array_search('city', $header);
            $country = array_search('country', $header);
            $region = array_search('region', $header);
            $stores = array_search('stores', $header);
            $redirectUrl = array_search('redirect_url', $header);
            $image_name = array_search('image_name', $header);
            $productSkus = array_search('product_skus', $header);
            $position = array_search('position', $header);
            if (!empty($project_id)) {
                unset($header[$project_id]);
            }
            unset($csvData[0]);
            $errorMessages = $this->validateCsvFile($csvData, $projectName, $status, $country, $region, $stores, $productSkus);
            if (empty($errorMessages)) {
                $countries = $this->getCountries();
                foreach ($csvData as $value) {
                    try {
                        $projectData = [];
                        if ($project_id !== false) {
                            $id = $value[$project_id];
                            $project = $this->projectRepository->getById($id);
                            $projectData['project_id'] = $id;
                        } else {
                            $project = $this->projectRepository->getNew();
                        }

                        $countryValue = array_search($value[$country], $countries);
                        $regionValue = '';
                        if (!empty($value[$region])) {
                            $regionValue = $value[$region];
                        } elseif ($countryValue !== 'MY') {
                            $regionValue = $value[$country];
                        }

                        $additionSku = [];
                        $productIds = [];
                        $productSkusArray = explode(',', $value[$productSkus] ?? '');
                        if (!empty($productSkusArray[0])) {
                            foreach ($productSkusArray as $productSku) {
                                $productId = $this->isProductExist($productSku);
                                if ($productId !== 0) {
                                    $productIds[] = $productId;
                                } else {
                                    $additionSku[] = $productSku;
                                }
                            }
                        }

                        $projectData = [
                            'project_name' => $value[$projectName],
                            'completion_year' => $value[$completionYear],
                            'status' => $value[$status],
                            'street' => $value[$street],
                            'city' => $value[$city],
                            'country_id' => $countryValue,
                            'region' => $regionValue,
                            'region_id' => !empty($this->getRegionId($value[$region])) ? $this->getRegionId($value[$region]) : null,
                            'stores' => $value[$stores],
                            'addition_sku' => implode(',', $additionSku),
                            'redirect_url' => $value[$redirectUrl],
                            'position' => (int)$value[$position]
                        ];
                        $project->addData($projectData);
                        $project->save();

                        if ($project_id == false) {
                            $projectId = $project->getId();
                            if (!empty($projectId) && !empty($productIds)) {
                                $this->productResource->assignProject($projectId, $productIds);
                            }

                            $imagesArray = explode(',', $value[$image_name] ?? '');
                            if (!empty($imagesArray[0])) {
                                foreach ($imagesArray as $imageName) {
                                    $newImage = $this->galleryFactory->create();
                                    $newImage->addData(
                                        [
                                            'project_id' => $projectId,
                                            'image_name' => str_replace(' ', '', $imageName),
                                            'is_base' => 0
                                        ]
                                    );
                                    $this->galleryResource->save($newImage);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $errorMessage = $e->getMessage();
                        $this->logger->info($errorMessage);
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                }
            }

            return $errorMessages;
        }

        return [];
    }

    /**
     * Validate csv file
     * @param $csvData
     * @param $projectName
     * @param $status
     * @param $country
     * @param $region
     * @param $stores
     * @param $productSkus
     * @return array
     */
    public function validateCsvFile($csvData, $projectName, $status, $country, $region, $stores, $productSkus): array
    {
        try {
            //            project_name,status,street,city,country,region,stores,redirect_url,image_name,product_skus

            $errorMessages = [];
            $countries = $this->getCountries();
            foreach ($csvData as $key => $value) {
                $row = $key + 1;
                // start validate
                if ($value[$projectName] == null) {
                    $errorMessages[] = 'project_name is a required field in row ' . $row;
                }

                if ($value[$status] == null) {
                    $errorMessages[] = 'status is a required field in row ' . $row;
                }

                if (!preg_match("/^[0-2]*$/", $value[$status])) {
                    $errorMessages[] = "status only contains valid characters: 0 or 1. '0' is InActive, '1' is Active in row " . $row;
                }

                if ($value[$country] == null) {
                    $errorMessages[] = 'country is a required field in row ' . $row;
                }

                if (!in_array($value[$country], $countries)) {
                    $errorMessages[] = 'country isn\'t correct in row ' . $row;
                }

//                if ($value[$region] == null) {
//                    $errorMessages[] = 'region is a required field in row ' . $row;
//                }

                if ($value[$stores] == null) {
                    $errorMessages[] = 'stores is a required field in row ' . $row;
                }

                $storesArray = explode(',', $value[$productSkus] ?? '');
                foreach ($storesArray as $store) {
                    if (!$this->isStoreExist($store)) {
                        $errorMessages[] = 'Store Id ' . $store . ' isn\'t exist on magento in ' . $row;
                    }
                }

//                $productSkusArray = explode(',', $value[$productSkus] ?? '');
//                if (!empty($productSkusArray[0])) {
//                    foreach ($productSkusArray as $productSku) {
//                        if (!$this->isProductExist($productSku)) {
//                            $errorMessages[] = $productSku . ' isn\'t exist on magento in ' . $row;
//                        }
//                    }
//                }
                // end validate
            }
            return $errorMessages;
        } catch (Exception $exception) {
            $this->logger->error('Import validate problem : ' . $exception->getMessage());
            return ['Something went wrong'];
        }
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        $countryCollection = $this->countryCollectionFactory->create();
        $countryCollection = $countryCollection->toOptionArray();

        return array_column($countryCollection, 'label', 'value');
    }

    /**
     * Check if store exist or not
     *
     * @param $storeId
     * @return bool
     */
    public function isStoreExist($storeId): bool
    {
        $connection = $this->resource->getConnection();
        $storeTable = $this->resource->getTableName('store');
        $query = $connection->select()
            ->from(
                $storeTable,
                'COUNT(*)'
            )
            ->where('store_id = ?', $storeId);
        return (bool)$connection->fetchOne($query);
    }

    /**
     * Check if sku exist or not
     *
     * @param $sku
     * @return int
     */
    public function isProductExist($sku)
    {
        $connection = $this->resource->getConnection();
        $productEntityTable = $this->resource->getTableName('catalog_product_entity');
        $query = $connection->select()
            ->from(
                $productEntityTable,
                'entity_id'
            )
            ->where('sku = ?', $sku);
        $entityId = $connection->fetchOne($query);
        if (!empty($entityId)) {
            return (int)$entityId;
        }

        return 0;
    }

    /**
     * @param string $region
     * @return mixed|string
     */
    public function getRegionId(string $region)
    {
        $region = $this->regionCollectionFactory->create()
            ->addRegionNameFilter($region)
            ->getFirstItem()
            ->toArray();

        if (!empty($region['region_id'])) {
            return $region['region_id'];
        }

        return '';
    }
}
