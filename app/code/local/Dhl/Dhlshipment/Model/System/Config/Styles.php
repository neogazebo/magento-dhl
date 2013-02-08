<?php

class Dhl_Dhlshipment_Model_System_Config_Styles 
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Static')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Auto switch')),
            array('value' => 3, 'label'=>Mage::helper('adminhtml')->__('Replacement')),
            array('value' => 4, 'label'=>Mage::helper('adminhtml')->__('Slider with thumbnails')),
            array('value' => 5, 'label'=>Mage::helper('adminhtml')->__('Slider only')),
        );
    }
}