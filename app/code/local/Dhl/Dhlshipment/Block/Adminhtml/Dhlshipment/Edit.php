<?php

class Dhl_Dhlshipment_Block_Adminhtml_Dhlshipment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'dhlshipment';
        $this->_controller = 'adminhtml_dhlshipment';
        
        $this->_updateButton('save', 'label', Mage::helper('dhlshipment')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('dhlshipment')->__('Delete '));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('dhlshipment_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'dhlshipment_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'dhlshipment_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('dhlshipment_data') && Mage::registry('dhlshipment_data')->getId() ) {
            return Mage::helper('dhlshipment')->__("Edit Tracking AWB '%s'", $this->htmlEscape(Mage::registry('dhlshipment_data')->getTitle()));
        } else {
            return Mage::helper('dhlshipment')->__('Add Tracking AWB');
        }
    }
}