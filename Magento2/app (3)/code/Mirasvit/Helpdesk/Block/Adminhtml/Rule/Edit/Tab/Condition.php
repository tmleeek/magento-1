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



namespace Mirasvit\Helpdesk\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Condition extends Generic implements TabInterface //extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Source\Rule\Event
     */
    protected $configSourceRuleEvent;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $widgetFormRendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrlManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @param \Mirasvit\Helpdesk\Model\Config\Source\Rule\Event    $configSourceRuleEvent
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $widgetFormRendererFieldset
     * @param \Magento\Rule\Block\Conditions                       $conditions
     * @param \Magento\Framework\Data\FormFactory                  $formFactory
     * @param \Magento\Backend\Model\Url                           $backendUrlManager
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Backend\Block\Widget\Context                $context
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array                                                $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config\Source\Rule\Event $configSourceRuleEvent,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $widgetFormRendererFieldset,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->configSourceRuleEvent = $configSourceRuleEvent;
        $this->widgetFormRendererFieldset = $widgetFormRendererFieldset;
        $this->conditions = $conditions;
        $this->formFactory = $formFactory;
        $this->backendUrlManager = $backendUrlManager;
        $this->registry = $registry;
        $this->context = $context;
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);
        /** @var \Mirasvit\Helpdesk\Model\Rule $rule */
        $rule = $this->registry->registry('current_rule');

        $fieldset = $form->addFieldset('event_fieldset', ['legend' => __('Event')]);
        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', [
                'name' => 'rule_id',
                'value' => $rule->getId(),
            ]);
        }
        $fieldset->addField('event', 'select', [
            'label' => __('Event'),
            'required' => true,
            'name' => 'event',
            'value' => $rule->getEvent(),
            'values' => $this->configSourceRuleEvent->toOptionArray(),
        ]);

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/rule/newConditionHtml/form/rule_conditions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Conditions')]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions'), 'required' => true]
        )->setRule(
            $rule
        )->setRenderer(
            $this->conditions
        );

        //        $fieldset = $form->addFieldset('condition_fieldset', ['legend' => __('Conditions')]);
        //        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
        //            ->setTemplate('promo/fieldset.phtml')
        //            ->setNewChildUrl($this->backendUrlManager->getUrl(
        //              '*/adminhtml_rule/newConditionHtml/form/rule_conditions_fieldset',
        //               ['rule_type' => $rule->getType()]
        //          )
        //          );
        //        $fieldset->setRenderer($renderer);
        //
        //        $fieldset->addField('condition', 'text', [
        //            'name' => 'condition',
        //            'label' => __('Filters'),
        //            'title' => __('Filters'),
        //            'required' => true,
        //        ])->setRule($rule)
        //            ->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        return parent::_prepareForm();
    }

    /**
     * Prepare content for tab.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /************************/
}
