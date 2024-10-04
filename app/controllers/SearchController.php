<?php

use custom\helpers\Midrepo;
use custom\helpers\Responder;

class SearchController extends BaseController
{

    public function search()
    {
        $query = Input::get('query');
        $userId = Auth::user()->id;
        $result = new stdClass();
        $result->projects = DB::select(DB::raw(
            "select news.id as id, spaces.title, spaces.code, users.full_name, users.id as user_id, news.created_at, news.news, body AS ".
            "relevance FROM ftindex ".
            "join news on (ftindex.indexable_id = news.id and ftindex.indexable_type='News') ".
            "join users on (news.user_id = users.id) ".
            "join spaces on (spaces.description LIKE '%$query%' OR spaces.title LIKE '%$query%')".
            "join space_user on (spaces.id = space_user.space_id and space_user.user_id = $userId and role >=2) ".
            "order by relevance DESC LIMIT 100"));

        $result->people = DB::select(DB::raw(
            "SELECT * FROM users WHERE full_name LIKE '%$query%' ORDER BY id DESC LIMIT 100"
        ));

        return Responder::json(true)->withData($result)->send();

    }
}