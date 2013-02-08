<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('dhlshipment_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('dhlshipment')->__('Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('dhlshipment')->__('General Information'),
          'title'     => Mage::helper('dhlshipment')->__('General Information'),
          'content'   => $this->getLayout()->createBlock('dhlshipment/adminhtml_dhlshipment_edit_tab_form')->toHtml(),
      ));
	  
	  $this->addTab('display_section',array(
			'label'		=> Mage::helper('dhlshipment')->__('Categories'),
			'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
            'class'     => 'ajax',
	  ));
     
      return parent::_beforeToHtml();
  }
}