<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Returnawb extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{

		$value = $row->getData($this->getColumn()->getIndex());
		if ($value > 0)
			return '<a href="' . $this->getUrl('*/*/pdf', array('id' => $row->getId(), 'type_xml' => 'return')) . '">' . $value . '</a><span style="text-decoration:none;"> | </span><a href="'.$this->getUrl('*/*/mail', array('id' => $row->getId(),'type_xml'=>'return')).'">Mail</a>';
		elseif (!$row->_origData["tracking_awb"] > 0)
			return 'none';
		else
		{
			$config = new Dhl_Dhlshipment_Model_Carrier_Dhlshipment();
			if ($config->getConfigXml('credit_memo_return') == 'M')
			{
				return '<a href="' . $this->getUrl('*/*/returnawb', array('id' => $row->getId())) . '">Return AWB</a>';
			}
			else
			{
				return 'none';
			}
		}
	}
}

?>
