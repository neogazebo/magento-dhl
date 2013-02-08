<?php

class Dhl_Dhlshipment_Model_Carrier_Source_Method
{
 public function toOptionArray()
    {
        $dhl = Mage::getSingleton('dhlshipment/carrier_dhlshipment');
        $arr = array();
        foreach ($dhl->getCode('service') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}
