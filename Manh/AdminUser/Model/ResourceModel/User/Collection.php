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
}
