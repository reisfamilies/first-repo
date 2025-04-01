<?php

namespace Mageplus\Project\Block\Adminhtml\Import;

use Exception;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;

class Project extends Container
{
    /**
     * @var Registry|null
     */
    protected $_coreResgistry = null;

    /**
     * @var string
     */
    protected $_mode = '';

    protected $storeManager;

    /**
     * @var Repository
     */
    protected $assetRepository;

    /**
     * Project Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param Repository $assetRepository
     * @param array $data
     */
    public function __construct(
        Context               $context,
        Registry              $registry,
        StoreManagerInterface $storeManager,
        Repository            $assetRepository,
        array                 $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreResgistry = $registry;
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @return Phrase|string
     */
    public function getHeaderText()
    {
        return __('Import Project');
    }

    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function getDefaultCsvPath()
    {
        $fileId = 'Mageplus_Project::files/alpha_project.csv';
        $params = [
            'area' => 'frontend'
        ];
        try {
            return $this->assetRepository->getUrlWithParams($fileId, $params);
        } catch (Exception $e) {
            return null;
        }
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'Mageplus_Project';
        $this->_controller = 'adminhtml_import';
        $this->setTemplate("Mageplus_Project::import/project.phtml");
        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');
    }
}

