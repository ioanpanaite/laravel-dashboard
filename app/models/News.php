<?php

use custom\interfaces\Calendarable;
use custom\interfaces\Commentable;
use custom\interfaces\Likeable;
use custom\interfaces\Starable;
use custom\interfaces\Authorizable;
use custom\interfaces\Attachable;
use custom\helpers\Midrepo;

class News extends BaseModel implements Calendarable, Commentable, Likeable, Starable, Authorizable, Attachable
{
    protected $table = "news";
    
    protected $classId = null;

    protected $extractedTags = [];
    protected $extractedMentions=[];

    public $tagsChanged = false;

    public static function boot()
    {
        parent::boot();

        self::observe(new custom\observers\CommentObserver);
        self::observe(new custom\observers\LikesObserver);
        self::observe(new custom\observers\StarObserver);
        self::observe(new custom\observers\AttachObserver);

        self::created(
            function ($model) {

                DB::table('ftindex')->insert([
                    'indexable_id' => $model->id,
                    'body'         => $model->news,
                    'indexable_type' => 'News'
                                             ]);
            }
        );

        self::deleting(
            function ($model) {
                DB::table('ftindex')->where('indexable_id', $model->id)->delete();
                DB::table('news_images')->where('id', $model->image_id)->delete();
            }
        );
    }


    /**
     * Get the list of new
     */
    public static function getList($userid=0){

		if($userid){
			return DB::table('news')->where('user_id', '=', $userid)->get();
		}
		else{
			return DB::table('news')->where('user_id', '=', Auth::id())->get();
		}		
    }
    
    public static function getNews($newsId) {

        return DB::table('news')
            ->leftJoin('news_images', 'news.image_id', '=', 'news_images.id')
            ->leftJoin('users', 'news.user_id', '=', 'users.id')
            ->where('news.id', '=', $newsId)
            ->select('news.id', 'news.news', 'news.user_id', 'news.created_at', 'news_images.image', 'users.full_name')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('Tag', 'content_tags', 'content_id', 'tag_id');
    }

    /**
     * @param $interface
     * @return bool
     */
    public function authorize($interface)
    {
        if (in_array($interface, ['Commentable', 'Likeable', 'Starable'])) {
            return Auth::User()->inSpace($this->user_id) >= ROLE_NONE;
        }

        return false;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function space()
    {
        return $this->belongsTo('Space');
    }

    /**
     * @param $query
     * @param $spaceId
     * @return mixed
     */
    public function scopeFromSpace($query, $spaceId)
    {
        return $query->where('space_id', $spaceId);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getContentDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @return mixed
     */
    public function taskSummary()
    {
        return $this->hasMany('Task')->where('archived', 0)
            ->with('user')
            ->with('calendar')
            ->with('assignedTo')
            ->where(
                function ($q) {
                    $q->where('user_id', Auth::user()->id)
                        ->orWhereHas(
                            'assignedTo', function ($q) {
                                $q->where('user_id', Auth::user()->id);
                            }
                        );
                }
            );

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany('Task');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany('Vote');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function calendar()
    {
        return $this->morphOne('Calendar', 'calendarable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany('Comment', 'commentable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function likes()
    {
        return $this->morphMany('Likes', 'likeable');
    }


    /**
     * @return mixed
     */
    public function starred()
    {
        return $this->morphMany('Star', 'starable')->where('user_id', Auth::user()->id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function stars()
    {
        return $this->morphMany('Star', 'starable');
    }

    /**
     * @return mixed
     */
    public function myStars()
    {
        return $this->morphMany('Star', 'starable')->where('user_id', 1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany('Attachment', 'attachable');
    }

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('User')->select('users.id', 'users.code', 'users.full_name');
    }

    /**
     * @return mixed
     */
    public function sharedFrom()
    {
        return $this->belongsTo('User','shared_from_id')->select('users.id', 'users.code', 'users.full_name');
    }



    /**
     * @param $oldName
     * @param null $newName
     */
    public function renameTag($oldName, $newName = null)
    {
        $text    = $this->content_text;
        $newName = $newName ? '[#' . $newName . ']' : '';

        $text = str_replace('[#' . $oldName . ']', $newName, $text);

        $this->content_text = $text;
        $this->save();
    }


    /**
     * @param $str
     * @return mixed|string
     */
    public function prepareHtmlText($str)
    {
        $str = strip_tags($str);

        foreach ($this->extractedTags as $tag) {
            $tag = '#' . $tag;
            $str = str_replace($tag, '<span class="hashtag">' . $tag . '</span>', $str);
        }

        for ($i = 0; $i < count($this->extractedMentions); ++$i) {
            $mention = $this->extractedMentions[$i];
            if ($user = User::existsByCode($mention, Midrepo::getOrFail('space')->id)) {
                $mention                     = '@' . $mention;
                $str                         = str_replace(
                    $mention, '<span class="mention">@' . $user->full_name . '</span>', $str
                );
                $this->extractedMentions[$i] = $user->id;
            } else {
                unset($this->extractedMentions[$i]);
            }
        }

        // links
        $str = preg_replace_callback(
            '#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', function ($item) {
                //'<a href="$1" target="blank">$1</a>',
                if (!starts_with($item[0], 'http://') && !starts_with($item[0], 'https://')) {
                    $item[0] = 'http://' . $item[0];
                }

                return "<a href='{$item[0]}' target='_blank'>{$item[0]}</a>";

            }, $str
        );

        //crlf
        $str = nl2br($str);

        return $str;

    }


    /**
     *
     */
    public function createFromInput()
    {

        $this->setRule('content_text', 'required');

        $this->extractedTags = extractTags(Input::get('content_text'), '#');

        $this->extractedMentions = extractTags(Input::get('content_text'), '@');

        $this->content_text = $this->prepareHtmlText(Input::get('content_text'));

        $this->space_id   = Midrepo::getOrFail('space')->id;
        $this->user_id    = Auth::user()->id;
        $this->updated_by = Auth::user()->id;
        $this->class_id   = $this->classId;


    }

    /**
     * @param $contentId
     * @return mixed|null
     */
    public static function getSpaceId($contentId)
    {
        $content = self::find($contentId);

        if (isset($content)) {
            return $content->space_id;
        }

        return null;
    }

    /**
     * @param $query
     * @return mixed
     */
    public static function scopeStream($query)
    {
        return $query->with('likes.user')
            ->with(
                [
                    'comments' => function ($query) {
                            $query->orderBy('id')
                                ->with('user')
                                ->with('likes')
                                ->with('likes.user');
                        }
                ]
            )
            ->with('starred')
            ->orderBy('id', 'desc');
    }

    /**
     * Set the image in attached.
     */
    public static function storeImage($url){
		$user_id = Auth::id();
		$id = DB::table('news_images')->insertGetId(
				array('user_id' => $user_id, 'image' => $url)
			);
		if($id){
			return $id;
		}
		else{
			return false;
		}
    }    

    /**
     * @return mixed
     */
    public function newsImage() {
        return $this->belongsTo('NewsImages', 'image_id')->select('news_images.id', 'news_images.image');
    }
}