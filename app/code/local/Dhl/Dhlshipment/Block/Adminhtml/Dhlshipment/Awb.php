<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Awb extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
			
		$value = $row->getData($this->getColumn()->getIndex());
		if($value>0)
			return '<a href="'.$this->getUrl('*/*/pdf', array('id' => $row->getId())).'">'.$value.'</a><span style="text-decoration:none;"> | </span><a href="'.$this->getUrl('*/*/mail', array('id' => $row->getId())).'">Mail</a><span style="text-decoration:none;"> | </span><a href="' . $this->getUrl('*/*/checkawb', array('id' => $row->getTrackingAwb())) . '">Check Awb<a>';
		else
			return '<a href="'.$this->getUrl('*/*/createawb', array('id' => $row->getId())).'">Create AWB</a>';
	}
}

?>
