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


namespace Mirasvit\Report\Model\Config\Map;

use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Loads reports configuration from XML file by merging them together
 */
class Reader extends Filesystem
{
    /**
     * Mapping XML name nodes
     *
     * @var array
     */
    protected $_idAttributes = ['/config/table' => 'name', '/config/eavTable' => 'name', '/config/relation' => 'name'];

    /**
     * Construct the FileSystem Reader Class
     *
     * @param FileResolverInterface    $fileResolver
     * @param Converter                $converter
     * @param SchemaLocator            $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string                   $fileName
     * @param array                    $idAttributes
     * @param string                   $domDocumentClass
     * @param string                   $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = 'mreport.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
