<?
//------------------------------------------------------------------------------
// TO DO  
//------------------------------------------------------------------------------
function read_log_file($_post_data) {
	$file_name 	= $_post_data["file_name"];
	$start_line = $_post_data["start_line"];
	$end_line 	= $_post_data["end_line"];
		
	// grep -n '' carvatar.50ea20195cc00  | head -20 | tail -10

	$ret = array();
		
	$ret["cmd"] = $cmd = "grep -n '' \"$file_name\" | head -$end_line | tail -$start_line 2>&1";    
    exec($cmd, $ret["cmd_result"]);
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function tail_log_learn_slu($_post_data) {
    $_post_data["file_name"] = $_post_data["_DIALOG_SYSTEM_LOG_PATH_"]."/".$_post_data["file_name"];
    
    tail_log($_post_data);
}
//------------------------------------------------------------------------------
function learn_slu($_post_data)
{
    $engine_path = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"]; // www_data // tghong
    
	//$_post_data["_DIALOG_SYSTEM_DATA_PATH_"] = /www_data/dialog_domain/DOMAIN_NAME // tghong
    $data_path = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]; // www_date/dialog_domain/DOMAIN_NAME // tghong
	//$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"] = DOMAIN_NAME_DB.sqlite3"; // tghong 
    $db_file_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];
    
    $train_file_name = "$data_path/".$_post_data["project_name"].".txt";
	$asr_train_file_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["project_name"].".all.asr.txt";

	$www_data_path = $_post_data["_WWW_DATA_PATH_"];
	
    $engine = $_post_data["dialog_system_engine"];
	
    $project_type = $_post_data["project_type"];
    
    $log_path = $_post_data["_DIALOG_SYSTEM_LOG_PATH_"];
    if( !is_dir($log_path) ) mkdir($log_path);
	
    $bin_path = $_post_data["_DIALOG_SYSTEM_BIN_PATH_"];
    
    // db to text
    
    $uid = uniqid();
    
    $log_file_name = "$uid.learn_slu.log";
	
    $fn_sh = "$log_path/$uid.learn_slu.sh";
  	$fp = fopen($fn_sh, "w"); 

    fwrite($fp, "#!/bin/bash\n");
    fwrite($fp, "\n");
    
    fwrite($fp, "sync\n");
    fwrite($fp, "\n");
	
	//fwrite($fp, "echo 'project_type'.$project_type\n"); // tghong
	//$project_name = $_post_data["project_name"];	// tghong
	//fwrite($fp, "echo 'project_name'.$project_name\n");	// test // tghong
	//fwrite($fp, "echo 'engine'.$engine\n");	// test // tghong
	
	if( $project_type == "guided" ) {
		// asr train		
	    fwrite($fp, "perl $bin_path/sqlite2asr_train_corpus.pl -t guided -fn $db_file_name > $asr_train_file_name\n");
	    fwrite($fp, "sync\n");
	    fwrite($fp, "\n");
		
	    fwrite($fp, "$bin_path/../asr_english_builder/build.sh ".$_post_data["project_name"]." $bin_path/../asr_english_builder $data_path $bin_path/../asr_english_builder/dic\n");
	    fwrite($fp, "sync\n");
	    fwrite($fp, "\n");
		
		// build slu				
	} else {	// $project_type == 'dynamic' // tghong
		//$_post_data["_DIALOG_SYSTEM_DATA_PATH_"] = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"]."/".$_post_data["project_name"]; // tghong
		$asr_general = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["project_name"].".general.asr.txt";	
		
	    fwrite($fp, "sqlite3 $db_file_name \"SELECT utter FROM scenario\" | iconv -c -f utf8 -t cp949 > $asr_train_file_name\n");
	    fwrite($fp, "sync\n");
	    fwrite($fp, "\n");

		//asr train
	    // fwrite($fp, "cd $bin_path/../asr_korean_builder\n");
	    // fwrite($fp, "$bin_path/../asr_korean_builder/build.sh ".$_post_data["project_name"]." $bin_path/../asr_korean_builder $data_path $asr_train_file_name $asr_general\n");
	    // fwrite($fp, "sync\n");
	    // fwrite($fp, "\n");
		
		// build slu
	    fwrite($fp, "cd $data_path\n");
		fwrite($fp, "sqlite3 $db_file_name \"SELECT utter, slot_tagged, dialog_action FROM scenario\" | tr '|' '\\t' | iconv -c -f utf8 -t cp949  > $train_file_name\n");
	    fwrite($fp, "sync\n");
	    
		// fwrite($fp, "echo '\nHERE-1'\n"); // tghong
		//fwrite($fp, "echo $data_path\n"); // tghong
		// $data_path = /webtool/www_data/dialog_domain/DOMAIN_NAME // tghong
		// $engine_path = /www_data // tghong
		
		//fwrite($fp, "$engine_path/$engine -cmd LEARN_SLU -dic $engine_path -domain ".$_post_data["project_name"]." -fn $train_file_name\n"); // tghong
		fwrite($fp, "$engine_path/$engine -cmd LEARN_SLU -dic $www_data_path -domain ".$_post_data["project_name"]." -fn $train_file_name\n"); // tghong
	    
		// fwrite($fp, "echo '\nHERE-2'\n"); // tghong
		
		fwrite($fp, "sync\n"); // segfault occurred // tghong
		
		// fwrite($fp, "echo '\nHERE-2.5'\n"); // tghong
		
	    fwrite($fp, "perl $bin_path/update_slot_freq.pl -quite -fn $db_file_name\n");
		
		// fwrite($fp, "echo '\nHERE-3'\n"); // tghong
		
	    fwrite($fp, "sync\n");
	    fwrite($fp, "\n");
	}    
	
    fwrite($fp, "echo 'FINISHED'\n");

    fclose($fp);

	$ret = array();
	$ret["log_file_name"] = $log_file_name;

    $cmd = "echo '' > $log_path/$log_file_name;";
    $cmd .= "sync;";
    $cmd .= "chmod +x $fn_sh;";
    $cmd .= "sync;";
    $cmd .= "".$_post_data["_DIALOG_SYSTEM_SSHPASS_"]." ".$_post_data["_DIALOG_SYSTEM_SERV_IP_"]." nohup sh $fn_sh > $log_path/$log_file_name 2>&1 &";
    $cmd .= "sync;";
	
	$ret["cmd"] = $cmd;

    system($cmd);
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_make_delete_project($_post_data) {
    $ret = array();

    $prj_path = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"];
	// -> "/www_data/dialog_domain"
    $prj_name = $_post_data["project_name"];
    
    if( !is_dir("$prj_path/$prj_name") ) {
        $ret["msg"] = "project is not exist.";
        echo json_encode($ret);
        return;     
    }
	
	$backup_path = "$prj_path/../project.history";
	error_log("backup_path: $backup_path");
	
	if( !is_dir($backup_path) ) mkdir($backup_path, 0777);
    
    $ret["cmd"] = $cmd = "mv $prj_path/$prj_name $backup_path/$prj_name.".date("Y-m-d_H_i_s")." 2>&1";    
    exec($cmd, $ret["cmd_result"]);

    $ret["msg"] = "OK";
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_make_new_project($_post_data) {
	$ret = array();
	
	$prj_path = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"];
	// -> "/www_data/dialog_domain"
	$new_prj_name = $_post_data["new_project_name"];
	
	if( is_dir("$prj_path/$new_prj_name") ) {
		$ret["msg"] = "project is already exist.";
		echo json_encode($ret);
		return;		
	}
	
	// cpoy empty prj
	$cmd = "rsync -av $prj_path/empty/ $prj_path/$new_prj_name >/dev/null 2>/dev/null";
	system($cmd);
	
	$ret["cmd_sync"] = $cmd;
	
	// change file name
	$file_list = array(
		".DAtype.txt",
		".dialogLib.txt",
		".slot.txt",
		".svm",
		".svm.slack.txt",
		".task.txt",
		"_asr.txt",
		"_DB.sqlite3",
		"_DB.xls",
		"_pat.txt",
		"_tm_asr.txt",
		"_train.txt",
		"_word.txt"
	);
	
	$cmd_list = array();
	foreach( $file_list as $tail ) {
		//$cmd = "mv $prj_path/$new_prj_name/data/empty$tail $prj_path/$new_prj_name/data/".$new_prj_name."$tail >/dev/null 2>/dev/null";
		$cmd = "mv $prj_path/$new_prj_name/empty$tail $prj_path/$new_prj_name/".$new_prj_name."$tail >/dev/null 2>/dev/null"; // tghong
		array_push($cmd_list, $cmd);
	}
	
	$cmd = join("; ", $cmd_list);
	system($cmd);
	
	error_log($cmd);
	
	$ret["cmd_mv"] = $cmd;	
	$ret["msg"] = "OK";
	
	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function get_prj_list($_post_data) {
    $ret = array();
    
    $cmd = "ls -1 ".$_post_data["_DIALOG_SYSTEM_PRJ_PATH_"];
    exec($cmd, $ret);
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function reset_db($_post_data) {
	// tghong: ???
    $cmd = "cp ".$_post_data["_WWW_DATA_PATH_"]."/sample_db/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"]." ".$_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/";
    exec($cmd, $ret_cmd);
    
    $ret = array("msg" => "OK", "cmd" => $cmd, "ret_cmd" => $ret_cmd);
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function slot_tagger_delete_sentance($_post_data) {
    $no = $_post_data["no"];
    
	// /www_data/dialog_domain/DOMAIN_NAME
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	try {
        // query        
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "DELETE FROM '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."'";
        $sql .= " WHERE \"_idx\"='$no'";
        
		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));
		
		$sth->execute();

        $ret = array("msg" => "OK"); 
        
        echo json_encode($ret);

		// close the database connection
		$db = NULL;
               
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function dialog_act_tagger_get_db_table_list($_post_data) { 
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "SELECT tbl_name FROM sqlite_master WHERE type='table'";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));
		
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
function get_column_list(&$db, $table_name) {
	try {
		//open the database
//		$ret = array();
        
//        $sql = "PRAGMA table_info($table_name)";
//
//        // execute
//		$sth = $db->prepare($sql);
//		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));
//
//		$sth->execute();
//
//        // fetch
//		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
//
//		foreach( $result as $k=>$v ) {
//            $ret[$v["name"]] = 1;
//        }

        $sql = "SELECT * FROM $table_name LIMIT 1";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

        // fetch
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $column_list = array();

        foreach( $result as $rows ) {
            foreach( $rows as $k => $v ) {
                $column_list[$k] = 1;
            }
        }
        
        return $column_list;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function task_manager_save_task_info($_post_data) {
    $task_info = $_post_data["task_info"];
    
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");
        
        $column_list = get_column_list($db, $_post_data["_DB_TABLE_NAME_TASK_MANAGER_"]);

        $ret = array();

        // delete all task manager data
        $sql = "DELETE FROM '".$_post_data["_DB_TABLE_NAME_TASK_MANAGER_"]."'";
        
        array_push($ret, $sql);

   		$sth = $db->prepare($sql);
   		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));
   		
        $sth->execute();

        $idx = 0;
        // insert new task manager data
        foreach( $task_info as $i => $task ) {
            $tags = array();
            $vals = array();
            foreach( $task as $tag => $val ) {
                    
                if( !isset($ret["debug"]) ) $ret["debug"] = array();
                array_push($ret["debug"], "$tag");

                if( !isset($column_list[$tag]) ) continue;  
                
                if( $tag == "task-_idx" ) $val = ++$idx; 

                if( $tag == "task-related_slot") {
//                    $val = join("\n", explode(",", $val));
                    $val = str_replace(",", "\n", $val);
                }
                
                if( $tag == "task-fill_slot" && isset($val["slot"]) ) {          
                    if( is_array($val["slot"]) ) {
                        $str_slot = join(",", $val["slot"]);
                    } else {
                        $str_slot = $val["slot"];
                    }
					
                    $val = "";
                    if( $str_slot != "" ) {
                    	foreach( explode(",", $str_slot) as $slot ) {
                    		$slot = trim($slot); 
                    		if( $slot != "" ) $val .= "<slot>$slot, 1, progress</slot>\n";
                    	}
					}
                }
                
                if( is_array($val) ) {
                    $val = array2xml2($val);
                }
                
                $val = str_replace("\\", "", $val);
                $val = str_replace("'", "''", $val);

                array_push($tags, "\"$tag\"");                
                array_push($vals, "'$val'");                
            }
            
            $sql = "REPLACE INTO '".$_post_data["_DB_TABLE_NAME_TASK_MANAGER_"]."' (".join(",", $tags).")";
            $sql .= " VALUES (".join(",", $vals).")";
			
            array_push($ret, $sql);
            
            // error_log($sql);
                
      		$sth = $db->prepare($sql);
      		if( !$sth ) {
      			error_log($sql .":". join(",", $db->errorInfo()));
			} else {
				
			}
      		
            $sth->execute();
        }
        
        $ret["ret_cmd"] = task_manager_run_xml2db($_post_data, "task_manager", "task");
        
        echo json_encode(array_merge($ret, $_post_data["task_info"]));
        
		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function task_manager_run_xml2db($_post_data, $table_name, $tail){
    // sync ; run xml2db.pl 
           
    $path = $_post_data["_DIALOG_SYSTEM_BIN_PATH_"];
    $data_path = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"];
    
    $cmd = "sync;";
    $cmd .= "perl $path/db2xml.pl";
    $cmd .= " -fn $data_path/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];
    $cmd .= " -table_name ".$table_name;
    $cmd .= " | iconv -c -f utf8 -t cp949";
    $cmd .= " > $data_path/".$_post_data["project_name"].".".$tail.".txt";
    $cmd .= "; sync";
    
    $ret = array();
    exec($cmd, $ret);
    
    error_log($cmd);
    
    return $ret;
}
//------------------------------------------------------------------------------
function array2xml2($array, $prev_key=""){
	// error_log(var_export($array, true));

	$buf = "";
	foreach( $array as $key=>$item ) {
        $val = $item;

        if( is_array($item) ) {
            $val = "";
            
            if( is_int($key) && $prev_key != "" ) {
                $val .= "<$prev_key>\n";
            }
            
            $val .= array2xml2($item, $key);

            if( is_int($key) && $prev_key != "" ) {
                // $val = str_replace("\\", "", $val);
                $val .= "</$prev_key>\n";
            }
        }
        
        $val_check = trim($val);
        if( substr($val_check, 0, strlen("<$key>")) == "<$key>" && substr($val_check, -strlen("</$key>"), strlen("</$key>")) == "</$key>" ) {
            $buf .= $val;    
        } else if( is_int($key) && $prev_key != "" && !preg_match("/^<$prev_key>/i", $val) ) {
        
/* in case of
<intention>
    <DA>request(mode)</DA>
    <pattern>aaa</pattern>
    <pattern>bbb</pattern>
</intention>
 */            
            $val = trim($val);
            $val = str_replace("\\", "", $val);
            $buf .= "<$prev_key>$val</$prev_key>\n";

            // error_log(">>> prev_key:".$prev_key.", key:".$key.", val:".$val.", buf:". $buf);
        } else if( is_int($key) || $key == "" ) {
            $buf .= $val;    
        } else {
            $val = trim($val);
            $val = str_replace("\\", "", $val);
    		$buf .= "<$key>$val</$key>\n";
        }
	}

	return $buf;    
}
//------------------------------------------------------------------------------
function validate_xml_form($xml) {
    preg_match_all('/<(.+?)>/', $xml, $matches);
    foreach( $matches as $val ) {
        $target = str_replace(" ", "_", $val);
        $xml = str_replace($val, $target, $xml);
    }
    
	$xml = str_replace(array("\n", "\r", "\t"), '', $xml);
	$xml = trim(str_replace('"', "'", $xml));
    
    return "<root>$xml</root>";
}
//------------------------------------------------------------------------------
function tagging_result_to_dialog_action($da, $str) {
    $da_type = StripText2($da, "#", "(");
    $da_type = trim($da_type);
    
    $da_value = array();
    if( preg_match_all( '@\<(.+?)=(.+?)\>@uxis', $str, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) ) {
        foreach( $m as $set ) {
            $k = trim($set[1][0]);
            $v = trim($set[2][0]);
            
            array_push($da_value, "$k=\"$v\"");     
        }
    }

    return "# ".$da_type."(".join(", ", $da_value).")";
}
//------------------------------------------------------------------------------
function run_utter_dialog_act_tagger($_post_data) {
    if( $_post_data["cmd"] == "SET_SLOT_TAGGING" ) {
        $tagging_result = $_post_data["tagging_result"];
        
        $xml = array(
            "machine_utter_str" => $tagging_result["machine_utter_str"],
            "dialog_action" => $tagging_result["dialog_action"],
            "slot_tagged" => $tagging_result["slot_tagged"],
            "comment" => $tagging_result["comment"]
        );
        
        $xml["dialog_action"] = tagging_result_to_dialog_action($xml["dialog_action"], $xml["slot_tagged"]);
        
        $ret = array();
        
        $ret["result"] = $xml;        
        // $ret["error"] = $error;
     
        echo json_encode($ret);
        return;
    }

    $engine_path = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"];
    $dic_path = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"];
	    
    $cmd = "GET_SLOT_TAGGING";
    $utter = $_post_data["utter"];
    $slot_tagged = ( isset($_post_data["slot_tagged"]) ) ? $_post_data["slot_tagged"] : "";
    
    $descriptorspec = array(
       0 => array("pipe", "r"), 
       1 => array("pipe", "w"),  
       2 => array("pipe", "w") 
    );
    
    $env = array();
    
    $shell_cmd = "$engine_path/dial -dic $dic_path -domain ".$_post_data["project_name"]." -cmd $cmd";
    $process = proc_open($shell_cmd, $descriptorspec, $pipes, $engine_path, $env);

    // error_log($shell_cmd);

    $stdin = "$utter\n";
    
    $ret = array("in" => $stdin);
    if( is_resource($process) ) {
        // write
        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);
    
        // read result
        $result = "";
        while($s = fgets($pipes[1], 1024)) { 
            $result .= $s; 
        } 
        fclose($pipes[1]);

        // read error
        $error = "";
        while($s = fgets($pipes[2], 1024)) { 
            $error .= $s; 
        } 
        
        fclose($pipes[2]);
    
        // set display order
        $xml = array(
            "machine_utter_str" => "",
            "dialog_action" => "",
            "slot_tagged" => "",
            "comment" => ""
        );


        $result = preg_replace("/^.+<utter>/s", "<utter>", $result);
        $xml = array_merge($xml, xml2array($result));

        // parse SLU LOG
        $t = explode("<BR>", $xml["SLU_LOG"]);
        
        $ret["debug"] = $t;
    
        if( count($t) > 0 ) {
            foreach( $t as $line ) {
                if( strpos($line, "SLU1:") === false ) continue;

                $str = str_replace("SLU1: ", "", $line);
                list($xml["slot_tagged"]) = explode(" #", $str, 2);
                
                $xml["slot_tagged"] = str_replace("<TAB>", "", $xml["slot_tagged"]);
                break;
            }
        }

        // 
        $tagging_result = $_post_data["tagging_result"];
        
        $xml["machine_utter_str"] = $tagging_result["machine_utter_str"];
        $xml["comment"] = $tagging_result["comment"];

        $xml["slot_tagged"] = preg_replace("/^.+:\s*/s", "", $xml["slot_tagged"]);
        
        unset($xml["kma"]);
        unset($xml["morph"]);
        unset($xml["machine_utter"]);
        unset($xml["SLU_LOG"]);
        
        $ret["result"] = $xml;        

        // error_log(json_encode($ret));

		$ret["shell_cmd"] = $shell_cmd;
        
        $return_value = proc_close($process);
    } else {
        error_log("error to start: ".$shell_cmd);
    }
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function run_utter_dialog_act_tagger_script_type($_post_data) {
    $cwd = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"];
    
    $cmd = $_post_data["cmd"];
    $utter = $_post_data["utter"];
    $slot_tagged = ( isset($_post_data["slot_tagged"]) ) ? $_post_data["slot_tagged"] : "";
    
    $descriptorspec = array(
       0 => array("pipe", "r"), 
       1 => array("pipe", "w"),  
       2 => array("pipe", "w") 
    );
    
    $env = array();

	// tghong: ???
    $shell_cmd = "perl $cwd/slotagger.pl $cwd";
    error_log($shell_cmd);
    
    $process = proc_open($shell_cmd, $descriptorspec, $pipes, $cwd, $env);
    
    $stdin = "$cmd\t$utter\n";
    if( $cmd == "SET_SLOT_TAGGING" ) $stdin = "$cmd\t$utter\t$slot_tagged\n";
    
    $ret = array("in" => $stdin);
    
    if( is_resource($process) ) {
        // write
        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);
    
        // read result
        $result = "";
        while($s= fgets($pipes[1], 1024)) { 
            $result .= $s; 
        } 
        fclose($pipes[1]);

        // read error
        $error = "";
        while($s= fgets($pipes[2], 1024)) { 
            $error .= $s; 
        } 
        
        fclose($pipes[2]);
        
        // set display order
        $xml = array(
            "machine_utter_str" => "",
            "slot_tagged" => "",
            "dialog_action" => "",
            "comment" => ""
        );

        $xml = array_merge($xml, xml2array($result));
    
        unset($xml["kma"]);
        unset($xml["morph"]);
        unset($xml["machine_utter"]);
    
        $ret["result"] = $xml;        
        $ret["error"] = $error;
        
        $return_value = proc_close($process);
    }
    
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function get_utter_slot_list($tagging_result) { 
    preg_match_all('/<([^=]+)=([^>]+)>/', $tagging_result, $matches, PREG_SET_ORDER);

    return $matches;    
}
//------------------------------------------------------------------------------
function get_utter_tagging_candidate($_post_data) { 
    // <consumable=냉각수>+가 새+ㄴ지 안 새+ㄴ지 <consumable_action=확인> 하+려면 어떻게 하+어야되+ㅂ니까+?
    
    $no = $_post_data["no"];
    $tagging_result = $_post_data["tagging_result"];
    
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	try {
        // get slot list
        $matches = get_utter_slot_list($tagging_result);
        
        $tag_list = array();
        
        $where = "";
        foreach( $matches as $match ) {
            $tag = $match[0];
            
            if( $where != "" ) $where .= " OR ";
            $where .= "slot_tagged LIKE '%$tag%'";
            
            array_push($tag_list, $tag);
        }
        
        if( $where != "" ) $where = "WHERE no!='$no' AND ($where)";

        // query        
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "SELECT _idx AS no,utter,slot_tagged,machine_utter_str,comment,dialog_action,worker";
		$sql .= " FROM '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."'";
        $sql .= " $where";
        
		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));
		
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        $ret = array(); 

        foreach( $tag_list as $k => $tag ) {
            if( !isset($ret[$tag]) || !is_array($ret[$tag]) ) $ret[$tag] = array();
        }
        
        foreach( $result as $rows ) {
            if( !isset($rows["slot_tagged"]) ) continue;
            
            foreach( $tag_list as $k => $tag ) {
                if( strpos($rows["slot_tagged"], $tag) !== false ) {
                    array_push($ret[$tag], array(
                            "no"=>$rows["no"], 
                            "worker"=>$rows["worker"], 
                            "slot_tagged" => $rows["slot_tagged"], 
                            "dialog_action" => $rows["dialog_action"]
                    ));
                }
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
function save_utter_tagging_result($_post_data) { 
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "PRAGMA table_info(".$_post_data["_DB_TABLE_NAME_SCENARIO_"].")";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));
		
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        $table_info = array();        
        foreach( $result as $rows ) {
            $table_info[$rows["name"]] = 1;
        }
        
        // check extra & main col list
        $cols = array();
        $vals = array();
        $buf_xml = "";
        foreach( $_post_data["tagging_result"] as $k => $v ) {
            if( substr($k, 0, 1) == "_" ) continue;
            
            $k = trim($k);
            $v = trim($v);
            
            $v = str_replace("\\", "", $v);
            $v = str_replace("'", "''", $v);
            
            if( isset($table_info[$k]) && $table_info[$k] != "" ) {
                array_push($cols, $k);

                if( $_post_data["tagging_result"]["no"] < 0 ) {
                    array_push($vals, "'$v'");
                } else {
                    if( $k == "m_date" ) {
                        array_push($vals, "$k=datetime('now','localtime')");
                    } else {
                        array_push($vals, "$k='$v'");
                    }
                }
            } else {
                $buf_xml .= "<$k>$v</$k>";
            }
        }

        if( $_post_data["tagging_result"]["no"] < 0 ) {
//            array_push($cols, "result");
//            array_push($vals, "'$buf_xml'");
        
            array_push($cols, "_idx");
            array_push($vals, "(SELECT MAX(CAST(\"_idx\" AS INTEGER))+1 FROM '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."')");
            
            $sql = "INSERT INTO '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."'";
            $sql .= " (".join(", ", $cols).")";
            $sql .= " VALUES (".join(", ", $vals).")";
        } else {
//            array_push($vals, "result='$buf_xml'");

            $sql = "UPDATE '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."' SET ".join(", ", $vals);
            $sql .= " WHERE \"_idx\"='".$_post_data["tagging_result"]["no"]."'";
        }
        
        $table_info["debug"] = $sql;
        
        if( !$sth = $db->prepare($sql) ) error_log($sql.":".join(",", $db->errorInfo()));
		$sth->execute();
        
        echo json_encode($table_info);
        
        if( 0 ) {
            // make tag index                 
            $matches = get_utter_slot_list($_post_data["tagging_result"]["slot_tagged"]);
            
            foreach( $matches as $match ) {
                $slot_name = str_replace("'", "''", $match[1]);
                $slot_value = str_replace("'", "''", $match[2]);
                
                $sql = "INSERT INTO 'cavata_slot_freq' (slot_name, slot_value, freq) VALUES ('$slot_name', '$slot_value', '1')";
    
        		$sth = $db->prepare($sql);
        		$sth->execute();
            }
        }
        
		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function get_utter_tagged_result($_post_data) { 
    $no = $_post_data["no"];
    
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];
    
//    error_log($db_name);

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "SELECT _idx AS no,worker,status,m_date,utter,slot_tagged,machine_utter_str,comment,dialog_action";
		$sql .= " FROM '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."'";
        $sql .= " WHERE no='$no'";

        if( !$sth = $db->prepare($sql) ) error_log(join(",", $db->errorInfo()));
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
function get_utter_list($_post_data) {   
    $keyword = $_post_data["keyword"];
    $status  = $_post_data["status"];
    $worker  = $_post_data["worker"];
    
    $page_number = $_post_data["page_number"] - 1;    
    $page_size = $_post_data["page_size"];
    
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];
    
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

    	$limit = "LIMIT ".sprintf("%d,%d", $page_size*$page_number, $page_size);

        $keyword = str_replace("'", "''", $keyword);

        // make where condition
        $where = "";
        if( $keyword != "" ) $where .= "utter LIKE '%$keyword%'";
        
        if( $status != "NONE" && $status != "" ) {
            if( $where != "" ) $where .= " AND ";
            $where .= "status = '$status'";   
        }
        
        if( $worker != "" ) {
            if( $where != "" ) $where .= " AND ";
            $where .= "worker = '$worker'";   
        }        
        
        if( $where != "" ) $where = "WHERE $where";

		// query utter list
		$sql = "SELECT _idx AS no,speaker,utter,status,worker";
		$sql .= " FROM '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."'";
		$sql .= " $where $limit";
        
		$sth = $db->prepare($sql);
        if( !$sth ) error_log($sql.":".join(",", $db->errorInfo()));
        
		$sth->execute();

        $ret["rows"] = $sth->fetchAll(PDO::FETCH_ASSOC);

        // query total count
		$sql = "SELECT COUNT(*) AS total";
		$sql .= " FROM '".$_post_data["_DB_TABLE_NAME_SCENARIO_"]."'";
		$sql .= " $where";

		$sth = $db->prepare($sql);
		$sth->execute();
        
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $ret["total"] = $result[0]["total"];
        
        // display result
        echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function html2a( $html ) {
	$html = trim($html);
	
    if ( !preg_match_all( '@\<(\w+)\>(.*?)\<\/\\1\>@uxis', $html, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) ) {
        return $html; 
    }

    $ret = array();
    
    foreach( $m as $set ) {
        $k = trim($set[1][0]);
        $v = trim($set[2][0]);

        $v = str_replace("\\", "", $v);

        if( preg_match('@\<(\w+)\>(.*?)\<\/\\1\>@uxis', $v) ) {
            if( isset($ret[$k]) ) {
                if( is_obj($ret[$k]) ) {
                    $vv = $ret[$k];
                    unset($ret[$k]);

                    $ret[$k] = array($vv, html2a($v));
                } else {
                    $vv = $ret[$k];
                    unset($ret[$k]);

                    array_push($vv, html2a($v));

                    // $vv = str_replace("\\", "", $vv);
                    
                    $ret[$k] = $vv;
                }                
            } else {
                $ret[$k] = html2a($v);
            }
        } else {
            if( isset($ret[$k]) ) {
                // in case of
                // <intention>
                //     <DA>request(mode)</DA>
                //     <pattern>aaa</pattern>
                //     <pattern>bbb</pattern>
                //     <pattern>ccc</pattern>
                // </intention>

//                $vv = $ret[$k];
//                unset($ret[$k]);

                // $v = str_replace("\\", "", $v);
                array_push($ret[$k], $v);
            } else {
                $ret[$k] = array($v);
            }
        }        
    }

    return $ret;
}
//------------------------------------------------------------------------------
function is_obj( $object )
{
    foreach( $object as $k => $v ) {
        if( is_string($k) ) return true;        
    }
    
    return false;
}
//------------------------------------------------------------------------------
function dialog_act_tagger_db_convert($_post_data)
{
    $filename 	  = $_post_data["filename"];
    $user_name 	  = $_post_data["user_name"];
    $project_name = $_post_data["project_name"];
	$project_type = $_post_data["project_type"];
    
    $fileinfo = pathinfo($filename);
    $path 	  = $fileinfo["dirname"];
    $type 	  = $fileinfo["extension"];
    $basename = $fileinfo["filename"];
    
    $bin_path = $_post_data["_DIALOG_SYSTEM_BIN_PATH_"];
	
    $cmd = "sync;";
	
    if( $type == "xls" ) {
		// db backup
        $cmd .= "cp $path/".$project_name."_DB.sqlite3 $path/".$project_name."_DB.`date '+%F_%H-%M-%S'`.sqlite3;";
        $cmd .= "sync;";
		
		if( $project_type == "guided" ) {
			$cmd .= "cd $path;";
	        $cmd .= "perl $bin_path/xls2text.pl $filename >$path/$basename.log 2>&1;";
	        $cmd .= "$bin_path/process.sh $filename.map.txt $project_name $path $bin_path 2>&1;";
	        $cmd .= "$bin_path/task_xml2sqlite.sh $bin_path $path $project_name 2>&1;";
	        
			// to to domoin.map.txt to sqlite
	        $cmd .= "perl $bin_path/guide_map2sqlite.pl -fn $filename.map.txt -out $path/".$project_name."_DB.sqlite3 2>&1;sync;";						
		} else {
	        $cmd .= "perl $bin_path/xls2sqlite.pl -fn $filename -out $path/".$project_name."_DB.sqlite3 >$path/$basename.log 2>&1;sync;";
	        $cmd .= "perl $bin_path/update_slot_freq.pl -fn $path/".$project_name."_DB.sqlite3 >>$path/$basename.log 2>&1;sync;";
	        $cmd .= "perl $bin_path/update_related_slot.pl -fn $path/".$project_name."_DB.sqlite3 >>$path/$basename.log 2>&1;sync;";

	        $cmd .= "sqlite3 $path/".$project_name."_DB.sqlite3 \".dump\" >$path/".$project_name."_DB.sql;sync;";	        	        
	        $cmd .= "mv $path/".$project_name."_DB.sqlite3 $path/".$project_name."_DB.`date '+%F_%H-%M-%S'`.sqlite3;sync;";	        	        
	        $cmd .= "sqlite3 $path/".$project_name."_DB.sqlite3 <$path/".$project_name."_DB.sql;sync;";	        	        
		}
    } else if( $type == "json" ) {
    	
    } else if( $type == "sqlite3" ) {
        $cmd .= "mv $path/".$project_name."_DB.xls $path/".$project_name."_DB.`date '+%F_%H-%M-%S'`.xls;";
        $cmd .= "sync;";

		if( $project_type == "guided" ) {
		} else {
	        $cmd .= "perl $bin_path/update_slot_freq.pl -fn $filename >$path/$basename.log 2>&1;";
	        $cmd .= "perl $bin_path/update_related_slot.pl -fn $filename >>$path/$basename.log 2>&1;sync;";
	        $cmd .= "perl $bin_path/sqlite2xls.pl -fn $filename -out $path/".$project_name."_DB.xls >>$path/$basename.log 2>&1;";
		}
    }

    $cmd .= "sync";
	
//	error_log($cmd);
    
    $ret = array();
    exec($cmd, $ret["cmd_result"]);
    
	if( $type == "xls" ) {
	    $ret["ret_cmd"] = task_manager_run_xml2db($_post_data, "dialog_lib", "dialogLib");
	    $ret["ret_cmd"] = task_manager_run_xml2db($_post_data, $_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"], "slot");
		$ret["ret_cmd"] = task_manager_run_xml2db($_post_data, "task_manager", "task");
		$ret["ret_cmd"] = task_manager_run_xml2db($_post_data, $_post_data["_DB_TABLE_NAME_DA_TYPE_"], "DAtype");
	}

    $ret["log"] = file_get_contents("$path/$basename.log");
    $ret["cmd"] = $cmd;
    $ret["fileinfo"] = $fileinfo;

    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_download_file($_post_data)
{
    $file_dir = "";
    $file_name = $_post_data["filename"];
    
    if( strpos($_post_data["filename"], "..") !== false ) return;
    if( !is_file($file_dir.$file_name) ) return;

    // 다운로드 방식을 구한다. 
    $ext = array_pop(explode(".", $file_name)); 
    
    $file_type = null;
    
    if ($ext=="avi" || $ext=="asf")         $file_type = "video/x-msvideo"; 
    else if ($ext=="mpg" || $ext=="mpeg")   $file_type = "video/mpeg"; 
    else if ($ext=="jpg" || $ext=="jpeg")   $file_type = "image/jpeg"; 
    else if ($ext=="gif")                   $file_type = "image/gif"; 
    else if ($ext=="png")                   $file_type = "image/png"; 
    else if ($ext=="txt")                   $file_type = "text/plain"; 
    else if ($ext=="zip")                   $file_type = "application/x-zip-compressed"; 
    
    if( file_exists($file_dir.$file_name) ) { 
        $fp = fopen($file_dir.$file_name, "rb"); 
    
        if( $file_type ) { 
            header("Content-type: $file_type"); 
            Header("Content-Length: ".filesize($file_dir.$file_name));     
            Header("Content-Disposition: attachment; filename=$file_name");   
            Header("Content-Transfer-Encoding: binary"); 
            header("Expires: 0"); 
        } else { 
            if( eregi("(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)", $_SERVER["HTTP_USER_AGENT"]) ){ 
                Header("Content-type: application/octet-stream"); 
                Header("Content-Length: ".filesize($file_dir.$file_name));     
                Header("Content-Disposition: attachment; filename=$file_name");   
                Header("Content-Transfer-Encoding: binary");   
                Header("Expires: 0");   
            } else{ 
                Header("Content-type: file/unknown");     
                Header("Content-Length: ".filesize($file_dir.$file_name)); 
                Header("Content-Disposition: attachment; filename=$file_name"); 
                Header("Content-Description: PHP3 Generated Data"); 
                Header("Expires: 0"); 
            } 
        } 
    
    
        fpassthru($fp); 
        fclose($fp); 
    } 
    
    return; 
} 
//------------------------------------------------------------------------------
function dialog_act_tagger_delete_data_file($_post_data)
{
    if( strpos($_post_data["filename"], "..") !== false ) return;
    
    $filename = $_post_data["filename"];
    
    $post_result = array("msg" => "error");
    
    if( !is_file($filename) ) {
        $post_result["filename"] = $filename;
        $post_result["msg"] = "This is not file.";

        echo json_encode($post_result);
        return;
    }
    
    if( !unlink($filename) ) {
        $err = error_get_last();
        if( $err["message"] != "" ) {
            $post_result["error_msg"] = $err["message"];
            $post_result["msg"] = preg_replace("/^.+:\s*/", "", $err["message"]);
        }

        echo json_encode($post_result);
        return;
    }

    $post_result["msg"] = "ok";

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_read_file_list($_post_data)
{
    $path = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"];
    
    $disp_ext = array("xls" => 1, "sqlite3" => 1, "bin" => 1, "svm" => 1, "json" => 1);
    
    // read file
    $listFile = array();
    if( $handler = opendir($path) ) {
        while( ($sub = readdir($handler)) !== FALSE ) {
            if( $sub == "." || $sub == ".." ) continue;
			
			// added by tghong
			if ($sub == ".cdt" || $sub == ".ctx" || $sub == "asr_korean" || $sub == "crf_tool") continue;

            // hidden file
            if( strpos($sub, ".") !== false && strpos($sub, ".") == 0 ) continue;
            
            $filename = $path."/".$sub;
            if( !is_dir($filename) ){                
                $size = filesize($filename);    
                $fileinfo = pathinfo($filename);
                
                if( !isset($disp_ext[$fileinfo["extension"]]) ) continue;

                array_push($listFile, array(
                    "name" => $sub, 
                    "size" => $size, 
                    "dirname" => $path,
                    "extension" => $fileinfo["extension"]
                )); 
            }
        }
        
        closedir($handler); 
    } 

    echo json_encode($listFile);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_upload_file($_post_data) {
    error_reporting(E_ALL | E_STRICT);
	
    include_once("res_manager.upload.php");
    
    $options = array(
        "upload_dir" => $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/",
        "param_name" => "files",
        "max_file_size" => null,
        "min_file_size" => 1,
        "accept_file_types" => "/.+$/i",
        "max_number_of_files" => null,
        "discard_aborted_uploads" => true
    );
    
    $upload_handler = new UploadHandler($options);
    
    header("Pragma: no-cache");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Content-Disposition: inline; filename=\"files.json\"");
    header("X-Content-Type-Options: nosniff");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size");

    $upload_handler->post();
}
//------------------------------------------------------------------------------
?>