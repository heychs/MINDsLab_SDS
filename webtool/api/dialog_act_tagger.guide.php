<?
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_get_task_info($_post_data) {
	$fn = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/corpus.json";
	
    $fp = fopen($fn, "rb"); 

	$corpus = "";
	if( $fp ) {
	    while( ($buffer = fgets($fp, 4096)) !== false ) {
	        $corpus .= $buffer;
	    }
	    if( !feof($fp) ) {
	        echo "Error: unexpected fgets() fail\n";
	    }
	    fclose($fp);
	}
	
	$data = json_decode($corpus, true);
	
	$transition_kb = $data["transition_kb"];
	
	$cnt=0;
	$task_info = array();
	
    foreach ($transition_kb as $i => $block_info ) {
        $block_id = $block_info["block_id"];
        $to_block_id = $block_info["to_block_id"];
        
		$item = array(
        	"task_id" => "task_".$i,
        	"task-task_name" => $block_id,
        	"task-next_task" => array(
        		"next_task_item" => array(
        			"task_name" => $to_block_id
				)
			)
		);
		
		// empty position
		if( !isset($item["task-_position"]["top"]) ) {
			$top = $i;
			$left = 3;
			
			if( $i > 3 ) {
				$top = $i - 4;
				$left = 150;					
			}
			
			$item["task-_position"]["top"] = 3 + $top * 43;
			$item["task-_position"]["left"] = $left;
			$item["task-_position"]["width"] = 120;
		} 

        array_push($task_info, $item);
    }
	
	echo json_encode($task_info);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_get_task_detail($_post_data) {
	$db_name = $_post_data["_DIALOG_SYSTEM_DATA_PATH_"]."/".$_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"];

	if( !file_exists($db_name) ) {
		error_log("File Not Exists: ".$db_name);
		echo json_encode(array("error"));
		return;
	}
	
	$task_name = $_post_data["task_name"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// query utter list
		$sql = "SELECT * FROM '".$_post_data["_DB_TABLE_NAME_GUIDE_MAP_"]."' WHERE task_name='$task_name'";

		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		// $ret = array();
		// foreach( $result as $rows ) {
// 			
		// }

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