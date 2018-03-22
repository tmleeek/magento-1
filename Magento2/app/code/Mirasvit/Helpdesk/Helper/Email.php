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



namespace Mirasvit\Helpdesk\Helper;

class Email
{
    /**
     * @var \Mirasvit\Helpdesk\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Helpdesk\Helper\StringUtil
     */
    protected $helpdeskString;
    /**
     * @var \Mirasvit\Helpdesk\Helper\Process
     */
    protected $helpdeskProcess;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $string;

    /**
     * @param \Mirasvit\Helpdesk\Model\Config      $config
     * @param \Mirasvit\Helpdesk\Helper\StringUtil $helpdeskString
     * @param \Mirasvit\Helpdesk\Helper\Process    $helpdeskProcess
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config $config,
        \Mirasvit\Helpdesk\Helper\StringUtil $helpdeskString,
        \Mirasvit\Helpdesk\Helper\Process $helpdeskProcess,
        \Magento\Framework\Stdlib\StringUtils $string
    ) {
        $this->config = $config;
        $this->helpdeskString = $helpdeskString;
        $this->helpdeskProcess = $helpdeskProcess;
        $this->string = $string;
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Email $email
     *
     * @return bool|\Mirasvit\Helpdesk\Model\Ticket
     */
    public function processEmail($email)
    {
        // $code = false;
        // if ($this->config->getNotificationIsShowCode()) {
        //get object by code from subject
        $code = $this->helpdeskString->getTicketCodeFromSubject($email->getSubject());
        // }

        if (!$code) {
            $code = $this->helpdeskString->getTicketCodeFromBody($email->getBody());
        }

        //        if (strpos($code, 'RMA') === 0 && $this->getConfig()->isActiveRma()) {
        //            return $this->rmaProcess->processEmail($email, $code);
        //        } else {
        return $this->helpdeskProcess->processEmail($email, $code);
        //        }
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Ticket $ticket
     * @param string                          $subject
     *
     * @return string
     */
    public function getEmailSubject($ticket, $subject = '')
    {
        $result = '';
        if ($this->config->getNotificationIsShowCode()) {
            $result = "[#{$ticket->getCode()}] ";
        }
        if ($subject) {
            $result .= "$subject - ";
        }
        $result .= $ticket->getSubject();

        return $result;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getHiddenCode($code)
    {
        return
            "<span style='color:#FFFFFF;font-size:5px;margin:0px;padding:0px;'>Message-Id:--#{$code}--</span>";
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return '##- ' . __('please type your reply above this line') . ' -##';
    }

    /**
     * @return string
     */
    public function getHiddenSeparator()
    {
        return "<span style='color:#FFFFFF;font-size:5px;margin:0px;padding:0px;'>" . $this->getSeparator() . '</span>';
    }

    /**
     * @param string $preheaderText
     * @return string
     */
    public function getPreheaderText($preheaderText)
    {
        $maxLen = 150;
        $len = $this->string->strlen($preheaderText);
        if ($len < $maxLen) {
            $preheaderText = str_pad($preheaderText, $maxLen, ' ');
        } else {
            $preheaderText = $this->string->substr($preheaderText, 0, $maxLen);
        }
        return "<span style='opacity:0; color:transparent;font-size:5px;margin:0px;padding:0px;'>" .
                $preheaderText. '  &nbsp;&nbsp;&nbsp;</span>';
    }
}
