<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Алексей
 * Date: 30.01.13
 * Time: 0:55
 * To change this template use File | Settings | File Templates.
 */

class Ecomdev_Youtube_Block_Adminhtml_Video extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_video';
        $this->_blockGroup = 'youtube';
        $this->_headerText = Mage::helper('youtube/video')->__('Video Manager');
        $this->_addButtonLabel = Mage::helper('youtube/video')->__('Add Video');
        parent::__construct();
    }
}