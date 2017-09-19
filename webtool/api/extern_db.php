<?

// echo "<node>";
// echo "<Weather.Gu><count>23</count></Weather.Gu>";
// echo "<Weather.city>서울시</Weather.city>";
// echo "<Weather.date>오늘</Weather.date>";
// echo "<Weather.info><count>123</count></Weather.info>";
// echo "</node>";

// echo "<node>";
// echo "<Weather.Gu><count>123</count></Weather.Gu>";
// echo "<Weather.city>서울시</Weather.city>";
// echo "<Weather.date><count>456</count></Weather.date>";
// echo "<Weather.info><count>789</count></Weather.info>";
// echo "</node>";

// echo "<node>";
// echo "<Weather.Gu><count>1</count></Weather.Gu>";
// echo "<Weather.city><count>2</count></Weather.city>";
// echo "<Weather.date><count>3</count></Weather.date>";
// echo "<Weather.info><count>4</count></Weather.info>";
// echo "</node>\n";

// exit(0);

my_main($_POST);


function my_main($_post_data) {
	header('Content-Type: text/html; charset=utf-8');

	// xml_query
	$query_list = $_post_data["xml_query"];

	$result = array();
	while( ($xml_query = StripText2($query_list, "<query>", "</query>")) != "" ) {
		query_db($xml_query, $_post_data, $result);
	}
}


function query_db($xml_query, $_post_data, &$result)
{
	$_DEBUG = 1;

	$fp_debug = NULL;
	if( $_DEBUG ) $fp_debug = fopen('/webtool/log/debug.txt', 'a');

	if( $fp_debug ) {
		fwrite($fp_debug, print_r($_post_data, true));
		fflush($fp_debug);
	}

	// $request = StripText2($xml_query, "<request>", "</request>");
	// if( !isset($request) || $request == "" ) exit();

	$domain = StripText2($xml_query, "<domain>", "</domain>");
	$condition = StripText2($xml_query, "<condition>", "</condition>");
	$request_slot = StripText2($xml_query, "<request_slot>", "</request_slot>");

	$db_name = $domain."_DB.sqlite3";

	$ask = array();
	if( $request_slot != "" ) {
		foreach( split(",", $request_slot) as $slot ) {
			$slot = trim($slot);
			$slot = trim($slot, "\"");

			if( $slot == "*" ) {
				array_push($ask, $slot);
			} else {
				array_push($ask, "\"$slot\"");
			}
		}
	} else {
		array_push($ask, "*");
	}

	$sql = "SELECT ".join(",", $ask)." FROM task_information";

	// parse condition xml format
	$ask = array();
	$extra_where = "";
	$where = parse_condition($condition, $ask, $extra_where);
	
	if( count($where) > 0 ) $sql .= " WHERE ".join(" AND ", $where);	
	if( $extra_where != "" ) $sql .= " ".$extra_where;

	if( $fp_debug ) {
		fwrite($fp_debug, "# domain: ".$domain."\n");
		fwrite($fp_debug, "# condition: ".$condition."\n");
		fwrite($fp_debug, "# request_slot: ".$request_slot."\n");
		fwrite($fp_debug, "# ask: ".join(",", $ask)."\n");
		fwrite($fp_debug, "# sql:".$sql."\n");
		fflush($fp_debug);
	}

	// db query
	try {
		//open the database
		//$full_db_name = "/webtool/www_data/project/$domain/data/$db_name";
				
		$full_db_name = "/webtool/www_data/$db_name"; // tghong - unresolved
		$db = new PDO("sqlite:$full_db_name");

		// query
		$sth = $db->prepare($sql);
		if( !$sth ) error_log($sql .":". join(",", $db->errorInfo()));

		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		$node_count = 0;
		$count = array();

		$buf = "";
		foreach( $result as $rows ) {
			$node = "";
			foreach( $rows as $k => $v ) {
				$k = trim($k, '"');
				$v = trim($v, '"');

				if( $v != "" ) $node .= "<$k>$v</$k>";

				if( !isset($count[$k]) ) $count[$k] = 0;
				$count[$k]++;
			}

			$node_count++;
			if( $node != "" ) $buf .= "<node>$node</node>\n";

			if( $node_count > 20 ) {
				break;
			}
		}

		if( $fp_debug ) {
			fwrite($fp_debug, "node_count: $node_count\n");
			fwrite($fp_debug, "ORG RET:[$buf]\n");
			fflush($fp_debug);
		}

		// if( $node_count > 3 ) {
		// 	$buf = "";

		// 	foreach( $count as $k => $v ) {
		// 		$buf .= "<$k> <count> $v </count> </$k>";
		// 	}

		// 	foreach( $ask as $k => $v ) {
		// 		$buf .= "<$k> $v </$k>";
		// 	}

		// 	// $buf .= "<_COUNT_>".implode(",", array_keys($count))."</_COUNT_>";

		// 	$buf = "<node>$buf</node>";
		// }

		// $ret = "<etri_dialog>$buf</etri_dialog>\n";		
		// echo $ret;
		
		echo $buf."\n";

		if( $fp_debug ) {
			fwrite($fp_debug, $full_db_name."\n");
			fwrite($fp_debug, "node_count: $node_count\n");
			fwrite($fp_debug, "RET:[$buf]\n");
			fwrite($fp_debug, "## END ############################################\n");
			fflush($fp_debug);
		}

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}

	if( $fp_debug ) fclose($fp_debug);	
}


function StripText2(&$STR, $L_TAG, $R_TAG)
{
	if( strpos($STR, $L_TAG) === false ) return "";
	if( strpos($STR, $R_TAG) === false ) return "";
	
	$st = strpos($STR, $L_TAG) + strlen($L_TAG);
    
    $ret = substr($STR, $st, strpos($STR, $R_TAG) - $st);
    
    $front = substr($STR, 0, strpos($STR, $L_TAG));    
    $next  = substr($STR, strpos($STR, $R_TAG) + strlen($R_TAG), strlen($STR));
    
    $STR = $front.$next;
	
	return $ret;
}


function xml2array($xml) {
	$ret = Array();
	
	$limit = 1000;
	
	$offset = 0;
	do {
		$st = strpos($xml, "<", $offset);
		if( $st === false ) break;
		
		$en = strpos($xml, ">", $st+1);
		if( $en === false ) {
			$offset = $st + 1;
			continue;	
		}
		
		$tag = substr($xml, $st+1, $en-$st-1);
		
		$st = strpos($xml, "<$tag>", $offset);
		if( $st === false ) break;
		
		$en = strpos($xml, "</$tag>", $st+1);
		if( $en === false ) {
			$offset = $st + 1;
			continue;	
		}
	
		$val = substr($xml, $st+strlen("<$tag>"), $en-$st-strlen("<$tag>"));
	
		$ret[$tag] = $val;
	
		$xml = substr_replace($xml, "", $st, $en-$st+strlen("</$tag>"));
	} while( $limit-- < 0 || $tag != "" );
	
	return $ret;
}


function SplitKeyValueCondition($item, $op, &$k, &$v, &$cond)
{
	if( strpos($item, $op) === false ) {
		return false;
	}
	
	list($k, $v) = explode($op, $item, 2);
	
	$k = trim($k, "\"");
	$v = trim($v, "\"");
	$cond = $op;

	return true;	
}


function parse_condition($str_condition, &$ask, &$extra_where)
{
	$ret = array();
	
	// a="v", b="v2", ...
	foreach( explode(",", $str_condition) as $item ) {
		$k = $v = $cond = "";
		
		if( SplitKeyValueCondition($item, "~=", $k, $v, $cond) ) {
		} else if( SplitKeyValueCondition($item, "!=", $k, $v, $cond) ) {
		} else if( SplitKeyValueCondition($item, ">=", $k, $v, $cond) ) {
		} else if( SplitKeyValueCondition($item, "<=", $k, $v, $cond) ) {
		} else if( SplitKeyValueCondition($item, "<<", $k, $v, $cond) ) { // insert
		} else if( SplitKeyValueCondition($item, ">>", $k, $v, $cond) ) { // delete
		} else if( SplitKeyValueCondition($item, ">", $k, $v, $cond) ) {
		} else if( SplitKeyValueCondition($item, "<", $k, $v, $cond) ) {
		} else if( SplitKeyValueCondition($item, "=", $k, $v, $cond) ) {
		}
		
		$ask[$k] = $v;
		if( $v == "min" || $v == "max" ) {
			array_push($ret, "\"$k\"!=\"NULL\"");
			
			if( $v == "min" ) {
				$extra_where = "ORDER BY \"$k\" LIMIT 1";
			} else {
				$extra_where = "ORDER BY \"$k\" DESC LIMIT 1";
			}
		} else {
			array_push($ret, "\"$k\"$cond\"$v\"");
		}
	}	
	
	return $ret;
}


?>