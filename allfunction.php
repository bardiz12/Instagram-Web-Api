<?php

//requiring the instagramwebapi
require_once "InstagramApi/instagram2.php";

//Define user
$ig = new InstagramApi("username","password");

//Login with username and password given when defining new Api
$do = $ig->login();

/**
   * Get User Information
   *
   * @param numeric $username_id
   *   Target Instagram ID 
   */
$do = $ig->userInfo($username_id);


/**
   * Get Owner User Information
   *
   * @param numeric $username_id
   *   Target Instagram ID 
   */
$do = $ig->AccountInfo();


/**
   * Get Media Information
   *
   * @param string $code
   *   Target Instagram Post Code.
   */
$do = $ig->getMediaInfo($code);

/**
   * Get Some Media from user
   *
   * @param numeric $userid
   *   Target instagram User ID
   * @param string $after
   *   End cursor of instagram media result (default NULL)
   * @param int $count
   *   Count of media you want to get ( default 12)
   */
$do = $ig->GetUserMedia($userid,$after,$count);
$do = $ig->like("media_id");
$do = $ig->unlike("media_id");
$do = $ig->comment("media_id","text");
$do = $ig->deleteComment("media_id","comment_id");
$do = $ig->followUser("username_id");
$do = $ig->unfollowUser("username_id");
$do = $ig->getUserFollowing("username_id","after_page=null)";
$do = $ig->getUserFollowers("username_id","after_page=null)";
$do = $ig->blockUser("username_id");
$do = $ig->getTimeline();
$do = $ig->unblockUser("username_id");
$do = $ig->seeNotif();
$do = $ig->getMediabyHashtag($hashtag,$after_page=NULL);
$do = $ig->editAccount($name,$email,$phone_number,$gender,$bio,$url);
$do = $ig->getExplore();
$do = $ig->logout();


print_r($do);

//$do = $ig->login();
//$do = $ig->getMediaInfo('BPm136hjjSU');
//$do = ;
//$do = $ig->refreshDATA();


/*foreach ($do as $dat) {
	$id= $dat->id;
	print_r($ig->like($id));
	echo "\n\n";
}*/
//$do = $ig->deleteComment(1433228518993229247,17859619069116020);
//$do = $ig->getUserFollowing("1244548587","AQDXXbh2wvE3qiAjyhS0-E9ty4SlnyuYFloZD9t5fYTysw0kcjoeOz51lR-AwiFLZNv71uxsDX_HubgN8jawV3r-HFsZV4LivBEcI2XMMwPwxVVzFxGUxUThhpPdtppPn2w");
//$do = $ig->getUserFollowers("1244548587","AQDc96Gspv0w3dLaG6Q23kfJvLQUM_uHmJN0tqZGzyjKDvGoB9K3FtoYnwz7SztoThYfOvBiyfJM20ZaD_OP8XENeBYoj3MIE6Ot92V6gzPbyfZGr1DvnUlaByeATgGZ0tE");
//$do = $ig->unblockUser("1244548587");
//$do = $ig->seeNotif();
//$do = $ig->getExplore();
//$do = $ig->getMediabyHashtag("jualpaketlaguitunes","J0HWIlPlAAAAF0HWIHSRgAAAFjgA");


?>
</pre>