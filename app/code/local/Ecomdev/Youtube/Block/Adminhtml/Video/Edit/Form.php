<?php

class Ecomdev_Youtube_Block_Adminhtml_Video_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getVideoData())
        {
            $data = Mage::getSingleton('adminhtml/session')->getVideoData();
            Mage::getSingleton('adminhtml/session')->getVideoData(null);
        }
        elseif (Mage::registry('video_data'))
        {
            $data = Mage::registry('video_data')->getData();
        }
        else
        {
            $data = array();
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('video_form', array(
            'legend' =>Mage::helper('youtube/video')->__('Video Information')
        ));

        $fieldset->addField('video_url', 'text', array(
            'label'     => Mage::helper('youtube/video')->__('Video URL'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'video_url',
            'note'      => Mage::helper('youtube/video')->__('Youtube video Code or URL.'),
            'style'     => 'width: 600px;',
        ));

        $fieldset->addField('user_comment', 'text', array(
            'label'     => Mage::helper('youtube/video')->__('Your Comment'),
            //'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'user_comment',
            'style'     => 'width: 600px;',
        ));

        $fieldset->addField('products_skus', 'text', array(
            'label'     => Mage::helper('youtube/video')->__('Products SKUs.'),
            //'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'products_skus',
            'note'     => Mage::helper('youtube/video')->__('Products SKUs which video connected to (separated by comma).'),
            'style'     => 'width: 600px;',
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}