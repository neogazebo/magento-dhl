<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('dhlshipment_form', array('legend'=>Mage::helper('dhlshipment')->__('General information')));
			
			$fieldset->addField('order_id', 'text', array(
          'label'     => Mage::helper('dhlshipment')->__('Order ID'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'order_id',
      ));
			
			$fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('dhlshipment')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('dhlshipment')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('dhlshipment')->__('Disabled'),
              ),
          ),
      ));
			
			$fieldset->addField('tracking_awb', 'text', array(
          'label'     => Mage::helper('dhlshipment')->__('Tracking Awb'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'tracking_awb',
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getDhlshipmentData() )
      {
          $data = Mage::getSingleton('adminhtml/session')->getDhlshipmentData();
          Mage::getSingleton('adminhtml/session')->setDhlshipmentData(null);
      } elseif ( Mage::registry('dhlshipment_data') ) {
          $data = Mage::registry('dhlshipment_data')->getData();
      }
	  $data['store_id'] = explode(',',$data['stores']);
	  $form->setValues($data);
	  
      return parent::_prepareForm();
  }
}