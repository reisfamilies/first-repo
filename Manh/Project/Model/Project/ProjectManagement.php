<?php

namespace Mageplus\Project\Model\Project;

use Mageplus\Project\Api\Data\ProjectInterface;
use Mageplus\Project\Model\GalleryFactory;
use Mageplus\Project\Model\ResourceModel\Gallery as GalleryResource;
use Mageplus\Project\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Mageplus\Project\Model\ResourceModel\Product as ProductResource;
use Mageplus\Project\Model\ResourceModel\ProjectTag as TagResource;

class ProjectManagement
{
    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var TagResource
     */
    private $tagResource;

    /**
     * @var GalleryFactory
     */
    private $galleryFactory;

    /**
     * @var GalleryResource
     */
    private $galleryResource;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * ProjectManagement Constructor.
     *
     * @param ProductResource $productResource
     * @param TagResource $tagResource
     * @param GalleryFactory $galleryFactory
     * @param GalleryResource $galleryResource
     * @param GalleryCollection $galleryCollection
     */
    public function __construct(
        ProductResource   $productResource,
        TagResource       $tagResource,
        GalleryFactory    $galleryFactory,
        GalleryResource   $galleryResource,
        GalleryCollection $galleryCollection
    )
    {
        $this->productResource = $productResource;
        $this->tagResource = $tagResource;
        $this->galleryFactory = $galleryFactory;
        $this->galleryResource = $galleryResource;
        $this->galleryCollection = $galleryCollection;
    }

    /**
     * @param ProjectInterface $project
     * @return null
     */
    public function processProject(ProjectInterface $project)
    {
        return $this->processProjectProducts($project)->processProjectTags($project)->processImageGallery($project);
    }

    private function processImageGallery(ProjectInterface $project)
    {
        $data = $project->getData();
        $projectId = $data['project_id'];
        $allImages = $this->galleryCollection->getImagesByProjectId($projectId);
        $baseImgName = $data['base_img'] ?? '';

        if (!isset($data['gallery_image'])) {
            foreach ($allImages as $image) {
                $this->galleryResource->delete($image);
            }
            return;
        }
        $galleryImages = $data['gallery_image'];
        $imagesOfProject = [];
        $isImport = false;

        foreach ($allImages as $image) {
            $imagesOfProject[$image->getData('image_name')] = $image;
        }

        foreach ($galleryImages as $galleryImage) {
            $isImageNew = isset($galleryImage['tmp_name']);
            if (array_key_exists($galleryImage['name'], $imagesOfProject)) {
                unset($imagesOfProject[$galleryImage['name']]);

                if ($isImageNew) {
                    continue;
                }
            }
            if ($isImageNew && isset($galleryImage['name'])) {
                $isImport = true;
                $newImage = $this->galleryFactory->create();
                $newImage->addData(
                    [
                        'project_id' => $projectId,
                        'image_name' => $galleryImage['name'],
                        'is_base' => $baseImgName === $galleryImage['name'],
                        'is_object_new' => true
                    ]
                );
                $this->galleryResource->save($newImage);
            }
        }

        if (!empty($galleryImages) && !$isImport) {
            foreach ($imagesOfProject as $imageToDelete) {
                $this->galleryResource->delete($imageToDelete);
            }
        }

        $baseImg = $this->galleryCollection->getByNameAndProjectId($projectId, $baseImgName);

        if (!empty($baseImg->getData())) {
            foreach ($allImages as $image) {
                if ($image->getData('is_base')) {
                    $image->addData(['is_base' => false]);
                    $this->galleryResource->save($image);
                }
            }
            $baseImg->addData(['is_base' => true]);
            $this->galleryResource->save($baseImg);
        }
    }

    private function processProjectTags(ProjectInterface $project)
    {
        $tagIds = array_unique($project->getTagIds());
        $origTagIds = $this->tagResource->getTagIdsByProjectId($project->getProjectId());

        if ($origTagIds != $tagIds) {
            $oldProductIds = array_diff($origTagIds, $tagIds);
            if ($oldProductIds) {
                $this->tagResource->unassignProject($oldProductIds);
            }

            $newProducts = array_diff($tagIds, $oldProductIds);
            if ($newProducts) {
                $this->tagResource->assignProject($project->getProjectId(), $newProducts);
            }
        }

        return $this;
    }

    private function processProjectProducts(ProjectInterface $project)
    {
        $productIds = array_unique($project->getProductIds());
        $origProductIds = $this->productResource->getProductIdsByProjectId($project->getProjectId());

        if ($origProductIds != $productIds) {
            $oldProductIds = array_diff($origProductIds, $productIds);
            if ($oldProductIds) {
                $this->productResource->unassignProject($oldProductIds);
            }

            $newProducts = array_diff($productIds, $oldProductIds);
            if ($newProducts) {
                $this->productResource->assignProject($project->getProjectId(), $newProducts);
            }
        }

        return $this;
    }

    /**
     * @param ProjectInterface $project
     * @return $this
     */
    public function processProjectDelete(ProjectInterface $project)
    {
        $projectId = $project->getProjectId();
        $tagIds = array_unique($project->getTagIds());
        $productIds = array_unique($project->getProductIds());
        if (!empty($tagIds)) {
            $this->tagResource->removeProjectTag($projectId);
        }

        if (!empty($productIds)) {
            $this->productResource->removeProjectProduct($projectId);
        }

        $this->galleryResource->removeProjectGallery($projectId);

        return $this;
    }
}
