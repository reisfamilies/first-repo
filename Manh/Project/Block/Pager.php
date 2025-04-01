<?php

namespace Mageplus\Project\Block;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * Return correct URL for ajax request
     *
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $ajaxUrl = $this->_urlBuilder->getUrl('mpproject/index/index');
        if ($query = $this->getRequest()->getParam('query')) {
            $params['query'] = $query;
        }

        return $ajaxUrl . '?' . http_build_query($params);
    }
}
