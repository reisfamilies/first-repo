<?php

namespace Mageplus\Project\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplus\Project\Controller\Index\Ajax;
use Mageplus\Project\Controller\Index\Index;
use Mageplus\Project\Model\ConfigProvider;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface|Http
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Router Constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ActionFactory         $actionFactory,
        ScopeConfigInterface  $scopeConfig,
        StoreManagerInterface $storeManager,
        ConfigProvider        $configProvider
    )
    {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    public function match(RequestInterface $request)
    {
        $this->request = $request;
        $projectPage = $this->configProvider->getUrl();

        $identifier = trim($this->request->getPathInfo(), '/');

        $request->setRouteName('mpproject');

        $identifier = current(explode("/", $identifier));

        if ($identifier == $projectPage) {
            $this->request->setDispatched(true);
            $this->request->setModuleName('mpproject')->setControllerName('index')->setActionName('index');
            $this->request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create(Index::class);
        } else {
            return null;
        }

        return $this->actionFactory->create(Forward::class);
    }
}
