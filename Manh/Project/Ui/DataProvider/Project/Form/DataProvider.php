<?php

namespace Mageplus\Project\Ui\DataProvider\Project\Form;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mageplus\Project\Api\Data\ProjectInterface;
use Mageplus\Project\Api\projectRepositoryInterface;
use Mageplus\Project\Model\ImageProcessor;
use Mageplus\Project\Model\RegistryConstants;
use Mageplus\Project\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Mageplus\Project\Model\ResourceModel\Product\Grid\CollectionFactory as ProductGridCollectionFactory;
use Mageplus\Project\Model\ResourceModel\Project\CollectionFactory;
use Mageplus\Project\Model\ResourceModel\Tag\Grid\CollectionFactory as TagGridCollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var projectRepositoryInterface
     */
    private $projectRepository;

    /**
     * @var ProductGridCollectionFactory
     */
    private $productGridCollectionFactory;

    /**
     * @var TagGridCollectionFactory
     */
    private $tagGridCollectionFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        CollectionFactory            $collectionFactory,
                                     $name,
                                     $primaryFieldName,
                                     $requestFieldName,
        DataPersistorInterface       $dataPersistor,
        projectRepositoryInterface   $projectRepository,
        ProductGridCollectionFactory $productGridCollectionFactory,
        TagGridCollectionFactory     $tagGridCollectionFactory,
        RegionFactory                $regionFactory,
        GalleryCollection            $galleryCollection,
        ImageProcessor               $imageProcessor,
        array                        $meta = [],
        array                        $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->projectRepository = $projectRepository;
        $this->productGridCollectionFactory = $productGridCollectionFactory;
        $this->tagGridCollectionFactory = $tagGridCollectionFactory;
        $this->regionFactory = $regionFactory;
        $this->galleryCollection = $galleryCollection;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getData()
    {
        $data = parent::getData();
        if ($data['totalRecords'] > 0) {
            if (isset($data['items'][0][ProjectInterface::PROJECT_ID])) {
                $projectId = (int)$data['items'][0][ProjectInterface::PROJECT_ID];
                $project = $this->projectRepository->getById($projectId);
                $data = [$projectId => $project->getData()];
                $data[$projectId]['product_ids'] = $this->getProductData($project);
                $data[$projectId]['tag_ids'] = $this->getTagData($project);

                $galleryImages = $this->galleryCollection->getImagesByProjectId($projectId);
                if (!empty($galleryImages)) {
                    $data[$projectId]['gallery_image'] = [];

                    foreach ($galleryImages as $image) {
                        $imgName = $image->getData('image_name');
                        $imgUrlParams = [ImageProcessor::PROJECT_GALLERY_MEDIA_PATH, $imgName];
                        $imgUrl = '';
                        $imgSize = '';
                        if ($this->imageProcessor->isExist($imgUrlParams)) {
                            $imgUrl = $this->imageProcessor->getImageUrl($imgUrlParams);
                            $imgSize = $this->imageProcessor->getImageSize($imgUrlParams);
                        }

                        $data[$projectId]['gallery_image'][] = ['name' => $imgName, 'url' => $imgUrl, 'size' => $imgSize];

                        if ($image->getData('is_base')) {
                            $data[$projectId]['base_img'] = $imgName;
                        }
                    }
                }

                if ($project->getRegionId()) {
                    $region = $this->regionFactory->create()->load($project->getRegionId());
                    $data[$projectId]['region'] = $region->getName();
                }
            }
        }

        if ($savedData = $this->dataPersistor->get(RegistryConstants::PROJECT_DATA)) {
            $savedProjectId = $savedData[ProjectInterface::PROJECT_ID] ?? null;
            if (isset($data[$savedProjectId])) {
                $data[$savedProjectId] = array_merge($data[$savedProjectId], $savedData);
            } else {
                $data[$savedProjectId] = $savedData;
            }
            $this->dataPersistor->clear(RegistryConstants::PROJECT_DATA);
        }

        return $data;
    }

    /**
     * @param ProjectInterface $project
     * @return array
     */
    private function getProductData(ProjectInterface $project)
    {
        $productCollection = $this->productGridCollectionFactory->create();
        $productCollection->addNameAttributeToSelect()->addProductIdFilter($project->getProductIds());
        $result = [];

        foreach ($productCollection->getItems() as $product) {
            $result[] = $this->fillData($product);
        }

        return ['project_product_container' => $result];
    }

    /**
     * @param $product
     *
     * @return array
     */
    private function fillData($product)
    {
        return [
            'entity_id' => $product->getId(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
        ];
    }

    private function getTagData(ProjectInterface $project)
    {
        $tagCollection = $this->tagGridCollectionFactory->create();
        $tagCollection->addTagIdFilter($project->getTagIds());
        $result = [];

        foreach ($tagCollection->getItems() as $tag) {
            $result[] = $this->fillTagData($tag);
        }

        return ['project_tag_container' => $result];
    }

    private function fillTagData($tag)
    {
        return [
            'tag_id' => $tag->getTagId(),
            'tag_name' => $tag->getTagName(),
            'status' => $tag->getStatus()
        ];
    }
}
