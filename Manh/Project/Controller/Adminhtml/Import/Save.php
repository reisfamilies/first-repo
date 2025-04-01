<?php

namespace Mageplus\Project\Controller\Adminhtml\Import;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Io\File;
use Mageplus\Project\Model\ImageProcessor;

class Save extends Action
{
    /**
     * @var File
     */
    protected File $file;

    /**
     * Uploader
     */
    private $imageProcessor;

    /**
     * Save Constructor.
     *
     * @param Context $context
     * @param File $file
     * @param ImageProcessor $imageProcessor
     */
    public function __construct(
        Context        $context,
        File           $file,
        ImageProcessor $imageProcessor
    )
    {
        parent::__construct($context);
        $this->file = $file;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $imagesData = $this->getRequest()->getPostValue();
        if (!empty($imagesData)) {
            try {
                $results = [];
                if (isset($imagesData['gallery_image'])) {
                    $results = $this->saveImages($imagesData['gallery_image']);
                }

                if (count($results) != 0) {
                    $this->messageManager->addSuccessMessage(str_replace(' ', ', ', implode(" ", $results)) . ' already exist!. These image will override old image.');
                } else {
                    $this->messageManager->addSuccessMessage(__('You uploaded images.'));
                }

                return $resultRedirect->setPath('*/*/index');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Can not save these images. Please try again!'));
                return $resultRedirect->setPath('*/*/index');
            }
        }
    }

    /**
     * @param $images
     * @return array
     */
    public function saveImages($images)
    {
        $imagesError = [];
        foreach ($images as $image) {
            $imageAbsoluteDir = $this->imageProcessor->getMediaDirectoryWithPath(ImageProcessor::PROJECT_MEDIA_IMPORT_TMP_PATH . '/' . $image['name']);
            $destination = $this->imageProcessor->getMediaDirectoryWithPath(ImageProcessor::PROJECT_GALLERY_MEDIA_PATH . '/' . $image['name']);

            if (!$this->file->fileExists($imageAbsoluteDir, false)) {
                $this->file->mkdir($imageAbsoluteDir, 0775, true);
            }

            if ($this->file->fileExists($destination)) {
                $imagesError[] = $image['name'];
            }

            $this->file->mv($imageAbsoluteDir, $destination);
        }

        return $imagesError;
    }
}
