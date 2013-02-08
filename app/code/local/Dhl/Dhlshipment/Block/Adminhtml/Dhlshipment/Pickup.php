<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Pickup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{

		$value = $row->getData($this->getColumn()->getIndex());
		if ($value > 0)
			return $value;
		elseif(!$row->_origData["tracking_awb"]>0)
			return 'none';
		else
		{
			return '<a href="' . $this->getUrl('*/*/pickup', array('id' => $row->getId())) . '">Pickup</a>';
		}
	}
}

?>
