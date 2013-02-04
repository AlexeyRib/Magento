<?php

class Ecomdev_Youtube_Model_Video extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('youtube/video');
    }

    /* Returns YouTube "embed" video URL */
    public function getYoutubeVideoEmbedUrl($video_url)
    {
        if(strpos($video_url, "www.youtube.com/embed") !== false) { // already "embed" link
            return $video_url;
        }

        if(strpos($video_url, "youtu.be") !== false or strpos($video_url, "www.youtube.com/embed") !== false) { // short link
            $video_code = substr($video_url, strrpos($video_url, '/')+1);
        } elseif(strpos($video_url, "www.youtube.com") !== false) { // full link
            preg_match('/v=[0-9a-zA-Z]+/', $video_url, $matches);
            $video_code = $matches[0];
            $video_code = str_replace('v=', '', $video_code);
        } else { // it is probably just video code
            $video_code = $video_url;
        }

        $video_url = 'http://www.youtube.com/embed/' . $video_code;

        return $video_url;
    }
}