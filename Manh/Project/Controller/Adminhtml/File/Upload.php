<?php

namespace Mageplus\Project\Controller\Adminhtml\File;

use Exception;
use Magento\Backend\App\Action;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Mageplus\Project\Model\ImageProcessor;

/**
 * Class Upload
 */
class Upload extends Action
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    public function __construct(
        Action\Context $context,
        ImageUploader  $imageUploader
    )
    {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Upload file controller action.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $imageType = 'gallery_image';
            $this->imageUploader->setBaseTmpPath(
                ImageProcessor::PROJECT_MEDIA_TMP_PATH
            );
            $result = $this->imageUploader->saveFileToTmpDir($imageType);

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
