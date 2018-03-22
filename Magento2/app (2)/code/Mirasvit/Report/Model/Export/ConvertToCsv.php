<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.1.15-beta3
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Report\Model\Export;

use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;

class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    /**
     * @param Filesystem       $filesystem
     * @param Filter           $filter
     * @param MetadataProvider $metadataProvider
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider
    ) {
        parent::__construct($filesystem, $filter, $metadataProvider);
    }
}
