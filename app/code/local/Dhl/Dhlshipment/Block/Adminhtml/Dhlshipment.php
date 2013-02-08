<?php
class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_dhlshipment';
    $this->_blockGroup = 'dhlshipment';
		$this->_headerText = Mage::helper('sales')->__('Manage Shipment Order');
//    $this->_headerText = Mage::helper('dhlshipment')->__('Tracking AWB Manager');
//    $this->_addButtonLabel = Mage::helper('dhlshipment')->__('Add Tracking AWB');
    parent::__construct();
  }
}