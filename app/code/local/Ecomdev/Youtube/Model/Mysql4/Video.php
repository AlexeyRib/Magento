<?php

class Ecomdev_Youtube_Model_Mysql4_Video extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('youtube/video', 'video_id');
    }
}