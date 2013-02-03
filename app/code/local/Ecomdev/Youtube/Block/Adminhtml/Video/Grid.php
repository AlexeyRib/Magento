<?php
class Ecomdev_Youtube_Block_Adminhtml_Video_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('youtubeVideoGrid');
        $this->setDefaultSort('video_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('youtube/video')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('video_id', array(
            'header'    => Mage::helper('youtube/video')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'video_id',
        ));

        $this->addColumn('video_url', array(
            'header'    => Mage::helper('youtube/video')->__('URL'),
            'align'     => 'left',
            'index'     => 'video_url',
        ));

        $this->addColumn('video_title', array(
            'header'    => Mage::helper('youtube/video')->__('Title'),
            'align'     => 'left',
            'index'     => 'video_title',
        ));

        $this->addColumn('user_comment', array(
            'header'    => Mage::helper('youtube/video')->__('Your Comment'),
            'align'     => 'left',
            'index'     => 'user_comment',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}