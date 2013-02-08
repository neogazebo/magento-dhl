<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

//	public function __construct()
//	{
//		parent::__construct();
//		$this->setId('sales_order_grid');
//		$this->setUseAjax(true);
//		$this->setDefaultSort('created_at');
//		$this->setDefaultDir('DESC');
//		$this->setSaveParametersInSession(true);
//	}
//
//	protected function _getCollectionClass()
//	{
//		return 'dhlshipment/dhlshipment';
//	}

	protected function _prepareCollection()
	{
//		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$collection = Mage::getModel('dhlshipment/dhlshipment')->getCollection();
		$collection->setOrder('id');
		$collection->getSelect()->join(
				array('order' => 'sales_flat_order_grid'), 'main_table.order_id=order.increment_id', array('order.*'));
		$collection->getSelect()->join(
				array('order2' => 'sales_flat_order'), 'main_table.order_id=order2.increment_id AND order2.shipping_description LIKE "%dhl%"', array('order2.*'));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('real_order_id', array(
			'header' => Mage::helper('sales')->__('Order #'),
			'width' => '80px',
			'type' => 'text',
			'index' => 'order_id',
			'renderer' => 'Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Checkawb'
		));
		$this->addColumn('billing_name', array(
			'header' => Mage::helper('sales')->__('Bill to Name'),
			'index' => 'billing_name',
		));

		$this->addColumn('shipping_name', array(
			'header' => Mage::helper('sales')->__('Ship to Name'),
			'index' => 'shipping_name',
		));
		$this->addColumn('grand_total', array(
			'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
			'index' => 'grand_total',
			'type' => 'currency',
			'currency' => 'order_currency_code',
		));
		$config = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		if ($config->getConfigXml('xml_debug') == 1)
		{
			$this->addColumn('status', array(
				'header' => Mage::helper('dhlshipment')->__('Xml Sipment Validation'),
				'align' => 'left',
				'index' => 'tracking_awb',
				'renderer' => 'Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Xmltracking'
			));
		}
		$this->addColumn('tracking_awb', array(
			'header' => Mage::helper('dhlshipment')->__('Shipment Validation'),
			'align' => 'left',
			'index' => 'tracking_awb',
			'renderer' => 'Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Awb',
			'width' => 200
		));
		if ($config->getConfigXml('xml_debug') == 1)
		{
			$this->addColumn('status_pickup', array(
				'header' => Mage::helper('dhlshipment')->__('Xml Pickup'),
				'align' => 'left',
				'index' => 'pickup',
				'renderer' => 'Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Xmlpickup'
			));
		}
		$this->addColumn('pickup', array(
			'header' => Mage::helper('dhlshipment')->__('Pickup'),
			'align' => 'left',
			'index' => 'pickup',
			'renderer' => 'Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Pickup'
		));
		if ($config->getConfigXml('xml_debug') == 1)
		{
			$this->addColumn('status_return', array(
				'header' => Mage::helper('dhlshipment')->__('Xml Return'),
				'align' => 'left',
				'index' => 'return_awb',
				'renderer' => 'Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Xmlreturn'
			));
		}
		$this->addColumn('return_awb', array(
			'header' => Mage::helper('dhlshipment')->__('Return Awb'),
			'align' => 'left',
			'index' => 'return_awb',
			'renderer' => 'Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Returnawb'
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('dhlshipment')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('dhlshipment')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('order_ids');
		$this->getMassactionBlock()->setUseSelectAll(false);


		$this->getMassactionBlock()->addItem('create_awb', array(
			'label' => Mage::helper('dhlshipment')->__('Create AWB'),
			'url' => $this->getUrl('*/*/masscreateawb'),
		));

		$this->getMassactionBlock()->addItem('return_awb', array(
			'label' => Mage::helper('dhlshipment')->__('Return AWB'),
			'url' => $this->getUrl('*/*/massreturnawb'),
		));

		$this->getMassactionBlock()->addItem('pickup', array(
			'label' => Mage::helper('dhlshipment')->__('Pickup'),
			'url' => $this->getUrl('*/*/masspickup'),
		));

		return $this;
	}

	public function getRowUrl($row)
	{
//		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}