<?
//------------------------------------------------------------------------------
function translate_sentence($_post_data) {
	$lang_type = $_post_data["lang_type"];
	
	$src   	= $_post_data["src"];
	$ref   	= $_post_data["ref"];

	$engine = $_post_data["_ENGINE_PATH_"]."/".$_post_data["engine"];
	$dictionary = $_post_data["dic_path"]."/".$_post_data["dictionary"];

	// remove html tag
	$src = preg_replace('/<[^>]+?>/', '', $src);

	$act_page = preg_replace("/^.+\//", "", $_SERVER["PHP_SELF"]);

	$request = $lang_type."_mt";

	$PACKET = array();
	$PACKET["SERV_IP"]   = $_post_data["_DEAMON_IP_"];
	$PACKET["SERV_PORT"] = $_post_data["_DEAMON_PORT_"];

	$PACKET["TO_DO"] = $request;
    
    $cmd = "STDIN_MID_ONE_LINE";
//    if( $lang_type == "ke" ) $cmd = "STDIN_WORKBENCH_XML";

	$PACKET["TX"] = array2xml(Array(
		"TYPE"=>"xml", 
		"SRC"=>$src, 
		"ENGINE"=>$engine, 
		"DIC"=>$dictionary, 
		"CMD"=>$cmd, 
		"CONF"=>$engine.".conf", 
		"ENCODING"=>"utf8", 
		"ENCODING_OUT"=>"utf8", 
		"REQUEST_PAGE"=>$act_page, 
		"ARGS"=>"-encode utf8", 
	));
    
    $ret = array();
//    $ret = array_merge($ret, $_post_data);
	
	$rx = SendPacket2PerlServ($PACKET);
	if( $rx == "Connection refused" || $rx == "연결이 거부됨" ) {
		$cmd = get_daemon_cmd($act_page, $engine, $dictionary, $request
					, $PACKET["SERV_IP"], $PACKET["SERV_PORT"], $cmd, "utf8"
					, $_post_data["_DEAMON_BIN_PATH_"], $_post_data["_DEAMON_LOG_PATH_"], "");
		if( $cmd != "" ) {
			system($cmd);
            
            $ret["_shell_cmd_"] = $cmd;

			error_log("(".__LINE__.") ".$cmd);
			sleep(2);

			$rx = SendPacket2PerlServ($PACKET);
		}
	}
    
    $ret["_raw_rx_"] = $rx;

    // parse result
    $ret = array_merge($ret, xml2array($rx));
    
    return $ret;
}
//------------------------------------------------------------------------------
function translate_sent_html($_post_data) {
	$data = translate_sent($_post_data);
	
	$lang_type = $_post_data["lang_type"];
	
	$on_mouse = "onmouseover=\"this.style.cursor='pointer'\"";

	// conv. space to &nbsp; and tab to &nbsp;
	foreach( $data as $k => $v ) {	
		$v = str_replace("<TAB>", "&nbsp;&nbsp;&nbsp;&nbsp;", $v);
		$v = str_replace("    ", "&nbsp;&nbsp;&nbsp;&nbsp;", $v);
		$v = str_replace("<", "&lt;", $v);
		$v = str_replace(">", "&gt;", $v);

		$v = str_replace("&lt;BR&gt;", "<BR>", $v);
		
		$v = preg_replace("/&lt;(dic .+?)&gt;/", "<$1>", $v);
		$v = str_replace("&lt;/dic&gt;", "</dic>", $v);

		$v = preg_replace("/&lt;(dic.+?)&gt;/", "<$1>", $v);

		$data[$k] = $v;
	}
	
	$mid_result_opt = array();
	if( $_post_data["mid_result_opt"] == "" ) $_post_data["mid_result_opt"] = "TRG,MA_OUT,PARSE_OUT,TF_OUT,KTREE_OUT";
	foreach( split(",", $_post_data["mid_result_opt"]) as $item ) {
		$mid_result_opt[$item] = 1;
	}

	$uid   = $_post_data["uid"];

	if( $lang_type == "ck" ) {	
        $ret = array();
        
        array_push($ret, array("title" => "Result", "content" => $data["trg"]) );
        array_push($ret, array("title" => "MA",     "content" => $data["ma_out"], "tag" => "MA" ) );
        array_push($ret, array("title" => "PARSE",  "content" => $data["parse_out"]) );
        array_push($ret, array("title" => "TF",     "content" => $data["tf_out"]) );
        array_push($ret, array("title" => "KTREE",  "content" => $data["ktree_out"]) );
        array_push($ret, array("title" => "TM",     "content" => $data["tm_out"]) );

        echo json_encode($ret);
	}

	if( $lang_type == "ek" ) {
       $ret = array();

        array_push($ret, array("title" => "Result",     "content" => $data["trg"]) );
        array_push($ret, array("title" => "MA",         "content" => $data["ma_out"], "tag" => "MA" ) );
        array_push($ret, array("title" => "Chunking",   "content" => $data["Chunking Result"]) );
        array_push($ret, array("title" => "Ksp",        "content" => $data["Ksp Result"]) );
        array_push($ret, array("title" => "Parsing",    "content" => $data["ParsingResultWithTag"]) );
        array_push($ret, array("title" => "Col",        "content" => $data["ColWithTag"]) );

        echo json_encode($ret);
	}

	if( $lang_type == "ke" || $lang_type == "kc" ) {	
        $ret = array();
        
        array_push($ret, array("title" => "Result",     "content" => $data["trg"]) );
        array_push($ret, array("title" => "MA",         "content" => $data["ma_out"], "tag" => "MA" ) );
        array_push($ret, array("title" => "Post Kma",   "content" => $data["PostKmaResult"]) );
        array_push($ret, array("title" => "Prev Ksp",   "content" => $data["PrevKspResult"]) );
        array_push($ret, array("title" => "Ksp",        "content" => $data["KspResult"]) );
        array_push($ret, array("title" => "PS Link",    "content" => $data["PSLinkResult"]) );
        array_push($ret, array("title" => "Part Trans", "content" => $data["PartTransResult"]) );
        array_push($ret, array("title" => "Part Trans Tree", "content" => $data["PartTransTreeResult"]) );
        array_push($ret, array("title" => "Refine",     "content" => $data["RefineResult"]) );
        array_push($ret, array("title" => "Morph Gen",  "content" => $data["MorphGenResult"]) );
        array_push($ret, array("title" => "Sent Pattern", "content" => $data["SentPatternResult"]) );

        echo json_encode($ret);
	}
}
//------------------------------------------------------------------------------
function get_report_box($_post_data) {
	// TO_DO
	// workbench_log -> tag 에 tab_bitext 입력
	// 
	
	$items = Array(
		"src_err" 			 => Array("원문", 		"원문 오류", 		""),
		"pos_err" 			 => Array("품사태깅", 	"품사 태깅 오류",	""),
		"seg_err" 			 => Array("단어분리", 	"단어 분리 오류",	""),
		"unknown_err"		 => Array("미등록어", 	"미등록어 오류",	""),
		"ctf_align_word_err" => Array("대역어", 	"대역어 오류",		""),
		"ctf_pattern_err" 	 => Array("변환패턴", 	"변환 패턴 오류",	""),
		"parse_err" 		 => Array("구문 분석", 	"구문 분석",		""),
		"gen_err" 			 => Array("생성", 		"생성",				""),
		"etc_err" 			 => Array("기타", 		"기타",				""),
		"bitext" 			 => Array("Bitext", 	"Bitext",			""),
		"tm" 				 => Array("TM", 		"TM",				""),
	);

	if( isset($_post_data["src"]) && $_post_data["src"] != "" ) {
		try {
			$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_WORKBENCH_LOG_DB_FILE_NAME_"];
		
			$_post_data["src"] = preg_replace('/<[^>]+?>/', '', $_post_data["src"]);
		
			$sql = "SELECT engine,dic,tag,user_email,src,trg,cma,err_type,comment,m_date ";
			$sql .= " FROM ".$_post_data["_WORKBENCH_LOG_TABLE_NAME_"]." ";
			$sql .= " WHERE src='".conv_sql_value($_post_data["src"])."' ";
			$sql .= " ORDER BY m_date";
			
			$db = new PDO("sqlite:$db_name");
		
			$sth = $db->prepare($sql);
			$sth->execute();
			
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);	
			foreach( $result as $rows ) {
				$new_items = Array();
				
				foreach( $items as $err_type => $data ) {
					$new_items[$err_type] = $data;
					
					list($caption, $value, $checked) = $data;
					
					if( strpos($rows["err_type"], $value) !== false ) {
						$new_items[$err_type][2] = "checked='checked'";
					}
				}
	
				echo display_one_report_box($_post_data["uid"], $rows, $new_items, $_post_data["result_id"]);
			}
			$db = NULL;
		} catch(PDOException $e) {
			print 'Exception : '.$e->getMessage();
			error_log("(".__LINE__.") ".$e->getMessage());
		}
	}

	$rows = Array("comment" => "", "engine" => "", "dic" => "", "tag" => "", "user_email" => "", "src" => "", "trg" => "", "cma" => "", "err_type" => "", "comment" => "", "m_date" => "");
	echo display_one_report_box($_post_data["uid"], $rows, $items, $_post_data["result_id"]);
}
//------------------------------------------------------------------------------
function display_one_report_box($uid, $rows, $items, $result_id) {
	$sub_uid = uniqid();
	
	$on_mouse  = "onmouseover=\"this.style.cursor='pointer'\"";
	$chk_style = "onclick=\"this.parentNode.childNodes[0].checked=!this.parentNode.childNodes[0].checked;\" $on_mouse";
	
	$buf = "";

	$buf .= "<table style='width:100%; border: 1px solid black;'>";
	$buf .= "	<tr>";
	$buf .= "		<td style='width:100px; text-align:center;'>";
	$buf .= "			REPORT";
	$buf .= "		</td>";
	$buf .= "		<td>";
	
	$buf .= "<table style='width:100%; border: 0px solid black;' border=1>";
	$buf .= "	<tr>";
	$buf .= "		<td style='font-size:80%;' colspan=2>";
	$buf .= "			&nbsp;";

	foreach( $items as $err_type => $data ) {
		if( $err_type == "/" ) {
			$buf .= "/";
			continue;
		}
		
		list($caption, $value, $checked) = $data;

		$buf .= "		<span title='$value'>";
		$buf .= "			<input type=checkbox id='".$sub_uid."_type_".$err_type."' value='$value' $checked>";
		$buf .= "			<span $chk_style>$caption</span>";
		$buf .= "		</span>";
	}	
	
	$buf .= "		</td>";
	$buf .= "	</tr>";
	$buf .= "</table>";

	if( $rows["user_email"] != "" ) {
		$buf .= "<table style='width:100%; border: 0px solid black;' border=1>";
		$buf .= "	<tr>";
		$buf .= "		<td style=''>";
		$buf .= "			<span id='".$sub_uid."_user_email'>".$rows["user_email"]."</span> ";
		$buf .= "			<span id='".$sub_uid."_m_date'>".$rows["m_date"]."</span> ";
		$buf .= "			<span id='".$sub_uid."_err_type'>".$rows["err_type"]."</span> ";
		$buf .= "			<span id='".$sub_uid."_tag'>".$rows["tag"]."</span> ";
		$buf .= "			<span id='".$sub_uid."_src' style='display:none;'>".$rows["src"]."</span> ";
		$buf .= "			<img src='/imgs/delete.png' $on_mouse title='delete' onclick=\"delete_report(this, '$uid', '".$sub_uid."_user_email', '".$sub_uid."_m_date', '$result_id')\">";
		$buf .= "		</td>";
		$buf .= "	</tr>";
		$buf .= "</table>";
	}

	$buf .= "<table style='width:100%; border: 0px solid black;' border=1>";
	$buf .= "	<tr>";
	$buf .= "		<td style='width:10px; text-align:center;'>";
	$buf .= "			<img src='/imgs/save.png' $on_mouse title='Save' onclick=\"save_report(this, '$uid', '$sub_uid', '$result_id')\">";
	$buf .= "		</td>";
	$buf .= "		<td style='width:100%'>";
	$buf .= "			<textarea id='".$sub_uid."_comment' type=text style='width:100%'>".$rows["comment"]."</textarea>";
	$buf .= "			<span style='display:none' id='".$sub_uid."_all_err_list'>src_err,pos_err,seg_err,unknown_err,ctf_align_word_err,ctf_pattern_err,parse_err,gen_err,etc_err,bitext,tm</span>";
	$buf .= "		</td>";
	$buf .= "	</tr>";
	$buf .= "</table>";

	$buf .= "		</td>";
	$buf .= "	</tr>";
	$buf .= "</table>";

	$buf .= "<div id='".$sub_uid."_debug' style='display:none;'></div>";
	
	return $buf;
}
//------------------------------------------------------------------------------
function delete_report($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_WORKBENCH_LOG_DB_FILE_NAME_"];
	
	if( isset($_post_data["user_email"]) ) $_post_data["user_email"] = preg_replace('/<[^>]+?>/', '', $_post_data["user_email"]);
	if( isset($_post_data["m_date"]) ) $_post_data["m_date"] = preg_replace('/<[^>]+?>/', '', $_post_data["m_date"]);	

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// insert data
		$sql = "DELETE FROM ".$_post_data["_WORKBENCH_LOG_TABLE_NAME_"]." ";
		$sql .= " WHERE user_email = '".$_post_data["user_email"]."' AND m_date = '".$_post_data["m_date"]."'";

		$st = $db->prepare($sql);
		$st->execute();

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
	
	echo "OK";
}
//------------------------------------------------------------------------------
function save_report($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_WORKBENCH_LOG_DB_FILE_NAME_"];
	
	if( isset($_post_data["src"]) ) $_post_data["src"] = preg_replace('/<[^>]+?>/', '', $_post_data["src"]);
	if( isset($_post_data["trg"]) ) $_post_data["trg"] = preg_replace('/<[^>]+?>/', '', $_post_data["trg"]);	
	if( isset($_post_data["cma"]) ) $_post_data["cma"] = preg_replace('/<[^>]+?>/', '', $_post_data["cma"]);

	$_post_data["engine"] 	= conv_sql_value($_post_data["engine"]);
	$_post_data["dic"] 		= conv_sql_value($_post_data["dic"]);
	$_post_data["src"] 		= conv_sql_value($_post_data["src"]);
	$_post_data["trg"] 		= conv_sql_value($_post_data["trg"]);
	$_post_data["tag"] 		= conv_sql_value($_post_data["tag"]);
	$_post_data["ma"] 		= conv_sql_value($_post_data["ma_out"]);
	$_post_data["comment"] 	= conv_sql_value($_post_data["comment"]);
	$_post_data["err_type"] = conv_sql_value($_post_data["err_type"]);
	$_post_data["lang_type"] = conv_sql_value($_post_data["lang_type"]);
	$_post_data["user_email"] = conv_sql_value($_post_data["user_email"]);

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
		$sql = "CREATE TABLE IF NOT EXISTS ".$_post_data["_WORKBENCH_LOG_TABLE_NAME_"];
		$sql .= " (sent_no INTEGER, engine TEXT, dic TEXT, src TEXT, trg TEXT, cma TEXT, comment TEXT, user_email TEXT, tag TEXT, err_type TEXT, lang_type TEXT, m_date DATE UNIQUE DEFAULT (datetime('now','localtime')))";

		$st = $db->prepare($sql);
		$st->execute();

		// insert data
		$sql = "INSERT INTO ".$_post_data["_WORKBENCH_LOG_TABLE_NAME_"]." (engine,dic,src,trg,cma,comment,user_email,tag,err_type,lang_type) ";
		$sql .= " VALUES ('".$_post_data["engine"]."', '".$_post_data["dic"]."', '".$_post_data["src"]."', '".$_post_data["trg"]."', '".$_post_data["ma"]."', '".$_post_data["comment"]."', '".$_post_data["user_email"]."', '".$_post_data["tag"]."', '".$_post_data["err_type"]."', '".$_post_data["lang_type"]."')";
		
		$st = $db->prepare($sql);
		$st->execute();

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function get_ma_result_html($result_id, $ma_result, $selected_word="", $trg_src_id="", $delimiter=" ") {
//	echo "<textarea style='width:100%;height:500px;'> \n\n  $ma_result    </textarea>";
	
	$ma_result = str_replace("&nbsp;", " ", $ma_result);

	$on_mouse = "onmouseover=\"this.style.backgroundColor='#D3D3D3'; this.style.cursor='pointer'\" onmouseout=\"this.style.backgroundColor='';\"";

	$buf = "";
	$token = explode($delimiter, $ma_result);
	foreach( $token as $word ) {
		if( $word == $selected_word ) $word = "<b>$word</b>";
		$buf .= "<span $on_mouse onclick=\"search_word(this, '$result_id', '$trg_src_id');\" flag_select=0>$word</span> ";
	}

	return $buf;
}
//------------------------------------------------------------------------------
function search_word_ck($_post_data) {
	$lang_type = $_post_data["lang_type"];

	$engine = $_post_data["_ENGINE_PATH_"]."/".$_post_data["engine"];
	$dic    = $_post_data["dic_path"]."/".$_post_data["dic"];
	$word   = $_post_data["src"];
	$trg_src_id = $_post_data["trg_src_id"];
	
	$cookie_env = json_decode($_COOKIE["env"], true);

	// remove tag
	$word = preg_replace('/<[^>]+?>/', '', $word);

	$root = $word;
	if( strpos($word, ":") !== false ) {
		list($root, $pos) = explode(":", $word, 2);
	} else if( strpos($word, "/") !== false ) {
		list($root, $pos) = explode("/", $word, 2);
	}

    echo "<KEY_VALUE>";

	// CLEX
	$db = "CLEX.db";
	$result_clex = search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $root);
    
	if( $cookie_env["dictionary"]["LxNGram.db"] == 1 ) {
		to_xml($db, $result_clex);        
	}

 	if( !isset($result_clex) || count($result_clex) == 0 ) $result_clex[0] = "";
 	
 	$ctf_key_list = get_ctf_key_list($result_clex);

	if( $cookie_env["dictionary"]["LxNGram.db"] == 1 ) {
		$db = "LxNGram.db";
		$result_buf = array();	
		foreach( get_ctf_key_list($result_clex, ":") as $key ) {
			$result_buf = array_merge($result_buf, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $key));
		}
		$result_buf = array_merge($result_buf, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $word));
		to_xml($db, $result_buf);
	}
    
	if( $cookie_env["dictionary"]["CTF.db"] == 1 ) {
		$db = "CTF.db";
		$result_ctf = array();
		foreach( $ctf_key_list as $key ) {
			$result_ctf = array_merge($result_ctf, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $key));
		}
		to_xml($db, $result_ctf);
	}

	if( $cookie_env["dictionary"]["CGENKO3_DIC_LAYER2.db"] == 1 ) {
		$db = "CGENKO3_DIC_LAYER2.db";
		$result_buf = array();	
		foreach( get_cgenko_key_list($result_ctf) as $key ) {
			$result_buf = array_merge($result_buf, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $key));
		}
		to_xml($db, $result_buf);
	}

	if( $cookie_env["dictionary"]["CPOST.db"] == 1 ) {
		$db = "CPOST.db";
		$result_buf = array();	
		foreach( $ctf_key_list as $key ) {
			$result_buf = array_merge($result_buf, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $key));
		}
		to_xml($db, $result_buf);
	}
	
	if( $cookie_env["dictionary"]["CPARSER.db"] == 1 ) {
		$db = "CPARSER.db";
		to_xml($db, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $root));
	}

	if( $cookie_env["dictionary"]["TM.db"] == 1 ) {
		$db = "TM.db";
		to_xml($db, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $root));
	}

//	if( $cookie_env["dictionary"]["TM.db"] == 1 ) {
		$db = "CSTRFEATURE.db";
		to_xml($db, search_gdbm_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $root));
//	}

	if( $cookie_env["dictionary"]["naver"] == 1 ) {
		$db = "Naver";
		$val = search_naver_dic_cn($_post_data, $root);
		$result = array();
		$result[$root] = $val;
		to_xml($db, $result);
	}

    echo "</KEY_VALUE>";
}
//------------------------------------------------------------------------------
function update_kemt_shell($engine, $dic, $db, $key, $val) {
	$val = str_replace("\r\n", "\n", $val);

	$fn = "/tmp/".uniqid();
	
	$buf = "";
	foreach( explode("\n", $val) as $str ) {
		$buf .= "$key\t$str\n";
	}
	$buf .= "exit\n";

	$fh=fopen($fn, "w");
	fwrite($fh, $buf);
	fclose($fh); 	
	
	$cmd = "cat $fn | $engine -cmd UPDATE_DB -fn $dic/$db 2>&1";
	system($cmd);

	$cmd = "rm -f $fn";
	system($cmd);
}
//------------------------------------------------------------------------------
function update_ekmt_shell($engine, $dic, $db, $key, $val) {
	$val = str_replace("\r\n", "\n", $val);

	$fn = "/tmp/".uniqid();
//	$fn = "/home_mt/pub/ekmt.in.txt";
	
	$buf = "";
	foreach( explode("\n", $val) as $str ) {
		$buf .= "$key\t$str\n";
	}
	$buf .= "exit\n";

	$fh=fopen($fn, "w");
	fwrite($fh, $buf);
	fclose($fh); 	
	
	$db = str_replace(".db", "", $db);

	$ret = array();

	$cmd = "cd $dic/DB ; cat $fn | $engine -cmd UPDATE_DB -fn $db 2>&1";
	exec($cmd, $ret);

//	error_log($cmd);
//	error_log(join(" || ", $ret));

	$ret = array();
	
	$cmd = "rm -f $fn";
	exec($cmd, $ret);
	
//	error_log(join(" || ", $ret));
}
//------------------------------------------------------------------------------
function search_ekmt_shell($_scripts, $engine, $dic, $db, $key) {
	$ret = array();

	$cmd = "cd $dic ; echo \"$key\" | $engine -cmd SEARCH_DB -fn $db 2>/dev/null";
	exec($cmd, $ret);
	
//	error_log($cmd);
//	error_log(join(" || ", $ret));
	
	// merge CTF.db
	$buf = array();
	foreach( $ret as $line ) {
		if( trim($line) == "" ) continue;

		list($key, $val) = explode("\t", $line, 2);

		if( !isset($buf[$key]) ) $buf[$key] = "";

		if( $buf[$key] != "" ) $buf[$key] .= "\n";
		$buf[$key] .= trim($val);
	}

	return $buf;
}
//------------------------------------------------------------------------------
function search_word_ek($_post_data) {
	$lang_type = $_post_data["lang_type"];

	$engine = $_post_data["_ENGINE_PATH_"]."/".$_post_data["engine"];
	$dic    = $_post_data["dic_path"]."/".$_post_data["dic"]."/DB";
	$word   = $_post_data["src"];
	$file_name  = $_post_data["file_name"];
	
    echo "<KEY_VALUE>";

	// default dictionary
	if( $file_name == "" ) {
		// remove tag
		$word = preg_replace('/<[^>]+?>/', '', $word);
	
		$root = $word;
		if( strpos($word, ":") !== false ) {
			list($word, $root, $pos) = explode(":", $word, 3);
		} else if( strpos($word, "/") !== false ) {
			list($word, $root, $pos) = explode("/", $word, 3);
		}
			
		// PROBDICT
		$db = "PROBDICT";
		
		$ret = search_ekmt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, "$root");
		to_xml($db.".db", $ret);	
	
		// dictionary
		$db = "dictionary";
		
		$dictionary = search_ekmt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, "$root@$pos");
		to_xml($db.".db", $dictionary);	
	} else {
		$file_name = str_replace(".db", "", $file_name);
		
		$db = "$file_name";
		
//		echo "$engine :: $dic :: $file_name :: $word";

		$ret = search_ekmt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, "$word");
		to_xml($db.".db", $ret);	
	}

    echo "</KEY_VALUE>";
}
//------------------------------------------------------------------------------
function search_word_ke($_post_data, $lang) {
	$lang_type = $_post_data["lang_type"];

	$engine = $_post_data["_ENGINE_PATH_"]."/".$_post_data["engine"];
	$dic    = $_post_data["dic_path"]."/".$_post_data["dic"];
	$word   = $_post_data["src"];
	$trg_src_id = $_post_data["trg_src_id"];
	$file_name  = $_post_data["file_name"];
	
	// remove tag
	$word = preg_replace('/<[^>]+?>/', '', $word);

	$root = $word;
	if( strpos($word, "[") !== false ) {
		$word = str_replace("[", "\t", $word);
		$word = str_replace("]", "\t", $word);
		
		list($root, $pos) = explode("\t", $word, 2);
	}

    echo "<KEY_VALUE>";

	if( $file_name == "" ) {       
//	      Lexicon.dat	
//		 배꼽	1 7 7 8 16 배꼽 1 ;
		$db = "Lexicon.dat";
		$key = "$root";	
	
		$lex = search_kemt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $key);
		to_xml($db, $lex);	
	
//		 Sem.dat	
//		 배꼽_7	신체부분
		$uniq_sem_key = array();
		
		foreach( explode(";", $lex[$root]) as $item ) {
			if( !isset($item) ) continue;
			
			$item = trim($item);
			if( $item == "" ) continue;
			
			list($lcon, $lpos, $rpos, $rcon, $freq) = explode(" ", $item, 5);
			
			if( $lpos == "" ) continue;
	
			$db = "Sem.dat";
			$sem_key = $root."_".$lpos;	
	
//			 check uniq sem key.
			if( isset($uniq_sem_key[$sem_key]) ) continue;
			$uniq_sem_key[$sem_key] = 1;
			
			$sem = array();
	
			if( $lpos < 8 ) {
				$sem = search_kemt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $sem_key);
			}		
	
			if( !isset($sem[$sem_key]) ) $sem[$sem_key] = "";
			
			if( $lpos < 8 ) to_xml($db, $sem);
		
//			 KEGen.dat	
//			 배꼽_7_신체부분		
			foreach( explode(" ", $sem[$sem_key]) as $gen_item ) {
				$db = "Gen.dat";
				if( $lang == "ke" )	$db = "KEGen.dat";
				if( $lang == "kc" )	$db = "KCGen.dat";
				
				$gen_key = $sem_key."_".$gen_item;	
			
				$gen = search_kemt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $gen_key);
				if( !isset($gen[$gen_key]) ) $gen[$gen_key] = "";
				to_xml($db, $gen);	
			}
		}
	} else if( $file_name == "LEXICON" ) {
        $db = "LEXICON";        
		$key = "$root";		
		$ret = search_kemt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, $key);
        
        echo join("\n", $ret);
	} else {
		$db = "$file_name";

		$ret = search_kemt_shell($_post_data["_SCRIPTS_PATH_"], $engine, $dic, $db, "$word");
		to_xml($db, $ret);	
	}

    echo "</KEY_VALUE>";
}
//------------------------------------------------------------------------------
function search_kemt_shell($_scripts, $engine, $dic, $db, $key) {
   	$buf = array();
    
    if( $db == "LEXICON" ) {
    	$ret = array();
        $cmd = "echo \"READ LEXICON $key\" | $engine -cmd STDIN_LEXICON_ACCESS -dic $dic -conf $engine.conf";	
    	exec($cmd, $ret);
        
        return $ret;
    } else {
    	$ret = array();
        $cmd = "echo \"$key\" | $engine -cmd SEARCH_DB -fn $dic/$db";	
    	exec($cmd, $ret);
    	
    	foreach( $ret as $line ) {
    		if( trim($line) == "" ) continue;
    
    		list($key, $val) = explode("\t", $line, 2);
    
    		if( !isset($buf[$key]) ) $buf[$key] = "";
    
    		if( $buf[$key] != "" ) $buf[$key] .= "\n";
    		
    		if( strpos($dic, "SentPattern") !== false ) $val = trim($val);
    		$buf[$key] .= $val;
    	}
    }  

	return $buf;
}
//------------------------------------------------------------------------------
function get_cgenko_key_list($result, $sep="\t") {
	$key_list = array();
	
	// <te> 精神/NN #@# 정신/NN; 활력/NN
	foreach( $result as $key => $val ) {
		if( $val == "" ) continue;
		
		list($root, $p) = explode("/", $key);

		foreach( explode("\n", $val) as $str ) {
			if( strpos($str, "<te>") === false ) continue;
			
			list($a, $kor) = explode("#@#", $str);
			
			foreach( explode(";", $kor) as $t ) {
				$t = trim($t);
				
				list($k, $pos) = explode("/", $t, 2);
				
				$k = str_replace("+", "", $k);
				$k = str_replace("_", "", $k);

				array_push($key_list, $k.$sep.$pos.$sep.$root);
			}
		}
	}

	return $key_list;
}
//------------------------------------------------------------------------------
function search_naver_dic_cn($_post_data, $keyword) {
	// http://sug.dic.daum.net/eng2_nsuggest?q=get%20over&mod=json&code=utf_in_out
	// http://tooltip.dic.naver.com/tooltip.nhn?wordString=context

	$dic_type = "zh";

	$val = search_tooltip_search_log($_post_data, $keyword);

//error_log("(".__LINE__.") val: $val");
	if( $val != "" ) return $val;

	$url = "http://cndic.naver.com/toolTip.nhn?entryName=$keyword";
	if( $dic_type != "zh" ) {
		$url = "http://tooltip.dic.naver.com/tooltip.nhn?wordString=$keyword";
	}

	$result = get_url($url);
	$result = json_decode($result, true);

	$key = $result["entryName"];
	$val = "";

	if( $dic_type == "zh" ) {
		if( isset($result["means"][0]) && isset($result["means"][0]["mean"]) ) $val = $result["means"][0]["mean"];

		$val = str_replace("|", ", ", $val);
	} else {
		$val = join(", ", $result["mean"]);
	}

	save_dic_search_result($_post_data, $key, $val);

	return $val;
}
//------------------------------------------------------------------------------
function search_tooltip_search_log($_post_data, $keyword) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/tooltip_seach_log.db";

	$k = str_replace("'", "''", $keyword);
	$where = "k='$k'";

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
		$sql = "CREATE TABLE IF NOT EXISTS tooltip_seach_log (k TEXT, v TEXT)";

		$st = $db->prepare($sql);
		$st->execute();

		// select
		$sql = "SELECT k,v FROM tooltip_seach_log WHERE $where";

//error_log("(".__LINE__.") $sql");

		$sth = $db->prepare($sql);
		$sth->execute();

		$result = $sth->fetchAll(PDO::FETCH_ASSOC);

		// close the database connection
		$db = NULL;

		if( !isset($result[0]) || !isset($result[0]["v"]) ) return "";

		return $result[0]["v"];
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}

	return "";
}
//------------------------------------------------------------------------------
function save_dic_search_result($_post_data, $key, $val) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/tooltip_seach_log.db";

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
		$sql = "CREATE TABLE IF NOT EXISTS tooltip_seach_log (k TEXT, v TEXT)";

		$st = $db->prepare($sql);
		$st->execute();

		// insert data
		$key = str_replace("'", "''", $key);
		$val = str_replace("'", "''", $val);

		$sql = "INSERT INTO tooltip_seach_log (k,v) VALUES ('$key', '$val')";

//error_log("(".__LINE__.") $sql");

		$st = $db->prepare($sql);
		$st->execute();

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function to_xml($db, $_result) {
	if( !isset($_result) || count($_result) == 0 ) $_result[""] = "";
	
	// alias db name
	$_alias_db_name = array(
			// ck
			"CLEX.db" => "형태소",
			"CPATTERN.db" => "구문 패턴",
			"CPARSER.db" => "구문 속성",
			"CPOST.db" => "연어 패턴",
			"CTF.db" => "변환 사전",
			"CGENKO.db" => "생성 사전",
			"CLMT.db" => "CLMT_DIC",
			"CPINYIN.db" => "병음",
			"CLEX_LOG.db" => "CLEX_LOG_DIC",
			"CGENKO2.db" => "CGENKO2_DIC",
			"CROOT.db" => "어근",
			"CSTRFEATURE.db" => "의미 속성",
			"CMW.db" => "양사",
			"CBIG2GB.db" => "CBIG2GB_DIC",
			"CCOMPOUND.db" => "복합명사",
			"CTESTFEATURE.db" => "CTESTFEATURE_DIC",
			"CGENKO3_DIC_LAYER1.db" => "CGENKO3_DIC_LAYER1",
			"CGENKO3_DIC_LAYER2.db" => "한국어 생성",
			"CGENKO3_DIC_EOMI.db" => "CGENKO3_DIC_EOMI",
			"CGENKO3_DIC_POST.db" => "CGENKO3_DIC_SC",
			"CGENKO3_DIC_SC.db" => "단어분리(CWFREQ)",
			"TM.db" => "TM 사전",
			"LxNGram.db" => "Lx NGram",
			// ke/kc
			"Lexicon.dat" => "형태소",
			"Sem.dat" => "의미코드",
			"KEGen.dat" => "대역어 (KE)",
			"KCGen.dat" => "대역어 (KC)",
			// ek
			"dictionary.db" => "대역사전",
			"PROBDICT.db" => "확률사전",			
	);	
	
	$str_db = $db;
	if( isset($_alias_db_name[$db]) ) $str_db = $_alias_db_name[$db];
    
    $tag_db = str_replace(".db", "", $db);
    
	foreach( $_result as $key => $val ) {
		$val = preg_replace("/\s+$/i", "", $val);

		$key = str_replace("<TAB>", "\t", $key);
		$val = str_replace("<TAB>", "\t", $val);
        
        echo "<$tag_db>";
        echo "<file_name><![CDATA[$tag_db]]></file_name>";
        echo "<key><![CDATA[$key]]></key>";
        echo "<val><![CDATA[$val]]></val>";
    	echo "</$tag_db>";
	}
}
//------------------------------------------------------------------------------
function to_html($db, $_result, $f_title, $trg_src_id) {
	if( !isset($_result) || count($_result) == 0 ) $_result[""] = "";
	
	if( !isset($f_title) ) $f_title = 0;
	
	// alias db name
	$_alias_db_name = array(
			// ck
			"CLEX.db" => "형태소",
			"CPATTERN.db" => "구문 패턴",
			"CPARSER.db" => "구문 속성",
			"CPOST.db" => "연어 패턴",
			"CTF.db" => "변환 사전",
			"CGENKO.db" => "생성 사전",
			"CLMT.db" => "CLMT_DIC",
			"CPINYIN.db" => "병음",
			"CLEX_LOG.db" => "CLEX_LOG_DIC",
			"CGENKO2.db" => "CGENKO2_DIC",
			"CROOT.db" => "어근",
			"CSTRFEATURE.db" => "의미 속성",
			"CMW.db" => "양사",
			"CBIG2GB.db" => "CBIG2GB_DIC",
			"CCOMPOUND.db" => "복합명사",
			"CTESTFEATURE.db" => "CTESTFEATURE_DIC",
			"CGENKO3_DIC_LAYER1.db" => "CGENKO3_DIC_LAYER1",
			"CGENKO3_DIC_LAYER2.db" => "한국어 생성",
			"CGENKO3_DIC_EOMI.db" => "CGENKO3_DIC_EOMI",
			"CGENKO3_DIC_POST.db" => "CGENKO3_DIC_SC",
			"CGENKO3_DIC_SC.db" => "단어분리(CWFREQ)",
			"TM.db" => "TM 사전",
			"LxNGram.db" => "Lx NGram",
			// ke/kc
			"Lexicon.dat" => "형태소",
			"Sem.dat" => "의미코드",
			"KEGen.dat" => "대역어 (KE)",
			"KCGen.dat" => "대역어 (KC)",
			// ek
			"dictionary.db" => "대역사전",
			"PROBDICT.db" => "확률사전",			
	);	
	
	$str_db = $db;
	if( isset($_alias_db_name[$db]) ) $str_db = $_alias_db_name[$db];

	echo "<table style='width:100%' border=1>";

	if( $f_title ) {
		echo "  <tr>";
		echo "    <td width=100px>DB</td>";
		echo "    <td width=100px>KEY</td>";
		echo "    <td>VALUE</td>";
		echo "    <td>&nbsp;</td>";
		echo "  </tr>";
	}

	foreach( $_result as $key => $val ) {
		$uid = uniqid();

		$key = str_replace("<TAB>", "\t", $key);
		
		$value_height = "";
		if( $val == "" ) $value_height = "height: 50px;";
		
		if( strlen($val) > 300 ) $value_height = "height: 100px;";

		echo "    	<div id='$uid.key_org' style='display:none;'>$key</div>";
		echo "    	<div id='$uid.val_org' style='display:none;'>$val</div>";

		echo "  <tr>";
		echo "    <td width='100px'>$str_db</td>";
		echo "    <td width='100px' valign='center'>";
		echo "    	<input type='text' id='$uid.key' style='width: 100%; height: 100%;' value=\"$key\">";
		echo "    </td>";
		echo "    <td style='margin:0; padding:0; $value_height'>";
		echo "    	<textarea id='$uid.val' style='margin:0; padding:0; width:100%; height:100%;'>$val</textarea>";
		echo "    	<textarea id='$uid.comment' style='margin:0; padding:0; width:100%; height:100%; display:none;'></textarea>";
		echo "    </td>";
		echo "    <td width='60px' align='center'>";

		if( strpos($db, ".db") !== false || strpos($db, ".dat") !== false ) {
			echo "		<img src='/imgs/save.png'   onmouseover=\"this.style.cursor='pointer'\" title='Save'   onclick=\"update_gdbm(this, '$db', '$uid', '$trg_src_id')\">";
			echo "		<img src='/imgs/delete.png' onmouseover=\"this.style.cursor='pointer'\" title='Delete' onclick=\"delete_gdbm(this, '$db', '$uid', '$trg_src_id')\">";
			echo "		<img src='/imgs/check4.png' onmouseover=\"this.style.cursor='pointer'\" title='Comment' onclick=\"var obj=document.getElementById('$uid.comment'); if( obj.style.display == '' ) obj.style.display='none'; else obj.style.display='';\">";
		} else {
//			echo "		<img src='/imgs/search.png' onmouseover=\"this.style.cursor='pointer'\" title='Search' onclick=\"\">";
		}

		echo "    </td>";
		echo "  </tr>";
	}

	echo "</table>";
}
//------------------------------------------------------------------------------

?>