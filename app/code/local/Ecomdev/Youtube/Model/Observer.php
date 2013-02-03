<?php

class Ecomdev_Youtube_Model_Observer
{
    /*
     *  After product is saved, we should check if video changed, and if it does, change SKUs list's for those videos (old and new)
     */
    public function productSaveAfter(Varien_Event_Observer $observer)
    {
        Mage::log("productSaveAfter", null, "save.log");
        $product = $observer->getEvent()->getProduct();
        $sku = $product->getSku();

        /* Getting old and new video, and if they didn't match - change their SKUs lists */
        $old_option_id = $product->getOrigData("youtube_video");
        $new_option_id = $product->getData("youtube_video");
        Mage::log("old_option_id == " . $old_option_id, null, "skus.log");
        Mage::log("new_option_id == " . $new_option_id, null, "skus.log");
        if($old_option_id != $new_option_id) {

            /* If old video exists, remove current product SKU from it's SKUs list */
            if($old_option_id) {
                $option_label_old = $product->getResource()->getAttribute("youtube_video")->getSource()->getOptionText($old_option_id);
                $video = Mage::getModel('youtube/video')->load($option_label_old, 'video_option_label');
                $skus = $video->getData("products_skus");
                Mage::log("old video skus == " . $skus, null, "skus.log");
                Mage::log("option_label_old == " . $option_label_old, null, "skus.log");
                /* Yes, the code below could be slow because of many strings functions, but I tried to envisage maximum issues that could appear while parsing */
                $skus = preg_replace('/\s+/', ' ', $skus); // replace multiply spaces with one space
                $skus = str_replace(', ', ',', $skus); // remove spaces after comma if have any
                $skus = str_replace(' ,', ',', $skus); // remove spaces before comma if have any
                $skus = explode(',', $skus);
                Mage::log($skus, null, "skus.log");
                $ind = array_search($sku, $skus);
                if($ind === false) {
                    /*
                     * This observer's method could be called after $product->save() method in VideoController,
                     * so, in this case, there will be no SKU of current product in video SKUs list already.
                     * That is why I comment out this Warning.
                     */
                    //Mage::log("Warning: There is no SKU '" . $sku . "' in '" . $option_label_old . "' video. Nothing to remove.", null, "Ecomdev_Youtube.log");
                } else {
                    unset($skus[$ind]); // remove current product SKU from array
                    $skus = implode(',', $skus);
                    Mage::log("old video skus changed == " . $skus, null, "skus.log");
                    $video->setData("products_skus", $skus);
                    $video->save();
                }
            }

            /* If new video exists, add current product SKU to the new video SKUs list */
            if($new_option_id) {
                $option_label_new = $product->getResource()->getAttribute("youtube_video")->getSource()->getOptionText($new_option_id);
                $video = Mage::getModel('youtube/video')->load($option_label_new, 'video_option_label');
                $skus = $video->getData("products_skus");
                Mage::log("new video skus == " . $skus, null, "skus.log");
                Mage::log("option_label_new == " . $option_label_new, null, "skus.log");
                if($skus) {
                    $skus .= ','; // adding comma only if we already have skus
                }
                $skus .= $sku; // adding current product SKU to the end of the list
                Mage::log("new video skus changed == " . $skus, null, "skus.log");
                $video->setData("products_skus", $skus);
                $video->save();
            }
        }

        Mage::log("====================================", null, "skus.log");
    }
}