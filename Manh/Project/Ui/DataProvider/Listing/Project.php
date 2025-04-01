<?php

namespace Mageplus\Project\Ui\DataProvider\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

/**
 * DataProvider component.
 */
class Project extends DataProvider
{
    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $labelItemsData = array_reduce($searchResult->getItems(), function (array $carry, DataObject $item): array {
            $carry[] = $item->getData();

            return $carry;
        }, []);

        return [
            'items' => $labelItemsData,
            'totalRecords' => $searchResult->getTotalCount()
        ];
    }
}
