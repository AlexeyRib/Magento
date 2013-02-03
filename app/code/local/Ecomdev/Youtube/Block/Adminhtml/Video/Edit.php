<?php
class Ecomdev_Youtube_Block_Adminhtml_Video_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'youtube';
        $this->_controller = 'adminhtml_video';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('youtube/video')->__('Save Video'));

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('video_data') && Mage::registry('video_data')->getId())
        {
            return Mage::helper('youtube/video')->__('Edit Video "%s"', Mage::registry('video_data')->getData("video_title"));
        } else {
            return Mage::helper('youtube/video')->__('New Video');
        }
    }

}