<?php

/**
 * Class Space
 */
class Idea extends BaseModel
{

    protected $table = 'ideas';

    function __construct()
    {
        parent::__construct();
        $this->setRule('title', 'required|unique:ideas');
        $this->setRule('description', 'required');
        $this->setRule('purpose_of_idea', 'required');
        $this->setRule('terms', 'required');
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
			return DB::table('ideas')->where('user_id', '=', $userid)->get();
		}
		else{
			return DB::table('ideas')->where('user_id', '=', Auth::id())->get();
		}
		
	}
	
	
	public static function getQuestionList($userid=0){
		if($userid){
			return DB::table('questions')->where('user_id', '=', $userid)->get();
		}
		else{
			return DB::table('questions')->where('user_id', '=', Auth::id())->get();
		}
		
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
        return $this->belongsToMany('User', 'idea_user')
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
