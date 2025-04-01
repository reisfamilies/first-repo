<?php

namespace Mageplus\Project\Block\Adminhtml\Form\Buttons;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Delete entity button.
 */
class Delete implements ButtonProviderInterface
{
    public const ID = 'project_id';

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * Delete Constructor.
     *
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface     $urlBuilder,
        RequestInterface $request
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    public function getButtonData()
    {
        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
            'sort_order' => 222
        ];
    }

    private function getDeleteUrl(): string
    {
        return $this->urlBuilder->getUrl(
            '*/*/delete',
            [self::ID => $this->request->getParam(self::ID)]
        );
    }
}
