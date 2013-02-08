<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Checkawb extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$value = $row->getData($this->getColumn()->getIndex());
		if ($value > 0)
			return '<a href="'.Mage::getBaseUrl().'admin/sales_order/view/order_id/'.$row->getEntityId().'/">'.$value.'<a>';
		else
			return 'none';
	}
}

?>
