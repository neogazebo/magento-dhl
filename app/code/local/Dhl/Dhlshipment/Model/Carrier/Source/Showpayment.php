<?php

class Dhl_Dhlshipment_Model_Carrier_Source_Showpayment
{
 public function toOptionArray()
    {
        $dhl = Mage::getSingleton('dhlshipment/carrier_dhlshipment');
        $arr = array();
        foreach ($dhl->getCode('show_payment') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}
