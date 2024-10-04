<?php

use Embed\Embed;
use custom\helpers\Responder;

class EmbedController extends BaseController {

    public function getURLContent() {
        $url = Input::get('url');
        $oembed = Embed::create($url);

        $info = new stdClass();
        $info->description = $oembed->description;
        $info->title = $oembed->title;
        $info->images = $oembed->images;
        $info->code = $oembed->code;
        $info->imageWidth = $oembed;  
        $info->imageHeight = $oembed;

        $info->width = $oembed->width;
        $info->height = $oembed->height;
        $info->aspectRatio = $oembed->aspectRatio;

        $info->authorName = $oembed->authorName; 
        $info->authorUrl = $oembed->authorUrl; 

        $info->providerName = $oembed->providerName;
        $info->providerUrl = $oembed->providerUrl; 
        $info->providerIcons = $oembed->providerIcons;
        $info->providerIcon = $oembed->providerIcon; 

        $info->publishedDate = $oembed->publishedDate;
        $info->license = $oembed->license; 
        $info->linkedData = $oembed->linkedData;
        $info->feeds = $oembed->feeds;

        return Responder::json(true)->withData($info)->send();
    }

}