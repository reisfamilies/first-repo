<?php

namespace Mageplus\Project\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Mageplus\Project\Controller\Adminhtml\Project\AbstractProject;
use Mageplus\Project\Model\Backend\Project\Registry as ProjectRegistry;
use Mageplus\Project\Model\ImageProcessor;
use Mageplus\Project\Model\Repository\ProjectRepository;

class Index extends AbstractProject
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Index Constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ProjectRepository $projectRepository
     * @param ProjectRegistry $projectRegistry
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context           $context,
        ForwardFactory    $resultForwardFactory,
        RedirectFactory   $resultRedirectFactory,
        ProjectRepository $projectRepository,
        ProjectRegistry   $projectRegistry,
        Filesystem        $filesystem
    )
    {
        parent::__construct($context, $resultForwardFactory, $resultRedirectFactory, $projectRepository, $projectRegistry);
        $this->filesystem = $filesystem;
    }

    /**
     * @return ResultInterface
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute()
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($mediaDirectory->isDirectory(ImageProcessor::PROJECT_MEDIA_IMPORT_TMP_PATH)) {
            foreach ($mediaDirectory->read(ImageProcessor::PROJECT_MEDIA_IMPORT_TMP_PATH) as $item) {
                try {
                    $mediaDirectory->delete($item);
                } catch (Exception $e) {
                    throw new LocalizedException(__('Couldn\'t clear `%1` folder', ImageProcessor::PROJECT_MEDIA_IMPORT_TMP_PATH));
                }
            }
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(__('Upload Gallery Images'));

        return $resultPage;
    }
}
