<?php
class Dhl_Dhlshipment_Helper_Data extends Mage_Core_Helper_Abstract
{
	const DISP_HOME_PAGE = '0';
	const DISP_CATEGORY = '1';
	
	public function getDisplayOption(){
		return array(
			array('value'=>self::DISP_HOME_PAGE, 'label'=>$this->__('Home page')),
			array('value'=>self::DISP_CATEGORY, 'label'=>$this->__('Category')),
		);
	}
	public function xmlRequest($orderId, $xmlRequestType, $type = 'tracking')
  {
    $obs = new Dhl_Dhlshipment_Model_Order_Observer();
		return $xmlObj = $obs->asisXml($orderId,$xmlRequestType,$type)->regionType()->xmlType()->xmlGenarate();
  }
}