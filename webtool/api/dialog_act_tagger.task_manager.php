<?
//------------------------------------------------------------------------------
function task_manager_search_user_dict($_post_data)
{
//	$engine_path = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"];
//	$dic_path = $_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"];
//
//	$cmd = "USERDIC_MANAGER";
//	$utter = $_post_data["utter"];
//	$slot_tagged = ( isset($_post_data["slot_tagged"]) ) ? $_post_data["slot_tagged"] : "";
//
//	$descriptorspec = array(
//		0 => array("pipe", "r"),
//		1 => array("pipe", "w"),
//		2 => array("pipe", "w")
//	);
//
//	$env = array();
//
//	$shell_cmd = "$engine_path/dial -dic $dic_path -domain ".$_post_data["project_name"]." -cmd $cmd";
//	$process = proc_open($shell_cmd, $descriptorspec, $pipes, $engine_path, $env);
//
//	// error_log($shell_cmd);
//
//	$stdin = "$utter\n";
//
//	$ret = array("in" => $stdin);
//	if( is_resource($process) ) {
//		// write
//		fwrite($pipes[0], $stdin);
//		fclose($pipes[0]);
//
//		// read result
//		$result = "";
//		while($s = fgets($pipes[1], 1024)) {
//			$result .= $s;
//		}
//		fclose($pipes[1]);
//
//		// read error
//		$error = "";
//		while($s = fgets($pipes[2], 1024)) {
//			$error .= $s;
//		}
//
//		fclose($pipes[2]);
//
//		// set display order
//		$xml = array(
//			"machine_utter_str" => "",
//			"dialog_action" => "",
//			"slot_tagged" => "",
//			"comment" => ""
//		);
//
//
//		$result = preg_replace("/^.+<utter>/s", "<utter>", $result);
//		$xml = array_merge($xml, xml2array($result));
//
//		// parse SLU LOG
//		$t = explode("<BR>", $xml["SLU_LOG"]);
//
//		$ret["debug"] = $t;
//
//		if( count($t) > 0 ) {
//			foreach( $t as $line ) {
//				if( strpos($line, "SLU1:") === false ) continue;
//
//				$str = str_replace("SLU1: ", "", $line);
//				list($xml["slot_tagged"]) = explode(" #", $str, 2);
//
//				$xml["slot_tagged"] = str_replace("<TAB>", "", $xml["slot_tagged"]);
//				break;
//			}
//		}
//
//		//
//		$tagging_result = $_post_data["tagging_result"];
//
//		$xml["machine_utter_str"] = $tagging_result["machine_utter_str"];
//		$xml["comment"] = $tagging_result["comment"];
//
//		$xml["slot_tagged"] = preg_replace("/^.+:\s*/s", "", $xml["slot_tagged"]);
//
//		unset($xml["kma"]);
//		unset($xml["morph"]);
//		unset($xml["machine_utter"]);
//		unset($xml["SLU_LOG"]);
//
//		$ret["result"] = $xml;
//
//		// error_log(json_encode($ret));
//
//		$ret["shell_cmd"] = $shell_cmd;
//
//		$return_value = proc_close($process);
//	} else {
//		error_log("error to start: ".$shell_cmd);
//	}
//
//	echo json_encode($ret);
}
//------------------------------------------------------------------------------
function get_task_manager_info($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	if( !file_exists($db_name) ) {
		error_log("File Not Exists: ".$db_name);
		echo json_encode(array("error"));
		return;
	}

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "SELECT * FROM '".$_post_data["_DB_TABLE_NAME_TASK_MANAGER_"]."'";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$ret = array();
		foreach( $result as $rows ) {
			array_push($ret, $rows);
		}

		// xml to array
		for( $i=0 ; $i<count($ret) ; $i++ ) {
			task_manager_convert_xml_to_array($ret, $i, "task-next_task");
//			task_manager_convert_xml_to_array($ret, $i, "task-_position");
			$ret[$i]["task-_position"] = html2a($ret[$i]["task-_position"]);

			if( isset($ret[$i]["task-fill_slot"]) ) {
				$ret[$i]["task-fill_slot"] = html2a( $ret[$i]["task-fill_slot"] );
			}
			
			// empty position
			if( !isset($ret[$i]["task-_position"]["top"]) ) {
				$top = $i;
				$left = 3;
				
				if( $i > 3 ) {
					$top = $i - 4;
					$left = 150;					
				}
				
				$ret[$i]["task-_position"]["top"] = 3 + $top * 43;
				$ret[$i]["task-_position"]["left"] = $left;
				$ret[$i]["task-_position"]["width"] = 120;
			}
			
			// "task-fill_slot"
			if( isset($ret[$i]["task-fill_slot"]["slot"]) ) {
				$fill_slot = $ret[$i]["task-fill_slot"]["slot"];
				
				$fill_slot = join(", ", $fill_slot);
				$fill_slot = str_replace(", 1, progress", "", $fill_slot);
				$fill_slot = str_replace("\n", "", $fill_slot);
				
				$ret[$i]["task-fill_slot"]["slot"] = $fill_slot;
			} else {
				$ret[$i]["task-fill_slot"]["slot"] = "";
			}
			
			// related slot
			$related_slot = ( isset($ret[$i]["task-related_slot"]) ) ? $ret[$i]["task-related_slot"] : "";

			$related_slot = str_replace("\r\n", "\n", $related_slot);
			$related_slot = str_replace("\n", ",", $related_slot);
			$ret[$i]["task-related_slot"] = $related_slot;
		
			// task goal
			// IsDATypeAtPreviousUtter(\\"user\\", \\"request_visit\\")==true
    //     	foreach( $ret[$i] as $k => $v ) {
				// $v = str_replace("\\", "", $v);
    //     		$ret[$i][$k] = $v;
    //     	}
			// prev error correction
			$ret[$i]["task-task_goal"] = str_replace("\\", "", $ret[$i]["task-task_goal"]);
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
function task_manager_convert_xml_to_array(&$ret, $idx, $tag) {
	$val = ( isset($ret[$idx][$tag]) ) ? $ret[$idx][$tag] : "";

	$val = validate_xml_form($val);
	$val = simplexml_load_string($val);

	$ret[$idx][$tag] = $val;
}
//------------------------------------------------------------------------------


?>