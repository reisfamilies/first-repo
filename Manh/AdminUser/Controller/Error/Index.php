<?php

namespace Mageplus\VivaTicket\Controller\Error;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Mageplus\VivaTicket\Helper\EmptyShoppingCart;

class Index extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    protected EmptyShoppingCart $emptyShoppingCart;

    /**
     * @param EmptyShoppingCart $emptyShoppingCart
     * @param Context $context
     */
    public function __construct(
        EmptyShoppingCart  $emptyShoppingCart,
        Context $context
    ) {
        $this->emptyShoppingCart = $emptyShoppingCart;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    public function execute()
    {
        $emptyCart = $this->emptyShoppingCart->execute();
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultPage->setPath('checkout/cart/index');
        if ($emptyCart) {
            $this->messageManager->addErrorMessage(__('Your cart have problem, Please try to add cart again.'));
        }
        return $resultPage;
    }
}
