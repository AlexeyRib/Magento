<?php

class Ecomdev_Youtube_Adminhtml_VideoController extends Mage_Adminhtml_Controller_action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('youtube/video')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Video Manager'), Mage::helper('adminhtml')->__('Video Manager'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->loadLayout();

        $id = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('youtube/video');
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('youtube/video')->__('Video does not exist.'));
                $this->_redirect('*/*/');
            }
            /* Adding block with youtube video iframe */
            $video_url = $model->getData("video_url");
            /* We should change link to "embed" before pasting into iframe */
            $video_code = $this->getYoutubeVideoCode($video_url);
            $video_url = 'http://www.youtube.com/embed/' . $video_code;
            $video_html = '<iframe width="420" height="315" src="' . $video_url . '" frameborder="0" allowfullscreen></iframe>';
            $video_block = $this->getLayout()
                ->createBlock('core/text', 'video_block')
                ->setText($video_html);
            $this->_addContent($video_block);
        }
        Mage::register('video_data', $model);

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost())
        {
            $id = $this->getRequest()->getParam('id');

            /* Trying to load video with this label, if it exists, and it is not current video - redirect with error */
            $data = $this->getDataFromYoutubeApi($data);
            $option_label_new = $this->getOptionLabel($data);
            $data["video_option_label"] = $option_label_new;
            $try_model = Mage::getModel('youtube/video')->load($option_label_new, "video_option_label");

            if($try_model and $try_model->getId() != $id) {
                $try_url = $this->getUrl('*/*/edit', array('id' => $try_model->getId()));
                Mage::getSingleton('adminhtml/session')->addError('Error: This video already exists, you could find it <a href="' . $try_url . '">here</a>.');
                if ($id) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/');
                }

                return;
            }

            $new_skus = $data["products_skus"];
            if($new_skus) {
                /* Yes, the code below could be slow because of many strings functions, but I tried to envisage maximum issues that could appear while parsing */
                $new_skus = preg_replace('/\s+/', ' ', $new_skus); // replace multiply spaces with one space
                $new_skus = str_replace(', ', ',', $new_skus); // remove spaces after comma if have any
                $new_skus = str_replace(' ,', ',', $new_skus); // remove spaces before comma if have any
                $new_skus = explode(',', $new_skus);
            }

            $model = Mage::getModel('youtube/video');
            if ($id) {
                $model->load($id);
                $old_data = $model->getData();
                $option_label_old = $old_data["video_option_label"]; //$this->getOptionLabel($old_data);
                /* Removing attribute value from videos that are not in list now */
                $old_skus = $old_data["products_skus"];
                if($old_skus) {
                    /* Yes, the code below could be slow because of many strings functions, but I tried to envisage maximum issues that could appear while parsing */
                    $old_skus = preg_replace('/\s+/', ' ', $old_skus); // replace multiply spaces with one space
                    $old_skus = str_replace(', ', ',', $old_skus); // remove spaces after comma if have any
                    $old_skus = str_replace(' ,', ',', $old_skus); // remove spaces before comma if have any
                    $old_skus = explode(',', $old_skus);

                    if(isset($new_skus) and !empty($new_skus)) {
                        $skus_to_remove = array_diff($old_skus, $new_skus);
                    } else {
                        $skus_to_remove = $old_skus;
                    }

                    foreach($skus_to_remove as $sku)
                    {
                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                        if($product) {
                            $product->setData("youtube_video", null); // removing video from product attribute
                            $product->save();
                        } else {
                            Mage::log("Warning: There is no product with SKU '" . $sku . "' can't remove it. SKUs list: '" . $old_data["products_skus"] . "'.", null, "Ecomdev_Youtube.log");
                        }
                    }
                }
            }

            /* Adding attribute value to the new videos that added to list */
            if($new_skus) {
                if(isset($old_skus) and !empty($old_skus)) {
                    $skus_to_add = array_diff($new_skus, $old_skus);
                } elseif(isset($new_skus) and !empty($new_skus)) {
                    $skus_to_add = $new_skus;
                }

                $invalid_skus = array();
                foreach($skus_to_add as $key => $sku)
                {
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                    if($product) {
                        $option_id = $product->getResource()->getAttribute("youtube_video")->getSource()->getOptionId($option_label_new);
                        $product->setData("youtube_video", $option_id); // adding video to product attribute
                        $product->save();
                        /*
                         * After $product->save() method, our observer triggered and remove old product SKU form SKUs list.
                         * So, that is why, we do not have to do it here.
                         */
                    } else {
                        $invalid_skus[] = $sku;
                        unset($new_skus[$key]); // removing invalid SKU from the list
                        Mage::log("Warning: There is no product with SKU '" . $sku . "' can't add it. SKUs list: '" . $data["products_skus"] . "'.", null, "Ecomdev_Youtube.log");
                    }
                }
                $new_skus = implode(",", $new_skus);
                $data["products_skus"] = $new_skus; // replacing SKUs list in case we had invalid SKUs
            }

            $model->setData($data);
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('youtube/video')->__('Error saving video.'));
                }

                // removing old option from attribute for this video (if it is not a new video, and label is changed)
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'youtube_video');
                if($id and $option_label_old != $option_label_new) {
                    $options = $attribute->getSource()->getAllOptions();
                    $optionsDelete = array();
                    foreach($options as $option)
                    {
                        if ($option['label'] == $option_label_old) {
                            $optionsDelete['delete'][$option['value']] = true;
                            $optionsDelete['value'][$option['value']] = true;
                            break;
                        }
                    }

                    if(empty($optionsDelete)) {
                        Mage::log("Warning: YouTube video with id '" . $id . "' have no option with label '" . $option_label_old . "'. Nothing to remove.", null, "Ecomdev_Youtube.log");
                    }

                    $attribute->setData('option', $optionsDelete);
                    $attribute->save();
                }

                /* Adding new option to attribute for this video (if label is changed or not exist) */
                if(!isset($option_label_old) or $option_label_old != $option_label_new) {
                    $option = array(
                        'value' => array(
                            'video_id_' . $model->getId() => array($option_label_new), // the key for option will be id of video [ UPD: now I know, it doesn't matter :) ]
                        )
                    );
                    $attribute->setData('option', $option);
                    $attribute->save();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('youtube/video')->__('Video was successfully saved. %', implode(", ", $invalid_skus)));
                if(!empty($invalid_skus)) {
                    $msg = Mage::helper('youtube/video')->__('Warning: some of the SKU(s) that you provided were invalid and removed from the list, here it is: %s', implode(", ", $invalid_skus));
                    Mage::getSingleton('adminhtml/session')->addWarning($msg);
                }
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('youtube/video')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('youtube/video');
                $model->load($id);

                /* Removing option from attribute for this video */
                $data = $model->getData();
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'youtube_video');
                $option_label = $this->getOptionLabel($data);
                $options = $attribute->getSource()->getAllOptions();
                $optionsDelete = array();
                foreach($options as $option)
                {
                    if ($option['label'] == $option_label) {
                        $optionsDelete['delete'][$option['value']] = true;
                        $optionsDelete['value'][$option['value']] = true;
                        break;
                    }
                }
                if(empty($optionsDelete)) {
                    Mage::log("Warning: YouTube video with id '" . $id . "' have no option with label '" . $option_label . "'. Nothing to remove. (deleteAction)", null, "Ecomdev_Youtube.log");
                }
                $attribute->setData('option', $optionsDelete);
                $attribute->save();

                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('youtube/video')->__('The video has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the video to delete.'));
        $this->_redirect('*/*/');
    }

    public function getDataFromYoutubeApi($data, $error_id = null)
    {
        // The Youtube's API url
        define('YT_API_URL', 'http://gdata.youtube.com/feeds/api/videos?q=');
        // Get the video code.
        if(isset($data["video_url"])) {
            $video_code = $this->getYoutubeVideoCode($data["video_url"]);
        }

        // Using cURL php extension to make the request to youtube API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, YT_API_URL . $video_code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $feed holds a rss feed xml returned by youtube API
        $feed = curl_exec($ch);
        curl_close($ch);

        // Using SimpleXML to parse youtube's feed
        $xml = simplexml_load_string($feed);

        $entry = $xml->entry[0];
        // If no entry was found, then youtube didn't find any video with specified id
        if(!$entry) {
            Mage::getSingleton('adminhtml/session')->addError('Invalid video URL or Code ("' . $video_code . '").');
            if ($error_id) {
                $this->_redirect('*/*/edit', array('id' => $error_id));
            } else {
                $this->_redirect('*/*/');
            }

            return;
        }
        $media = $entry->children('media', true);
        $group = $media->group;

        $title = $group->title; // $title: The video title
        $desc = $group->description; // $desc: The video description
        $thumb = $group->thumbnail[0];// There are 4 thumbnails, the first one (index 0) is the largest.
        list($thumb_url) = $thumb->attributes();

        $data["video_title"] = $title[0];
        $data["video_description"] = $desc[0];
        $data["video_thumbnail_url"] = $thumb_url;

        return $data;
    }

    /* Returns label for YouTube video option, according to $data */
    public function getOptionLabel($data)
    {
        $option_label = $data["video_title"];
        if(isset($data["user_comment"]) and $data["user_comment"] != '') {
            $option_label .= " (" . $data["user_comment"] . ")";
        }

        $url = $data["video_url"];
        $video_code = $this->getYoutubeVideoCode($url);
        $option_label .= " " . $video_code;

        return $option_label;
    }

    /* Returns YouTube video code from URL */
    public function getYoutubeVideoCode($video_url)
    {
        if(strpos($video_url, "youtu.be") !== false or strpos($video_url, "www.youtube.com/embed") !== false) { //short link or "embed" link
            $video_code = substr($video_url, strrpos($video_url, '/')+1);
        } elseif(strpos($video_url, "www.youtube.com") !== false) { // full link
            preg_match('/v=[0-9a-zA-Z]+/', $video_url, $matches);
            $video_code = $matches[0];
            $video_code = str_replace('v=', '', $video_code);
        } else { // it is probably just video code
            $video_code = $video_url;
        }

        return $video_code;
    }
}