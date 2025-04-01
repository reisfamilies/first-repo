<?php

namespace Mageplus\VivaTicket\Model;

use Magento\Cron\Model\ConfigInterface;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplus\VivaTicket\Api\ScheduleManagementInterface;
use Mageplus\VivaTicket\Api\ScheduleRepositoryInterface;

class ScheduleManagement implements ScheduleManagementInterface
{
    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * ScheduleManagement Constructor.
     *
     * @param ScheduleRepositoryInterface $scheduleRepository
     * @param ConfigInterface $config
     * @param DateTime $dateTime
     * @param ScheduleFactory $scheduleFactory
     */
    public function __construct(
        ScheduleRepositoryInterface $scheduleRepository,
        ConfigInterface             $config,
        DateTime                    $dateTime,
        ScheduleFactory             $scheduleFactory
    )
    {
        $this->scheduleRepository = $scheduleRepository;
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->scheduleFactory = $scheduleFactory;
    }

    /**
     * @inheritDoc
     */
    public function scheduleNow(string $jobCode): Schedule
    {
        return $this->createSchedule($jobCode);
    }

    /**
     * @inheritDoc
     */
    public function createSchedule(string $jobCode, $time = null): Schedule
    {
        $time = date(ScheduleManagementInterface::TIME_FORMAT, $time ?? $this->dateTime->gmtTimestamp());

        $schedule = $this->scheduleFactory->create()
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(
                date(ScheduleManagementInterface::TIME_FORMAT, $this->dateTime->gmtTimestamp())
            )->setScheduledAt($time);

        $this->scheduleRepository->save($schedule);

        return $schedule;
    }
}
