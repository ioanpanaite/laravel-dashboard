<?php
/**
 * Created by PhpStorm.
 * User: lautarosrur
 * Date: 01/10/14
 * Time: 08:51
 */

namespace custom\transformers;



use Illuminate\Support\Facades\Auth;

class QuestionListTransformer extends Transformer {

    public function transform($item)
    {
        $ret['id']       = $item->id;
        $ret['code']       = $item->code;
        $ret['title']      = $item->title;
        $ret['description']      = $item->description;
        $ret['sector']      = $item->sector;
        $ret['reward']      = $item->reward;
        $ret['user_id']      = $item->user_id;
        $ret['updated_at']      = $item->updated_at;

        return $ret;
    }

} 