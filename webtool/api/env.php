<?

	// mysql 관련 설정, 만약 sqlite로 사용할 경우 아래 "_MYSQL_*"를 주석처리
//	$_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] = "1"; // 하나의 task_information을 사용


//	$_post_data["_MYSQL_HOST_"] = "192.168.56.101";

//	$_post_data["_MYSQL_PORT_"] = "3306";
//	$_post_data["_MYSQL_DBNAME_"] = "webtool";
//	$_post_data["_MYSQL_USER_"] = "webtool";
//	$_post_data["_MYSQL_PW_"] = "webtool2012";




	$_post_data["_SSH_ID_"]   = "webtool";
	$_post_data["_SSH_PASS_"] = "webtool2012";	

	putenv("PS=ps -ewo user,pid,cmd");
	putenv("SSH_PASS=sshpass -p ".$_post_data["_SSH_PASS_"]." ssh -T -oStrictHostKeyChecking=no -p 22 -l ".$_post_data["_SSH_ID_"]);	
	
	$_post_data["_USER_INFO_DB_FILE_NAME_"] = "web_workbench.db";
	$_post_data["_USER_INFO_TABLE_NAME_"] 		= "user_info";

    $_post_data["_ROOT_PATH_"] = $root_path = "/webtool";

	// set path & file name	
	$_post_data["_SCRIPTS_PATH_"] 			= "$root_path/scripts";
	$_post_data["_WWW_DATA_PATH_"] 			= "$root_path/www_data";	
	$_post_data["_USER_PATH_"] 				= "$root_path/www_data/users";	
	$_post_data["_SEARCH_BOX_PATH_"] 		= "$root_path/www_data/db";

	$_post_data["_DOWNLOAD_PATH_"] 			= $_SERVER["DOCUMENT_ROOT"]."/down";
	$_post_data["_DOWNLOAD_URL_"] 			= "http://".$_SERVER["HTTP_HOST"]."/down";

	$_post_data["upload_path"] 	= ( isset($_post_data["upload_path"]) ) ? $_post_data["upload_path"] : $root_path."/www_data/upload";

	// cookie env.
	$cookie_env = array();
	if( isset($_COOKIE["env"]) ) {
	   $str = json_decode($_COOKIE["env"], true);  
	   $cookie_env = json_decode($str, true);  
	}
    
	// default engine
	$_post_data["_DEAMON_IP_"] = "127.0.0.1";
	$_post_data["_DEAMON_PORT_"] = 41000;
?>