<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Project Account for Magento 2
 */

namespace Mageplus\Project\Model\Repository;

use Exception;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Mageplus\Project\Api\Data\ProjectInterface;
use Mageplus\Project\Api\ProjectRepositoryInterface;
use Mageplus\Project\Model\Project;
use Mageplus\Project\Model\Project\ProjectManagement;
use Mageplus\Project\Model\ProjectFactory;
use Mageplus\Project\Model\ResourceModel\Project as ProjectResource;
use Mageplus\Project\Model\ResourceModel\Project\Collection;
use Mageplus\Project\Model\ResourceModel\Project\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProjectRepository implements ProjectRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ProjectFactory
     */
    private $projectFactory;

    /**
     * @var ProjectResource
     */
    private $projectResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $projects;

    /**
     * @var CollectionFactory
     */
    private $projectCollectionFactory;

    /**
     * @var ProjectManagement
     */
    private $projectManagement;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        ProjectFactory                        $projectFactory,
        ProjectResource                       $projectResource,
        CollectionFactory                     $projectCollectionFactory,
        ProjectManagement                     $projectManagement
    )
    {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->projectFactory = $projectFactory;
        $this->projectResource = $projectResource;
        $this->projectCollectionFactory = $projectCollectionFactory;
        $this->projectManagement = $projectManagement;
    }

    /**
     * @inheritdoc
     */
    public function save(ProjectInterface $project)
    {
        $isProjectExisted = (bool)$project->getProjectId();
        if ($isProjectExisted) {
            $project = $this->getById($project->getProjectId())->addData($project->getData());
        }
        $project->setRegionId($project->getRegionId() ?: null);
        $project->setRegion($project->getRegion() ?: null);
        $this->validate($project);
        try {
            $this->projectResource->save($project);
        } catch (Exception $e) {
            if ($project->getProjectId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save project with ID %1. Error: %2',
                        [$project->getProjectId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new project. Error: %1', $e->getMessage()));
        }

        $this->projectManagement->processProject($project);
        unset($this->projects[$project->getProjectId()]);

        return $project;
    }

    /**
     * @inheritdoc
     */
    public function getById($projectId)
    {
        if (!isset($this->projects[$projectId])) {
            /** @var Project $project */
            $project = $this->projectFactory->create();
            $this->projectResource->load($project, $projectId);
            if (!$project->getProjectId()) {
                throw new NoSuchEntityException(__('Project with specified ID "%1" not found.', $projectId));
            }
            $this->projects[$projectId] = $project;
        }

        return $this->projects[$projectId];
    }

    /**
     * @throws CouldNotSaveException
     */
    private function validate(ProjectInterface $project): void
    {
        $couldCotSaveException = new CouldNotSaveException(__('Unable to save the project.'));
        foreach (ProjectInterface::REQUIRE_FIELDS as $field) {
            if (empty($project->getData($field))) {
                $couldCotSaveException->addException(new LocalizedException(__('Required field', $field)));
            }
        }

        if (!empty($couldCotSaveException->getErrors())) {
            throw $couldCotSaveException;
        }
    }

    public function getNew(): ProjectInterface
    {
        return $this->projectFactory->create();
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @param bool $withExtensions
     * @return Project
     */
    public function getByField($fieldName, $value)
    {
        /** @var Project $project */
        $project = $this->projectFactory->create();
        $this->projectResource->load($project, $value, $fieldName);

        return $project;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($projectId)
    {
        $projectModel = $this->getById($projectId);
        $this->delete($projectModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(ProjectInterface $project)
    {
        try {
            $this->projectManagement->processProjectDelete($project);
            $this->projectResource->delete($project);
            unset($this->projects[$project->getProjectId()]);
        } catch (Exception $e) {
            if ($project->getProjectId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove project with ID %1. Error: %2',
                        [$project->getProjectId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove project. Error: %1', $e->getMessage()));
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

        /** @var Collection $projectCollection */
        $projectCollection = $this->projectCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $projectCollection);
        }

        $searchResults->setTotalCount($projectCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $projectCollection);
        }

        $projectCollection->setCurPage($searchCriteria->getCurrentPage());
        $projectCollection->setPageSize($searchCriteria->getPageSize());

        $projects = [];
        /** @var ProjectInterface $project */
        foreach ($projectCollection->getItems() as $project) {
            $projects[] = $this->getById($project->getProjectId());
        }

        $searchResults->setItems($projects);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $projectCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $projectCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $projectCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $projectCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $projectCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $projectCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
