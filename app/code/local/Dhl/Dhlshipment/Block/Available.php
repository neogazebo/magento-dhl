<?php 

class Technolyze_Ambikuk_Block_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
	protected function getInfoText($carrierCode)
	{
		if ($text = Mage::getStoreConfig('carriers/'.$carrierCode.'/infotext')) {
            return $text;
        }
        return '';
	}
}
