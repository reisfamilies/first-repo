<?php

namespace Mageplus\VivaTicket\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Mageplus\VivaTicket\Api\ScheduleManagementInterface;
use Mageplus\VivaTicket\Api\ScheduleRepositoryInterface;

/**
 * @deprecated
 * @see ScheduleManagementInterface
 * @see ScheduleRepositoryInterface
 */
class Manager
{
    /**
     * @var ScheduleManagementInterface
     */
    private $scheduleManagement;

    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    public function __construct(
        ScheduleManagementInterface $scheduleManagement,
        ScheduleRepositoryInterface $scheduleRepository,
        WriterInterface             $configWriter,
        TypeListInterface           $cacheTypeList
    )
    {
        $this->scheduleManagement = $scheduleManagement;
        $this->scheduleRepository = $scheduleRepository;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function createCronJob($jobCode, $time)
    {
        return $this->scheduleManagement->createSchedule($jobCode, strtotime($time));
    }

    public function saveCronJob(
        $jobId,
        $jobCode = null,
        $status = null,
        $time = null
    )
    {
        $schedule = $this->scheduleRepository->get($jobId);

        if (!is_null($jobCode)) {
            $schedule->setJobCode($jobCode);
        }
        if (!is_null($status)) {
            $schedule->setStatus($status);
        }
        if (!is_null($time)) {
            $schedule->setScheduledAt(date(ScheduleManagementInterface::TIME_FORMAT, strtotime($time)));
        }

        $this->scheduleRepository->save($schedule);
    }


    public function scheduleNow($jobCode)
    {
        return $this->scheduleManagement->scheduleNow($jobCode);
    }

    public function setProductSkuToSync($productSku)
    {
        $this->setValue('vivaticket/data_mapping/product_sku_to_sync', $productSku);
    }

    public function setValue($path, $value)
    {
        $this->configWriter->save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
        $this->cacheTypeList->cleanType('config');
        $this->cacheTypeList->cleanType('full_page');
    }

    public function setComboProductSkuToSync($productSku)
    {
        $this->setValue('vivaticket/data_mapping/combo_product_sku_to_sync', $productSku);
    }

    public function setB2bProductSkuToSync($productSku)
    {
        $this->setValue('vivaticket/data_mapping/b2b_product_sku_to_sync', $productSku);
    }
}
