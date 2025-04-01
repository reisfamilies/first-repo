<?php

namespace Mageplus\Project\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\Project\Api\Data\ProjectInterface;
use Mageplus\Project\Model\Project;

/**
 * @api
 */
interface ProjectRepositoryInterface
{
    /**
     * Save
     *
     * @param ProjectInterface $project
     * @return ProjectInterface
     * @throws LocalizedException
     *
     * @throws CouldNotSaveException
     */
    public function save(ProjectInterface $project);

    /**
     * Get by id
     *
     * @param int $projectId
     * @return ProjectInterface
     * @throws NoSuchEntityException
     */
    public function getById($projectId);

    /**
     * @return ProjectInterface
     */
    public function getNew(): ProjectInterface;

    /**
     * @param string $fieldName
     * @param string $value
     * @return Project
     */
    public function getByField($fieldName, $value);

    /**
     * Delete
     *
     * @param ProjectInterface $project
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(ProjectInterface $project);

    /**
     * Delete by id
     *
     * @param int $projectId
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function deleteById($projectId);

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
