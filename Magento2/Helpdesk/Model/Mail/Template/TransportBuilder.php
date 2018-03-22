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
 * @package   mirasvit/module-helpdesk
 * @version   1.1.25
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Helpdesk\Model\Mail\Template;

/**
 * Extended core class for implement attachments functionality
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Creates a \Zend_Mime_Part attachment
     * @param  string $body
     * @param  string $mimeType
     * @param  string $disposition
     * @param  string $encoding
     * @param  string $filename OPTIONAL A filename for the attachment
     * @return $this
     */
    public function createAttachment(
        $body,
        $mimeType = \Zend_Mime::TYPE_OCTETSTREAM,
        $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding = \Zend_Mime::ENCODING_BASE64,
        $filename = null
    ) {
        $this->message->createAttachment($body, $mimeType, $disposition, $encoding, $filename);

        return $this;
    }
}