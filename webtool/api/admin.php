<?
//------------------------------------------------------------------------------
function get_release_log($_post_data)
{
	echo "OK";
}
//------------------------------------------------------------------------------
function sync_dictionary($_post_data)
{
	$path = $_post_data["_USER_PATH_"]."/".$_post_data["user_email"];
	
	$from_path 		= $_post_data["from_path"];
	$to_path 		= $_post_data["to_path"];
	$file_name_log 	= $path."/".$_post_data["file_name_log"];
	
	if( !is_dir($path) ) {
		$cmd = "mkdir -p ".$path;
		exec($cmd);
	}
	
	$cmd = "export LC_ALL=en_US.UTF-8; nohup rsync -av --progress $from_path/ $to_path > $file_name_log; echo 'FINISHED' >> $file_name_log 2>&1 &";		
	exec($cmd);
	
	//error_log($cmd);
}
//------------------------------------------------------------------------------
function clear_build($_post_data)
{
	$rev 			= $_post_data["rev"];
	$lang_type 		= $_post_data["lang_type"];
	$user_name 		= $_post_data["user_email"];
	$build_target 	= $_post_data["build_target"];
    $user_home   	= $_post_data["user_home"];	
	$svn_home   	= $user_home."/".$_post_data["svn_home"];	
	
	$cmd = "rm -rf $svn_home* 2>&1";
	
	system($cmd);
	
	echo json_encode(array("msg"=>"OK"));
}
//------------------------------------------------------------------------------
function svn_checkout($_post_data)
{
	$rev 			= $_post_data["rev"];
	$svn_url 		= $_post_data["svn_url"];
	$lang_type 		= $_post_data["lang_type"];
	$user_name 		= $_post_data["user_email"];
	$build_target 	= $_post_data["build_target"];
	$user_home   	= $_post_data["user_home"];	
	$build_log 		= $user_home."/".$_post_data["build_log"];
	$svn_home   	= $user_home."/".$_post_data["svn_home"];	
	
	if( $lang_type == "ke" || $lang_type == "kc" ) $build_home = "$svn_home/build/linux";
	if( $lang_type == "ek" ) $build_home = "$svn_home/build";
	if( $lang_type == "ck" ) $build_home = "$svn_home/build/linux";

    $ret = array();

	$cmd = "#!/bin/bash\n";
	$cmd .= "export LC_ALL=en_US.UTF-8\n";
	$cmd .= "sync\n";
	$cmd .= "rm -rf $svn_home\n";
	$cmd .= "sync\n";
	$cmd .= "mkdir -p $svn_home\n";
	$cmd .= "sync\n";
	$cmd .= "cd $user_home\n";
	$cmd .= "svn co --no-auth-cache --username ".$_post_data["_SVN_USER_NAME_"]." --password ".$_post_data["_SVN_USER_PASSWD_"]." -r $rev $svn_url $svn_home 2>&1 | cat -n > $build_log\n";
	$cmd .= "sync\n";
	
	$cmd .= "echo 'FINISHED' >> $build_log\n";
	$cmd .= "sync\n";

	$fn_sh = "$svn_home.sh";
	
	$fh = fopen($fn_sh, "w");
	fwrite($fh, $cmd);
	fclose($fh);
	
	// run
	$cmd = "nohup /bin/bash $fn_sh 2>&1 &";		
	exec($cmd, $ret);
    
    $post_result = array(
        "log_file_name" => $build_log
    );

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function add_service($_post_data)
{
	$rev 			= $_post_data["rev"];
	$user_name 		= $_post_data["user_email"];
	$lang_type 		= $_post_data["lang_type"];
	$build_target 	= $_post_data["build_target"];
	$svn_home   	= $_post_data["_USER_PATH_"]."/".$_post_data["user_email"]."/".$_post_data["svn_home"];	
	
	if( $lang_type == "ke" || $lang_type == "kc" ) $build_home = "$svn_home/build/linux";
	if( $lang_type == "ek" ) $build_home = "$svn_home/build";
	if( $lang_type == "ck" ) $build_home = "$svn_home/build/linux";
	if( $lang_type == "dial" ) $build_home = "$svn_home/build";
	
	$fn_engine = $_post_data["_ENGINE_PATH_"]."/$build_target.$rev";

	$cmd = "";
	if( $lang_type == "ke" || $lang_type == "kc" ) {
		$cmd = "cp $build_home/$build_target $fn_engine";
		$cmd .= ";cp $build_home/kemt.conf $fn_engine.conf";
	} else {
		$cmd = "cp $build_home/$build_target $fn_engine";
	}

//	echo $lang_type;
//	echo "<br>";
//	echo $cmd;
//	echo "<br>";

//	error_log($cmd);
	
	$msg = array();
	exec($cmd." 2>&1", $msg);
	
	$ret = array();
	
	$ret["fn_engine"] = $fn_engine;
	$ret["msg"] = join("<br />", $msg);
	
	if( $ret["msg"] == "" ) $ret["msg"] = "OK";
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function is_engine_alive($engine, $arr, $k_buf)
{
	$ip = ( isset($arr[$k_buf[0]][$k_buf[1]]["SERV_IP"]) ) ? $arr[$k_buf[0]][$k_buf[1]]["SERV_IP"] : $_post_data["_DEAMON_IP_"]; 	
//	error_log($ip .":". join(",", $k_buf));

	if( $engine == "dummy" ) return false;
	
	$ret = array();
	
	$cmd_shell = "\$PS | grep '$engine' | tr -s ' ' 2>&1";
	
	$cmd = "\$SSH_PASS ".$ip." \"$cmd_shell\" ";
	
	$str = "";
	if( !isset($GLOBALS["is_engine_alive:".$cmd]) ) {
		exec($cmd, $ret);
		$str = join(",", $ret);

		$GLOBALS["is_engine_alive:".$cmd] = $str;	
	} else {
		$str = $GLOBALS["is_engine_alive:".$cmd];		
	}	
	
	if( strpos($str, "run.daemon.") === false ) return false;		
	
	return true;
}
//------------------------------------------------------------------------------
function add_user($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_USER_INFO_DB_FILE_NAME_"];

	$user_email = $_post_data["user_email"];
	$user_name 	= $_post_data["user_name"];
	$user_pw 	= $_post_data["user_pw"];
	$comment	= $_post_data["comment"];
	$level 		= $_post_data["level"];
	$env 		= $_post_data["env"];

	if( $user_email == "" || $user_name == "" ) return;

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
		$sql = "CREATE TABLE IF NOT EXISTS ".$_post_data["_USER_INFO_TABLE_NAME_"];
		$sql .= " (env TEXT, user_email TEXT, user_pw TEXT, user_name TEXT, level TEXT, comment TEXT, m_date DATE UNIQUE DEFAULT (datetime('now','localtime')))";

		$st = $db->prepare($sql);
		$st->execute();

		// insert new user
		$sql = "INSERT INTO ".$_post_data["_USER_INFO_TABLE_NAME_"]." (user_email,user_name,level,comment,user_pw,env) ";
		$sql .= " VALUES ('$user_email', '$user_name', '$level', '$comment', '$user_pw', '$env')";

		$st = $db->prepare($sql);
		$st->execute();

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
	
	change_htpasswd($_post_data["_TRAC_PASSWD_FILE_NAME_"], $user_email, $user_pw);
}
//------------------------------------------------------------------------------
function update_user($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_USER_INFO_DB_FILE_NAME_"];

	$user_email = $_post_data["user_email"];
	$user_name 	= $_post_data["user_name"];
	$user_pw 	= $_post_data["user_pw"];
	$comment	= $_post_data["comment"];
	$level 		= $_post_data["level"];
	$env 		= $_post_data["env"];
	
	$request_type = ( isset($_post_data["request_type"]) ) ? $_post_data["request_type"] : "";
    
	if( $user_email == "" || $user_name == "" ) return;
	
	$ret = array();

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
//		$sql = "CREATE TABLE IF NOT EXISTS ".$_post_data["_USER_INFO_TABLE_NAME_"];
//		$sql .= " (env TEXT, user_email TEXT, user_pw TEXT, user_name TEXT, level TEXT, comment TEXT, m_date DATE UNIQUE DEFAULT (datetime('now','localtime')))";
//
//		$st = $db->prepare($sql);
//		$st->execute();

        $sql = "DELETE FROM ".$_post_data["_USER_INFO_TABLE_NAME_"]." WHERE user_email='$user_email'";

        $st = $db->prepare($sql);
        $st->execute();
		
		$ret["sql_delete"] = $sql;
		
		if( $request_type != "delete" ) {
	        // insert new user
			$sql = "INSERT INTO ".$_post_data["_USER_INFO_TABLE_NAME_"];
	        $sql .= " (user_email, user_name, level, comment, user_pw, env)";
			$sql .= " VALUES ('$user_email', '$user_name', '$level', '$comment', '$user_pw', '$env')";
	
			$st = $db->prepare($sql);
			$st->execute();

			$ret["sql_update"] = $sql;
		}
		
		$ret["msg"] = "OK";
		
		echo json_encode($ret);
		
		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
	
	change_htpasswd($_post_data["_TRAC_PASSWD_FILE_NAME_"], $user_email, $user_pw);
}
//------------------------------------------------------------------------------
function delete_user($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_USER_INFO_DB_FILE_NAME_"];

	$user_email = $_post_data["user_email"];
	$m_date 	= $_post_data["m_date"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		$sql = "DELETE FROM ".$_post_data["_USER_INFO_TABLE_NAME_"]." WHERE m_date='$m_date' AND user_email='$user_email'";

		$st = $db->prepare($sql);
		$st->execute();

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
	
	change_htpasswd($_post_data["_TRAC_PASSWD_FILE_NAME_"], $user_email, "");
}
//------------------------------------------------------------------------------
function get_user_list($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_USER_INFO_DB_FILE_NAME_"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
//		$sql = "CREATE TABLE IF NOT EXISTS ".$_post_data["_USER_INFO_TABLE_NAME_"];
//		$sql .= " (env TEXT, user_email TEXT, user_pw TEXT, user_name TEXT, level TEXT, comment TEXT, m_date DATE UNIQUE DEFAULT (datetime('now','localtime')))";
//
//		$st = $db->prepare($sql);
//		$st->execute();

		// query user email
        $col_list = "user_email,user_name,comment,level,m_date,'***' AS user_pw,env";        
		if( getenv('REMOTE_ADDR') == "129.254.186.79" || $_COOKIE["user_email"] == "ejpark" ) {
            $col_list = "user_email,user_name,comment,level,m_date,user_pw,env";
        }
        
		$sql = "SELECT $col_list ";
		$sql .= " FROM ".$_post_data["_USER_INFO_TABLE_NAME_"];
        $sql .= " ORDER BY level,comment,user_name";

		$sth = $db->prepare($sql);
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($result);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
?>