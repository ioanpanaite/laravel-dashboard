<?php
/**
 * Created by PhpStorm.
 * User: lautarosrur
 * Date: 01/10/14
 * Time: 08:51
 */

namespace custom\transformers;



use Illuminate\Support\Facades\Auth;

class FollowersTransformer extends Transformer {

    public function transform($item)
    {
        $ret['full_name']       = $item->full_name;
        $ret['email']       = $item->email;
        $ret['organization']      = $item->organization;
        $ret['phone']      = $item->phone;
        $ret['id']      = $item->id;

        return $ret;
    }

} 