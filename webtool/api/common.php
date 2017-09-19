<?
//------------------------------------------------------------------------------
function show_deamon_log($_post_data)
{
	$start_line_num = $_post_data["start_line_num"];
	
	$fn = $_post_data["path"]."/".$_post_data["file_name"];
	
	$ret = array();	
	exec("wc -l $fn", $ret);
	
	$ret[0] = trim($ret[0]);
	
	list($max_cnt) = explode(" ", $ret[0]);
	
	echo $max_cnt."\t";
	
	$current = $max_cnt - $start_line_num;
	
	if( $current > 200 ) $current = 200;
	
	if( $current > 0 ) {
		$ret = array();
		$cmd = "export LC_ALL=en_US.UTF-8; tail -n $current $fn 2>&1";
		exec($cmd, $ret);
//		system($cmd);

		foreach( $ret as $line ) {
			echo $line;
			echo "\n";
		}
	}		
}
//------------------------------------------------------------------------------
function sent_eval($_post_data)
{
	$fn_tst_a = $_post_data["upload_path"]."/".$_post_data["fn_a"];
	$fn_tst_b = $_post_data["upload_path"]."/".$_post_data["fn_b"];

	$fn_data_set = $_post_data["_DATA_SET_PATH_"]."/".$_post_data["fn_data_set"];
	
	$scripts 	 = $_post_data["_SCRIPTS_PATH_"];
	
	$n_list = $_post_data["n_list"];
	 
	$cmd = "perl $scripts/ngram/cmp_bleu_bitext.pl -n_list $n_list -a $fn_tst_a -b $fn_tst_b -data_set $fn_data_set | sort | perl $scripts/diff.sent.pl -col 2,3 -show_tag -show_as_table > $fn_tst_a.htm ; cat $fn_tst_a.htm";
	system($cmd);
}
//------------------------------------------------------------------------------
function change_my_passwd($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_USER_INFO_DB_FILE_NAME_"];

	$user_email = $_post_data["user_email"];
	$user_pw 	= $_post_data["user_pw"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// change my passwd
		$sql = "UPDATE ".$_post_data["_USER_INFO_TABLE_NAME_"];
		$sql .= " SET user_pw='$user_pw'";
		$sql .= " WHERE user_email='$user_email'";

		$st = $db->prepare($sql);
		$st->execute();

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}

	// change trac id
	change_htpasswd($_post_data["_TRAC_PASSWD_FILE_NAME_"], $user_email, $user_pw);
}
//------------------------------------------------------------------------------
function delete_log_db($_post_data) {
	$tag 		= conv_sql_value($_post_data["tag"]);
	$user_email	= conv_sql_value($_post_data["user_email"]);
	$src 		= conv_sql_value($_post_data["src"]);
	$m_date 	= conv_sql_value($_post_data["m_date"]);

	$table_name	= conv_sql_value($_post_data["table_name"]);

	if( $table_name == "workbench_log" ) {
		$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_WORKBENCH_LOG_DB_FILE_NAME_"];
	} else {
		$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_EVALUATION_RESULT_DB_FILE_NAME_"];
	}

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		$sql = "DELETE FROM $table_name WHERE m_date='$m_date' AND user_email='$user_email' AND src='$src' AND tag='$tag'";

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
function query_db($db_name, $sql) {
	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		$result = $db->query($sql);
		$query_result = db_result_array($result);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}

	return $query_result;
}
//------------------------------------------------------------------------------
function get_combobox_data_format($_data, $_selected) {
	$ret = array();
	foreach( $_data as $k => $v ) {
        if( is_numeric($k) ) $k = $v;
        
        if( strpos($v, ".conf") !== false ) continue;
        
        $selected = false;
        if( $k == $_selected ) $selected = true;
        
		array_push($ret, array("text" => $v, "value" => $k, "selected" => $selected));
	}
	
	return $ret;
}
//------------------------------------------------------------------------------
function get_engine_info($_post_data) {
	$lang_type = $_post_data["lang_type"];
	
	if( !isset($_post_data["engine"]) ) $_post_data["engine"] = "";
	if( !isset($_post_data["dic"]) )    $_post_data["dic"] = "";
	
	$cookie_env = array();
	if( isset($_COOKIE["env"]) ) $cookie_env = json_decode($_COOKIE["env"], true);
    
    $ret = array(); 
    
    // lang list
    $lang_data = array(
        "ck" => "중국어 > 한국어", 
        "ek" => "영어 > 한국어", 
        "ke" => "한국어 > 영어", 
        "kc" => "한국어 > 중국어", 
        "dial" => "대화처리", 
    );
    
    $ret["lang"] = get_combobox_data_format($lang_data, $lang_type);
    
    // engine
    $engine_data = array();
       
	if( isset($cookie_env["default"][$lang_type]["engine_fix"]) && $cookie_env["default"][$lang_type]["engine_fix"] == 1 ) {	   
		array_push($engine_data, array("text" => $_post_data["engine"], "value" => $_post_data["engine"], "selected" => true));
	} else {
    	if( $lang_type == "dial" ) {
    		$engine_data = get_combobox_data_format(get_file_list($_post_data["_ENGINE_PATH_"], $lang_type), $_post_data["engine"]);
    	} else {
    		$engine_data = get_combobox_data_format(get_file_list($_post_data["_ENGINE_PATH_"], $lang_type."_"), $_post_data["engine"]);
    	}
    }
    
    $ret["engine"] = $engine_data;

    // dictionary  
    $dictionary_data = array();
      
	if( isset($cookie_env["default"][$lang_type]["dictionary_fix"]) && $cookie_env["default"][$lang_type]["dictionary_fix"] == 1 ) {
		array_push($engine_data, array("text" => $_post_data["dic"], "value" => $_post_data["dic"], "selected" => true));
	} else {
	   if( $lang_type == "kc" ) $lang_type = "ke";       
		$dictionary_data = get_combobox_data_format(get_dic_list($_post_data["dic_path"], "dic".$lang_type, 0), $_post_data["dic"]);
	}

	$ret["dictionary"] = $dictionary_data;     
    
    // echo result
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function json_urlencode($arr, $sub_k, &$new_arr) {
	foreach( $arr as $k => $v ) {		
		array_push($sub_k, rawurlencode($k));
		
		if( is_array($v) ) {
			json_urlencode($v, $sub_k, $new_arr);
		} else {
			$v = rawurlencode($v);
			
			if( count($sub_k) == 1 ) $new_arr[$sub_k[0]] = $v;
			if( count($sub_k) == 2 ) $new_arr[$sub_k[0]][$sub_k[1]] = $v;
			if( count($sub_k) == 3 ) $new_arr[$sub_k[0]][$sub_k[1]][$sub_k[2]] = $v;
			if( count($sub_k) == 4 ) $new_arr[$sub_k[0]][$sub_k[1]][$sub_k[2]][$sub_k[3]] = $v;
		}
		
		array_pop($sub_k);
	}
}
//------------------------------------------------------------------------------
function login($_post_data) {
	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_USER_INFO_DB_FILE_NAME_"];

	try {
		//open the database
		$db = new PDO("sqlite:$db_name");

		// create table
		$sql = "CREATE TABLE IF NOT EXISTS ".$_post_data["_USER_INFO_TABLE_NAME_"];
		$sql .= " (env TEXT, user_email TEXT, user_pw TEXT, user_name TEXT, level TEXT, comment TEXT, m_date DATE UNIQUE DEFAULT (datetime('now','localtime')))";

		$st = $db->prepare($sql);
		$st->execute();

		// query user email
		$sql = "SELECT user_name,level,env,user_email,user_pw FROM ".$_post_data["_USER_INFO_TABLE_NAME_"];
		$sql .= " WHERE user_email = '".$_post_data["user_email"]."' AND user_pw = '".$_post_data["user_pw"]."'";

		$result = $db->query($sql);
		$rows = $result->fetch(PDO::FETCH_ASSOC);

		if( !isset($_post_data["user_name"]) ) $_post_data["user_name"] = "";

        $ret = array(
            "user_name" => $rows["user_name"],
            "user_email" => $rows["user_email"],
            "user_pw" => $rows["user_pw"],
            "level" => $rows["level"],
            "env" => $rows["env"]
        );
        
        echo json_encode($ret);
        
//		$new_json = array();
//		$sub_k = array();
//		
//		json_urlencode($cookie_env, $sub_k, $new_json); 
//			
//		$env = json_encode($new_json);
//        $dir = dirname(getenv("SCRIPT_NAME"));
//    
//		setcookie("env", $env, 0, $dir);

		// close the database connection
		$db = NULL;
	} catch(PDOException $e) {
		print 'Exception : '.$db_name.' : '.$e->getMessage();
		error_log("(".__LINE__.") ".$e->getMessage());
	}
}
//------------------------------------------------------------------------------
function get_one_trans_result_html($uid, $cap, $out) 
{
	$out_org = $out;
	
	$uid_target = uniqid();
	if( strpos($out_org, "</dic>") !== false ) {
		// <dic filename="LexRule.db" key="compared_to"> { compared! to } -> {IN }(#1) <@PREP_compared_to> </dic>		
		// => <span>Thus/thus/ADV</span> 	
		
		$mouse = "onmouseover=\"this.style.backgroundColor='#D3D3D3'; this.style.cursor='pointer'\"";
		$mouse .= " onmouseout=\"this.style.backgroundColor='';\"";
		$mouse .= " style=\"font-weight: bold;\"";
		$mouse .= " flag_select='0'";
		$mouse .= " onclick=\"search_word(this, '$uid_target', '');\" ";
		
		$out = str_replace("<dic ", "<span ".$mouse, $out);
		$out = str_replace("</dic>", "</span>", $out);
	}
	
	$style_first_col = "style='width:100px; text-align:center; background-color:#D0D0D0;'";

	$buf = "";

	$buf .= "<table style='width:100%;' border=1>";
	$buf .= "	<tr>";
	$buf .= "		<td $style_first_col>";
	$buf .= "			$cap";
	$buf .= "		</td>";
//	$buf .= "	</tr><tr>";
	$buf .= "		<td id='$uid' style='font-size:90%; no-word-wrap:break-word; word-break:break-all;'>";
	$buf .= "			$out";
	$buf .= "		</td>";
	$buf .= "	</tr>";
	$buf .= "</table>";

	if( strpos($out_org, "</dic>") !== false ) {
		$buf .= "<div id='$uid_target' style='padding-left: 20px; width:100%; display:none;'></div>";
	}

	return $buf;
}
//------------------------------------------------------------------------------
function hex_to_utf8( $unicode_hex ) 
{	
	$unicode = hexdec($unicode_hex);

	$utf8 = '';	
	if ( $unicode < 128 ) {	
	    $utf8 = chr( $unicode );	
	} elseif ( $unicode < 2048 ) {	
	    $utf8 .= chr( 192 + ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
	    $utf8 .= chr( 128 + ( $unicode % 64 ) );	
	} else {	
	    $utf8 .= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
	    $utf8 .= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
	    $utf8 .= chr( 128 + ( $unicode % 64 ) );	
	} 

    return $utf8;
} 
//------------------------------------------------------------------------------
function utf8_to_hex($_str)
{
	$ret = "";
	
	for( $i=0 ; $i < mb_strlen($_str, "UTF-8") ; $i++ ) {
		$w = mb_substr($_str, $i, 1, "UTF-8");
		
	    if( IsHan($w) ){
//	    	echo "::".sprintf("0x%04x", uni_ord($w))."<br>\n";
	        $ret .= sprintf("0x%04x", uni_ord($w));
	    } else {
	        $ret .= $w;
	    }
	}
	
	return $ret;
}
//------------------------------------------------------------------------------
function IsHan($c)
{
	$c = uni_ord($c);
	
	if($c >= hexdec('0x4E00') && $c <= hexdec('0x9FA5')) return 1;
	if($c >= hexdec('0xF900') && $c <= hexdec('0xFA29')) return 1;

	return 0;
}
//------------------------------------------------------------------------------
function uni_ord($c) {
    $h = ord($c{0});
    if ($h <= 0x7F) {
        return $h;
    } else if ($h < 0xC2) {
        return false;
    } else if ($h <= 0xDF) {
        return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
    } else if ($h <= 0xEF) {
        return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
                                 | (ord($c{2}) & 0x3F);
    } else if ($h <= 0xF4) {
        return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
                                 | (ord($c{2}) & 0x3F) << 6
                                 | (ord($c{3}) & 0x3F);
    } else {
        return false;
    }
}
//------------------------------------------------------------------------------
function StripText($STR, $L_TAG, $R_TAG)
{
	if( strpos($STR, $L_TAG) === false ) return "";
	if( strpos($STR, $R_TAG) === false ) return "";
	
	$st = strpos($STR, $L_TAG) + strlen($L_TAG);
	
	return substr($STR, $st, strrpos($STR, $R_TAG) - $st);
}
//------------------------------------------------------------------------------
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
//------------------------------------------------------------------------------
function time_to_sec($time) {
    $hours = substr($time, 0, -6);
    $minutes = substr($time, -5, 2);
    $seconds = substr($time, -2);

    return $hours * 3600 + $minutes * 60 + $seconds;
}
//------------------------------------------------------------------------------
function sec_to_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor($seconds % 3600 / 60);
    $seconds = $seconds % 60;

    return sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
}
//------------------------------------------------------------------------------
function get_daemon_cmd($_act_page, $_engine, $_dic, $_domain, $_serv_ip, $_port, $_cmd, $_encoding, $_bin_path, $_log_path, $_args="")
{
	// copy to /build_farm/engine.service from engine if not exists at engine.service
	
	$cmd = "";

	if( !isset($_engine) || !isset($_dic) ) {
		echo "ERROR engine: $_engine & dic path: $_dic, $_act_page, $_domain";
		return "";
	}
	
	$encoding 	= "euc-kr";
	$conf   	= $_engine.".conf";
	$cmd 		= "STDIN_AUTO_EVAL";
	
	if( strpos($_domain, "ck_")  !== false ) $encoding = "gb2312";	

	if( isset($_encoding) ) $encoding = $_encoding;
	
	if( isset($_cmd) ) $cmd = $_cmd;
	
	$fn_log = $_log_path."/$_act_page.$_domain.$_port.log";
	
	if( $_args != "" ) $_args = "-args $_args";
	
	$cmd_shell = "nohup ".
				"   perl $_bin_path/run.daemon.farm.pl ".
				"      -tag      $_act_page".
				"      -engine   $_engine".
				"      -dic      $_dic".
				"      -port     $_port".
				"      -cmd      $cmd".
				"      -encoding $encoding".
				"      -encoding_out $encoding".
				"      -conf     $conf".
				"      $_args".
				"      >>        $fn_log 2>&1 &";
				
	return $cmd_shell;
}
//------------------------------------------------------------------------------
function print_globals($arr, $k_buf, $uid, &$k_list, $_post_data)
{
	foreach( $arr as $k => $v ) {
		if( substr($k, 0, 1) == "_" ) continue;
		if( $k == "GLOBALS" ) continue;
		
		if( count($k_buf) == 0 ) {
			echo "<div style='width:100%; background-color:lightgray;'>$k</div>";
		}

		$uid = $k.uniqid();
		array_push($k_list, $uid);
			
		array_push($k_buf, $k);
		if( is_array($v) ) {
			print_globals($v, $k_buf, $uid, $k_list, $_post_data);
		} else {
			$list = array();
			for( $i=0 ; $i<3 ; $i++ ) {
				$str = ( isset($k_buf[$i]) ) ? $k_buf[$i] : "";
				array_push($list, $str);
			}
			
			$bg_color = "";
//			if( $list[1] ) $bg_color = "bgcolor='black'";
			
			$bg_color = "";
			
			// print line break
			foreach( $list as $sub_k ) {
				if( $sub_k != "#" ) continue;

				echo "<hr />";
				
				$bg_color = "background-color: #CCCCCC;";
				break;
			}
			
			// print contents
			echo "<table style='width:100%;' border=1>";
			echo "	<tr>";
			
			$cnt_k = 0;
			foreach( $list as $sub_k ) {				
				echo "	<td style='width:15%;' $bg_color>";
				echo "		<input id='".$uid."_key_".$cnt_k."' type=text style='width: 100%; $bg_color' value=\"$sub_k\" />";
				echo "	</td>";
				
				$cnt_k++;
			}
			echo "	<td>";
			echo "		<input id='".$uid."_value' type=text style='width: 100%; $bg_color' value=\"$v\" />";
			echo "	</td>";

			if( $list[2] == "ENGINE" || $list[2] == "DIC" ) {
				echo "	<td style='text-align:center; width:20px;'>";
								
				if( file_exists($v) || is_dir($v) ) {
					if( is_dir($v) ) {
						$path = $v;
						
						$path = str_replace($_post_data["_ROOT_PATH_"], $_post_data["samba_header"]."/pub", $path);
						
						echo "<img src=/imgs/folder_icon.png onmouseover=\"this.style.cursor='pointer'\" onclick=\"window.open('$path', '');\" />";	
					} else {
						echo "<img src=/imgs/file_o.png />";
					}
				} else {
					echo "	<img src=/imgs/file_x.png />";					
				}
				
				echo "	</td>";
			}			

			echo "	</tr>";
			echo "</table>\n";
		}
		array_pop($k_buf);
		
		if( count($k_buf) == 0 ) {
			// print empty 
			$uid = $k.uniqid();
			array_push($k_list, $uid);
			
			echo "<table style='width:100%;' border=1>";
			echo "	<tr>";
			
			for( $i=0 ; $i<3 ; $i++ ) {
				echo "	<td style='width:15%;'>";
				echo "		<input id='".$uid."_key_".$i."' type=text style='width:100%;' value='' />";
				echo "	</td>";
			}
			echo "	<td>";
			echo "		<input id='".$uid."_value' type=text style='width:100%;' value='' />";
			echo "	</td>";
			
			echo "	</tr>";
			echo "</table>\n";
				
			echo "<hr>";	
		}
	}
} 
//------------------------------------------------------------------------------
function SendPacket2PerlServ(&$PACKET)
{
	define("_MAX_LINE", "4096");
	define("_MAX_PACKET", (_MAX_LINE / 2));

	// connect engin
	error_reporting(0); // E_ALL & ~E_DEPRECATED & ~E_NOTICE
	
	$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if( socket_connect($sock, $PACKET["SERV_IP"], $PACKET["SERV_PORT"]) == false ) {
		// if engine died "Connection refused"
		return socket_strerror(socket_last_error());
	}

	$today = date("Y-m-d").".".preg_replace("/^.+\//", ".", $_SERVER["PHP_SELF"]);

	$tx = "<PACKET>".$PACKET["TX"]."</PACKET>\n";
	
	if( strlen($tx) > _MAX_PACKET ) {
		$TOKEN = split(" ", $tx);
		$tx = "";
		
		for( $i=0 ; $i < count($TOKEN) ; $i++ ) {
			$tx .= $TOKEN[$i]." ";
			
			if( strlen($tx) > _MAX_PACKET ) {
				socket_write($sock, $tx);
				$tx = "";
			}
		}
	}
	
	if( $tx != "" ) {
		socket_write($sock, $tx);
	}	
	
	// RX buffering
	$cnt = 1000;
	$rx = "";
	do {
		$rx .= socket_read($sock, _MAX_LINE);	

		if( --$cnt < 0 ) {break;}
	} while( strpos($rx, "</PACKET>") === false );	
	
	socket_close($sock);

	// parse RX
	$rx = StripText($rx, "<PACKET>", "</PACKET>");

	$rx = preg_replace('#0x(\w\w\w\w)#e', "hex_to_utf8('$1$2')", trim($rx));
	
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE); // E_ALL & ~E_DEPRECATED & ~E_NOTICE
	
	return $rx;
}
//------------------------------------------------------------------------------
function conv_sql_value($_str)
{
	if( !isset($_str) ) return "";
	
	$_str = str_replace("'", "''", $_str);
	$_str = str_replace("&nbsp;", " ", $_str);
	
	return $_str;
}
//------------------------------------------------------------------------------
function get_prev_eval_result($_post_data, $user_email, $tag_name) {
	$sql = "SELECT src FROM ".$_post_data["_EVALUATION_RESULT_TABLE_NAME_"]." WHERE user_email='$user_email' AND tag='$tag_name'";

	$db_name = $_post_data["_SEARCH_BOX_PATH_"]."/".$_post_data["_EVALUATION_RESULT_DB_FILE_NAME_"];
	$db = new PDO("sqlite:$db_name");

	$sth = $db->prepare($sql);
	$sth->execute();

	$result = $sth->fetchAll();

	$db = NULL;

	return $result;
}
//------------------------------------------------------------------------------
function change_htpasswd($htpasswd, $user_email, $user_passwd) {
	if( !file_exists($htpasswd) ) return;
	
	$ret = array();

	$path = dirname($htpasswd);
	$fn = basename($htpasswd);

	$htpasswd_bak = $path."/htpasswd.history/$fn.".date("Y-m-d_H_i_s");

	$cmd = "cp $htpasswd $htpasswd_bak";	
	$cmd .= " ; grep -v '^$user_email:' $htpasswd_bak | grep -ve '^ *\$' > $htpasswd";
	
	if( $user_passwd != "" ) $cmd .= " ; htpasswd -nb $user_email $user_passwd >> $htpasswd";
	
	exec($cmd, $ret);
}
//------------------------------------------------------------------------------
function get_page_index_tag($uid, $page_no, $total_page, $func_prev, $func_next, $max_display) {
	$on_mouse = "onmouseover=\"this.style.cursor='pointer'\"";

	$buf = "";

	$buf .= "<table style='width:100%;'>";
	$buf .= "	<tr><td style='width:10px; text-align:left;'>";
	$buf .= "		<img src='/imgs/prev.png' $on_mouse title='Previous' onclick=\"$func_prev\">";
	$buf .= "	</td><td>";
	$buf .= "		<input type=text id='$uid' style='width:100%; text-align:center;' value='$page_no' onkeydown=\"if( event.keyCode == 13 ) { set_text_by_id('$uid', this.value-1); $func_next}\">";

	if( $total_page > 0 ) {
		$buf .= "</td><td style=''>";
		$buf .= "	$total_page";
	}

	if( $max_display > 0 ) {
		$buf .= "</td><td style=''>";
		$buf .= "	<input type=text id='".$uid."_max_display' style='width:100%; text-align:center;' value='$max_display'  onkeydown=\"if( event.keyCode == 13 ) { set_text_by_id('$uid', get_text_by_id('$uid')-1); $func_next}\">";
	}

	$buf .= "	</td><td style='width:10px; text-align:right;'>";
	$buf .= "		<img src='/imgs/next.png' $on_mouse title='Next' onclick=\"$func_next\">";
	$buf .= "	</td></tr>";
	$buf .= "</table>";

	return $buf;
}
//------------------------------------------------------------------------------
function conv_ma_form($str_ma, $lang) 
{
	$ret = "";
	if( $lang == "ke" ) {
		$str_ma = str_replace("<TAB>", "", $str_ma);
		
		$t = explode("<BR>", $str_ma);
		foreach( $t as $str ) {
			if( $str == "" ) continue;
			
			list($w1, $ma) = explode(" ", $str, 2);
			
			$ma = str_replace("+", " ", $ma);

			if( $ret != "" ) $ret .= " ";
			$ret .= $ma;
		}
	}
	
//	$ret = $str_ma;
	
	return $ret;
}
//------------------------------------------------------------------------------
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
//------------------------------------------------------------------------------
function array2xml($array) {
	$buf = "";
	foreach( $array as $key=>$item ) {
//		$buf .= "<$key><![CDATA[$item]]></$key>";
		$buf .= "<$key>$item</$key>";
	}

	return $buf;
}
//------------------------------------------------------------------------------
function file_exists_2($file)
{
	$ret = exec("ls $file");
	
	return (!empty($ret));
}
//------------------------------------------------------------------------------
function db_result_array($result) {
	$query_result = array();

	foreach( $result as $rows ) {
		$buf = "";
		for( $i=0 ; $i<sizeof($rows) ; $i++ ) {
			if( !isset($rows[$i]) ) continue;

			if( $buf != "" ) $buf .= "\t";
			$buf .= $rows[$i];
		}

//		$buf = join("\t", $rows);

		array_push($query_result, $buf);
	}

	return 	$query_result;
}
//------------------------------------------------------------------------------
function convert_to ( $source, $target_encoding ) {
    // detect the character encoding of the incoming file
    $encoding = mb_detect_encoding( $source, "auto" );

    echo "(encoding: $encoding)";

    // escape all of the question marks so we can remove artifacts from
    // the unicode conversion process
    $target = str_replace( "?", "[question_mark]", $source );

    // convert the string to the target encoding
    $target = mb_convert_encoding( $target, $target_encoding, $encoding);

    // remove any question marks that have been introduced because of illegal characters
    $target = str_replace( "?", "", $target );

    // replace the token string "[question_mark]" with the symbol "?"
    $target = str_replace( "[question_mark]", "?", $target );

    return $target;
}
//------------------------------------------------------------------------------
function diff($old, $new){
	$maxlen = -1;

	foreach($old as $oindex => $ovalue){
		$nkeys = array_keys($new, $ovalue);
		foreach($nkeys as $nindex){
			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
			$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
			if($matrix[$oindex][$nindex] > $maxlen){
				$maxlen = $matrix[$oindex][$nindex];
				$omax = $oindex + 1 - $maxlen;
				$nmax = $nindex + 1 - $maxlen;
			}
		}
	}

	if( !isset($maxlen) || $maxlen <= 0 ) return array(array('d'=>$old, 'i'=>$new));

	return array_merge(
				diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
				array_slice($new, $nmax, $maxlen),
				diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}
//------------------------------------------------------------------------------
function htmlDiff($old, $new, $token_type)
{
//	if( $old == "" || $new == "" ) return array("", $old, $new);
	
	mb_internal_encoding('UTF-8');
	mb_regex_encoding('UTF-8');

	if( !isset($token_type) ) {
		$old = mb_ereg_replace("\s+", "_", $old);
		$new = mb_ereg_replace("\s+", "_", $new);

		$old = mb_ereg_replace("(.)", "\\1 ", $old);
		$new = mb_ereg_replace("(.)", "\\1 ", $new);

	//	return array("", $old, $new);
	} else {
		$old = mb_ereg_replace("\s+", "_ ", $old);
		$new = mb_ereg_replace("\s+", "_ ", $new);
	}

	$diff = diff(explode(' ', $old), explode(' ', $new));

	$ret_a = $ret_b = "";

	$ret = "";
	foreach($diff as $k){
		if( is_array($k) ) {
			$ret .= (!empty($k['d'])?"<del>".implode('',$k['d'])."</del>"."":'').
					(!empty($k['i'])?"<ins>".implode('',$k['i'])."</ins>"."":'');

			$ret_a .= (!empty($k['d'])?"<font color=red>".implode('',$k['d'])."</font>"."":'');
			$ret_b .= (!empty($k['i'])?"<font color=red>".implode('',$k['i'])."</font>"."":'');
		} else {
			$ret .= $k . '';

			$ret_a .= $k . '';
			$ret_b .= $k . '';
		}
	}

	$ret_a = str_replace("_", " ", $ret_a);
	$ret_b = str_replace("_", " ", $ret_b);

	return array($ret, $ret_a, $ret_b);
}
//------------------------------------------------------------------------------
function get_hash_to_html_table($uid, $_data, $attr) {
	$max_col 	= $attr["max_col"];
	$font_size 	= $attr["font_size"];
	$text_align = $attr["text_align"];
	
	$key_diff_a = $attr["key_diff_a"];
	$key_diff_b = $attr["key_diff_b"];

	$key_width = $val_width = (100/($max_col*2))."%";

	if( isset($attr["key_width"]) && $attr["key_width"] != "" ) {
		$key_width = $attr["key_width"];
		$val_width = "*";
	}
	
	// get diff
	if( isset($key_diff_a) && isset($key_diff_b) ) {
		$val_diff_a = $val_diff_b = "";
		foreach( $_data as $k => $v ) {
			if( trim($v) == "" ) continue;
			
			if( $key_diff_a == $k ) $val_diff_a = $v;
			if( $key_diff_b == $k ) $val_diff_b = $v;
			
			if( $val_diff_a != "" && $val_diff_b != "" ) break;
		}
		
		list($a, $_data[$key_diff_a], $_data[$key_diff_b]) = htmlDiff($val_diff_a, $val_diff_b, 1);
	}	

	$ret = "";

	$col = 1;

	$ret .= "<table style='width:100%; border-color:gray; font-size:$font_size;' border=1>";
	$ret .= "	<tr>";
	foreach( $_data as $k => $v ) {
		if( trim($v) == "" ) continue;

		$ret .= "	<td style='width:$key_width; text-align:center; background-color:#D0D0D0;'>$k</td>";
		$ret .= "	<td id='".$uid."_$k' style='width:$val_width; text-align:$text_align;'>$v</td>";

		if( !($col++ % $max_col) ) $ret .= "</tr><tr>";
	}
	$ret .= "	</tr>";
	$ret .= "</table>";

	return $ret;
}
//------------------------------------------------------------------------------
function get_select_tag($_caption, $_id, $_option_list, $opt=array()) {
	if( !isset($opt["EVENT"]) ) $opt["EVENT"] = "";
	if( !isset($opt["CAPTION"]) ) $opt["CAPTION"] = "";
	if( !isset($opt["DEFAULT"]) ) $opt["DEFAULT"] = "";

	$buf = "";

	if( $opt["CAPTION"] != "NO_CAPTION" ) $buf .= "&nbsp; $_caption:";

	$buf .= "<select id='$_id' ".$opt["EVENT"].">";

	if( count($_option_list) != 1 ) {
		if( $opt["CAPTION"] == "NO_CAPTION" ) {
			$buf .= "	<option value=''>$_caption</option>";
		} else {
			$buf .= "	<option value=''>Select</option>";
		}
	}

	foreach( $_option_list as $k => $v ) {
		$selected = "";
		if( $opt["DEFAULT"] == $k ) $selected = "selected";
		
		$buf .= "	<option value='$k' $selected>$v</option>";
	}
	$buf .= "</select>";

	return $buf;
}
//------------------------------------------------------------------------------
function get_dic_list($_dic_path, $_file_header, $_none=1) {
	$cmd = "ls -1 $_dic_path 2>/dev/null";

	$list = array();
	exec($cmd, $list);

	if( count($list) <= 0 ) return;

	$ret = array();
	if( $_none ) array_push($ret, "NONE");
	foreach( $list as $line ) {
		$line = str_replace("$_dic_path/", "", $line);

		if( $_file_header != "*" && strpos($line, $_file_header) === false ) continue;

		array_push($ret, $line);
	}
	
	return $ret;
}
//------------------------------------------------------------------------------
function get_engine_list($_post_data) {
	$engine_path = $_post_data["_ENGINE_PATH_"];
	$file_header = $_post_data["file_header"];

	$cmd = "ls -1 $engine_path/$file_header* | grep -v conf";

	$list = array();
	exec($cmd, $list);

	if( count($list) <= 0 ) return;

	echo "NONE\n";
	foreach( $list as $line ) {
		$line = str_replace("$engine_path/", "", $line);
		echo "$line\n";
	}
}
//------------------------------------------------------------------------------
function array_to_hash($_data) {
	$ret = array();
	foreach( $_data as $str ) {
		$ret[$str] = $str;
	}
	
	return $ret;
}
//------------------------------------------------------------------------------
function get_file_list($_path, $_header) {
	$cmd = "";
	foreach( explode("|", $_header) as $fn ) {
		if( $cmd != "" ) $cmd .= ";";
		$cmd .= "ls -1 ".$_path."/".$fn."* 2>/dev/null";
	}

	if( $cmd == "" ) return $ret;

	$result = array();
	exec($cmd, $result);

	$ret = array();
	foreach( $result as $line ) {
		$line = preg_replace("/^.+\//", "", $line);
		
		if( $line == "" ) continue;
		
		array_push($ret, $line);
	}
	
	return $ret;
}
//------------------------------------------------------------------------------
function get_url($url) {
	$flag_get = 1;

    $info = parse_url($url);

    $host = $info["host"];

    $port = 80;
    if( isset($info["port"]) ) $port = $info["port"];

    $path = $info["path"];

	if( $flag_get ) {
	    if( isset($info["query"]) && $info["query"] != "" ) $path .= "?" . $info["query"];

	    $out = "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n";
	} else {
	    $out = "POST " . $path . " HTTP/1.1\r\n"
	        . "Host: " . $host . "\r\n"
	        . "Content-type: application/x-www-form-urlencoded\r\n"
	        . "Content-length: " . strlen($info["query"]) . "\r\n"
	        . "Connection: close\r\n\r\n" . $info["query"];
	}

    $fp = fsockopen($host, $port, $errno, $errstr, 30);

    if (!$fp) return "$errstr ($errno) <br>\n";

    fputs($fp, $out);

    $start = false;
    $retVal = "";
    while(!feof($fp)) {

        $tmp = fgets($fp, 1024);

        if ($start == true) $retVal .= $tmp;
        if ($tmp == "\r\n") $start = true;
    }

    fclose($fp);

    return $retVal;
}
//------------------------------------------------------------------------------
function get_post_data($_post_data) {
	$buf = "";
	foreach( $_post_data as $k => $v ) {
		$buf .= "<table style='width:100%' border=1><tr><td style='width:200px;'>$k</td><td>$v</td></table>\n";
	}

	return $buf;
}
//------------------------------------------------------------------------------

?>