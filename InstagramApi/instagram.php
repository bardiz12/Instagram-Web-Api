<?php

/**
* Instagram Web Private API
* By Bardizba
* 24 Jan 2017
*/
class InstagramApi
{	
	protected $username;
	protected $username_id;
	protected $password;
	protected $useragent;
	protected $IGtoken;
	protected $IGtokenPATH;
	//protected $IGdataPATH;
	protected $DIR_DATA = "mycac";

	function __construct($username,$password,$ua="web")
	{
		$this->output = new stdClass;
		$this->output->ok = 0;
		$this->username = $username;
		$this->password = $password;
		$this->useragent = $ua;
		$this->IGtokenPATH = dirname(__FILE__)."/".$this->DIR_DATA."/".$this->username."-token.dat";
		$this->IGdataPATH = dirname(__FILE__)."/".$this->DIR_DATA."/".$this->username."-data.dat";
		if(!file_exists($this->IGdataPATH)){
			file_put_contents($this->IGdataPATH, "");
		}

		if(!file_exists($this->IGtokenPATH)){
			file_put_contents($this->IGtokenPATH, "");
		}else{
			$this->IGtoken = file_get_contents($this->IGtokenPATH);
		}
	}

	public function login($force=0){
		$token = $this->getToken();
		if($token->ok){
			file_put_contents($this->IGtokenPATH, $token->data);
			$this->IGtoken = file_get_contents($this->IGtokenPATH);
		}
		//Force Login
		if($force){
			if(file_exists($this->IGdataPATH)){
				file_put_contents($this->IGdataPATH, "");
				$do = $this->LoginQuery();
				if($do->ok){
					$this->output->ok=1;
					$this->output->data = $do->data;
				}
			}else{
				$this->output->ok=0;
				$this->output->reason="Can't Force Login";
				$this->output->data = "";
			}
		}else{

			if(!$this->AuthorityCheck()->ok){
				$do = $this->LoginQuery();
				if($do->ok){
					$this->output->ok=1;
				}else{
					$this->output->reason="Login ERROR : Can't Authenticate!";
				}
			}else{
				$this->output->ok=1;
			}

			$getme = $this->AccountInfo($this->username);
			if($getme->ok){
				$this->output->data = $getme->data;
			}
				
			
		}
			$this->username_id = $this->output->data->id;
			return $this->output;
			
	}

	public function refreshDATA(){
		$token = $this->getToken();
		if($token->ok){
			file_put_contents($this->IGtokenPATH, $token->data);
			$this->IGtoken = file_get_contents($this->IGtokenPATH);
		//return $this->IGtoken;
		}

	}

	public function AccountInfo(){
		$do = $this->query($this->username."/?__a=1");
		if($do->http==200){
			$this->output->ok=1;
			$this->output->data = json_decode($do->data);
		}else if($do->http==404){
			$this->output->ok=0;
			$this->output->reason="Requested Query not Found";
		}

		return $this->output;
	}

	public function getUserInfo($username_id){
		$do = $this->query($username_id."/?__a=1");
		if($do->http==200){
			$this->output->ok=1;
			$this->output->data = $do->data->user;
		}else{
			$this->output->ok=0;
			$this->output->reason="Requested Query not Found";
		}

		return $this->output;
	}

	public function getTimeline(){
		$do = $this->query("?__a=1");
		if($do->http==200){
			$this->output->ok=1;
			$this->output->data = $do->data->feed->media;
		}else if($do->http==404){
			$this->output->ok=0;
			$this->output->reason="Requested Query not Found";
		}

		return $this->output;
	}

	public function getMediaInfo($code){
		$do = $this->query("p/".$code."/?__a=1");
		if($do->http==200){
			$this->output->ok=1;
			$this->output->data = $do->data;
		}else if($do->http==404){
			$this->output->ok=0;
			$this->output->reason="Requested Query not Found";
		}
		return $this->output;
	}

	public function getUserMedia($userid,$after=0,$count=12){
		$post = "q=ig_user(".$userid.")+%7B+media.after(".$after."%2C+".$count.")+%7B%0A++count%2C%0A++nodes+%7B%0A++++caption%2C%0A++++code%2C%0A++++comments+%7B%0A++++++count%0A++++%7D%2C%0A++++comments_disabled%2C%0A++++date%2C%0A++++dimensions+%7B%0A++++++height%2C%0A++++++width%0A++++%7D%2C%0A++++display_src%2C%0A++++id%2C%0A++++is_video%2C%0A++++likes+%7B%0A++++++count%0A++++%7D%2C%0A++++owner+%7B%0A++++++id%0A++++%7D%2C%0A++++thumbnail_src%2C%0A++++video_views%0A++%7D%2C%0A++page_info%0A%7D%0A+%7D&ref=users%3A%3Ashow";
		//$this->refreshDATA();
		$do = $this->query("query/",$post);
		if($do->http==200){
			$this->output->ok = 1;
			$this->output->data = $do->data->media;
		}else{
			$this->output->data = "";
			$this->output->reason = "Invalid Query Response";
		}
		return $this->output;
	}

	public function getPostComment($code,$after=FALSE){
		if(!$after){
			$do = $this->query("p/".$code."/?__a=1");
		}else{
			$post = "q=ig_shortcode(".$code.")+%7B%0A++comments.before(%0A++++++++++++".$after."%2C%0A++++++++++++20%0A++++++++++)+%7B%0A++++count%2C%0A++++nodes+%7B%0A++++++id%2C%0A++++++created_at%2C%0A++++++text%2C%0A++++++user+%7B%0A++++++++id%2C%0A++++++++profile_pic_url%2C%0A++++++++username%0A++++++%7D%0A++++%7D%2C%0A++++page_info%0A++%7D%0A%7D%0A&ref=media%3A%3Ashow";
			$do = $this->query("query/",$post);
		}
		if($do->http==200){
			$this->output->ok=1;
			if(!$after){
			$this->output->data=$do->data->media->comments;
			}else{
				$this->output->data=$do->data;
			}
		}else{
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}
		return $this->output;
	}

	public function like($media_id){
		$do = $this->query("web/likes/".$media_id."/like/");
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function unlike($media_id){
		$do = $this->query("web/likes/".$media_id."/unlike/");
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function comment($media_id,$text){
		$do = $this->query("web/comments/".$media_id."/add/","comment_text=".$text);
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function deleteComment($media_id,$comment_id){
		$do = $this->query("web/comments/".$media_id."/delete/".$comment_id."/");
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function followUser($username_id){
		$do = $this->query("web/friendships/".$username_id."/follow/");
		if($do->http==200){
			$this->output->ok=1;
			$this->output->data=$do->data;

		}else if($do->http==403){
			$this->output->ok=0;
			$this->output->reason="Can't Follow, Too Many request!";
		}else{
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}
		return $this->output;
	}

	public function unfollowUser($username_id){
		$do = $this->query("web/friendships/".$username_id."/unfollow/");
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function getUserFollowing($username_id,$after_page=null){
		if(is_null($after_page)){
			$post = "q=ig_user(".$username_id.")+%7B%0A++follows.first(10)+%7B%0A++++count%2C%0A++++page_info+%7B%0A++++++end_cursor%2C%0A++++++has_next_page%0A++++%7D%2C%0A++++nodes+%7B%0A++++++id%2C%0A++++++is_verified%2C%0A++++++followed_by_viewer%2C%0A++++++requested_by_viewer%2C%0A++++++full_name%2C%0A++++++profile_pic_url%2C%0A++++++username%0A++++%7D%0A++%7D%0A%7D%0A&ref=relationships%3A%3Afollow_list";
		}else{
			$post = "q=ig_user(".$username_id.")+%7B%0A++follows.after(".$after_page."%2C+10)+%7B%0A++++count%2C%0A++++page_info+%7B%0A++++++end_cursor%2C%0A++++++has_next_page%0A++++%7D%2C%0A++++nodes+%7B%0A++++++id%2C%0A++++++is_verified%2C%0A++++++followed_by_viewer%2C%0A++++++requested_by_viewer%2C%0A++++++full_name%2C%0A++++++profile_pic_url%2C%0A++++++username%0A++++%7D%0A++%7D%0A%7D%0A&ref=relationships%3A%3Afollow_list&query_id=17867281162062470";
		}
		$do = $this->query("query/",$post);
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function getUserFollowers($username_id,$after_page=null){
		if(is_null($after_page)){
			$post = "q=ig_user(".$username_id.")+%7B%0A++followed_by.first(10)+%7B%0A++++count%2C%0A++++page_info+%7B%0A++++++end_cursor%2C%0A++++++has_next_page%0A++++%7D%2C%0A++++nodes+%7B%0A++++++id%2C%0A++++++is_verified%2C%0A++++++followed_by_viewer%2C%0A++++++requested_by_viewer%2C%0A++++++full_name%2C%0A++++++profile_pic_url%2C%0A++++++username%0A++++%7D%0A++%7D%0A%7D%0A&ref=relationships%3A%3Afollow_list";
		}else{
			$post = "q=ig_user(".$username_id.")+%7B%0A++followed_by.after(".$after_page."%2C+10)+%7B%0A++++count%2C%0A++++page_info+%7B%0A++++++end_cursor%2C%0A++++++has_next_page%0A++++%7D%2C%0A++++nodes+%7B%0A++++++id%2C%0A++++++is_verified%2C%0A++++++followed_by_viewer%2C%0A++++++requested_by_viewer%2C%0A++++++full_name%2C%0A++++++profile_pic_url%2C%0A++++++username%0A++++%7D%0A++%7D%0A%7D%0A&ref=relationships%3A%3Afollow_list";
		}
		$do = $this->query("query/",$post);
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function blockUser($username_id){
		$do = $this->query("web/friendships/".$username_id."/block/");
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function unblockUser($username_id){
		$do = $this->query("web/friendships/".$username_id."/unblock/");
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function seeNotif(){
		$do = $this->query("accounts/activity/?__a=1");
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function getMediabyHashtag($hashtag,$after_page=NULL){
		if(is_null($after_page)){
			$do = $this->query("explore/tags/".$hashtag."/?__a=1");
		}else{
			$post = "q=ig_hashtag(".$hashtag.")+%7B+media.after(".$after_page."%2C+6)+%7B%0A++count%2C%0A++nodes+%7B%0A++++caption%2C%0A++++code%2C%0A++++comments+%7B%0A++++++count%0A++++%7D%2C%0A++++comments_disabled%2C%0A++++date%2C%0A++++dimensions+%7B%0A++++++height%2C%0A++++++width%0A++++%7D%2C%0A++++display_src%2C%0A++++id%2C%0A++++is_video%2C%0A++++likes+%7B%0A++++++count%0A++++%7D%2C%0A++++owner+%7B%0A++++++id%0A++++%7D%2C%0A++++thumbnail_src%2C%0A++++video_views%0A++%7D%2C%0A++page_info%0A%7D%0A+%7D&ref=tags%3A%3Ashow";
			$do = $this->query("query/",$post);
		}
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function editAccount($name,$email,$phone_number,$gender,$bio,$url){
		$post = array(
			'first_name' => $name,
			'email' => $email,
			'username' => $this->username,
			'phone_number' => $phone_number,
			'gender' => $gender,
			'biography' => $bio,
			'external_url' => $url,
			'chaining_enabled' => "on"
			);

		$do = $this->query("accounts/edit/",$post);
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}


	public function getExplore(){
		$post="q=ig_sul()+%7B%0A++nodes+%7B%0A++++biography%2C%0A++++followed_by+%7B%0A++++++count%0A++++%7D%2C%0A++++followed_by_viewer%2C%0A++++full_name%2C%0A++++id%2C%0A++++is_private%2C%0A++++is_verified%2C%0A++++is_viewer%2C%0A++++media.first(3)+%7B%0A++++++nodes+%7B%0A++++++++code%2C%0A++++++++display_src%2C%0A++++++++id%2C%0A++++++++thumbnail_src%0A++++++%7D%0A++++%7D%2C%0A++++profile_pic_url%2C%0A++++requested_by_viewer%2C%0A++++username%0A++%7D%0A%7D%0A&ref=explore%3A%3Asul";
		$do = $this->query("query/",$post);
		if(!$do->http==200){
			$this->output->ok=0;
			$this->output->data="";
			$this->output->reason="Query Error";
		}else{
			$this->output->ok=1;
			$this->output->data=$do->data;
		}
		return $this->output;
	}

	public function logout(){
		$do = $this->query("accounts/logout/");
		if($do->http==302){
			$this->output->ok=1;
			$this->output->data->status="Logout success";
		}else{
			$this->output->ok=0;
			$this->output->reason="Logout Error";
		}
	}
	
	public function getToken($online=TRUE){
		if($online==TRUE){
		$csrf = $this->query();
		if($csrf->ok){
					$token = $csrf->data;
					preg_match_all('@<script type="text/javascript">window._sharedData = (.*?);</script>@si', $token, $matches);
						$data = json_decode($matches[1][0]);
						$token = $data->config->csrf_token;
						$this->output->ok=1;
						$this->output->data=$token;

				}else{
					$this->output->reason = "Can't GET CSRF Token";
				}
		}else{
			$data = file_get_contents($this->IGdataPATH);
			preg_match_all("@csrftoken	(.*?)\n@si", $data, $matches);
			if(!is_null($matches[1][0])){
				$this->output->ok=1;
				$this->output->data=$matches[1][0];
			}else{
				return $this->getToken();
			}
		}
		return $this->output;
	}

	private function LoginQuery(){
		$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/accounts/login/ajax/");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$post_data = array(
							"username" => $this->username,
							"password" => $this->password
						);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
					curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGdataPATH);
					curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGdataPATH);	
					$headers = array();
					//$headers[] = "Cookie: csrftoken=".$this->IGtoken;
					$headers[] = "Origin: https://www.instagram.com";
					$headers[] = "Accept-Language: en-US,en;q=0.8,id;q=0.6";
					$headers[] = "User-Agent: ".$this->get_ua($this->useragent);
					$headers[] = "X-Requested-With: XMLHttpRequest";
					$headers[] = "X-Csrftoken: ".$this->IGtoken;
					$headers[] = "X-Instagram-Ajax: 1";
					$headers[] = "Content-Type: application/x-www-form-urlencoded";
					$headers[] = "Accept: */*";
					$headers[] = "Referer: https://www.instagram.com/";
					$headers[] = "Authority: www.instagram.com";

					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					
					
					if (curl_errno($ch)) {
    					$this->output->reason='Error:' . curl_error($ch);
					}else{
						$this->output->ok=1;
						$this->output->data = json_decode(curl_exec($ch));
					}
					curl_close ($ch);
					return $this->output;
	}

	private function query($url=NULL,$post=NULL){

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/".$url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIESESSION, 1);	
	curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGdataPATH);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGdataPATH);

	if($post){
		if(is_array($post)){
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		}else{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		curl_setopt($ch, CURLOPT_POST, 1);
		
	}
	
	//$headers[] = "Cookie: csrftoken=".$this->IGtoken."; ";
	$headers[] = "Origin: https://www.instagram.com";
	//$headers[] = "Accept-Encoding: gzip, deflate, br";
	$headers[] = "Accept-Language: en-US,en;q=0.8,id;q=0.6";
	$headers[] = "User-Agent: ".$this->get_ua($this->useragent);
	$headers[] = "X-Requested-With: XMLHttpRequest";
	$headers[] = "X-Csrftoken: ".$this->IGtoken;
	$headers[] = "X-Instagram-Ajax: 1";
	$headers[] = "Content-Type: application/x-www-form-urlencoded";
	$headers[] = "Accept: */*";
	$headers[] = "Referer: https://www.instagram.com/";
	$headers[] = "Authority: www.instagram.com";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


	$hasil = new stdClass;
	$result = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$hasil->http = $httpcode;
	if (curl_errno($ch)) {
		$hasil->ok = 0;
    	$hasil->info = 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
	$hasil->ok = 1;
	$hasil->info = "";
	$hasil->data = json_decode($result);
	if($hasil->data == ""){
		$hasil->data = $result;
	}
	//$hasil->heads = $headers;

	return  $hasil;

	}

	private function AuthorityCheck(){
		$check = $this->query("accounts/edit/");
		if($check->http==200){
			$this->output->ok = 1;
		}else{
			$this->output->reason = "Not Loged in";
			$this->output->ok = 0;
		}
		return $this->output;
	}
	private function get_ua($code){
		$semua_ua = array(
					"web" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36",
					"apple" => "Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25"
			);
		return $semua_ua[$code];
	}
}

?>
