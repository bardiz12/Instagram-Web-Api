<?php

/**
* Instagram Private API (WEB)
* By Bardizba
* 24 Jan 2017
*/
class InstagramApi
{	
	protected $username;
	protected $password;
	protected $ua;
	protected $csrf_token;
	protected $CACHE_DIR = "mycac";
	function __construct($username,$password,$ua="web")
	{
		$this->keluaran = new stdClass;
		$this->keluaran->status = 0;
		$this->keluaran->reason = "";
		$this->keluaran->data = "";
		$this->username = $username;
		$this->password = $password;
		$this->useragent = $ua;
	}

	private function login($ua="web"){
		$csrf = $this->query();
		if($csrf->status){
			$token = $csrf->data;
			preg_match_all('@<script type="text/javascript">window._sharedData = (.*?);</script>@si', $token, $matches);
			//$this->keluaran->status = 1;
			

			if(!is_null($matches[1][0])){
				$data = json_decode($matches[1][0]);
				$token = $data->config->csrf_token;

				//LOGIN UTAMA
				$user_cache = dirname(__FILE__)."/".$this->CACHE_DIR."/".$this->username.".dat";
				if(!file_exists($user_cache)){
					$post_data = array(
							"username" => $this->username,
							"password" => $this->password
						);
					
					return $this->LoginQuery($token,$post_data,$user_cache,$ua);
				}else{
					if($this->AuthorityCheck()){
						$this->keluaran->status = 1;
					}else{
						
					}
				}
				//END LOGIN
			}else{
				$this->keluaran->reason = "CSRF token parsing error";
			}
		}else{
			$this->keluaran->reason = "Can't GET CSRF Token";
		}
		return $this->keluaran;
	}

	private function AuthorityCheck(){
		//query($url=NULL,$post=NULL,$set=NULL,$cookie=NULL,$heads=NULL)
		$do = $this->query("accounts/edit/",NULL,"read",dirname(__FILE__)."/".$this->CACHE_DIR."/".$this->username.".dat");
		if($do->http == 200){
			return 1;
		}
		return 0;
	}

	private function LoginQuery($token,$post_data,$user_cache){
		$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/accounts/login/ajax/");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
					//curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__).'/'.$un.'cookie.log');
					curl_setopt($ch, CURLOPT_COOKIEJAR, $user_cache);	
					//curl_setopt($ch, option, value)
					$headers = array();
					$headers[] = "Cookie: csrftoken=".$token;
					$headers[] = "Origin: https://www.instagram.com";
					$headers[] = "Accept-Language: en-US,en;q=0.8,id;q=0.6";
					$headers[] = "User-Agent: ".$this->get_ua($this->useragent);
					$headers[] = "X-Requested-With: XMLHttpRequest";
					$headers[] = "X-Csrftoken: ".$token;
					$headers[] = "X-Instagram-Ajax: 1";
					$headers[] = "Content-Type: application/x-www-form-urlencoded";
					$headers[] = "Accept: */*";
					$headers[] = "Referer: https://www.instagram.com/";
					$headers[] = "Authority: www.instagram.com";

					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					
					
					if (curl_errno($ch)) {
						$result = new stdClass();
    					$result->authenticated=0;
    					$result->status='Error:' . curl_error($ch);
					}else{
						$result = json_decode(curl_exec($ch));
					}
					curl_close ($ch);
					return $result;
	}
	private function query($url=NULL,$post=NULL,$set=NULL,$cookie=NULL,$heads=NULL){

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/".$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if($post){
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		curl_setopt($ch, CURLOPT_POST, 1);
	}

	$headers[] = "Origin: https://www.instagram.com";
	//$headers[] = "Accept-Encoding: gzip, deflate, br";
	$headers[] = "Accept-Language: en-US,en;q=0.8,id;q=0.6";
	$headers[] = "User-Agent: ".$this->get_ua($this->useragent);
	$headers[] = "X-Requested-With: XMLHttpRequest";
	$headers[] = "X-Csrftoken: AwsXybxH0RDivV1JaaOxl7JETuRM6nql";
	$headers[] = "X-Instagram-Ajax: 1";
	$headers[] = "Content-Type: application/x-www-form-urlencoded";
	$headers[] = "Accept: */*";
	$headers[] = "Referer: https://www.instagram.com/";
	$headers[] = "Authority: www.instagram.com";
	if($heads){
	if(is_array($heads)){
		foreach ($heads as $head) {
			$headers[] = $heads;
		}
	}else{
		$headers[] = $heads;
	}
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	if(!is_null($cookie)){
	switch ($set) {
		case 'set':
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
			break;

		case 'read':
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
			break;
		
		default:
			
			break;
	}
	}
	
	$hasil = new stdClass;
	$result = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$hasil->http = $httpcode;
	if (curl_errno($ch)) {
		$hasil->status = 0;
    	$hasil->info = 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
	$hasil->status = 1;
	$hasil->info = "";
	$hasil->data = json_decode($result);
	$hasil->heads = $headers;

	return  $hasil;

	}

	private function get_ua($code){
		$semua_ua = array(
					"web" => "Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25",
					"apple" => "Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25"
			);
		return $semua_ua[$code];
	}


	private function convert_array($arr){
		$object = new stdClass();
		foreach ($arr as $key => $value)
		{
    		$object->$key = $value;
		}
		return $object;
	}
	
}


?>