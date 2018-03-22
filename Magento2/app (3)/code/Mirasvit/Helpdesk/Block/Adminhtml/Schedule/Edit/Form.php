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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Schedule\Edit;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Html
     */
    protected $helpdeskHtml;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config
     */
    protected $config;

    /**
     * @param \Mirasvit\Helpdesk\Model\Config          $config
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Magento\Store\Model\System\Store        $systemStore
     * @param \Mirasvit\Helpdesk\Helper\Html           $helpdeskHtml
     * @param \Magento\Framework\Data\FormFactory      $formFactory
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Backend\Block\Widget\Context    $context
     * @param array                                    $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config $config,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Store\Model\System\Store $systemStore,
        \Mirasvit\Helpdesk\Helper\Html $helpdeskHtml,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->config = $config;
        $this->localeLists = $localeLists;
        $this->systemStore = $systemStore;
        $this->helpdeskHtml = $helpdeskHtml;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Helpdesk\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create()->setData([
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save', [
                'id'    => $this->getRequest()->getParam('id'),
                'store' => (int)$this->getRequest()->getParam('store'),
            ]),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        /** @var \Mirasvit\Helpdesk\Model\Schedule $schedule */
        $schedule = $this->registry->registry('current_schedule');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($schedule->getId()) {
            $fieldset->addField('schedule_id', 'hidden', [
                'name'  => 'schedule_id',
                'value' => $schedule->getId(),
            ]);
        }
        $fieldset->addField('store_id', 'hidden', [
            'name'  => 'store_id',
            'value' => (int)$this->getRequest()->getParam('store'),
        ]);

        $fieldset->addField('name', 'text', [
            'label'       => __('Schedule Name'),
            'name'        => 'name',
            'value'       => $schedule->getName(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label'  => __('Is Active'),
            'name'   => 'is_active',
            'value'  => $schedule->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],

        ]);
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::MEDIUM);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::MEDIUM);
        $fieldset->addField('active_from', 'date', [
            'label'       => __('Active From'),
            'name'        => 'active_from',
            'class'       => 'admin__control-text',
            'date_format' => $dateFormat,
            'time_format' => $timeFormat,
            'value'       => $schedule->getActiveFrom(),
        ]);
        $fieldset->addField('active_to', 'date', [
            'label'       => __('Active To'),
            'name'        => 'active_to',
            'class'       => 'admin__control-text',
            'date_format' => $dateFormat,
            'time_format' => $timeFormat,
            'value'       => $schedule->getActiveTo(),
        ]);
        $fieldset->addField('store_ids', 'multiselect', [
            'label'    => __('Stores'),
            'required' => true,
            'name'     => 'store_ids[]',
            'value'    => $schedule->getStoreIds(),
            'values'   => $this->systemStore->getStoreValuesForForm(false, true),
        ]);

        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name'  => 'sort_order',
            'value' => $schedule->getSortOrder() ? $schedule->getSortOrder() : '10',
            'note'  => __('Affects the priority of the schedule over other schedules'),
        ]);
        $fieldset->addField('is_holiday', 'select', [
            'label'  => __('Is Holiday'),
            'name'   => 'is_holiday',
            'value'  => $schedule->getIsHoliday(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('timezone', 'select', [
            'label'  => __('Timezone'),
            'name'   => 'timezone',
            'value'  => $schedule->getTimezone() ? $schedule->getTimezone() :
                $this->_localeDate->getConfigTimezone(),
            'values' => $this->localeLists->getOptionTimezones(),
        ]);
        $fieldset->addField('type', 'select', [
            'label'    => __('Working Hours'),
            'required' => true,
            'name'     => 'type',
            'value'    => $schedule->getType(),
            'values'   => [
                \Mirasvit\Helpdesk\Model\Config::SCHEDULE_TYPE_ALWAYS => __('24 hrs x 7 days'),
                \Mirasvit\Helpdesk\Model\Config::SCHEDULE_TYPE_CUSTOM => __('Select working days/hours'),
                \Mirasvit\Helpdesk\Model\Config::SCHEDULE_TYPE_CLOSED => __('Closed'),
            ],
        ]);

        $fieldset->addField('working_time', 'Mirasvit\Helpdesk\Block\Adminhtml\Schedule\Edit\Form\Field\Schedule', [
            'label' => __('Working days/hours'),
            'name'  => 'working_time',
            'value' => $schedule->getWorkingHours(),
        ], 'working_hours');

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "type",
                'type'
            )->addFieldMap(
                "working_time",
                'working_time'
            )->addFieldDependence(
                'working_time',
                'type',
                'custom'
            )
        );


        $fieldset->addField('open_message', 'textarea', [
            'label' => __('Open message'),
            'name'  => 'open_message',
            'value' => $schedule->getData('open_message'),
        ]);
        $fieldset->addField('closed_message', 'textarea', [
            'label' => __('Closed message'),
            'name'  => 'closed_message',
            'value' => $schedule->getData('closed_message'),
            'note'  => __(
                'You can use variable ' . \Mirasvit\Helpdesk\Model\Config::SCHEDULE_LEFT_HOUR_TO_OPEN_PLACEHOLDER
            )
        ]);


        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
