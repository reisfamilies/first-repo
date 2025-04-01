<?php

namespace Mageplus\Project\Model\Repository;

use Exception;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Mageplus\Project\Api\Data\TagInterface;
use Mageplus\Project\Api\TagRepositoryInterface;
use Mageplus\Project\Model\ResourceModel\Tag as TagResource;
use Mageplus\Project\Model\ResourceModel\Tag\Collection;
use Mageplus\Project\Model\ResourceModel\Tag\CollectionFactory;
use Mageplus\Project\Model\Tag;
use Mageplus\Project\Model\TagFactory;

class TagRepository implements TagRepositoryInterface
{
    /**
     * Model data storage
     *
     * @var array
     */
    private array $tags;

    /**
     * @var TagsFactory
     */
    private $tagFactory;

    private $tagResource;

    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * TagRepository Constructor.
     *
     * @param BookmarkSearchResultsInterfaceFactory $searchResultsFactory
     * @param TagFactory $tagFactory
     * @param TagResource $tagResource
     * @param CollectionFactory $tagCollectionFactory
     */
    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        TagFactory                            $tagFactory,
        TagResource                           $tagResource,
        CollectionFactory                     $tagCollectionFactory
    )
    {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->tagCollectionFactory = $tagCollectionFactory;
    }

    /**
     * @inheritdoc
     * @param TagInterface $tag
     * @return TagInterface|Tag
     * @throws CouldNotSaveException
     */
    public function save(TagInterface $tag)
    {
        try {
            $isTagExisted = (bool)$tag->getTagId();
            if ($isTagExisted) {
                $tag = $this->getById($tag->getTagId())->addData($tag->getData());
            }
            $this->tagResource->save($tag);
        } catch (Exception $e) {
            if ($tag->getTagId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save tag with ID %1. Error: %2',
                        [$tag->getTagId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new tag. Error: %1', $e->getMessage()));
        }

        unset($this->tags[$tag->getTagId()]);

        return $tag;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->tags[$entityId])) {
            /** @var Tag $tag */
            $tag = $this->tagFactory->create();
            $this->tagResource->load($tag, $entityId);
            if (!$tag->getTagId()) {
                throw new NoSuchEntityException(__('Tag with specified ID "%1" not found.', $entityId));
            }
            $this->tags[$entityId] = $tag;
        }

        return $this->tags[$entityId];
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @return Tag
     */
    public function getByField($fieldName, $value)
    {
        /** @var Tag $tag */
        $tag = $this->tagFactory->create();
        $this->tagResource->load($tag, $value, $fieldName);

        return $tag;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        $tag = $this->getById($entityId);
        $this->delete($tag);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(TagInterface $tag)
    {
        try {
            $this->tagResource->delete($tag);
            unset($this->tags[$tag->getTagId()]);
        } catch (Exception $e) {
            if ($tag->getTagId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove tag with ID %1. Error: %2',
                        [$tag->getTagId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove tag. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $tagCollection */
        $tagCollection = $this->tagCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $tagCollection);
        }

        $searchResults->setTotalCount($tagCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $tagCollection);
        }

        $tagCollection->setCurPage($searchCriteria->getCurrentPage());
        $tagCollection->setPageSize($searchCriteria->getPageSize());

        $tag = [];
        /** @var TagInterface $tag */
        foreach ($tagCollection->getItems() as $tag) {
            $tag[] = $this->getById($tag->getTagId());
        }

        $searchResults->setItems($tag);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $tagCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $tagCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $tagCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $tagCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $tagCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $tagCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return $this->tagCollectionFactory->create();
    }

    /**
     * @return TagInterface
     */
    public function getNew(): TagInterface
    {
        return $this->tagFactory->create();
    }
}
