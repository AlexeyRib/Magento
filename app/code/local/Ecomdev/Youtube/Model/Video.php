<?php

class Ecomdev_Youtube_Model_Video extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('youtube/video');
    }
}