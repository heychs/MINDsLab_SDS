<?
//------------------------------------------------------------------------------
function get_slot_structure_read_class_info(&$db, $_post_data) { 
	$sql = "SELECT * ";
	$sql .= " FROM '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
	$sql .= " WHERE \"class-name\"!=''";
	
	if( ($sth = $db->prepare($sql)) ) {
		$sth->execute();
	} else {
		error_log($sql .":". join(",", $db->errorInfo()));
	}
	
	$result = $sth->fetchAll(PDO::FETCH_ASSOC);
	
	$class_info = array();
	foreach( $result as $rows ) {
		$class_name = $rows["class-name"];
		$slots  = $rows["class-slots"];
	
		$slots = str_replace("\r", "", $slots);
		 
		foreach( explode("\n", $slots) as $k => $v ) {
			$v = trim($v);
			if( $v == "" ) continue;
	
			$class_info[$v] = $class_name;
		}
	}
	
	return $class_info;
}
//------------------------------------------------------------------------------
function get_slot_structure_slot_info(&$db, $_post_data, $class_info) { 
	// get slot list
	$sql = "SELECT *";
	$sql .= " FROM '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
	$sql .= " ORDER BY 'slot-name'";
	
	if( ($sth = $db->prepare($sql)) ) {
		$sth->execute();
	} else {
		error_log($sql .":". join(",", $db->errorInfo()));
	}
	
	$result = $sth->fetchAll(PDO::FETCH_ASSOC);
	
	$ret = array();
	foreach( $result as $rows ) {
		$slot_name = $rows["slot-name"];
		 
		$class_name = ( isset($class_info[$slot_name]) ) ? $class_info[$slot_name] : $rows["class-name"];
		if( $rows["class-name"] == "" ) {
			$rows["class-name"] = $class_name;
		}
	
		if( isset($rows["slot-value_define"]) ) {
			$rows["slot-value_define"] = html2a($rows["slot-value_define"]);
		}
		
		if( $class_name == "" ) $class_name = "unknown";
	
		$ret[$class_name][$slot_name] = $rows;
	}
	
	return $ret;
}
//------------------------------------------------------------------------------
function get_slot_structure($_post_data) { 
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		$class_info = get_slot_structure_read_class_info($db, $_post_data);
		$slot_info = get_slot_structure_slot_info($db, $_post_data, $class_info);

        echo json_encode($slot_info);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function slot_structure_save($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	$slot_data = $_post_data["slot_data"];

	$class_idx = $slot_data["class-_idx"];
	$slot_idx  = $slot_data["slot-_idx"];
	$slot_name = $slot_data["slot-name"];
	$slot_type = $slot_data["slot-type"];

	$prev_slot_name = "";
	if( array_key_exists("prev_slot_name", $slot_data) ) $prev_slot_name = $slot_data["prev_slot_name"];
	
	if( $slot_type == "int" ) {
		$slot_type = "INT";
	} else if( $slot_type == "float" || $slot_type == "double" ) {
		$slot_type = "REAL";
	} else {
		$slot_type = "TEXT";
	}
	
	$ret = array("msg" => "OK");
		
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// delete prev record
		$sql = "DELETE FROM '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
		$sql .= " WHERE \"class-_idx\"='$class_idx' AND \"slot-_idx\"='$slot_idx'";

		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			error_log($sql .":". join(",", $db->errorInfo()));
		}

		// update slot name list => class's slots
		slot_structure_update_class_slot_list($db, $_post_data);

		// make query
		$cols = $vals = array();
		foreach( $slot_data as $k => $v ) {
			$k = trim($k);
				
			if( is_array($v) ) {
				$v = array2xml2($v);
			} else {
				$v = trim($v);
			}
				
			$v = str_replace("'", "''", $v);
			$v = "'$v'";

			if( $k == "class-_idx" && $class_idx < 0 ) {
				$v = "(SELECT MAX(CAST(\"class-_idx\" AS INTEGER))+1 FROM '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."')";
			}

			if( $k == "slot-_idx" && $slot_idx < 0 ) {
				$v = "(SELECT MAX(CAST(\"slot-_idx\" AS INTEGER))+1 FROM '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."')";
			}

			array_push($cols, "\"$k\"");
			array_push($vals, $v);
		}

		$sql = "INSERT INTO '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
		$sql .= " (".join(",", $cols).") VALUES (".join(",", $vals).")";

		$ret["sql"] = $sql;

		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			error_log($sql .":". join(",", $db->errorInfo()));
		}

		// update slot name list => class's slots
		slot_structure_update_class_slot_list($db, $_post_data);
		
		$ret["ret_cmd"] = task_manager_run_xml2db($_post_data, $_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"], "slot");

		echo json_encode($ret);

		// add slot to task_information
		list($db_task_information, $table_name, $quote, $db_type) = dialog_act_tagger_open_extra_db($_post_data);

		$cols = get_column_list($db_task_information, $table_name);

//		error_log($slot_name);

		if( $slot_name != "" && !isset($cols[$slot_name]) ) {

			$sql = "ALTER TABLE $quote".$table_name."$quote";
			$sql .= " ADD COLUMN $quote$slot_name$quote $slot_type";

//			error_log($sql);

			if( ($sth = $db_task_information->prepare($sql)) ) {
				$sth->execute();
			} else {
				error_log($sql .":". join(",", $db_task_information->errorInfo()));
			}
		}

		// update slot freq		
	    $bin_path = $_post_data["_DIALOG_SYSTEM_BIN_PATH_"];
		
		$cmd = "";
	    $cmd .= "sync";
	    $cmd .= "perl $bin_path/update_slot_freq.pl -quite -fn $db_name >$db_name.log 2>&1;";
	    $cmd .= "sync";
	    
	    system($cmd, $ret["ret_cmd"]);	

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function slot_structure_update_class_slot_list($db, $_post_data) {
	$class_info = get_slot_structure_read_class_info($db, $_post_data);
	$slot_info = get_slot_structure_slot_info($db, $_post_data, $class_info);
	
	foreach( $slot_info as $class_name => $slot_list ) {
		$str_slots = join("\n", array_keys($slot_list));

		// clear slot
		$sql = "UPDATE '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
		$sql .= " SET \"slot-name\"='',";
		$sql .= "     \"slot-source\"='',";
		$sql .= "     \"slot-preceding_slots\"='',";
		$sql .= "     \"slot-_idx\"='',";
		$sql .= "     \"slot-_position\"='',";
		$sql .= "     \"slot-description\"='',";
		$sql .= "     \"slot-type\"='',";
		$sql .= "     \"slot-value_define\"=''";
		$sql .= " WHERE \"slot-name\" = ''";
		
		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			error_log($sql .":". join(",", $db->errorInfo()));
		}

		// clear class
		$sql = "UPDATE '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
		$sql .= " SET \"class-_position\"='',";
		$sql .= "     \"class-name\"='',";
		$sql .= "     \"class-source\"='',";
		$sql .= "     \"class-description\"='',";
		$sql .= "     \"class-_idx\"='',";
		$sql .= "     \"class-key\"='',";
		$sql .= "     \"class-slots\"=''";
		$sql .= " WHERE \"slot-name\" != ''";
		
		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			error_log($sql .":". join(",", $db->errorInfo()));
		}
		
		// update class's slots
		$sql = "UPDATE '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
		$sql .= " SET \"class-slots\"='".trim($str_slots)."'";
		$sql .= " WHERE \"class-name\" = '$class_name' AND \"slot-name\" = ''";
		
		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			error_log($sql .":". join(",", $db->errorInfo()));
		}
	}
}
//------------------------------------------------------------------------------
function slot_structure_delete($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	$slot_data = $_post_data["slot_data"];

	$class_idx = $slot_data["class-_idx"];
	$slot_idx  = $slot_data["slot-_idx"];
	
	$slot_name = $slot_data["slot-name"];
	 
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		$sql = "DELETE FROM '".$_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]."'";
		$sql .= " WHERE \"class-_idx\"='$class_idx' AND \"slot-_idx\"='$slot_idx'";

		$ret = array("msg" => "OK", "sql" => $sql);

		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			$ret["error_msg"] .= join(",", $db->errorInfo())."\n";
			error_log($sql .":". join(",", $db->errorInfo()));
		}

		$ret["ret_cmd"] = task_manager_run_xml2db($_post_data, $_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"], "slot");

		$class_info = get_slot_structure_read_class_info($db, $_post_data);
		$slot_info = get_slot_structure_slot_info($db, $_post_data, $class_info);

		change_task_information_column_name($_post_data, $slot_info, $slot_name, "", $ret);

		echo json_encode($ret);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function change_task_information_column_name($_post_data, $slot_info, $slot_name, $new_slot_name, &$ret) {		// alter table task_information
	list($db, $table_name, $quote, $db_type) = dialog_act_tagger_open_extra_db($_post_data);

	$cols = get_column_list($db, $table_name);

	if( $slot_name != "" && isset($cols[$slot_name]) ) {
		unset($cols[$slot_name]);

		$new_column_list = array("_idx");
		$new_table_column = array("'_idx' INT");

		foreach( $slot_info as $class_name => $slot_list ) {
			foreach( $slot_list as $name => $v ) {
				if( $name == "" ) continue;

				$slot_type = $v["slot-type"];
				if( $slot_type == "int" ) {
					$slot_type = "INT";
				} else if( $slot_type == "float" || $slot_type == "double" ) {
					$slot_type = "REAL";
				} else {
					$slot_type = "TEXT";
				}

				if( $name == $slot_name ) continue;

				if( isset($cols[$name]) ) {
					array_push($new_column_list, "$name");
				} else if( $name == $new_slot_name ) {
					array_push($new_column_list, "$slot_name AS $new_slot_name");
				}

				array_push($new_table_column, "$quote$name$quote $slot_type");
			}
		}

		// rename
		$sql = "ALTER TABLE $quote".$_post_data["_DB_TABLE_NAME_TASK_INFORMATION_"]."$quote";
		$sql .= " RENAME TO ".$quote."tmp_".$_post_data["_DB_TABLE_NAME_TASK_INFORMATION_"]."$quote";

		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			$ret["error_msg"] .= join(",", $db->errorInfo())."\n";
			error_log($sql .":". join(",", $db->errorInfo()));
		}

		// create new table
		$sql = "CREATE TABLE $quote".$_post_data["_DB_TABLE_NAME_TASK_INFORMATION_"]."$quote";
		$sql .= " (".join(", ", $new_table_column).")";

		$ret["create_table_sql"] = $sql;

		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			$ret["error_msg"] .= join(",", $db->errorInfo())."\n";
			error_log($sql .":". join(",", $db->errorInfo()));
		}

		// insert data
		$sql = "INSERT INTO $quote".$_post_data["_DB_TABLE_NAME_TASK_INFORMATION_"]."$quote";
		$sql .= " (".join(", ", $new_column_list).")";
		$sql .= " SELECT ".join(", ", $new_column_list);
		$sql .= " FROM $quote"."tmp_".$_post_data["_DB_TABLE_NAME_TASK_INFORMATION_"]."$quote";

		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
//			$ret["error_msg"] .= join(",", $db->errorInfo())."\n";
			error_log($sql .":". join(",", $db->errorInfo()));
		}

		// drop tmp table
		$sql = "DROP TABLE $quote"."tmp_".$_post_data["_DB_TABLE_NAME_TASK_INFORMATION_"]."$quote";

		if( ($sth = $db->prepare($sql)) ) {
			$sth->execute();
		} else {
			$ret["error_msg"] .= join(",", $db->errorInfo())."\n";
			error_log($sql .":". join(",", $db->errorInfo()));
		}
	}
}
//------------------------------------------------------------------------------


?>