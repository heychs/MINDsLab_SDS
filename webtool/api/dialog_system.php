<?
//------------------------------------------------------------------------------
function eval_form_download_survay($_post_data){
	$path_www_data = $_post_data["_WWW_DATA_PATH_"];
	
	$url_path = $_SERVER["DOCUMENT_ROOT"]."/webtool/download";

    $ret = array();
    
    $ret["url"] = "";
	
	// | 
	$cmd = "rm -f $url_path/survay.xls*;sync;";
	$cmd .= "sqlite3 $path_www_data/dialog_system_utter_log.sqlite3 'SELECT user_name,survay FROM survay'";
	$cmd .= "| $path_www_data/bin/text2xls.pl -out $url_path/survay.xls -sheet survay;";
	$cmd .= "gzip $url_path/survay.xls;sync";
	exec($cmd, $ret["cmd_result"]);
	
	$ret["cmd"] = $cmd;
	$ret["url"] = "/webtool/download/survay.xls.gz";
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function eval_form_download_utter_log($_post_data){
	$path_www_data = $_post_data["_WWW_DATA_PATH_"];
	
	$url_path = $_SERVER["DOCUMENT_ROOT"]."/webtool/download";

    $ret = array();
    
    $ret["url"] = "";
	
	$cmd = "rm -f $url_path/utter_log.xls*;sync;";
	$cmd .= "$path_www_data/bin/sqlite2xls.pl";
	$cmd .= " -fn $path_www_data/dialog_system_utter_log.sqlite3";
	$cmd .= " -out $url_path/utter_log.xls";
	$cmd .= " -table utter_log;sync;";
	$cmd .= "gzip $url_path/utter_log.xls;sync";
	exec($cmd, $ret["cmd_result"]);
	
	$ret["cmd"] = $cmd;
	$ret["url"] = "/webtool/download/utter_log.xls.gz";
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function eval_play_asr_file($_post_data){
	$path_log = $_post_data["_DIALOG_SYSTEM_LOG_PATH_"];
	$asr_file_name = $_post_data["asr_file_name"];
	
	$url_path = $_SERVER["DOCUMENT_ROOT"]."/webtool/api/asr";

    $ret = array();
    
    $ret["url"] = "";
	
    $fn_org = $path_log."/".$asr_file_name;
    $fn_trg = $url_path."/".$asr_file_name;

    if( file_exists($fn_org) ) {
		$cmd = "cp \"$fn_org\" \"$fn_trg\"";
		exec($cmd, $ret["cmd_result"]);
		
		$ret["cmd"] = $cmd;
		$ret["url"] = "/webtool/api/asr/".$asr_file_name;
    }
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function eval_delete_evaluation_form_data($_post_data){
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
		
	$table_name = "utter_log";
	
	$domain = $_post_data["domain"];
	$mission = $_post_data["mission"];	
	$dialog_id = $_post_data["dialog_id"];
	$user_name = $_post_data["user_name"];
	
	$ret = array();
	
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// update eval result
		$sql = "DELETE FROM $table_name ";
		$sql .= " WHERE user_name='$user_name' AND dialog_id='$dialog_id' AND mission='$mission' AND domain='$domain'";
		
		array_push($ret, $sql);
		$st = $db->prepare($sql);
		$st->execute();
		
		$ret["msg"] = "OK";
		
		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function eval_save_evaluation_form_data($_post_data){
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
		
	$table_name = "utter_log";
	
	$domain = $_post_data["domain"];
	$mission = $_post_data["mission"];	
	$dialog_id = $_post_data["dialog_id"];
	$user_name = $_post_data["user_name"];
	$eval_result = $_post_data["eval_result"];
	$utter_result = $_post_data["utter_result"];
	$eval_result_comment = $_post_data["eval_result_comment"];
	
	$eval_result_comment = str_replace("'", "''", $eval_result_comment);	
	
	$ret = array();
	
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// update eval result
		$sql = "UPDATE $table_name SET eval_result='$eval_result', eval_result_comment='$eval_result_comment'";
		$sql .= " WHERE user_name='$user_name' AND dialog_id='$dialog_id' AND mission='$mission' AND domain='$domain'";
		
		array_push($ret, $sql);
		$st = $db->prepare($sql);
		$st->execute();
		
		for( $i=0 ; $i<count($utter_result) ; $i++ ) {
			$utter_id = $utter_result[$i]["utter_id"];
			$utter_result_value = $utter_result[$i]["utter_result"];
			$utter_result_comment = $utter_result[$i]["utter_result_comment"];
			$sub_mission_result = $utter_result[$i]["sub_mission_result"];
			
			$utter_result_comment = str_replace("'", "''", $utter_result_comment);	

			$sql = "UPDATE $table_name SET utter_result='$utter_result_value', utter_result_comment='$utter_result_comment', sub_mission_result='$sub_mission_result'";
			$sql .= " WHERE user_name='$user_name' AND dialog_id='$dialog_id' AND mission='$mission' AND domain='$domain' AND utter_id='$utter_id'";
			
			array_push($ret, $sql);
			$st = $db->prepare($sql);
			$st->execute();
		}		
		
		$ret["msg"] = "OK";
		
		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}	
}
//------------------------------------------------------------------------------
function submit_survey($_post_data){	
	$survay_result = "";
	for( $i=1 ; $i<=10 ; $i++ ) {
		if( !isset($_post_data["survey_q".$i]) ) $_post_data["survey_q".$i] = "-1";

		if( $survay_result != "" ) $survay_result .= "\t";
		$survay_result .= $_post_data["survey_q".$i];
	}
	
	if( !isset($_post_data["e_mail"]) ) $_post_data["e_mail"] = "";
	
	$user_name = $_post_data["user_name"];	
	$survay_result = $_post_data["e_mail"] . "\t" . $survay_result;

	$ret = array("str" => $survay_result);
	
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
	
	$table_name = "survay";
	
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
		$sql = "CREATE TABLE IF NOT EXISTS $table_name";
		$sql .= " (user_name TEXT, survay TEXT, m_date DATE DEFAULT (datetime('now','localtime')))";

		$st = $db->prepare($sql);
		$st->execute();
		
		$sql = "INSERT INTO $table_name (user_name, survay) VALUES ('$user_name', '$survay_result')";
		
		$st = $db->prepare($sql);
		$st->execute();

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}	
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function get_mission_column_list($_post_data) {
	$domain = $_post_data["project_name"];
	$mission_tag = $_post_data["mission_tag"];
	
	$ret = array();
	
	if( $domain == "carvatar" ) {
		echo json_encode(array("id", "description"));
		return;
	}
		
	$mission_file_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/mission_data.xls.$mission_tag.txt";	
	
	$data = file_get_contents($mission_file_name);
	
	$mission_list = html2a($data);

	foreach( $mission_list as $k=>$v ) {
		foreach( $v as $kk=>$vv ) {
			
			$list = explode("\n", $vv);
			foreach( $list as $line ) {
				list($kkk, $vvv) = explode("\t", $line, 3);
				
				$ret[$kkk] = 1;
			}
			
			break;
		}
		break;
	}
	
	echo json_encode(array_keys($ret));	
}
//------------------------------------------------------------------------------
function get_mission_list($_post_data) {
	$domain = $_post_data["project_name"];
	$mission_tag = $_post_data["mission_tag"];
	
	$mission_file_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/mission_data.xls.$mission_tag.txt";

	// error_log("get_mission_list");
	// error_log($mission_file_name);
		
	$data = file_get_contents($mission_file_name);
	
	$mission_list = html2a($data);

	$rows = array();
	
	foreach( $mission_list as $k=>$v ) {
		foreach( $v as $kk=>$vv ) {
			// error_log($kk);
			
			$item = array();
			
			$list = explode("\n", $vv);
			foreach( $list as $line ) {
				list($kkk, $vvv) = explode("\t", $line, 3);
				
				if( $domain == "carvatar" ) {
					if( !isset($item["description"]) ) $item["description"] = "";
					
					$item["id"] = $kk;
					$item["description"] .= $vvv."\n";
				} else {
					$item[$kkk] = $vvv;
				}				
			}
			
			if( count($item) > 0 ) {
				array_push($rows, $item);
			}
		}
	}
	
	$ret = array(
		"total" => count($rows)+1,
		"rows" => $rows
	);
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function eval_get_utter_log($_post_data)
{
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
	$dialog_id = $_post_data["dialog_id"];
	
	$table_name = "utter_log";

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// get max dialog id
		$sql = "SELECT * FROM '$table_name' WHERE dialog_id='$dialog_id'";

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
function eval_get_utter_log_list($_post_data)
{
	$domain = $_post_data["project_name"];	
	$user_name = $_post_data["query_cond"]["user_name"];	
	$mission_id = $_post_data["query_cond"]["mission_id"];	
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
	
	$table_name = "utter_log";

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");
		
		// get max dialog id
		$sql = "SELECT user_name, domain, dialog_id, mission, eval_result, eval_result_comment";
		$sql .= " FROM '$table_name'";
		if( $domain != "" ) $sql .= " WHERE domain='$domain'";
		if( $user_name != "" ) $sql .= " AND user_name='$user_name'";
		if( $mission_id != "" ) $sql .= " AND mission='$mission_id'";
		$sql .= " GROUP BY dialog_id";
		$sql .= " ORDER BY domain, user_name, dialog_id, mission";

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
function get_eval_form_mission_list($_post_data)
{
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
	
	$utter_log = $_post_data["utter_log"];
	
	$table_name = "utter_log";
	
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// get max dialog id
		$sql = "SELECT mission AS mission_id FROM '$table_name' GROUP BY mission";

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
function get_eval_form_user_name_list($_post_data)
{
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
	
	$utter_log = $_post_data["utter_log"];
	
	$table_name = "utter_log";
	
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// get max dialog id
		$sql = "SELECT user_name FROM '$table_name' GROUP BY user_name";

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
function dialog_system_save_utter_log($_post_data)
{
	$db_name = $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"];
	
	$utter_log = $_post_data["utter_log"];
	
	$table_name = "utter_log";
	
	$mission = $utter_log[0]["MISSION"]["id"];
	
	$user_name = $utter_log[1]["POST_DATA"]["user_name"];
	$domain = $utter_log[1]["POST_DATA"]["project_name"];
	
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
		$sql = "CREATE TABLE IF NOT EXISTS '$table_name' (";
		$sql .= "  user_name TEXT, domain TEXT, speaker TEXT, utter TEXT,";
		$sql .= "  mission TEXT, sub_mission TEXT, sub_mission_result TEXT,";
		$sql .= "  asr_file_name TEXT, asr_result TEXT,";
		$sql .= "  eval_result TEXT, eval_result_comment TEXT,";
		$sql .= "  utter_result TEXT, utter_result_comment TEXT,";
		$sql .= "  dialog_id INT, utter_id INT,";
		$sql .= "  m_date DATE DEFAULT (datetime('now','localtime'))";
		$sql .= ")";

		$st = $db->prepare($sql);
		$st->execute();
		
		// get max dialog id
		$sql = "SELECT MAX(dialog_id)+1 AS dialog_id FROM '$table_name'";

		$sth = $db->prepare($sql);
		$sth->execute();
        
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $dialog_id = $result[0]["dialog_id"];
		if( $dialog_id == "" || $dialog_id <= 0 ) $dialog_id = 1;		

		// 		
		$ret = array();
			
		$utter_id = 0;
		for( $i=1 ; $i < count($utter_log) ; $i++ ) {
			$item = $utter_log[$i];
			
			$asr_text = $asr_file_name = $user = $system = $sub_mission = "";
			
			if( $item["TYPE"] == "INIT_SYSTEM" ) {
				$m_date = date("Y-m-d H:i:s");
				$system = $item["SYSTEM"];
			} else if( $item["TYPE"] == "COMPLETE_SYSTEM" ) {
				$user = "COMPLETE_SYSTEM";
			} else if( $item["TYPE"] == "SPEAK" ) {
				$m_date = $item["POST_RESULT"]["time"];
				$user = $item["USER"];
				$system = $item["SYSTEM"];
				$sub_mission = $item["SUB_MISSION"];
				
				$asr_text = $item["ASR_TEXT"];
				$asr_file_name = $item["ASR_FILE_NAME"];
			} else {
				continue;
			}
			
			$user = str_replace("'", "''", $user);
			$system = str_replace("'", "''", $system);
			$asr_text = str_replace("'", "''", $asr_text);
			$sub_mission = str_replace("'", "''", $sub_mission);
			
			$sql = "INSERT INTO $table_name (";
			$sql .= " user_name, domain, mission, sub_mission, speaker, utter,";
			$sql .= " eval_result, utter_result,";
			$sql .= " dialog_id, utter_id,";
			$sql .= " m_date,";
			$sql .= " asr_result, asr_file_name";
			$sql .= ") ";

			if( $user != "" ) {
				$utter_id++;
				
				$q = $sql." VALUES (";
				$q .= " '$user_name', '$domain', '$mission', '$sub_mission',";
				$q .= " 'USER', '$user',";
				$q .= " '-1', '-1',";
				$q .= " $dialog_id, $utter_id,";
				$q .= " '$m_date',";
				$q .= " '$asr_text', '$asr_file_name'";
				$q .= ")";

				$sth = $db->prepare($q);
				if( !$sth ) error_log($q.":".join(",", $db->errorInfo()));
				
				array_push($ret, $q);
				$sth->execute();
			}

			if( $system != "" ) {
				$utter_id++;
				
				$q = $sql." VALUES (";
				$q .= " '$user_name', '$domain', '$mission', '$sub_mission',";
				$q .= " 'SYSTEM', '$system',";
				$q .= " '-1', '-1',";
				$q .= " $dialog_id, $utter_id,";
				$q .= " '$m_date',";
				$q .= " '$asr_text', '$asr_file_name'";
				$q .= ")";

				$sth = $db->prepare($q);
				if( !$sth ) error_log($q.":".join(",", $db->errorInfo()));

				array_push($ret, $q);
				$sth->execute();
			}
		}
		
		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}	
}
//------------------------------------------------------------------------------
function dialog_system_update_engine_list($_post_data)
{
    $bin_path = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"];

    $ret = array();

    $cmd = "ls -1 $bin_path/dial*";    
    exec($cmd, $ret);
    
    for( $i=0 ; $i<count($ret) ; $i++ ) {
        $ret[$i] = str_replace("$bin_path/", "", $ret[$i]);
    }
    
    $ret = array_reverse($ret);
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function run_asr_english($_post_data) {
	set_time_limit(10);
    
    $path = $_SERVER["DOCUMENT_ROOT"]."/webtool/api";
	
	$fn = $fn_wav = $_post_data["asr_file_name"];
	$project_name = $_post_data["project_name"];
	$dialog_system_channel = $_post_data["dialog_system_channel"];
	
    // wait for file save delay
    while( !file_exists("$path/$fn_wav") ) {
        sleep(1);
    }
	
    $fn = str_replace(".wav", "", $fn);
    $fn = str_replace("asr/", "", $fn);
    
    $fn = $fn."_".$_SERVER['REMOTE_ADDR']."_".uniqid();

	$list = array();
	
	$user_name = "test";
	
	$tag = uniqid();
	
	$asr_server = "127.0.0.1";
	$asr_port = 8000 + ($dialog_system_channel % 10);
	
	error_log($fn);

    // make command    
   	$path_log = $_post_data["_DIALOG_SYSTEM_LOG_PATH_"];
	
	if( !is_dir($path_log) ) mkdir($path_log);

    // make command    
    $fn_sh = "$path_log/$fn.sh";
  	$fp = fopen($fn_sh, "w"); 

    fwrite($fp, "#!/bin/bash\n");
    fwrite($fp, "\n");

    fwrite($fp, "sync\n");
    fwrite($fp, "mv \"$path/$fn_wav\" \"$path_log/$fn.wav\"\n");
    fwrite($fp, "\n");

    // wav to raw
    fwrite($fp, "sox \"$path_log/$fn.wav\" -c 1 -r 16000 \"$path_log/$fn.raw\" >> \"$path_log/$fn.log\" 2>&1\n");
    fwrite($fp, "\n");

    fwrite($fp, "sync\n");

	$arec_client_path = "/webtool/www_data/asr_english/arec_client";
	
	$fn_in = $fn.".in";
	
	fwrite($fp, "echo \"$path_log/$fn.raw\" > $arec_client_path/tmp/$fn_in.txt\n");
	
	$line = "$arec_client_path/ARecClient.py";
	$line .= " -s $arec_client_path/tmp/$fn_in.txt";
	$line .= " -i $asr_server -p $asr_port";
	$line .= " -O $arec_client_path/result/$fn_in.mlf";
	$line .= " 1>/dev/null 2>/dev/null";
	$line .= "\n";

    fwrite($fp, $line);
    fwrite($fp, "cat $arec_client_path/result/$fn_in.mlf\n");

    fclose($fp);
    
    $asr_result = array();
    $cmd = "sync;chmod +x $fn_sh;sh $fn_sh";
    exec($cmd, $asr_result);
	
	array_shift($asr_result);
	array_shift($asr_result);
	
    if( !isset($asr_result[0]) || $asr_result[0] == "" ) $asr_result[0] = "ASR Failed.";

    $ret = array("cmd" => $cmd, "asr_result" => $asr_result, "asr_file_name" => "$fn.wav");    
    echo json_encode($ret);	

    set_time_limit(0);
}
//------------------------------------------------------------------------------
function run_asr_korean($_post_data) {
    set_time_limit(10);
    
    $path = $_SERVER["DOCUMENT_ROOT"]."/webtool/api";
    
    $fn = $fn_wav = $_post_data["asr_file_name"];	
	$project_name = $_post_data["project_name"];
    
    // wait for file save delay
    while( !file_exists("$path/$fn_wav") ) {
        sleep(1);
    }
    
    $fn = str_replace(".wav", "", $fn);
    $fn = str_replace("asr/", "", $fn);
    
	$fn = $fn."_".$_SERVER['REMOTE_ADDR']."_".uniqid();
    
    // asr
    $path_engine = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"];
    $path_log = $_post_data["_DIALOG_SYSTEM_LOG_PATH_"];
    $www_data_path = $_post_data["_WWW_DATA_PATH_"];
	
	if( !is_dir($path_log) ) mkdir($path_log);

    // make command    
    $fn_sh = "$path_log/$fn.sh";
  	$fp = fopen($fn_sh, "w"); 

    fwrite($fp, "#!/bin/bash\n");
    fwrite($fp, "\n");

    fwrite($fp, "sync\n");
    fwrite($fp, "mv \"$path/$fn_wav\" \"$path_log/$fn.wav\"\n");
    fwrite($fp, "\n");

    // wav to raw
    fwrite($fp, "sox \"$path_log/$fn.wav\" -c 1 -r 16000 \"$path_log/$fn.raw\" >> \"$path_log/$fn.log\" 2>&1\n");
    fwrite($fp, "\n");

    fwrite($fp, "sync\n");
	
	if( 1 ) {
		$line = "$www_data_path/ship2ejpark/client";
		$line .= " -i 127.0.0.1";
		$line .= " -p 8000";
		$line .= " \"$path_log/$fn.raw\"";
		$line .= " | iconv -c -f cp949 -t utf8 ";
		$line .= "2>> \"$path_log/$fn.log\"";
		$line .= "\n";
	} else {
		$line = "$path_engine/asr_korean";
		$line .= " -d \"$path_engine/data\"";
		$line .= " -t $project_name";
		$line .= " -o /dev/stdout";
		$line .= " \"$path_log/$fn.raw\"";
		$line .= " | iconv -c -f cp949 -t utf8 ";
		$line .= "2>> \"$path_log/$fn.log\"";
		$line .= "\n";
	}
	
    fwrite($fp, $line);
    fwrite($fp, "\n");

    fclose($fp);
    
    $asr_result = array();
    $cmd = "sync;chmod +x $fn_sh;sh $fn_sh";
    exec($cmd, $asr_result);
	
    if( !isset($asr_result[0]) || $asr_result[0] == "" ) $asr_result[0] = "ASR Failed.";
	
	if( 1 ) {
		// ASR_RESULT: "에스케이주유소 찾아줘#DIALOG_DISABLED", GS칼텍스 찾아줘#DIALOG_DISABLED
		$asr_result[0] = preg_replace("/ASR_RESULT: \"(.+)#.+\"/", "$1", $asr_result[0]);
	}
    
    $ret = array("cmd" => $cmd, "asr_result" => $asr_result, "asr_file_name" => "$fn.wav");    
    
    echo json_encode($ret);
    
    set_time_limit(0);
}
//------------------------------------------------------------------------------
function reset_asr_server_daemon($_post_data) {
    $ret = array();
    $cmd = "killall server";
    exec($cmd, $ret);
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function is_running_asr_server($_post_data) {
    $ret = array();
    $cmd = "ps -ef | grep svr.cfg";
    exec($cmd, $ret);
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function run_asr_server($_post_data) {
    set_time_limit(10);
    
    $ret = array();
    $cmd = "nohup /webtool/www_data/ship2ejpark/run_server.sh &";
    exec($cmd, $ret);
	
	error_log($cmd);
	error_log(var_dump($ret, true));
    
    set_time_limit(0);
}
//------------------------------------------------------------------------------
function get_asr_server_log_filename($_post_data) {
    $ret = array();
	// tghong: ???
    $cmd = "cd /webtool/www_data/project/dialog/log; ls -1 asrserver.log* | sort -r";
    exec($cmd, $ret);
	
	$ret[0] = str_replace("*", "", $ret[0]);
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function dialog_system_user_utter($_post_data)
{
    $ret = array();
	$ret["time"] = date("Y-m-d H:i:s");

    // set engine & channel
    $_post_data["_DIALOG_SYSTEM_SERV_PORT_"] = $_post_data["_DIALOG_SYSTEM_SERV_PORT_"] + $_post_data["dialog_system_channel"];    
    
	// set default
	$PACKET = array();
	$PACKET["SERV_IP"] 	 = $_post_data["_DIALOG_SYSTEM_SERV_IP_"];
	$PACKET["SERV_PORT"] = $_post_data["_DIALOG_SYSTEM_SERV_PORT_"];
	
//	$_post_data["user_utter"] = str_replace("<TAB>", "\t", $_post_data["user_utter"]);
    
	$PACKET["TX"]  = "<TYPE>text</TYPE>";	
	$PACKET["TX"] .= "<SRC>".$_post_data["user_utter"]."</SRC>";	

	if( $PACKET["TX"] == "" ) return;
	
    $ret["user"] = $_post_data["user_utter"]."\n";       
	
    $rx = SendPacket2PerlServ($PACKET);
	list($sys, $slu, $da, $IsDialogEnd, $slu_weight, $slu_similar_str) = explode("\t", $rx, 6);
	
    $sys = str_replace("<BR>", "\n", $sys);
    $slu = str_replace("<BR>", "\n", $slu);
	
    $ret["system"] = $sys;       
    $ret["slu"] = $slu;  
	$ret["slu_weight"] = $slu_weight;
	$ret["slu_similar_str"] = $slu_similar_str;     
    $ret["da"] = $da;       
	$ret["rx"] = $rx;
    $ret["IsDialogEnd"] = $IsDialogEnd;       
    $ret["debug"] = $PACKET["TX"];       

	if( $_post_data["shell_cmd"] == "GET_SLOT_TAGGING" ) {
		$ret = array_merge($ret, xml2array($rx));

        // parse SLU LOG
        $t = explode("<BR>", $ret["SLU_LOG"]);
		
        $ret["SLU_LOG"] = $t;
    
        if( count($t) > 0 ) $ret["kma"] = $t[0];
        if( count($t) > 1 ) {
            $str = str_replace("SLU1: ", "", $t[1]);
            list($ret["slot_tagged"]) = explode(" #", $str, 2);
			
			$ret["slot_tagged"] = str_replace("<TAB>", "", $ret["slot_tagged"]);
        }
	}
    
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function stop_dialog_system($_post_data)
{
    $ret = array();
	$ret["time"] = date("Y-m-d H:i:s");

    // set engine & channel
    $_post_data["_DIALOG_SYSTEM_SERV_PORT_"] = $_post_data["_DIALOG_SYSTEM_SERV_PORT_"] + $_post_data["dialog_system_channel"];    

	// init packet
	$PACKET = array();
	$PACKET["SERV_IP"] 	 = $_post_data["_DIALOG_SYSTEM_SERV_IP_"];
	$PACKET["SERV_PORT"] = $_post_data["_DIALOG_SYSTEM_SERV_PORT_"];
	$PACKET["TX"] 	 	 = "Stop Dial";	
	
	// STOP the engine
	$PACKET["TX"]  = "<STOP>";	
	$rx = SendPacket2PerlServ($PACKET);
	echo str_replace("<BR>", "\n", $rx);
    
    $ret["msg"] = $rx;       
    $ret["debug"] = join(",", $PACKET);       
           
    echo json_encode($ret);    
}
//------------------------------------------------------------------------------
function init_dialog_system($_post_data)
{
    $ret = array();

    // get uid
	$t = microtime(true);
	$micro = sprintf("%02d", ($t - floor($t)) * 1000000);
	$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
	
	$_post_data["UID"] = $d->format("Y-m-d_H_i_s_u")."-".$_SERVER['REMOTE_ADDR'];
    
    // set engine & channel
    $_post_data["_DIALOG_SYSTEM_ENGINE_"] = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"]."/".$_post_data["dialog_system_engine"];    
    $_post_data["_DIALOG_SYSTEM_SERV_PORT_"] = $_post_data["_DIALOG_SYSTEM_SERV_PORT_"] + $_post_data["dialog_system_channel"];    
	
	// set default
	$deamon_log_file_name = $_post_data["_DIALOG_SYSTEM_LOG_PATH_"]."/".$_post_data["user_name"].".".$_post_data["UID"].".log";
	if( isset($_post_data["deamon_log_file_name"]) ) $deamon_log_file_name = $_post_data["_DIALOG_SYSTEM_LOG_PATH_"]."/".$_post_data["deamon_log_file_name"];

	if( !is_dir($_post_data["_DIALOG_SYSTEM_LOG_PATH_"]) ) mkdir($_post_data["_DIALOG_SYSTEM_LOG_PATH_"]);

	// init packet
	$PACKET = array();
	$PACKET["SERV_IP"] 	 = $_post_data["_DIALOG_SYSTEM_SERV_IP_"];
	$PACKET["SERV_PORT"] = $_post_data["_DIALOG_SYSTEM_SERV_PORT_"];
	$PACKET["TX"] 	 	 = "Init Dial";	
	
	// STOP the engine
	$PACKET["TX"]  = "<STOP>";	
	$rx = SendPacket2PerlServ($PACKET);	
	if( $rx != "Connection refused" || $rx != "연결이 거부됨" ) {
		$ret["error_msg"] = $rx;
	}

	// start daemon
	$cmd = get_daemon_cmd_dial($_post_data, $PACKET["SERV_IP"], $PACKET["SERV_PORT"], $deamon_log_file_name);

	// error_log($cmd);

    ChromePhp::log($cmd);

    system($cmd);
	sleep(2);

	$PACKET["TX"]  = "<TYPE>text</TYPE>";	
	$PACKET["TX"] .= "<ENGINE>".$_post_data["_DIALOG_SYSTEM_ENGINE_"]."</ENGINE>";	
	$PACKET["TX"] .= "<DIC>".$_post_data["_DIALOG_SYSTEM_DICTIONARY_HOME_PATH_"]."</DIC>";	

	if( $_post_data["guide_mode"] == "yes" ) {
		$PACKET["TX"] .= "<ARGS>-domain ".$_post_data["project_name"]." -static_dialog static_dialog</ARGS>";
	} else {
		$PACKET["TX"] .= "<ARGS>-domain ".$_post_data["project_name"]."</ARGS>";
	}	

	$PACKET["TX"] .= "<REQUEST_PAGE>".$_post_data["UID"]."</REQUEST_PAGE>";	
	$PACKET["TX"] .= "<TIMEOUT>10000</TIMEOUT>";	
	$PACKET["TX"] .= "<CMD>".$_post_data["shell_cmd"]."</CMD>";	
	$PACKET["TX"] .= "<CONF></CONF>";	
	$PACKET["TX"] .= "<SRC>test</SRC>";	
	$PACKET["TX"] .= "<ENCODING>utf8</ENCODING>";	
	$PACKET["TX"] .= "<USER_NAME>".$_post_data["user_name"]."</USER_NAME>";	
	
	$rx = SendPacket2PerlServ($PACKET);
	
	list($sys, $slu) = explode("\t", $rx, 2);
	
    $sys = str_replace("<BR>", "\n", $sys);
    $slu = str_replace("<BR>", "\n", $slu);

    $ret["system"] = $sys;       
    $ret["slu"] = $slu;       
    $ret["debug"] = $PACKET["TX"];
    $ret["cmd"] = $cmd;
    $ret["rx"] = $rx;
	$ret["time"] = date("Y-m-d H:i:s");
	
	$ret["deamon_log_file_name"] = $_post_data["user_name"].".".$_post_data["UID"].".log";
	if( isset($_post_data["deamon_log_file_name"]) ) $ret["deamon_log_file_name"] = $_post_data["deamon_log_file_name"];

    $ret["_DIALOG_SYSTEM_ENGINE_"] = $_post_data["_DIALOG_SYSTEM_ENGINE_"];
    $ret["_DIALOG_SYSTEM_SERV_PORT_"] = $_post_data["_DIALOG_SYSTEM_SERV_PORT_"];
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function get_daemon_cmd_dial($_post_data, $_serv_ip, $_port, $_fn_log)
{
	$args = array(
		"tag" 			=> $_post_data["user_name"],
		"engine" 		=> $_post_data["_DIALOG_SYSTEM_ENGINE_"],
		"dic" 			=> $_post_data["_DIALOG_SYSTEM_DICTIONARY_HOME_PATH_"],
		"mysql_host" 	=> $_post_data["_MYSQL_HOST_"],
		"mysql_port" 	=> $_post_data["_MYSQL_PORT_"],
		"mysql_database" 	=> $_post_data["_MYSQL_DBNAME_"],
		"mysql_user" 	=> $_post_data["_MYSQL_USER_"],
		"mysql_user_pw" => $_post_data["_MYSQL_PW_"],
		"domain" 		=> $_post_data["project_name"],
		"port" 			=> $_port,
		"encoding" 		=> "utf8"
	);

	$str_args = "";

	foreach( $args as $k=>$v ) {
		if( $v == "" ) continue;
		$str_args .= "-$k $v ";
	}

	$cmd = $_post_data["_DIALOG_SYSTEM_SSHPASS_"]." $_serv_ip \"nohup ".$_post_data["_DIALOG_SYSTEM_DAEMON_"]." -noinit ".$str_args;
	$cmd .= "      >> $_fn_log 2>&1 &\" ";
    
    error_log($cmd);

	return $cmd;
}
//------------------------------------------------------------------------------

?>