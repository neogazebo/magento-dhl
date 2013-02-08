<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Xmlpickup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$value = $row->getData($this->getColumn()->getIndex());
		if ($value > 0)
			return '<a href="' . $this->getUrl('*/*/xml', array('id' => $row->getId(),'type'=>'pickup')) . '" target="_blank">xml<a>';
		else
			return 'none';
	}
}

?>
