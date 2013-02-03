<?php

$this->startSetup();

$this->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('youtube_videos')} (
        `video_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `video_url` text,
        `video_title` text,
        `video_description` text,
        `user_comment` text,
        `video_thumbnail_url` text,
        `products_skus` text,
        `video_option_label` text,
        PRIMARY KEY (`video_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$model = Mage::getModel('eav/entity_setup', 'core_setup');

$model->addAttribute('catalog_product', 'youtube_video', array(
    'type'              =>  'text',
    'input'             =>  'select',
    'label'             =>  'YouTube Video',
    'global'            =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => false,
    'user_defined'      => false,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
));

$this->endSetup();