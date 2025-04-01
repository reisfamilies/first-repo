<?php

namespace Mageplus\Project\Ui\DataProvider\Tag\Form;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mageplus\Project\Api\Data\TagInterface;
use Mageplus\Project\Api\TagRepositoryInterface;
use Mageplus\Project\Model\ResourceModel\Tag\CollectionFactory;

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
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    public function __construct(
        string                 $name,
                               $primaryFieldName,
                               $requestFieldName,
        DataPersistorInterface $dataPersistor,
        TagRepositoryInterface $tagRepository,
        CollectionFactory      $collectionFactory,
        array                  $meta = [],
        array                  $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collectionFactory = $collectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->tagRepository = $tagRepository;
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
            if (isset($data['items'][0][TagInterface::TAG_ID])) {
                $tagId = (int)$data['items'][0][TagInterface::TAG_ID];
                $tag = $this->tagRepository->getById($tagId);
                $data = [$tagId => $tag->getData()];
            }
        }

        if ($savedData = $this->dataPersistor->get('mageplus_tag')) {
            $savedEntityId = $savedData[TagInterface::TAG_ID] ?? null;
            if (isset($data[$savedEntityId])) {
                $data[$savedEntityId] = array_merge($data[$savedEntityId], $savedData);
            } else {
                $data[$savedEntityId] = $savedData;
            }
            $this->dataPersistor->clear('mageplus_tag');
        }

        return $data;
    }
}
