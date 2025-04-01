<?php

namespace Mageplus\Project\Model;

use Exception;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image;
use Magento\Framework\ImageFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ImageProcessor
{
    /**
     * Locator area inside media folder
     */
    public const PROJECT_MEDIA_PATH = 'mageplus/project';

    /**
     * Locator temporary area inside media folder
     */
    public const PROJECT_MEDIA_TMP_PATH = 'mageplus/project/tmp';

    public const PROJECT_MEDIA_IMPORT_TMP_PATH = 'mageplus/project/import/tmp';

    /**
     * Gallery area inside media folder
     */
    public const PROJECT_GALLERY_MEDIA_PATH = 'mageplus/project/gallery';

    /**
     * Gallery temporary area inside media folder
     */
    public const PROJECT_GALLERY_MEDIA_TMP_PATH = 'mageplus/project/gallery/tmp';

    /**
     * Type image option marker_img
     */
    public const MARKER_IMAGE_TYPE = 'marker_img';


    /**
     * Type image option gallery_image
     */
    public const GALLERY_IMAGE_TYPE = 'gallery_image';

    /**
     * @var array
     */
    protected $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ImageProcessor Constructor.
     *
     * @param Filesystem $filesystem
     * @param ImageUploader $imageUploader
     * @param ImageFactory $imageFactory
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem            $filesystem,
        ImageUploader         $imageUploader,
        ImageFactory          $imageFactory,
        StoreManagerInterface $storeManager,
        ManagerInterface      $messageManager,
        LoggerInterface       $logger
    )
    {
        $this->filesystem = $filesystem;
        $this->imageUploader = $imageUploader;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws FileSystemException
     */
    public function getImageSize($params)
    {
        $fileHandler = $this->getFileMediaPath($params);

        return $fileHandler['size'] ?? 0;
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws FileSystemException
     */
    private function getFileMediaPath($params)
    {
        return $this->getMediaDirectory()->stat(implode(DIRECTORY_SEPARATOR, $params));
    }

    /**
     * @return WriteInterface
     * @throws FileSystemException
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }

    /**
     * @param $params
     * @return bool
     * @throws FileSystemException
     */
    public function isExist($params)
    {

        return $this->getMediaDirectory()->isExist(implode(DIRECTORY_SEPARATOR, $params));
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrl($params = [])
    {
        return $this->getMediaUrl() . implode(DIRECTORY_SEPARATOR, $params);
    }

    /**
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Move file from temporary directory
     *
     * @param string $imageName
     * @param string $imageType
     * @param $projectId
     * @throws LocalizedException
     */
    public function processImage($imageName, $imageType, $projectId)
    {
        $this->setBasePaths($imageType);
        $this->imageUploader->moveFileFromTmp($imageName, true);

        $filename = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($imageName));
        try {
            $this->prepareImage($filename, $imageType);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $this->messageManager->addErrorMessage(
                __($errorMessage)
            );
            $this->logger->critical($e);
        }
    }

    /**
     * @param string $imageType
     */
    public function setBasePaths($imageType)
    {
        $tmpLocationId = '';
        $tmpPath = ImageProcessor::PROJECT_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $tmpLocationId;
        $this->imageUploader->setBaseTmpPath(
            $tmpPath
        );
        if ($imageType == ImageProcessor::GALLERY_IMAGE_TYPE) {
            $this->imageUploader->setBasePath(
                ImageProcessor::PROJECT_GALLERY_MEDIA_PATH
            );
        }
    }

    /**
     * @param string $imageName
     *
     * @return string
     */
    public function getImageRelativePath($imageName)
    {
        return $this->imageUploader->getBasePath() . DIRECTORY_SEPARATOR . $imageName;
    }

    /**
     * @param string $filename
     * @param string $imageType
     * @param bool $needResize
     */
    public function prepareImage($filename, $imageType, $needResize = false)
    {
        /** @var Image $imageProcessor */
        $imageProcessor = $this->imageFactory->create(['fileName' => $filename]);
        $imageProcessor->keepAspectRatio(true);
        $imageProcessor->keepFrame(true);
        $imageProcessor->keepTransparency(true);
        if ($imageType == self::MARKER_IMAGE_TYPE || $needResize) {
            $imageProcessor->resize(27, 43);
        }
        $imageProcessor->save();
    }

    public function getMediaDirectoryWithPath($imageRelativePath)
    {
        return $this->getMediaDirectory()->getAbsolutePath($imageRelativePath);
    }

    /**
     * @param string $imageName
     * @throws FileSystemException
     */
    public function deleteImage($imageName)
    {
        if ($imageName && strpos($imageName, '.') !== false) {
            $this->getMediaDirectory()->delete(
                $this->getImageRelativePath($imageName)
            );
        }
    }
}
