<?
//------------------------------------------------------------------------------
function dialog_act_tagger_save_da_type($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	$da_type_data = $_post_data["da_type_data"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query
		$sql = "DELETE FROM '".$_post_data["_DB_TABLE_NAME_DA_TYPE_"]."'";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

		// insert
		$cnt = 1;

		foreach( $da_type_data as $i => $data ) {
			$vals = $cols = array();
			foreach( $data as $k => $v ) {
				if( $k == "define-_idx" ) $v = $cnt++;

				array_push($cols, "\"$k\"");
				array_push($vals, "'$v'");
			}
				
			//
			$sql = "INSERT INTO '".$_post_data["_DB_TABLE_NAME_DA_TYPE_"]."' (".join(",", $cols).")";
			$sql .= " VALUES (".join(",", $vals).")";
				
			$sth = $db->prepare($sql);
			if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));
				
			$sth->execute();
		}

		$ret = array("msg" => "OK", "sql" => $sql);
		$ret["ret_cmd"] = task_manager_run_xml2db($_post_data, $_post_data["_DB_TABLE_NAME_DA_TYPE_"], "DAtype");

		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function get_da_type($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		$sql = "SELECT * FROM '".$_post_data["_DB_TABLE_NAME_DA_TYPE_"]."'";

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

?>