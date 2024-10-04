<?php

/**
 * Class Message
 */
class Profile extends Content
{

    protected $table = 'users';

    function __construct()
    {
        parent::__construct();
    }
	
	public static function storeFollow($user_id,$follower_id){
		$id = DB::table('followers')->insert(
				array('user_id' => $user_id, 'follower_id' => $follower_id)
			);
		if($id){
			return "1";
		}
		else{
			return "0";
		}
	}
	
	public static function getFollowStatus($user_id,$follower_id){
		$res = DB::table('followers')->where('user_id', $user_id)->where('follower_id', $follower_id)->get();
		if(!empty($res)){
			return "1";
		}
		else{
			return "0";
		}
		
	}
	
	public static function deleteFollow($user_id,$follower_id){
		$id = DB::table('followers')->where('user_id', $user_id)->where('follower_id', $follower_id)->delete();
		if($id){
			return "1";
		}
		else{
			return "0";
		}
	}
	
	public static function getFollowersList($user_id=0){
		if(!$user_id) $user_id = Auth::id();
		
		$result = DB::table('users')
			->join('followers', 'users.id', '=', 'followers.follower_id')
			->select('users.full_name', 'users.phone', 'users.organization', 'users.email', 'users.id')
			->where('followers.user_id', $user_id)
			->get();
		return $result;
	}
	
	public static function getFollowingList($user_id=0){
		if(!$user_id) $user_id = Auth::id();
		
		$result = DB::table('users')
			->join('followers', 'users.id', '=', 'followers.user_id')
			->select('users.full_name', 'users.phone', 'users.organization', 'users.email','users.id')
			->where('followers.follower_id', $user_id)
			->get();
		return $result;
	}
	
	public static function getUserImages($user_id=0){
		if(!$user_id) $user_id = Auth::id();
		
		$result = DB::table('user_images')->where('user_id', $user_id)->get();
		return $result;
	}
	
	public static function getUserVideos($user_id=0){
		if(!$user_id) $user_id = Auth::id();
		
		$result = DB::table('user_videos')->where('user_id', $user_id)->orderBy('id', 'desc')->get();
		return $result;
	}
	
	public static function storeImage($url){
		$user_id = Auth::id();
		$id = DB::table('user_images')->insert(
				array('user_id' => $user_id, 'url' => $url)
			);
		if($id){
			return "1";
		}
		else{
			return "0";
		}
	}
	
	public static function storeVideo($url){
		$user_id = Auth::id();
		$id = DB::table('user_videos')->insert(
				array('user_id' => $user_id, 'url' => $url)
			);
		if($id){
			return "1";
		}
		else{
			return "0";
		}
	}
	
	public static function storeReview($to,$content){
		$user_id = Auth::id();
		$id = DB::table('user_reviews')->insert(
				array('post_to' => $to, 'content' => $content,'post_from' => $user_id)
			);
		if($id){
			return "1";
		}
		else{
			return "0";
		}
	}

	public static function storeComment($to,$content){
		$user_id = Auth::id();
		$id = DB::table('user_comments')->insert(
				array('post_to' => $to, 'content' => $content,'post_from' => $user_id)
			);
		if($id){
			return "1";
		}
		else{
			return "0";
		}
	}	
	
	public static function getUserReviews($user_id=0){
		if(!$user_id) $user_id = Auth::id();
		
		$result = DB::table('users')
			->join('user_reviews', 'users.id', '=', 'user_reviews.post_from')
			->select('users.full_name', 'user_reviews.content', 'user_reviews.created')
			->where('user_reviews.post_to', $user_id)
			->get();
		return $result;
	}
	
	public static function getUserComments($user_id=0){
		if(!$user_id) $user_id = Auth::id();
		
		$result = DB::table('users')
			->join('user_comments', 'users.id', '=', 'user_comments.post_from')
			->select('users.full_name', 'user_comments.content', 'user_comments.created')
			->where('user_comments.post_to', $user_id)
			->get();
		return $result;
	}
	
	public static function getUsersActivities($user_id){
		$result = array();
		if($user_id){
		$result = DB::table('content')
			->join('spaces', 'spaces.id', '=', 'content.space_id')
			->join('users', 'users.id', '=', 'content.user_id')
			->select('content.user_id','content.id','users.full_name','spaces.title','spaces.code', 'content.content_text', 'content.created_at','content.class_id')
			->whereIn('content.user_id', $user_id)
			->get();
		}
		return $result;		
	}
	
}
