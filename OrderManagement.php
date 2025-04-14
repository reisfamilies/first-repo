<?php
/*
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2025 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\UnifiedOrdering\Model;

use Exception;
use JsonException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Model\InvoiceOrder;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\RefundOrder;
use Magento\Sales\Model\ShipOrder;
use Ramsey\Uuid\Uuid;
use Psr\Log\LoggerInterface as Logging;
use Tigren\UnifiedOrdering\Api\Data\UosResponseInterface;
use Tigren\UnifiedOrdering\Api\OrderManagementInterface;
use Tigren\UnifiedOrdering\Api\OrderItemDetailRepositoryInterface;

/**
 * Class OrderManagement
 * @package Tigren\UnifiedOrdering\Model
 */
class OrderManagement implements OrderManagementInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var Request
     */
    private Request $httpRequest;

    /**
     * @var CreditmemoItemCreationInterfaceFactory
     */
    private CreditmemoItemCreationInterfaceFactory $creditmemoItemCreationFactory;

    /**
     * @var InvoiceItemCreationInterfaceFactory
     */
    private InvoiceItemCreationInterfaceFactory $invoiceItemCreationFactory;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private ShipmentItemCreationInterfaceFactory $shipmentItemCreationFactory;

    /**
     * @var InvoiceOrder
     */
    private InvoiceOrder $invoiceOrder;

    /**
     * @var ItemRepository
     */
    private ItemRepository $orderItemRepository;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @var RefundOrder
     */
    private RefundOrder $refundOrder;

    /**
     * @var ShipOrder
     */
    private ShipOrder $shipOrder;

    /**
     * @var Logging
     */
    private Logging $logger;

    /**
     * @var UosResponseInterface
     */
    private UosResponseInterface $uosResponse;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    private OrderItemDetailRepositoryInterface $itemDetailRepository;

    private array $items;

    private bool $hasError = false;

    /**
     * OrderManagement Constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Request $httpRequest
     * @param CreditmemoItemCreationInterfaceFactory $creditmemoItemCreationFactory
     * @param InvoiceItemCreationInterfaceFactory $invoiceItemCreationFactory
     * @param ShipmentItemCreationInterfaceFactory $shipmentItemCreationFactory
     * @param InvoiceOrder $invoiceOrder
     * @param ItemRepository $orderItemRepository
     * @param OrderRepository $orderRepository
     * @param RefundOrder $refundOrder
     * @param ShipOrder $shipOrder
     * @param Logging $logger
     * @param UosResponseInterface $uosResponse
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Request $httpRequest,
        CreditmemoItemCreationInterfaceFactory $creditmemoItemCreationFactory,
        InvoiceItemCreationInterfaceFactory $invoiceItemCreationFactory,
        ShipmentItemCreationInterfaceFactory $shipmentItemCreationFactory,
        InvoiceOrder $invoiceOrder,
        ItemRepository $orderItemRepository,
        OrderRepository $orderRepository,
        RefundOrder $refundOrder,
        ShipOrder $shipOrder,
        Logging $logger,
        UosResponseInterface $uosResponse,
        ConfigProvider $configProvider,
        OrderItemDetailRepositoryInterface $itemDetailRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->httpRequest = $httpRequest;
        $this->creditmemoItemCreationFactory = $creditmemoItemCreationFactory;
        $this->invoiceItemCreationFactory = $invoiceItemCreationFactory;
        $this->shipmentItemCreationFactory = $shipmentItemCreationFactory;
        $this->invoiceOrder = $invoiceOrder;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->refundOrder = $refundOrder;
        $this->shipOrder = $shipOrder;
        $this->logger = $logger;
        $this->uosResponse = $uosResponse;
        $this->configProvider = $configProvider;
        $this->itemDetailRepository = $itemDetailRepository;
    }

    /**
     * @return UosResponseInterface
     */
    public function updateOrderStatus(): UosResponseInterface
    {
        $this->logger->info('[START UPDATE ORDER STATUS API]');
        $response = $this->uosResponse;
        try {
            $ordersData = $this->httpRequest->getBodyParams();
            $this->logger->info('[UPDATE ORDER STATUS DATA]', [$ordersData]);
            if (!is_array($ordersData) || empty($ordersData)) {
                throw new InputException(__("Invalid request data."));
            }

            $orderStatusMapping = $this->configProvider->getOrderStatusMapping();
            $orderItemState = $this->configProvider->getOrderItemStateMapping();
            $collectResponse = [];
            foreach ($ordersData as $orderData) {
                $referenceNumber = $orderData['referenceNumber'] ?? '';
                $orderNumber = $orderData['orderNumber'] ?? '';
                $lineItemNumber = $orderData['lineItemNumber'] ?? 0;
                $lineItemNumberIndex = $orderData['lineItemNumberIndex'] ?? 0;
                $updateType = $orderData['updateType'] ?? '';
                $newStatus = $orderData['newStatus'] ?? '';
                $updateTimestamp = $orderData['updateTimestamp'] ?? '';
                $validateData = [
                    'referenceNumber' => $referenceNumber,
                    'orderNumber' => $orderNumber,
                    'lineItemNumber' => $lineItemNumber,
                    'lineItemNumberIndex' => $lineItemNumberIndex,
                    'updateType' => $updateType,
                    'newStatus' => $newStatus,
                    'updateTimestamp' => $updateTimestamp
                ];

                try {
//                    $this->validate($validateData, $orderStatusMapping, $orderItemState);
                    if ($updateType == 'ORDER_STATUS_UPDATE' && $lineItemNumber == 0 && $lineItemNumberIndex == 0) {
                        $order = $this->getOrder($orderNumber, $referenceNumber);
                        $this->processOrderStatusUpdate($order, $newStatus, $orderStatusMapping);
                    }

                    if ($updateType == 'ORDER_LINE_ITEM_STATUS_UPDATE') {
                        $collectResponse[] = $validateData;
                    }
                } catch (InputException|NoSuchEntityException $e) {
                    $this->hasError = true;
                    $this->logger->info('[UPDATE ORDER STATUS ERROR]', [$referenceNumber, $e->getMessage()]);
                    continue;
                }
            }

            if (!empty($collectResponse)) {
                $groupedOrders = $this->groupOrders($collectResponse);
                foreach ($groupedOrders as $key => $orderGroup) {
                    try {
                        $this->processOrderItemStatusUpdate($orderGroup, $key, $orderItemState);
                    } catch (Exception $e) {
                        $this->hasError = true;
                        if (!empty($e->getMessage())) {
                            $this->logger->info('[UPDATE ORDER STATUS ERROR]', [$e->getMessage()]);
                        }
                        continue;
                    }
                }
            }

            if (!$this->hasError) {
                $response->setReceiptUUID($this->generateUuid());
            } else {
                $response->setReceiptUUID('null');
            }

        } catch (Exception $e) {
            $this->logger->info('[UPDATE ORDER STATUS ERROR]', [$e->getMessage()]);
            $response->setReceiptUUID('null');
        }

        $this->logger->info('[END UPDATE ORDER STATUS API]');

        return $response;
    }

    /**
     * @throws InputException
     */
    private function validate(array $data, array $orderStatus, array $orderItemState): void
    {
        $requiredFields = ['referenceNumber', 'orderNumber', 'updateType', 'updateTimestamp'];
        $allowedUpdateTypes = $this->configProvider->getAllowUpdateTypeMapping();
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new InputException(__("The field '%1' is required.", $field));
            }
        }

        if (!in_array($data['updateType'], $allowedUpdateTypes, true)) {
            throw new InputException(__("The update type '%1' is not allowed.", $data['updateType']));
        }

        if (
            empty($data['newStatus']) ||
            (!array_key_exists($data['newStatus'], $orderStatus) && !array_key_exists($data['newStatus'],
                    $orderItemState))
        ) {
            throw new InputException(__("The new status '%1' is not allowed.", $data['newStatus']));
        }

        if (!strtotime($data['updateTimestamp'])) {
            throw new InputException(__("The update timestamp '%1' is not a valid date-time.",
                $data['updateTimestamp']));
        }
    }

    /**
     * @param string $orderNumber
     * @param string $referenceNumber
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    private function getOrder(string $orderNumber, string $referenceNumber): mixed
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $referenceNumber)
//            ->addFilter('uos_order', $orderNumber)
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        if (empty($orders)) {
            throw new NoSuchEntityException(__('Order with order number "%1" and reference number "%2" does not exist.',
                $orderNumber, $referenceNumber));
        }

        return reset($orders);
    }

    /**
     * Process update order status
     *
     * @param $order
     * @param $newStatus
     * @param $orderStatusMapping
     * @return void
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function processOrderStatusUpdate($order, $newStatus, $orderStatusMapping): void
    {
        $orderStatus = 'processing';
        $orderState = 'processing';
//        if (isset($orderStatusMapping[$newStatus]['magento_status'], $orderStatusMapping[$newStatus]['magento_state'])) {
//            $orderStatus = $orderStatusMapping[$newStatus]['magento_status'];
//            $orderState = $orderStatusMapping[$newStatus]['magento_state'];
//        }

        if ($order->getState() === $orderState) {
            $this->hasError = true;
            return;
        }

        switch ($orderState) {
            case 'new':
                break;
            case 'canceled':
                if (!$order->canCancel()) {
                    throw new InputException(__("The order cannot be canceled."));
                }
                $order->cancel();
                break;
            case 'closed':
                $this->refundOrder->execute($order->getId());
                break;
            default:
                throw new InputException(__("The state '%1' is not allowed.", $orderState));
        }

        $oldUosStatus = (string)$order->getData('uos_order_status');
        if ($newStatus != $oldUosStatus) {
            $newOrder = $this->orderRepository->get($order->getId());
            $newOrder->setStatus($orderStatus);
            $newOrder->setData('uos_order_status', $newStatus);
            $this->orderRepository->save($newOrder);
        }
    }

    /**
     * @param array $orders
     * @return array
     */
    public function groupOrders(array $orders): array
    {
        $result = [];
        foreach ($orders as $order) {
            $status = $order['newStatus'];
            $referenceNumber = $order['referenceNumber'];
            $orderNumber = $order['orderNumber'];
            $lineItemNumber = $order['lineItemNumber'];
            $lineItemIndex = $order['lineItemNumberIndex'];

            if (!isset($result[$status])) {
                $result[$status] = [];
            }

            $orderKey = null;
            foreach ($result[$status] as $key => $existingOrder) {
                if ($existingOrder['referenceNumber'] === $referenceNumber && $existingOrder['orderNumber'] === $orderNumber) {
                    $orderKey = $key;
                    break;
                }
            }

            if ($orderKey === null) {
                $result[$status][] = [
                    'referenceNumber' => $referenceNumber,
                    'orderNumber' => $orderNumber,
                    'lineItem' => []
                ];
                $orderKey = array_key_last($result[$status]);
            }

            if (!isset($result[$status][$orderKey]['lineItem'][$lineItemNumber])) {
                $result[$status][$orderKey]['lineItem'][$lineItemNumber] = [];
            }

            if (!in_array($lineItemIndex, $result[$status][$orderKey]['lineItem'][$lineItemNumber], true)) {
                $result[$status][$orderKey]['lineItem'][$lineItemNumber][$lineItemIndex] = [];
            }
        }

        return $result;
    }

    /**
     * @param $orders
     * @param $uosOrderItemStatus
     * @param $orderItemStateMapping
     * @return void
     * @throws InputException
     * @throws JsonException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processOrderItemStatusUpdate($orders, $uosOrderItemStatus, $orderItemStateMapping): void
    {
        foreach ($orders as $orderData) {
            try {
                $orderNumber = $orderData['orderNumber'] ?? '';
                $referenceNumber = $orderData['referenceNumber'] ?? '';
                $lineItem = $orderData['lineItem'] ?? [];
                if (empty($orderNumber) || empty($referenceNumber)) {
                    throw new LocalizedException(__('Missing order number or reference number.'));
                }

                $orderState = 'processing';
                $order = $this->getOrder($orderNumber, $referenceNumber);
                $orderItems = $order->getAllVisibleItems();
                if (empty($orderItems) || !is_array($orderItems)) {
                    throw new LocalizedException(__('Invalid order items data'));
                }

                $orderId = $order->getId();
                if (!isset($this->items[$orderId])) {
                    $this->items[$orderId] = array_column($orderItems, null, 'id');
                }

                $invoiceItems = [];
                $shipmentItems = [];
                $creditMemoItems = [];
                $listItemsUpdate = [];
                $itemsObject = array_values($this->items[$orderId]);
                foreach ($lineItem as $lineItemNumber => $itemDetails) {
                    $itemObject = $itemsObject[(int)$lineItemNumber - 1] ?? null;
                    if (!$itemObject) {
                        throw new InputException(__("The line item number '%1' is not found.", $lineItemNumber));
                    }

//                    if (!$this->canProcessOrderItem($itemObject, $itemDetails, $uosOrderItemStatus)) {
//                        throw new InputException(__("The line item number '%1' is not allowed.", $lineItemNumber));
//                    }

                    $qty = is_array($itemDetails) && !in_array(0, $itemDetails, true)
                        ? count($itemDetails)
                        : $itemObject->getQtyOrdered();
                    $itemId = (int)$itemObject->getId();
                    match ($orderState) {
                        'processing' => $invoiceItems[] = $this->createInvoiceItem($itemId, $qty),
                        'canceled' => $this->cancelOrderItem($order, $itemObject, $qty),
                        'complete' => $shipmentItems[] = $this->createShipmentItem($itemId, $qty),
                        'closed' => $creditMemoItems[] = $this->createCreditMemoItem($itemId, $qty),
                        default => throw new InputException(__("The state '%1' is not allowed.", $orderState)),
                    };

                    $listItemsUpdate[$itemId] = $itemDetails;
                }

                $this->executeOrderStateAction($orderState, $order, $invoiceItems, $shipmentItems, $creditMemoItems);
                $this->updateOrderItemsStatus($listItemsUpdate, $uosOrderItemStatus);
            } catch (Exception $e) {
                $this->hasError = true;
                $this->logger->info('[UPDATE ORDER STATUS ERROR]', [$orderData, $e->getMessage()]);
                continue;
            }
        }
    }

    /**
     * Check if the order item can be processed
     *
     * @param $itemObject
     * @param array $item
     * @param $uosOrderItemStatus
     * @return bool
     * @throws JsonException
     */
    public function canProcessOrderItem($itemObject, array $item, $uosOrderItemStatus): bool
    {
        $uosOrderItem = json_decode(
            $itemObject->getData('uos_order_item') ?: '[]',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (empty($item)) {
            return false;
        }

        // Get the first value from the $item array.
        $firstValue = reset($item);

        // If the value is non-zero, check that the corresponding index exists
        // and that its status is not equal to $uosOrderItemStatus.
        if ($firstValue !== 0) {
            $index = $firstValue - 1;
            return isset($uosOrderItem[$index]) && $uosOrderItem[$index]['status'] !== $uosOrderItemStatus;
        }

        // If the value is zero, check if at least one element in $uosOrderItem has a status different from $uosOrderItemStatus.
        return count(array_filter($uosOrderItem, static fn($val) => $val['status'] !== $uosOrderItemStatus)) > 0;
    }

    /**
     * Creates an invoice item.
     *
     * @param int $itemId
     * @param int $qty
     * @return InvoiceItemCreationInterface
     */
    private function createInvoiceItem(int $itemId, int $qty): InvoiceItemCreationInterface
    {
        $invoiceItem = $this->invoiceItemCreationFactory->create();
        $invoiceItem->setQty($qty);
        $invoiceItem->setOrderItemId($itemId);

        return $invoiceItem;
    }

    /**
     * Cancels an order item.
     *
     * @param $order
     * @param $itemObject
     * @param int $qty
     * @return void
     * @throws InputException
     */
    private function cancelOrderItem($order, $itemObject, int $qty): void
    {
        if (!$order->canCancel()) {
            throw new InputException(__("The order cannot be canceled."));
        }

        $itemObject->setQtyCanceled($qty);
        $this->orderItemRepository->save($itemObject);
    }

    /**
     * Creates a shipment item.
     *
     * @param int $itemId
     * @param int $qty
     * @return ShipmentItemCreationInterface
     */
    private function createShipmentItem(int $itemId, int $qty): ShipmentItemCreationInterface
    {
        $shipmentItem = $this->shipmentItemCreationFactory->create();
        $shipmentItem->setQty($qty);
        $shipmentItem->setOrderItemId($itemId);

        return $shipmentItem;
    }

    /**
     * Creates a credit memo item.
     *
     * @param int $itemId
     * @param int $qty
     * @return CreditmemoItemCreationInterface
     */
    private function createCreditMemoItem(int $itemId, int $qty): CreditmemoItemCreationInterface
    {
        $creditMemoItem = $this->creditmemoItemCreationFactory->create();
        $creditMemoItem->setQty($qty);
        $creditMemoItem->setOrderItemId($itemId);

        return $creditMemoItem;
    }

    /**
     * Executes the order state action.
     *
     * @param string $orderState
     * @param $order
     * @param array $invoiceItems
     * @param array $shipmentItems
     * @param array $creditMemoItems
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function executeOrderStateAction(
        string $orderState,
        $order,
        array $invoiceItems,
        array $shipmentItems,
        array $creditMemoItems
    ): void {
        match ($orderState) {
            'processing' => $this->invoiceOrder->execute($order->getId(), true, $invoiceItems),
            'complete' => $this->shipOrder->execute($order->getId(), $shipmentItems),
            'closed' => $this->refundOrder->execute($order->getId(), $creditMemoItems),
            'canceled', 'new' => null,
            default => throw new InputException(__("The state '%1' is not allowed.", $orderState)),
        };

        $this->orderItemRepository->_resetState();
    }

    /**
     * Updates order items status.
     *
     * @param array $listItemsUpdate
     * @param string $uosOrderItemStatus
     * @return void
     * @throws InputException
     * @throws JsonException
     * @throws NoSuchEntityException
     */
    private function updateOrderItemsStatus(array $listItemsUpdate, string $uosOrderItemStatus): void
    {
        foreach ($listItemsUpdate as $itemId => $itemDetails) {


            if (array_key_exists(0, $itemDetails)) {
                $itemUnits = $this->itemDetailRepository->getByItemId($itemId);
                if (empty($itemUnits)) {
                    throw new InputException(__("The item ID '%1' is not found.", $itemId));
                }
                foreach ($itemUnits as $itemUnit) {
                    $itemUnit->setStatus($uosOrderItemStatus);
                    $this->itemDetailRepository->save($itemUnit);
                }
            } else {
                foreach ($itemDetails as $key => $value) {
                    $itemUnit = $this->itemDetailRepository->getByItemIdAndIndex($itemId, $key);
                    if (empty($itemUnit)) {
                        throw new InputException(__("The item ID '%1' is not found.", $itemId));
                    }

                    $itemUnit->setStatus($uosOrderItemStatus);
                    if (!empty($value) && is_array($value)) {
                        foreach ($value as $valueKey => $valueItem) {
                            // create mapping information index then set data before save
                        }
                    }
                }
            }
        }

        $this->orderRepository->_resetState();
    }

    public function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}
