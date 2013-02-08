<?php

class Dhl_Dhlshipment_Block_Dhlshipment extends Mage_Core_Block_Template
{
	public $_display = '0';
	protected $xml;

	public function __construct()
	{
		$timestamp = time();
		$this->time = date('c', $timestamp);
		$data = array();
		for ($i = 0; $i < 31; $i++)
		{
			$data[$i] = rand(1, 9);
		}
		$this->mref = implode('', $data);
	}

	public function getGenerateAwb()
	{
		$awb = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
		return $awb->getGenerateAwb();
	}

	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function getDhlshipment()
	{
		if (!$this->hasData('dhlshipment'))
		{
			$this->setData('dhlshipment', Mage::registry('dhlshipment'));
		}
		return $this->getData('dhlshipment');
	}
}
