<?php

class Dhl_Dhlshipment_Model_Carrier_Source_Creditmemoreturn
{
 public function toOptionArray()
    {
        $dhl = Mage::getSingleton('dhlshipment/carrier_dhlshipment');
        $arr = array();
        foreach ($dhl->getCode('creditmemoreturn') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}
