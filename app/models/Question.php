<?php

/**
 * Class Space
 */
class Question extends BaseModel
{

    protected $table = 'questions';

    function __construct()
    {
        parent::__construct();
        $this->setRule('title', 'required|unique:questions');
        // $this->setRule('description', 'required');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany('Tag');
    }
	
	public static function getList($userid=0){
		if($userid){
			return DB::table('questions')->where('user_id', '=', $userid)->get();
		}
		else{
			return DB::table('questions')->where('user_id', '=', Auth::id())->get();
		}
		
    }
    
    /**
     * Get the list of questions to anyone
     */
    public static function getListAnyone() {
        return DB::table('questions')
            ->select('users.full_name', 'users.organization', 'users.position', 'questions.*')
            ->leftJoin('users', 'users.id', '=', 'questions.user_id')
            ->orderBy('questions.updated_at', 'DESC')
            ->get();
    }


    /**
     * @param $code
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public static function getByCode($code)
    {
        return self::where('code', $code)->first();
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function folders()
    {
        return $this->hasMany('Folder');
    }

    /**
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany('User', 'initiative_user')
            ->where('state', USER_STATE_ACTIVE)
            ->withPivot('role', 'last_visit')
            ->orderBy('last_visit', 'DESC')->select('full_name', 'code');
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getOptionsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classes()
    {
        return $this->hasMany('CustomClass');
    }


    /**
     * Determine if a content class is valid for this space.
     * return className or False
     *
     * @param null $classId
     * @return bool|string
     */
    public function validateClass($classId = null)
    {
        if (!$classId) {
            $classId = Route::current()->getParameter('classId');
        }

        $content_classes = ['Message', 'Link', 'Table', 'Chart', 'Poll', 'ContentEvent', 'Location', 'Wiki'];

        if ($classId < count($content_classes)) {
            return $content_classes[$classId];

        } else {
            if ($cl = $this->classes()->find($classId)) {
                // custom class is active
                if ($cl->status) {
                    return "customcontent";
                }
            }
        }

        return false;
    }

}
