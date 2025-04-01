<?php

namespace Mageplus\Project\Block;

use Exception;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Mageplus\Project\Model\BaseImageProject;
use Mageplus\Project\Model\ConfigProvider;
use Mageplus\Project\Model\CountryDataProvider;
use Mageplus\Project\Model\Project as ProjectModel;
use Mageplus\Project\Model\ResourceModel\Product as ProductResource;
use Mageplus\Project\Model\ResourceModel\Project\Collection as ProjectCollection;
use Mageplus\Project\Model\ResourceModel\Project\CollectionFactory;
use Mageplus\Project\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Zend_Db_Select_Exception;

class Project extends Template
{
    /**
     * @var ConfigProvider
     */
    public $configProvider;
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Mageplus_Project::page/project.phtml';
    private array $category;
    /**
     * @var bool
     */
    private $isProjectCollectionPrepared = false;
    /**
     * @var ProjectCollection
     */
    private $projectCollection;
    /**
     * @var CollectionFactory
     */
    private $projectCollectionFactory;

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    private $pager;

    /**
     * @var BaseImageProject
     */
    private $baseImageProject;

    /**
     * @var CountryDataProvider
     */
    private $countryDataProvider;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var TagCollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var CategoryListInterface
     */
    private $categoryList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * Project Constructor.
     *
     * @param Context $context
     * @param ConfigProvider $configProvider
     * @param CollectionFactory $projectCollectionFactory
     * @param BaseImageProject $baseImageProject
     * @param CountryDataProvider $countryDataProvider
     * @param ProductCollectionFactory $productCollectionFactory
     * @param TagCollectionFactory $tagCollectionFactory
     * @param EncoderInterface $jsonEncoder
     * @param CategoryListInterface $categoryList
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductResource $productResource
     * @param array $data
     */
    public function __construct(
        Context                  $context,
        ConfigProvider           $configProvider,
        CollectionFactory        $projectCollectionFactory,
        BaseImageProject         $baseImageProject,
        CountryDataProvider      $countryDataProvider,
        ProductCollectionFactory $productCollectionFactory,
        TagCollectionFactory     $tagCollectionFactory,
        EncoderInterface         $jsonEncoder,
        CategoryListInterface    $categoryList,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        ProductResource          $productResource,
        array                    $data = []
    )
    {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->projectCollectionFactory = $projectCollectionFactory;
        $this->baseImageProject = $baseImageProject;
        $this->countryDataProvider = $countryDataProvider;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productResource = $productResource;
    }

    public function isEnableProject()
    {
        return $this->configProvider->getEnablePage();
    }

    public function getUrlProjectPage()
    {
        return $this->configProvider->getUrl();
    }

    /**
     * @param $project
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProjectAddressHtml($project)
    {
        $address = [];
        if (!empty($project->getStreet())) {
            $address[] = $project->getStreet();
        }

        if (!empty($project->getCity())) {
            $address[] = $project->getCity();
        }

        if ($project->getRegion()) {
            $address[] = $project->getRegion();
        }

        if (!empty($project->getCountryId())) {
            $address[] = $this->countryDataProvider->getCountryName($project->getCountryId());
        }

        $completionYear = '';
        if (!empty($project->getCompletionYear())) {
            $completionYear = $project->getCompletionYear();
        }

        return implode(', ', $address) . ' - ' . $completionYear;
    }

    public function getProductsCollectionByProject($project)
    {
        $productCollection = $this->productCollectionFactory->create()->addAttributeToSelect('sku_label_for_projects');
        if (!empty($project->getProductIds())) {
            $productCollection->addFieldToFilter('entity_id', ['in' => $project->getProductIds()]);
        } else {
            return [];
        }

        return $productCollection->getItems();
    }

    public function getTagsCollectionByProject($project)
    {
        $tagCollection = $this->tagCollectionFactory->create();
        if (!empty($project->getTagIds())) {
            $tagCollection->addFieldToFilter('tag_id', ['in' => $project->getTagIds()]);
        } else {
            return [];
        }

        return $tagCollection->getItems();
    }

    /**
     * Return main image url
     *
     * @param $project
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProjectImage($project)
    {
        $imageUrl = $this->baseImageProject->getMainImageUrl($project);
        if (empty($imageUrl)) {
            $imageUrl = $this->baseImageProject->getImageUrl($project);
        }

        return $imageUrl;
    }

    public function getProjectMediaUrl()
    {
        $storeUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $storeUrl . 'mageplus/project/';
    }

    public function getQueryString()
    {
        if ($this->getRequest()->getParam('product') !== null) {
            return '?' . http_build_query($this->getRequest()->getParams());
        }
        return '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [ProjectModel::CACHE_TAG];
    }

    /**
     * @return array|string[]
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        if ($this->getLimit()) {
            $cacheKeyInfo = array_merge(
                $cacheKeyInfo,
                [implode('-', $this->getClearProjectCollection()->getIdsOnPage())]
            );
        }

        return $cacheKeyInfo;
    }

    /**
     * Get specified products limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        $limit = $this->getRequest()->getParam('project_list_limit');
        if ($limit) {
            return $limit;
        }

        $limits = $this->getAvailableLimit();
        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        return $defaultLimit;
    }

    /**
     * Retrieve available limits for current view mode
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        return $this->configProvider->getAvailableLimit((string)$this->getCurrentMode());
    }

    /**
     * Retrieve current View mode
     *
     * @return string
     */
    public function getCurrentMode()
    {
        $mode = (string)$this->getRequest()->getParam('project_list_mode');
        if (empty($mode)) {
            return $this->configProvider->getDefaultViewMode();
        }

        return $mode;
    }

    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        return $this->configProvider->getDefaultLimitPerPageValue($this->getCurrentMode());
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getClearProjectCollection(): ProjectCollection
    {
        if (!$this->projectCollection) {
            $this->projectCollection = $this->projectCollectionFactory->create();
            $this->projectCollection->applyDefaultFilters();

            $this->projectCollection->setCurPage((int)$this->getRequest()->getParam('p', 1));
            $this->projectCollection->setPageSize($this->getLimit());
        }

        return $this->projectCollection;
    }

    public function getJsonProjects()
    {
        $projectArray = [];
        $projectArray['items'] = [];
        $projectArray['totalRecords'] = count($projectArray['items']);
        $store = $this->_storeManager->getStore(true)->getId();
        $projectArray['currentStoreId'] = $store;

        //remove double spaces
        $projectArray['block'] = $this->compressHtml($this->getProjectBlockHtml());

        return $this->jsonEncoder->encode($projectArray);
    }

    public function compressHtml($html)
    {
        return preg_replace(
            '#(?ix)(?>[^\S ]\s*|\s{2,})#', //remove break lines
            ' ',
            preg_replace('/<!--(?!\s*ko\s|\s*\/ko)[^>]*-->/', '', $html) //remove html comments
        );
    }

    /**
     * @return string
     */
    public function getProjectBlockHtml()
    {
        $originalTemplate = $this->getTemplate();
        $html = $this->setTemplate('Mageplus_Project::project/list.phtml')->toHtml();
        $this->setTemplate($originalTemplate);

        return $html;
    }

    public function setProjectCollectionPrepared($isProjectCollectionPrepared)
    {
        $this->isProjectCollectionPrepared = $isProjectCollectionPrepared;
        return $this;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getRegions()
    {
        $region = [];
        $regionName = [];
        $projectCollection = $this->projectCollectionFactory->create();
        $projectCollection->applyDefaultFilters();
        foreach ($projectCollection as $project) {
            $regionValue = $project->getRegion();
            if (!in_array($regionValue, $regionName) && !empty($regionValue)) {
                $region[] = [
                    'region_name' => $regionValue,
                    'url' => $this->getUrl('mpproject/index/filter', ['region' => $regionValue]),
                ];
            }
            $regionName[] = $regionValue;
        }

        usort($region, function ($a, $b) {
            return strcmp($a['region_name'], $b['region_name']);
        });

        return $region;
    }

    /**
     * @throws NoSuchEntityException|Zend_Db_Select_Exception
     */
    public function getProductSkus()
    {
        $productSkus = [];
        $projectIdsByProductId = [];
        $projectIdsByAdditionSku = [];
        $additionSkus = [];
        $excludeProductSkus = explode(',', $this->configProvider->getExcludeProducts() ?? '');
        $projectCollection = $this->projectCollectionFactory->create();
        $projectCollection->applyDefaultFilters();
        foreach ($projectCollection as $project) {
            if (!empty($project->getProductIds())) {
                foreach ($project->getProductIds() as $productId) {
                    $productIds[] = $productId;
                    $projectIdsByProductId[$productId][] = $project->getProjectId();
                }
            }

            if (!empty($project->getAdditionSku())) {
                foreach (explode(',', $project->getAdditionSku()) as $additionSku) {
                    if (!empty($additionSku) && !in_array($additionSku, $excludeProductSkus)) {
                        $additionSkus[] = $additionSku;
                        $projectIdsByAdditionSku[$additionSku][] = $project->getProjectId();
                    }
                }
            }
        }

        if (!empty($productIds)) {
            $productIds = array_unique($productIds);
            $productCollection = $this->productCollectionFactory->create()->addAttributeToSelect('sku_label_for_projects');
            $productCollection = $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            foreach ($productCollection as $product) {
                $sku =  !empty($product->getCustomAttribute('sku_label_for_projects')) ? $product->getCustomAttribute('sku_label_for_projects')->getValue() : $product->getSku();
                if (!in_array($sku, $excludeProductSkus)) {
                    $productSkus[] = [
                        'project_ids' => !empty($projectIdsByProductId[$product->getId()]) ? implode(',', $projectIdsByProductId[$product->getId()]) : '',
                        'sku_label' => $sku
                    ];
                }
            }
        }

        if (!empty($additionSkus)) {
            $additionSkus = array_unique($additionSkus);
            foreach ($additionSkus as $skuLabel) {
                $productSkus[] = [
                    'project_ids' => !empty($projectIdsByAdditionSku[$skuLabel]) ? implode(',', $projectIdsByAdditionSku[$skuLabel]) : '',
                    'sku_label' => $skuLabel ?? ''
                ];
            }
        }

        return $productSkus;
    }

    /**
     * @return ProjectCollection
     * @throws NoSuchEntityException
     * @throws Zend_Db_Select_Exception
     */
    public function getProjectCollection()
    {
        if (!$this->isProjectCollectionPrepared) {
            $this->getClearProjectCollection(); // init $this->projectCollection
            $this->projectCollection->joinMainImage();
            if (!empty($this->getRequest()->getParam('region'))) {
                $this->projectCollection->applyRegionFilter($this->getRequest()->getParam('region'));
            }
            if (!empty($this->getRequest()->getParam('text'))) {
                $this->projectCollection->applyTextFilter($this->getRequest()->getParam('text'));
            }

            if (!empty($this->getRequest()->getParam('project_ids'))) {
                $projectIds = base64_decode($this->getRequest()->getParam('project_ids'));
                $this->projectCollection->applyProjectsFilter(explode(',', $projectIds));
            }
            $this->projectCollection->setFlag(ProjectCollection::IS_NEED_TO_COLLECT_PROJECT_DATA, true);
            $this->isProjectCollectionPrepared = true;
        }

        return $this->projectCollection;
    }

    /**
     * @param $categoryId
     * @return string|void
     */
    public function getProjectIdsByCategoryId($categoryId)
    {
        if (!empty($this->category[$categoryId])) {
            return implode('_', $this->category[$categoryId]);
        }
    }

    /**
     * @return CategoryInterface[]
     * @throws Exception
     */
    public function getCategoriesCollection()
    {
        $categoryList = [];
        try {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $categoryList = $this->categoryList->getList($searchCriteria);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $categoryList->getItems();
    }

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * Check if limit is current used in toolbar.
     *
     * @param int $limit
     * @return bool
     */
    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    /**
     * Retrieve available view modes
     *
     * @return array
     */
    public function getModes()
    {
        return $this->configProvider->getAvailableViewMode();
    }

    /**
     * Compare defined view mode with current active mode
     *
     * @param string $mode
     * @return bool
     */
    public function isModeActive($mode)
    {
        return $this->getCurrentMode() == $mode;
    }

    /**
     * Add metadata to page header
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Zend_Db_Select_Exception
     */
    protected function _prepareLayout()
    {
        if ($this->getNameInLayout() && strpos($this->getNameInLayout(), 'link') === false
            && strpos($this->getNameInLayout(), 'jsinit') === false
        ) {
            $this->getPagerHtml();

            if ($this->pager && !$this->pager->isFirstPage()) {
                $this->addPrevNext(
                    $this->getUrl($this->getUrlProjectPage(), ['p' => $this->pager->getCurrentPage() - 1]),
                    ['rel' => 'prev']
                );
            }
            if ($this->pager && $this->pager->getCurrentPage() < $this->pager->getLastPageNum()) {
                $this->addPrevNext(
                    $this->getUrl($this->getUrlProjectPage(), ['p' => $this->pager->getCurrentPage() + 1]),
                    ['rel' => 'next']
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Return Pager for locator page
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Zend_Db_Select_Exception
     */
    public function getPagerHtml()
    {
        if ($this->getLayout()->getBlock('mageplus.project.paper')) {
            $this->pager = $this->getLayout()->getBlock('mageplus.project.paper');

            return $this->pager->toHtml();
        }
        if (!$this->pager) {
            $this->pager = $this->getLayout()->createBlock(
                Pager::class,
                'mageplus.project.paper'
            );

            if ($this->pager) {
                $this->pager->setUseContainer(
                    false
                )->setShowPerPage(
                    false
                )->setShowAmounts(
                    false
                )->setFrameLength(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame',
                        ScopeInterface::SCOPE_STORE
                    )
                )->setJump(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame_skip',
                        ScopeInterface::SCOPE_STORE
                    )
                )->setLimit(
                    $this->getLimit()
                )->setCollection(
                    $this->getProjectCollection()
                )->setTemplate(
                    'Mageplus_Project::pager.phtml'
                );

                return $this->pager->toHtml();
            }
        }

        return '';
    }

    /**
     * Add prev/next pages
     *
     * @param string $url
     * @param array $attributes
     *
     */
    protected function addPrevNext($url, $attributes)
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'link_rel',
            ['attributes' => $attributes]
        );
    }
}
