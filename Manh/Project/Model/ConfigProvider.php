<?php

namespace Mageplus\Project\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    /**
     * xpath prefix of module
     */
    public const PATH_PREFIX = 'mpproject';

    public const XPATH_ENABLE_PAGE = 'general/enable_pages';

    public const XPATH_URL = 'project/main_settings/url';

    public const XPATH_GRID_PER_PAGE = 'project/main_settings/grid_per_page';

    public const XPATH_EXCLUDE_PRODUCTS = 'project/main_settings/exclude_products';

    public const XPATH_GRID_PAGE_VALUE = 'project/main_settings/grid_per_page_values';

    public const XPATH_LIST_PAGE_VALUES = 'project/main_settings/list_per_page_values';


    public const XPATH_LIST_PER_PAGE = 'project/main_settings/list_per_page';

    public $_defaultAvailableLimit = [10 => 10, 20 => 20, 50 => 50];


    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getEnablePage($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_ENABLE_PAGE, $scopeCode);
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getUrl($scopeCode = null)
    {
        return $this->getValue(self::XPATH_URL, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getExcludeProducts($scopeCode = null)
    {
        return $this->getValue(self::XPATH_EXCLUDE_PRODUCTS, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getGridPerPage($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_GRID_PER_PAGE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getListPerPage($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_LIST_PER_PAGE, $scopeCode);
    }

    /**
     * Returns default view mode
     *
     * @param array $options
     * @return string
     */
    public function getDefaultViewMode()
    {
        return current(array_keys($this->getAvailableViewMode()));
    }

    /**
     * Returns available mode for view
     *
     * @return array|null
     */
    public function getAvailableViewMode()
    {
        return ['grid' => __('Grid'), 'list' => __('List')];
    }

    /**
     * Returns default value of `per_page` for view mode provided
     *
     * @param string $viewMode
     * @return int
     */
    public function getDefaultLimitPerPageValue($viewMode): int
    {
        $xmlConfigPath = sprintf('project/main_settings/%s_per_page', $viewMode);
        $defaultLimit = $this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE);

        $availableLimits = $this->getAvailableLimit($viewMode);
        return (int)($availableLimits[$defaultLimit] ?? current($availableLimits));
    }

    /**
     * Retrieve available limits for specified view mode
     *
     * @param string $viewMode
     * @return array
     */
    public function getAvailableLimit(string $viewMode): array
    {
        $availableViewModes = $this->getAvailableViewMode();

        if (!isset($availableViewModes[$viewMode])) {
            return $this->_defaultAvailableLimit;
        }

        if ($viewMode == 'grid') {
            $perPageValues = (string)$this->getGridPageValues();
        } else {
            $perPageValues = (string)$this->getListPageValues();
        }
        $perPageValues = explode(',', $perPageValues);

        return array_combine($perPageValues, $perPageValues);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getGridPageValues($scopeCode = null)
    {
        return $this->getValue(self::XPATH_GRID_PAGE_VALUE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getListPageValues($scopeCode = null)
    {
        return $this->getValue(self::XPATH_LIST_PAGE_VALUES, $scopeCode);
    }
}
