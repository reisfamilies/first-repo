<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Manh\AdminUser\Model\ResourceModel\User;

/**
 * Admin user collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\User\Model\ResourceModel\User\Collection
{

    /**
     * Collection Init Select
     *
     * @return $this
     * @since 101.1.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['apd' => $this->getTable('amasty_perm_dealer')],
            'apd.user_id = main_table.user_id',
            ['bcc_email' => "emails"]
        );
    }

    private function convertValues($booking)
    {
        $fileName = $booking->getQrCode();
        $image = [];
//        if ($this->getFileInfo()->isExist($fileName)) {
//            $stat = $this->getFileInfo()->getStat($booking['qr_code']);
//            $mime = $this->getFileInfo()->getMimeType($booking['qr_code']);
        $image[0]['name'] = 'asd';
        $image[0]['url'] = $booking['qr_code'];
        $image[0]['size'] = 1560;
//        }
        $booking->setQrCode($image);

        return $booking;
    }

    function getImageSizeInBytes($url) {
        $headers = get_headers($url, 1);

        if (isset($headers["Content-Length"])) {
            return (int) $headers["Content-Length"]; // Return size in bytes
        }

        return false; // Failed to retrieve size
    }
}
