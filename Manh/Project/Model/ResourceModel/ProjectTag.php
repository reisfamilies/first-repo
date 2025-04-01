<?php

namespace Mageplus\Project\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mageplus\Project\Api\Data\ProjectTagInterface;

class ProjectTag extends AbstractDb
{
    public function getTagIdsByProjectId($projectId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['main_table' => $this->getMainTable()], ['tag_id'])
            ->where('main_table.project_id = ?', $projectId);

        return $this->getConnection()->fetchCol($select);
    }

    public function unassignProject($tagIds = [])
    {
        if (!empty($tagIds)) {
            $this->getConnection()->update(
                $this->getTable(ProjectTagInterface::TABLE_NAME),
                [ProjectTagInterface::PROJECT_ID => null],
                ['tag_id in (?)' => $tagIds]
            );
        }

        return $this;
    }

    public function assignProject($projectId, $tagIds = [])
    {
        $insertData = [];
        foreach ($tagIds as $productId) {
            $data = [
                ProjectTagInterface::TAG_ID => $productId,
                ProjectTagInterface::PROJECT_ID => $projectId,
            ];
            $insertData[] = $data;
        }

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $insertData);

        return $this;
    }

    public function removeProjectTag($projectId)
    {
        if (!empty($projectId)) {
            $this->getConnection()->delete($this->getMainTable(), ['project_id = ?' => $projectId]);
        }

        return $this;
    }

    protected function _construct()
    {
        $this->_init(ProjectTagInterface::TABLE_NAME, ProjectTagInterface::TAG_ID);
    }
}
