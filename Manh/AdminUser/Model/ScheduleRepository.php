<?php

namespace Mageplus\VivaTicket\Model;

use Exception;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\VivaTicket\Api\ScheduleRepositoryInterface;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var ScheduleResource
     */
    private $scheduleResource;


    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var array
     */
    private $scheduleCache = [];

    /**
     * ScheduleRepository Constructor.
     *
     * @param ScheduleFactory $scheduleFactory
     * @param ScheduleResource $scheduleResource
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ScheduleFactory               $scheduleFactory,
        ScheduleResource              $scheduleResource,
        CollectionProcessorInterface  $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    )
    {
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleResource = $scheduleResource;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function save(Schedule $schedule): Schedule
    {
        try {
            $this->scheduleResource->save($schedule);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->scheduleCache[$schedule->getId()]);

        return $schedule;
    }

    public function deleteById(int $scheduleId): bool
    {
        return $this->delete($this->get($scheduleId));
    }

    public function delete(Schedule $schedule): bool
    {
        try {
            $this->scheduleResource->delete($schedule);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        unset($this->scheduleCache[$schedule->getId()]);

        return true;
    }

    public function get(int $scheduleId): Schedule
    {
        if (isset($this->scheduleCache[$scheduleId])) {
            return $this->scheduleCache[$scheduleId];
        }

        $schedule = $this->scheduleFactory->create();
        $this->scheduleResource->load($schedule, $scheduleId);

        if (!$schedule->getId()) {
            throw new NoSuchEntityException(__('The Schedule with the "%1" ID doesn\'t exist.', $scheduleId));
        }

        $this->scheduleCache[$scheduleId] = $schedule;
        return $this->scheduleCache[$scheduleId];
    }
}
