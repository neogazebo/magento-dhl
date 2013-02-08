<?php

class Dhl_Dhlshipment_Model_Mysql4_Dhlshipment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the dhlshipment_id refers to the key field in your database table.
        $this->_init('dhlshipment/dhlshipment', 'id');
    }
}