<?


function dialog_act_tagger_get_extra_db_column_list($_post_data) {
	$table_name = $_post_data["table_name"];

	try {
		//open the database
		list($db, $table_name, $quote, $db_type) = dialog_act_tagger_open_extra_db($_post_data);

		// query
		$sql = "SELECT * FROM ".$quote.$table_name.$quote;
		if ($db_type == "mysql" && $_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1) $sql .= " WHERE _domain='" . $_post_data["project_name"] . "'";
		$sql .= " LIMIT 1";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$ret = array();
		foreach( $result as $rows ) {
			foreach( $rows as $k => $v ) {
				$ret[$k] = $k;
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


function dialog_act_tagger_delete_extra_db_record($_post_data) {
	$table_name = $_post_data["table_name"];
	$db_record_idx = $_post_data["db_record_idx"];

	if( $db_record_idx == "" ) {
		echo json_encode(array( "MSG" => "ERROR" ));
		return;
	}

	try {
		//open the database
		list($db, $table_name, $quote, $db_type) = dialog_act_tagger_open_extra_db($_post_data);

		// query
		$sql = "DELETE FROM ".$quote.$table_name.$quote." WHERE _idx=$db_record_idx";
		if ($db_type == "mysql" && $_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1) $sql .= " AND _domain='" . $_post_data["project_name"] . "'";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

		$ret = array("sql" => $sql, "msg" => "OK");
		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}


function dialog_act_tagger_get_idx_extra_db($db, $quote, $table_name, $db_type, $_post_data)
{
	$int_cast = "INTEGER";
	if ($db_type == "mysql") $int_cast = "SIGNED";

	$sql = "SELECT MAX(CAST(_idx AS $int_cast))+1 AS idx FROM ".$quote.$table_name.$quote;
	if ($db_type == "mysql" && $_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1) $sql .= " WHERE _domain='" . $_post_data["project_name"] . "'";

//	error_log($sql);

	// execute
	$sth = $db->prepare($sql);
	if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

	$sth->execute();

	// fetch
	$result = $sth->fetchAll(PDO::FETCH_ASSOC);

	foreach( $result as $rows ) {
		foreach( $rows as $k => $v ) {
//			error_log($k);
//			error_log($v);
			if( $v == "" ) return 1;

			return $v;
		}
	}

	return 1;
}


function dialog_act_tagger_open_extra_db($_post_data) {
	$db = NULL;
	$quote = "'";
	$table_name = "";
	$db_type = "";

	try {
		if( array_key_exists("table_name", $_post_data) != TRUE ) {
			$_post_data["table_name"] = "task_information";
		}

		//open the database
		if ( array_key_exists("_MYSQL_HOST_", $_post_data) && $_post_data["_MYSQL_HOST_"] != "" && $_post_data["table_name"] == "task_information") {
			$quote = "`";
			$db_type = "mysql";

			if ($_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1) {
				$table_name = $_post_data["table_name"];
			} else {
				$table_name = $_post_data["project_name"];
			}

			$db = new PDO("mysql:host=" . $_post_data["_MYSQL_HOST_"] . ";dbname=" . $_post_data["_MYSQL_DBNAME_"] . ";port=" . $_post_data["_MYSQL_PORT_"],
				$_post_data["_MYSQL_USER_"], $_post_data["_MYSQL_PW_"]);
		} else {
			$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

			$table_name = $_post_data["table_name"];
			$db = new PDO("sqlite:$db_name");
		}
	} catch (PDOException $e) {
		print 'Exception : ' . $e->getMessage();
		error_log("(" . __LINE__ . ") " . $e->getMessage());
	}

//	error_log($table_name);
//	error_log($quote);
//	error_log($db_type);

	return array($db, $table_name, $quote, $db_type);
}


function dialog_act_tagger_save_extra_db($_post_data) {
	$table_name = $_post_data["table_name"];
	$db_record  = $_post_data["db_record"];

	try {
		//open the database
		list($db, $table_name, $quote, $db_type) = dialog_act_tagger_open_extra_db($_post_data);

		$idx = $db_record["_idx"];

		if( $idx >= 0 ) {
			$sql = "DELETE FROM ".$quote.$table_name.$quote." WHERE _idx=$idx";
			if ($db_type == "mysql" && $_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1) $sql .= " AND _domain='" . $_post_data["project_name"] . "'";

			$sth = $db->prepare($sql);
			if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

//			error_log($sql);

			$sth->execute();
		}

		// make insert sql
		$cols = array();
		$vals = array();
		foreach( $db_record as $k => $v ) {
			if( $k == "_idx" ) continue;

			$v = str_replace("\\", "", $v);
			$v = str_replace("'", "''", $v);

			array_push($cols, $quote.$k.$quote);
			array_push($vals, "'$v'");
		}

		// query
		array_push($cols, "_idx");

		if( $idx < 0 ) {
			array_push($vals, dialog_act_tagger_get_idx_extra_db($db, $quote, $table_name, $db_type, $_post_data));
		} else {
			array_push($vals, $idx);
		}

		if( $db_type == "mysql" && $_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1 && array_key_exists('_domain', $db_record) == false ) {
			array_push($cols, "_domain");
			array_push($vals, "'".$_post_data["project_name"]."'");
		}

		$sql = "INSERT INTO ".$quote.$table_name.$quote." (" . join(",", $cols) . ")";
		$sql .= " VALUES (".join(",", $vals).")";

		error_log($sql);

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();
		
		$ret = array("sql" => $sql, "msg" => "OK");

//		if( $table_name == "task_information" ) {
//	    	$bin_path = $_post_data["_DIALOG_SYSTEM_BIN_PATH_"];
//
//			$cmd = "sync;";
//	        $cmd .= "perl $bin_path/update_related_slot.pl -fn $db_name >>$db_name.log 2>&1;";
//			$cmd .= "sync";
//
//			exec($cmd, $ret["cmd_result"]);
//		}

		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}

function db_editor_read_slot_information($_post_data)
{
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	//open the database
	$db = new PDO("sqlite:$db_name");

	// get slot list
	$sql = "SELECT * FROM '" . $_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"] . "'";

	if (($sth = $db->prepare($sql))) {
		$sth->execute();
	} else {
		error_log($sql . ":" . join(",", $db->errorInfo()));
	}

	$result = $sth->fetchAll(PDO::FETCH_ASSOC);

	$ret = array();
	foreach ($result as $rows) {
		$slot_name = $rows["slot-name"];

		$ret[$slot_name] = $slot_name;
	}

	return $ret;
}

function dialog_act_tagger_search_extra_db($_post_data) {
	$keyword     = $_post_data["keyword"];
	$slot_type   = $_post_data["slot_type"];
	$table_name  = $_post_data["table_name"];
	$page_size   = $_post_data["page_size"];
	$page_number = $_post_data["page_number"];

	// append slot list to column list
	$slot_info = array();
	if( $_post_data["table_name"] == "task_information" ) {
		$slot_info = db_editor_read_slot_information($_post_data);
	}

	try {
		//open the database
		list($db, $table_name, $quote, $db_type) = dialog_act_tagger_open_extra_db($_post_data);

		// query
		$where = "";

		$keyword = str_replace("'", "''", $keyword);
		if( $slot_type != "" ) $where .= "\"$slot_type\" LIKE '%$keyword%'";

		if ($db_type == "mysql" && $_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1) {
			if ($where != "") $where .= " AND ";
			$where .= " _domain='" . $_post_data["project_name"] . "'";
		}

		if( $where != "" ) $where = "WHERE $where";

		$limit = "LIMIT ".sprintf("%d,%d", $page_size*$page_number, $page_size);

		$int_cast = "INTEGER";
		if ($db_type == "mysql") $int_cast = "SIGNED";

		$order = "ORDER BY CAST(_idx AS $int_cast)";
		$sql = "SELECT * FROM ".$quote.$table_name.$quote." $where $order $limit";

//		error_log($sql);

		// execute
		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

		// fetch
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$column_list = array();

		$data = array();
		foreach( $result as $rows ) {
			$item = array();
			if( count($column_list) == 0 ) {
				foreach( $rows as $k => $v ) {
					$column_list[$k] = $k;
				}
			}

			foreach( $rows as $k => $v ) {
				if( $v == "" ) continue;

				$item[$k] = $v;
			}

			if( count($item) > 0 ) {
				array_push($data, $item);
			}
		}

		// append slot info. to column list
		if( count($slot_info) > 0 ) {
			foreach ( $slot_info as $k => $v) {
				$column_list[$k] = $k;
			}
		}

		if (count($data) == 0) {
			$sql = "CREATE TABLE IF NOT EXISTS  " . $quote . $table_name . $quote . " (_idx text)";

			if ($db_type == "mysql" && $_post_data["_MYSQL_DB_TABLE_NAME_TYPE_"] == 1) {
				$sql = "CREATE TABLE IF NOT EXISTS  " . $quote . $table_name . $quote . " (_idx text, _domain text)";
			}

			$sth = $db->prepare($sql);
			if (!$sth) error_log($sql . ":" . join(",", $db->errorInfo()));

			$sth->execute();

			array_push($data, array("_idx" => "-1", "dummy" => "dummy"));
		}

		$ret = array("column_list" => $column_list, "data" => $data);

		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}


?>