<?php
class Ecomdev_Youtube_Block_Media extends Mage_Catalog_Block_Product_View_Media
{
    public function getYouTubeHtml($_product)
    {
        $html = '';
        $option_id = $_product->getData("youtube_video");
        if($option_id) {
            $option_label = $_product->getResource()->getAttribute("youtube_video")->getSource()->getOptionText($option_id);
            $video = Mage::getModel('youtube/video')->load($option_label, 'video_option_label');
            $thumbnail_url = $video->getData("video_thumbnail_url");
            $video_url = $video->getData("video_url");
            $video_url = $video->getYoutubeVideoEmbedUrl($video_url);
            $video_title = $video->getData("video_title");
            $html = "
                <li>
                    <a href=\"#\" onclick=\"popWin(
                                '" . $video_url ."',
                                'gallery',
                                'width=420,height=315'); return false;\"
                                 title=\"" . $video_title . "\">
                                 <img src=\"" . $thumbnail_url . "\" width=\"56\" height=\"56\" alt=\"" . $video_title . "\" />
                    </a>
                </li>";
        }
        return $html;
    }
}