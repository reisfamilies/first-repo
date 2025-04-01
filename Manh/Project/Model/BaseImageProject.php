<?php

namespace Mageplus\Project\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplus\Project\Model\ResourceModel\Gallery\Collection as GalleryCollection;

class BaseImageProject
{
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * BaseImageProject Constructor.
     *
     * @param ImageProcessor $imageProcessor
     * @param GalleryCollection $galleryCollection
     */
    public function __construct(
        ImageProcessor    $imageProcessor,
        GalleryCollection $galleryCollection
    )
    {
        $this->imageProcessor = $imageProcessor;
        $this->galleryCollection = $galleryCollection;
    }

    /**
     * @param Project $project
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMainImageUrl($project)
    {
        $baseImage = $project->getMainImageName();

        if ($baseImage) {
            return $this->imageProcessor->getImageUrl(
                [ImageProcessor::PROJECT_GALLERY_MEDIA_PATH, $baseImage]
            );
        }

        return '';
    }

    /**
     * @param Project $project
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws FileSystemException
     */
    public function getImageUrl($project)
    {
        $galleryImages = $this->galleryCollection->getImagesByProjectId($project->getProjectId());
        foreach ($galleryImages as $image) {
            $imgName = $image->getData('image_name');
            $imgUrlParams = [ImageProcessor::PROJECT_GALLERY_MEDIA_PATH, $imgName];
            if ($imgName && $this->imageProcessor->isExist($imgUrlParams)) {
                return $this->imageProcessor->getImageUrl(
                    [ImageProcessor::PROJECT_GALLERY_MEDIA_PATH, $imgName]
                );
            }
            break;
        }

        return '';
    }
}
