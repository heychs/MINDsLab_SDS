<?
//------------------------------------------------------------------------------
function task_manager_get_dialog_library($_post_data) { 
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	$task_name = $_post_data["task_name"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "SELECT *";
		$sql .= " FROM '".$_post_data["_DB_TABLE_NAME_DIALOG_LIBRARY_"]."'";
		$sql .= " WHERE \"dialog_node-related_task\"='$task_name' OR \"dialog_node-task\"='$task_name'";
        
		$sth = $db->prepare($sql);
        if( !$sth ) error_log(join(",", $db->errorInfo()));
        
		$sth->execute();
        
        $ret = array();
        
        $ret["sql"] = $sql;
        $ret["rows"] = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        // 
        for( $i=0 ; $i<count($ret["rows"]) ; $i++ ) {
            // TO DO
            $ret["rows"][$i] = str_replace("<utterance><utterance>", "<utterance>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</utterance></utterance>", "</utterance>", $ret["rows"][$i]);

            $ret["rows"][$i] = str_replace("<action><action>", "<action>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</action></action>", "</action>", $ret["rows"][$i]);

            $ret["rows"][$i] = str_replace("<pattern><pattern>", "<pattern>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</pattern></pattern>", "</pattern>", $ret["rows"][$i]);

            $ret["rows"][$i] = str_replace("<condition><condition>", "<condition>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</condition></condition>", "</condition>", $ret["rows"][$i]);

            $ret["rows"][$i] = str_replace("<utterance><utterance>", "<utterance>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</utterance></utterance>", "</utterance>", $ret["rows"][$i]);

            $ret["rows"][$i] = str_replace("<template><template>", "<template>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</template></template>", "</template>", $ret["rows"][$i]);

            $ret["rows"][$i] = str_replace("<intention><intention>", "<intention>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</intention></intention>", "</intention>", $ret["rows"][$i]);
            
            $ret["rows"][$i] = str_replace("<DA><DA>", "<DA>", $ret["rows"][$i]);
            $ret["rows"][$i] = str_replace("</DA></DA>", "</DA>", $ret["rows"][$i]);

            task_manager_convert_xml_to_array($ret["rows"], $i, "dialog_node-request_utterance");
            
            $ret["rows"][$i]["dialog_node-utterance_set"] = html2a($ret["rows"][$i]["dialog_node-utterance_set"]);
        }
        
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
function save_dialog_library_detail($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

    $dialog_library_data = $_post_data["dialog_library_data"];

	$idx = $dialog_library_data["dialog_node-_idx"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query utter list
        $sets = $cols = array();
        foreach( $dialog_library_data as $k=>$v ) {
            if( is_array($v) ) $v = array2xml2($v);
            
            $v = str_replace("'", "\"", $v);
            $v = str_replace("\\", "", $v);
            
            if( $idx < 0 ) {
                $v = "'$v'";
                if( $k == "dialog_node-_idx" ) $v = "(SELECT MAX(CAST(\"dialog_node-_idx\" AS INTEGER))+1 FROM '".$_post_data["_DB_TABLE_NAME_DIALOG_LIBRARY_"]."')";
                
                array_push($cols, "\"$k\"");
                array_push($sets, $v);
            } else {
                array_push($sets, "\"$k\"='$v'");
            }
        }            
        
        if( $idx < 0 ) {
            $sql = "INSERT INTO '".$_post_data["_DB_TABLE_NAME_DIALOG_LIBRARY_"]."'";
            $sql .= " (".join(", ", $cols).")";
            $sql .= " VALUES (".join(", ", $sets).")";
        } else {
    		$sql = "UPDATE '".$_post_data["_DB_TABLE_NAME_DIALOG_LIBRARY_"]."'";
            $sql .= " SET ".join(",", $sets);
    		$sql .= " WHERE \"dialog_node-_idx\"='$idx'";
        }

        // error_log($sql);

		$sth = $db->prepare($sql);
        if( !$sth ) error_log($sql.":".join(",", $db->errorInfo()));
        
		$sth->execute();
        
        $ret = array();
        $ret["sql"] = $sql;

        $ret["ret_cmd"] = task_manager_run_xml2db($_post_data, "dialog_lib", "dialogLib");
//        $ret["ret_cmd_task"] = task_manager_run_xml2db($_post_data, "task_manager", "task");

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
function delete_dialog_library_detail($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

    $dialog_library_data = $_post_data["dialog_library_data"];

	$idx = $dialog_library_data["dialog_node-_idx"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		$sql = "DELETE FROM '".$_post_data["_DB_TABLE_NAME_DIALOG_LIBRARY_"]."'";
		$sql .= " WHERE \"dialog_node-_idx\"='$idx'";

		$sth = $db->prepare($sql);
        if( !$sth ) error_log($sql.":".join(",", $db->errorInfo()));
        
		$sth->execute();
        
        $ret = array();
        $ret["sql"] = $sql;

        $ret["ret_cmd"] = task_manager_run_xml2db($_post_data, "dialog_lib", "dialogLib");

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

?>