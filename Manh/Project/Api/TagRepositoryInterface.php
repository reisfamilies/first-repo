<?php

namespace Mageplus\Project\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\Project\Api\Data\TagInterface;
use Mageplus\Project\Model\Tag;

/**
 * @api
 */
interface TagRepositoryInterface
{
    /**
     * Save
     *
     * @param TagInterface $tag
     * @return TagInterface
     * @throws LocalizedException
     *
     * @throws CouldNotSaveException
     */
    public function save(TagInterface $tag);

    /**
     * Get by id
     *
     * @param int $tagId
     * @return TagInterface
     * @throws NoSuchEntityException
     */
    public function getById($tagId);

    /**
     * @return TagInterface
     */
    public function getNew(): TagInterface;

    /**
     * @param string $fieldName
     * @param string $value
     * @return Tag
     */
    public function getByField($fieldName, $value);

    /**
     * Delete
     *
     * @param TagInterface $tag
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(TagInterface $tag);

    /**
     * Delete by id
     *
     * @param int $tagId
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function deleteById($tagId);

    /**
     * Lists
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
