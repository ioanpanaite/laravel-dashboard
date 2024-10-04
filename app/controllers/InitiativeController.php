<?php

use custom\helpers\Midrepo;
use custom\helpers\Responder;

/**
 * Class InitiativeController
 */
class InitiativeController extends BaseController
{

    /**
     * Delete initiative
     * Route: delete:/initiative/{initiativeCode}
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $initiative   = Midrepo::getOrFail('initiative');
        $initiativeId = $initiative->id;

        $content = Content::where('initiative_id', $initiativeId);

        // delete observers fired this way
        foreach ($content as $cnt) {
            $cnt->delete();
        }

        $initiative->delete();

        return Responder::json(true)->send();
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList($userid=0)
    {
		$initiatives = Initiative::getList($userid);
	
        return Responder::json(true)->withDataTransform($initiatives, 'InitiativeListTransformer')->send();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne()
    {

        $initiative = Midrepo::getOrFail('initiative');

        if(! $initiative->active && !Auth::user()->admin)
            //return Responder::json(false)->withMessage('unauthorized')->send();


        $initiative = $initiative->load('users');
        $initiative = $initiative->load(
            [
                'tags' => function ($q) {
                        $q->orderBy('counter', 'DESC');
                    }
            ]
        );

        DB::table('initiative_user')->where('user_id', Auth::user()->id)->where('initiative_id', $initiative->id)->update(
            ['last_visit' => now()]
        );


        $initiative->role = Auth::user()->ininitiative($initiative->id);

        return Responder::json(true)->withDataTransform($initiative, 'InitiativeTransformer')->send();
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {

        $initiative = Midrepo::getOrFail('initiative');

        $users = $initiative->users()->where('users.id', '<>', Auth::user()->id)->get();

        return Responder::json(true)->withDataTransform($users, 'AllUsersTransformer')->send();

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function userJoin()
    {
        $initiative = Midrepo::getOrFail('initiative');

        if ($initiative->access == 'PU' || ($initiative->access == 'PR' && Auth::user()->ininitiative($initiative->id) == ROLE_INVITED)) {
            $user = $initiative->users()->where('user_id', Auth::user()->id)->first();
            if (!$user) {
                $initiative->users()->attach(Auth::user()->id, ['role' => ROLE_MEMBER]);
            } else {
                $user->pivot->role = ROLE_MEMBER;
                $user->pivot->save();
            }

            return Responder::json(true)->send();
        }

        return Responder::json(false)->withMessage('access_denied')->send();

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNoUsers()
    {

        $initiativeId = Midrepo::getOrFail('initiative')->id;

        $sql   = "select users.id, users.full_name " .
            "from users left join initiative_user on (users.id = initiative_user.user_id and initiative_id = $initiativeId) " .
            "where users.state >= 2 and initiative_user.role is null order by full_name";
        $users = DB::select(DB::raw($sql));

        return Responder::json(true)->withData($users)->send();

    }

    /**
     * Activate/Deactivate a initiative
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($initiativeId)
    {
        $initiative = initiative::findOrFail($initiativeId);

        $initiative->active = ! $initiative->active;
        $initiative->save();

        return Responder::json(true)->withData($initiative->active)->send();

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {

        $initiative = Midrepo::getOrFail('initiative');

        $ini   = Input::get('ini') . '%';
        $users = User::where('full_name', 'like', $ini)
            ->where('state', '=', USER_STATE_ACTIVE)
            ->whereHas(
                'initiatives', function ($query) use ($initiative) {
                    $query->where('initiative_id', $initiative->id)->where('role', '>=', Input::get('role', ROLE_MEMBER));

                }
            )
            ->where('id', '<>', Auth::user()->id)
            ->with('initiatives')
            ->orderBy('full_name')
            ->get();

        return Responder::json(true)->withData($users)->send();

    }

    /**
     * Update initiative
     * Route: put:/api/initiative/{initiativeCode}
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws custom\exceptions\ApiException
     */
    public function update()
    {

        $initiative = Midrepo::getOrFail('initiative');

        $initiative->title       = Input::get('title');
        $initiative->description = Input::get('description');
        $initiative->purpose_of_initiative = Input::get('purpose_of_initiative');
        $initiative->stpes_of_development = Input::get('stpes_of_development');
        $initiative->planning_estimation = Input::get('planning_estimation');

        if (!$initiative->save()) {
            return Responder::json(false)->withValidator($initiative->validator)->send();
        }

        return Responder::json(true)->withData($initiative)->send();
    }


    /**
     * Stores a new initiative
     *
     * @throws custom\exceptions\ApiException
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $initiative = new Initiative;
        $initiative->setFromInput('title');
        $initiative->setFromInput('description');
        $initiative->setFromInput('purpose_of_initiative');
        $initiative->setFromInput('stpes_of_development');
        $initiative->setFromInput('planning_estimation');

        $initiative->user_id = Auth::user()->id;
		$initiative->options = json_encode(Input::get('options'));

        if(preg_match('/[^\\p{Common}\\p{Latin}]/u', $initiative->title))
        {
            $initiative->code = md5(date('U'));
        } else {
            $initiative->code = makeSlugs($initiative->title);

        }

        if (!$initiative->save()) {
            return Responder::json(false)->withValidator($initiative->validator)->send();
        }

        $initiative->users()->attach(Auth::user()->id, ['role' => ROLE_MODERATOR]);

        return Responder::json(true)->withData($initiative->code)->send();

    }


    public function getAdminList()
    {
        $initiatives = initiative::all()->toArray();
        foreach($initiatives as &$initiative)
        {
            $initiative['content_count'] = Content::where('initiative_id', $initiative['id'])->count();

            $initiative['user_count'] =  DB::table('initiative_user')->where('initiative_id', $initiative['id'])->where('role', '>=', ROLE_INVITED)->count();

            $initiative['last_visit'] =  DB::table('initiative_user')->where('initiative_id', $initiative['id'])->max('last_visit');

            $initiative['created_by'] =  User::where('id', $initiative['user_id'])->pluck('full_name');

        }

        return Responder::json(true)->withDataTransform($initiatives, 'AdmininitiativesTransformer')->send();
    }


}
