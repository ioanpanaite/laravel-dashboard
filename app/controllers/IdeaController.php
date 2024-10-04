 <?php

use custom\helpers\Midrepo;
use custom\helpers\Responder;

/**
 * Class InitiativeController
 */
class IdeaController extends BaseController
{

    /**
     * Delete idea
     * Route: delete:/idea/{ideaCode}
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $idea   = Midrepo::getOrFail('idea');
        $ideaId = $idea->id;

        $content = Content::where('idea_id', $ideaId);

        // delete observers fired this way
        foreach ($content as $cnt) {
            $cnt->delete();
        }

        $idea->delete();

        return Responder::json(true)->send();
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList($userid=0)
    {
		$ideas = Idea::getList($userid);
	
        return Responder::json(true)->withDataTransform($ideas, 'IdeaListTransformer')->send();
    }
	
	
    public function getQuestionList($userid=0)
    {
		$ideas = Idea::getQuestionList($userid);
	
        return Responder::json(true)->withDataTransform($ideas, 'QuestionListTransformer')->send();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne()
    {
        $idea = Midrepo::getOrFail('idea');

        if(! $idea->active && !Auth::user()->admin)
            //return Responder::json(false)->withMessage('unauthorized')->send();
        

        $idea = $idea->load('users');
        $idea = $idea->load(
            [
                'tags' => function ($q) {
                        $q->orderBy('counter', 'DESC');
                    }
            ]
        );

        print_r($idea);
        return 0;
        DB::table('idea_user')->where('user_id', Auth::user()->id)->where('idea_id', $idea->id)->update(
            ['last_visit' => now()]
        );


        $idea->role = Auth::user()->inidea($idea->id);

        return Responder::json(true)->withDataTransform($idea, 'ideaTransformer')->send();
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {

        $idea = Midrepo::getOrFail('idea');

        $users = $idea->users()->where('users.id', '<>', Auth::user()->id)->get();

        return Responder::json(true)->withDataTransform($users, 'AllUsersTransformer')->send();

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function userJoin()
    {
        $idea = Midrepo::getOrFail('idea');

        if ($idea->access == 'PU' || ($idea->access == 'PR' && Auth::user()->inidea($idea->id) == ROLE_INVITED)) {
            $user = $idea->users()->where('user_id', Auth::user()->id)->first();
            if (!$user) {
                $idea->users()->attach(Auth::user()->id, ['role' => ROLE_MEMBER]);
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

        $ideaId = Midrepo::getOrFail('idea')->id;

        $sql   = "select users.id, users.full_name " .
            "from users left join idea_user on (users.id = idea_user.user_id and idea_id = $ideaId) " .
            "where users.state >= 2 and idea_user.role is null order by full_name";
        $users = DB::select(DB::raw($sql));

        return Responder::json(true)->withData($users)->send();

    }

    /**
     * Activate/Deactivate a idea
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($ideaId)
    {
        $idea = idea::findOrFail($ideaId);

        $idea->active = ! $idea->active;
        $idea->save();

        return Responder::json(true)->withData($idea->active)->send();

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {

        $idea = Midrepo::getOrFail('idea');

        $ini   = Input::get('ini') . '%';
        $users = User::where('full_name', 'like', $ini)
            ->where('state', '=', USER_STATE_ACTIVE)
            ->whereHas(
                'ideas', function ($query) use ($idea) {
                    $query->where('idea_id', $idea->id)->where('role', '>=', Input::get('role', ROLE_MEMBER));

                }
            )
            ->where('id', '<>', Auth::user()->id)
            ->with('ideas')
            ->orderBy('full_name')
            ->get();

        return Responder::json(true)->withData($users)->send();

    }

    /**
     * Update idea
     * Route: put:/api/idea/{ideaCode}
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws custom\exceptions\ApiException
     */
    public function update()
    {
        $idea = Midrepo::getOrFail('idea');

        $idea->title       = Input::get('title');
        $idea->description = Input::get('description');
        $idea->purpose_of_idea = Input::get('purpose_of_idea');
        $idea->stpes_of_development = Input::get('stpes_of_development');
        $idea->planning_estimation = Input::get('planning_estimation');

        if (!$idea->save()) {
            return Responder::json(false)->withValidator($idea->validator)->send();
        }

        return Responder::json(true)->withData($idea)->send();
    }


    /**
     * Stores a new idea
     *
     * @throws custom\exceptions\ApiException
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $idea = new Idea;
        $idea->setFromInput('title');
        $idea->setFromInput('description');
        $idea->setFromInput('purpose_of_idea');
        $idea->setFromInput('stpes_of_development');
        $idea->setFromInput('planning_estimation');
        $idea->setFromInput('access');

        $idea->user_id = Auth::user()->id;
		$idea->options = json_encode(Input::get('options'));

        if(preg_match('/[^\\p{Common}\\p{Latin}]/u', $idea->title))
        {
            $idea->code = md5(date('U'));
        } else {
            $idea->code = makeSlugs($idea->title);

        }

        if (!$idea->save()) {
            return Responder::json(false)->withValidator($idea->validator)->send();
        }

        $idea->users()->attach(Auth::user()->id, ['role' => ROLE_MODERATOR]);

        return Responder::json(true)->withData($idea->code)->send();

    }


    public function getAdminList()
    {
        $ideas = idea::all()->toArray();
        foreach($ideas as &$idea)
        {
            $idea['content_count'] = Content::where('idea_id', $idea['id'])->count();

            $idea['user_count'] =  DB::table('idea_user')->where('idea_id', $idea['id'])->where('role', '>=', ROLE_INVITED)->count();

            $idea['last_visit'] =  DB::table('idea_user')->where('idea_id', $idea['id'])->max('last_visit');

            $idea['created_by'] =  User::where('id', $idea['user_id'])->pluck('full_name');

        }

        return Responder::json(true)->withDataTransform($ideas, 'AdminideasTransformer')->send();
    }


}
