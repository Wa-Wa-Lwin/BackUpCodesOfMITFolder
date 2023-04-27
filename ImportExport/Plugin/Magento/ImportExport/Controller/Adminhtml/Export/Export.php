<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\ImportExport\Plugin\Magento\ImportExport\Controller\Adminhtml\Export;

class Export
{

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $dir;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->dir = $dir;
    }

    public function afterExport(
        \Magento\ImportExport\Controller\Adminhtml\Export\Export $subject,
        $result
    ) {
        $command = 'php bin/magento cron:run';
        exec('cd ' . $this->dir->getRoot() . ' && ' . $command, $a, $b);
        return $result;
    }
}
