<?php
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * Class User
 */
class User extends BaseModel implements UserInterface, RemindableInterface
{
    protected $table = 'users';
    protected $hidden = array('password');

    function __construct()
    {
        parent::__construct();
        $this->setRule('email', 'required|email|unique:users');

    }

    public static function boot() {
        parent::boot();

        self::deleting(
            function ($model) {
                DB::table('space_user')->where('user_id', $model->id)->delete();
                DB::table('initiative_user')->where('user_id', $model->id)->delete();
                DB::table('spaces')->where('user_id', $model->id)->delete();
                DB::table('news')->where('user_id', $model->id)->delete();
                DB::table('task_users')->where('user_id', $model->id)->delete();
                DB::table('user_videos')->where('user_id', $model->id)->delete();
                DB::table('user_images')->where('user_id', $model->id)->delete();
                DB::table('content')->where('user_id', $model->id)->delete();
            }
        );
    }

    /**
     * @param $userId
     * @param $spaceId
     * @return int
     */
    public static function getUserSpaceInfo($userId, $spaceId)
    {
        $user = DB::table('space_user')->where('user_id', $userId)->where('space_id', $spaceId)->first();

        if (!$user) {
            return ROLE_NONE;
        }

        return $user->role;
    }

    /**
     *
     */
    public function invite()
    {

        $exp = env('invitation_expire', 1);
        $ext = '+'.$exp+' days';

        $this->state             = USER_STATE_INVITED;
        $this->activation_code   = md5(rand(10, 100) . Input::get('email')) . md5(microtime());
        $this->activation_expire = date('Y-m-d H:i:s', strtotime("+1 days"));
        $this->create_spaces     = env('DEFAULT_CRETE_SPACES', true);

    }

    /**
     * @param $query
     * @param $ini
     * @return mixed
     */
    public function scopeInitials($query, $ini)
    {
        if (!empty($ini)) {
            return $query->where('full_name', 'LIKE', '%' . $ini . '%');
        }
    }

    /**
     * @param $query
     * @param $state
     * @return mixed
     */
    public function scopeState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getStarredAttribute($value)
    {
        return json_decode($value, true);
    }


    /**
     * Check if the user belongs to a space
     *
     * return role if belongs, -1 if not
     */
    public function inSpace($spaceId)
    {
        foreach ($this->spaces as $space) {
            if ($space->id == $spaceId) {
                return $space->pivot->role;
            }
        }

        return -1;
    }
	
	
    /**
     * Check if the user belongs to a initiative
     *
     * return role if belongs, -1 if not
     */
    public function inInitiative($initiativeId)
    {

        foreach ($this->initiatives as $initiative) {
            if ($initiative->id == $initiativeId) {
                return $initiative->pivot->role;
            }
        }

        return -1;
    }
	
    /**
     * Check if the user belongs to a idea
     *
     * return role if belongs, -1 if not
     */
    public function inIdea($ideaId)
    {

        foreach ($this->ideas as $idea) {
            if ($idea->id == $ideaId) {
                return $idea->pivot->role;
            }
        }

        return -1;
    }

    /**
     * @return $this
     */
    public function spaces()
    {
        return $this->belongsToMany('Space', 'space_user')->withPivot('role', 'last_visit');
    }
	
    /**
     * @return $this
     */
    public function initiatives()
    {
        return $this->belongsToMany('Initiative', 'initiative_user')->withPivot('role', 'last_visit');
    }
	
    /**
     * @return $this
     */
    public function ideas()
    {
        return $this->belongsToMany('Idea', 'idea_user')->withPivot('role', 'last_visit');
    }

    /**
     * @return mixed
     */
    public function spaceMember()
    {
        return $this->belongsToMany('Space', 'space_user')->withPivot('role')->where('role', '>=', ROLE_MEMBER);
    }

    /**
     * @return mixed
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * @param string $value
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * @param $code
     * @param $spaceId
     * @return bool|\Illuminate\Database\Eloquent\Model|null|static
     */
    public static function existsByCode($code, $spaceId)
    {
        $u = User::where('code', $code)
            ->where('state', '=', USER_STATE_ACTIVE)
            ->whereHas(
                'spaces', function ($query) use ($spaceId) {
                    $query->where('space_id', $spaceId);
                }
            )
            ->where('id', '<>', Auth::user()->id)
            ->first();

        if (isset($u)) {
            return $u;
        }

        return false;
    }


    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    /**
     * User login
     */
    public function login($usr_id='')
    {
        Auth::logout();
        $u = static::find($usr_id);
        Auth::login($u);

    }
}
