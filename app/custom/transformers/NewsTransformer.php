<?php
/**
 * Created by PhpStorm.
 * User: lautarosrur
 * Date: 01/10/14
 * Time: 08:51
 */

namespace custom\transformers;


class NewsTransformer extends Transformer {

    public function transform($item)
    {
        $ret = $item;

        $ret['content_text'] = $item['news'];
        $ret['truncated'] = false;
        if(! \Input::has('truncate'))
        {
            if(strlen($ret['content_text']) > 450)
            {
                $ret['content_text'] = truncateHtml($ret['content_text'], 450);
                $ret['truncated'] = true;

            }
        }
        $ret['comment_count'] = count($item['comments']);
        $ret['starred']       = !empty($item['starred']);
        $ret['comments']      = array_slice($item['comments'],-3);

        return $ret;
    }

} 