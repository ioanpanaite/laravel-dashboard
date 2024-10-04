<?php

use custom\helpers\Midrepo;
use custom\helpers\Responder;

/**
 * Class InitiativeController
 */
class QuestionController extends BaseController
{

    /**
     * Delete space
     * Route: delete:/space/{spaceCode}
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $space   = Midrepo::getOrFail('space');
        $spaceId = $space->id;

        $content = Content::where('space_id', $spaceId);

        // delete observers fired this way
        foreach ($content as $cnt) {
            $cnt->delete();
        }

        $space->delete();

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
        $question = Midrepo::getOrFail('question');

        // if(! $question->active && !Auth::user()->admin)
        if(! $question->active && !Auth::user())
            return Responder::json(false)->withMessage('unauthorized')->send();

        return Responder::json(true)->withDataTransform($question, 'IdeaTransformer')->send();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionList() {

        $questions = Question::getListAnyone();

        return Responder::json(true)->withData($questions)->send();
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {

        $space = Midrepo::getOrFail('space');

        $users = $space->users()->where('users.id', '<>', Auth::user()->id)->get();

        return Responder::json(true)->withDataTransform($users, 'AllUsersTransformer')->send();

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function userJoin()
    {
        $space = Midrepo::getOrFail('space');

        if ($space->access == 'PU' || ($space->access == 'PR' && Auth::user()->inSpace($space->id) == ROLE_INVITED)) {
            $user = $space->users()->where('user_id', Auth::user()->id)->first();
            if (!$user) {
                $space->users()->attach(Auth::user()->id, ['role' => ROLE_MEMBER]);
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

        $spaceId = Midrepo::getOrFail('space')->id;

        $sql   = "select users.id, users.full_name " .
            "from users left join space_user on (users.id = space_user.user_id and space_id = $spaceId) " .
            "where users.state >= 2 and space_user.role is null order by full_name";
        $users = DB::select(DB::raw($sql));

        return Responder::json(true)->withData($users)->send();

    }

    /**
     * Activate/Deactivate a space
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($spaceId)
    {
        $space = Space::findOrFail($spaceId);

        $space->active = ! $space->active;
        $space->save();

        return Responder::json(true)->withData($space->active)->send();

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {

        $space = Midrepo::getOrFail('space');

        $ini   = Input::get('ini') . '%';
        $users = User::where('full_name', 'like', $ini)
            ->where('state', '=', USER_STATE_ACTIVE)
            ->whereHas(
                'spaces', function ($query) use ($space) {
                    $query->where('space_id', $space->id)->where('role', '>=', Input::get('role', ROLE_MEMBER));

                }
            )
            ->where('id', '<>', Auth::user()->id)
            ->with('spaces')
            ->orderBy('full_name')
            ->get();

        return Responder::json(true)->withData($users)->send();

    }

    /**
     * Update space
     * Route: put:/api/space/{spaceCode}
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
     * Stores a new space
     *
     * @throws custom\exceptions\ApiException
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $question = new Question;
        $question->setFromInput('title');
        $question->setFromInput('description');
        $question->setFromInput('reward');
        $question->setFromInput('sector');

        $question->user_id = Auth::user()->id;
		$question->options = json_encode(Input::get('options'));

        if(preg_match('/[^\\p{Common}\\p{Latin}]/u', $question->title))
        {
            $question->code = md5(date('U'));
        } else {
            $question->code = makeSlugs($question->title);

        }

        if (!$question->save()) {
            return Responder::json(false)->withValidator($question->validator)->send();
        }

        //$question->users()->attach(Auth::user()->id, ['role' => ROLE_MODERATOR]);

        return Responder::json(true)->withData($question->code)->send();

    }


    public function getAdminList()
    {
        $spaces = Space::all()->toArray();
        foreach($spaces as &$space)
        {
            $space['content_count'] = Content::where('space_id', $space['id'])->count();

            $space['user_count'] =  DB::table('space_user')->where('space_id', $space['id'])->where('role', '>=', ROLE_INVITED)->count();

            $space['last_visit'] =  DB::table('space_user')->where('space_id', $space['id'])->max('last_visit');

            $space['created_by'] =  User::where('id', $space['user_id'])->pluck('full_name');

        }

        return Responder::json(true)->withDataTransform($spaces, 'AdminSpacesTransformer')->send();
    }


}
