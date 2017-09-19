<?
//------------------------------------------------------------------------------
include_once "config.php";

if( !defined("DONT_CHECK_IP") ) include_once "check_ip.php";
//------------------------------------------------------------------------------
function conv_sql_value($_str)
{
	if( !isset($_str) ) return "";
	
	$_str = str_replace("'", "''", $_str);
	$_str = str_replace("&nbsp;", " ", $_str);
	
	return $_str;
}
//------------------------------------------------------------------------------
function get_daemon_cmd($_act_page, $_engine, $_dic, $_domain, $_serv_ip, $_port, $_cmd, $_encoding, $_args="")
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
	
	$fn_log = "/build_farm/log/$_act_page.$_domain.$_port.log";
	
	if( $_args != "" ) $_args = "-args $_args";
	
	$ret = $GLOBALS["SSH_PASS"]." $_serv_ip \"nohup ".
				"   perl /build_farm/bin/run.daemon.farm.pl ".
				"      -tag      $_act_page".
				"      -engine   $_engine".
				"      -dic      $_dic".
				"      -port     $_port".
				"      -cmd      $cmd".
				"      -encoding $encoding".
				"      -encoding_out $encoding".
				"      -conf     $conf".
				"      $_args".
				"      >>        $fn_log 2>&1 &\" ";
	
//	error_log("(".__LINE__.") ".$ret);			
	
	return $ret;
}
//------------------------------------------------------------------------------
function get_daemon_cmd_new($_act_page, $_engine, $_dic, $_domain, $_serv_ip, $_port, $_cmd, $_encoding, $_args="")
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
	
	$fn_log = "/home_mt/pub/build_farm/log/$_act_page.$_domain.$_port.log";
	
	if( $_args != "" ) $_args = "-args $_args";
	
	$ret = $GLOBALS["SSH_PASS"]." $_serv_ip \"nohup ".
				"   perl /home_mt/pub/build_farm/bin/run.daemon.farm.new.pl ".
				"      -tag      $_act_page".
				"      -engine   $_engine".
				"      -dic      $_dic".
				"      -port     $_port".
				"      -cmd      $cmd".
				"      -encoding $encoding".
				"      -encoding_out $encoding".
				"      -conf     $conf".
				"      $_args".
				"      >>        $fn_log 2>&1 &\" ";
	
//	error_log("(".__LINE__.") ".$ret);			
	
	return $ret;
}
//------------------------------------------------------------------------------
function check_file_exits($_sshpass, $_fn) {
	$cmd = "$_sshpass \" ls -1 $_fn 2>&1 \"";	

	$list = array();
	exec($cmd, $list);

	if( count($list) > 0 && strpos($list[0], "No such file or directory") === false ) {
		return true;
	}
	return false;
}
//------------------------------------------------------------------------------
function get_date_str() {
	return date("Y-m-d_H_i_s");
}
//------------------------------------------------------------------------------
function write_log($_fn, $_contents) 
{
	$_contents = str_replace("\r\n", "\n", $_contents);
	$f = file_put_contents($_fn, $_contents, FILE_APPEND);
}
//------------------------------------------------------------------------------
function upload_file($_sshpass, $_fn, $_contents) {
	$fn_tmp = "/tmp/".uniqid(get_date_str()).".tmp";
	
	$_contents = str_replace("\r\n", "\n", $_contents);

	$f = file_put_contents($fn_tmp, $_contents);
	
	// upload tst
	$cmd = "$_sshpass \" scp $fn_tmp $_fn \" ; rm -f $fn_tmp ";
	
//	echo $cmd;
	
	system($cmd);
}
//------------------------------------------------------------------------------
function print_run_time($_tag, $_time) {
	echo "<pre>\n";
	echo "$_tag: ".sec_to_time($_time)."\n";
	echo "</pre>";	
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
function WriteRemoteLog($log, $fn) 
{
	$log = str_replace("'", "", $log);	
	system("echo '[".date('Y-m-d h:i:s')."] \n\n\"".$log."\"' >> ".$fn." 2>&1");
}
//------------------------------------------------------------------------------
function get_param($_get, $_post)
{
	$list = array();
	foreach( $_get  as $k=>$v ) $list[$k] = $v;	
	foreach( $_post as $k=>$v ) $list[$k] = $v;	
	
	return $list;
}
//------------------------------------------------------------------------------
function StripText($STR, $L_TAG, $R_TAG, $del=0)
{
	if( strpos($STR, $L_TAG) === false ) return "";
	
	$st = strpos($STR, $L_TAG) + strlen($L_TAG);
	
	return substr($STR, $st, strrpos($STR, $R_TAG) - $st);
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
	
//	return $buf_ret;
}
//------------------------------------------------------------------------------
function SendPacket(&$PACKET)
{
	define("_MAX_LINE", "4096");
	define("_MAX_PACKET", (_MAX_LINE / 2));

	$PACKET["TX"] = "<PACKET>".
					"<CMD>TRANS_TEXT</CMD>".
					"<USER_DIC></USER_DIC>".
					"<ASK></ASK>".
					"<ORG>".$PACKET["TX"]."</ORG>".
				   "</PACKET>";

	
	// connect engin
	$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if( socket_connect($sock, $PACKET["SERV_IP"], $PACKET["SERV_PORT"]) == false ) {
		echo "[SOCKET ERROR] error code is: ".socket_last_error(). ", error message is: ". socket_strerror(socket_last_error());
		return;
	}
	
	$STX = sprintf("%c%c", 0x1b, 0x02);		
	$ETX = sprintf("%c\n", 0x03);		

	$BUF = $PACKET[TX];	
	if( strlen($BUF) > _MAX_PACKET ) {
		$TOKEN = split(" ", $BUF);
		$BUF = "";
		
		for( $i=0 ; $i < count($TOKEN) ; $i++ ) {
			$BUF .= $TOKEN[$i]." ";
			
			$LENGTH = sprintf("%c%c", ((strlen($BUF)>>7)&0x7f)|0x80, (strlen($BUF)&0x7f)|0x80);	
			if( strlen($BUF) > _MAX_PACKET ) {
				$TX = $STX."C".$LENGTH.$BUF.$ETX;	
				if( $i == count($TOKEN)-1 ) {
					$TX = $STX."F".$LENGTH.$BUF.$ETX;	
				}
				socket_write($sock, $TX);
				
				$RX = socket_read($sock, _MAX_LINE);
				$BUF = "";
			}
		}
	}
	
	if( $BUF != "" ) {
		$LENGTH = sprintf("%c%c", ((strlen($BUF)>>7)&0x7f)|0x80, (strlen($BUF)&0x7f)|0x80);	
		$TX = $STX."F".$LENGTH.$BUF.$ETX;	
		socket_write($sock, $TX);
	}	
	
	$BUF = "";
	$RX_BUF = "";	
	do {
		$RX = socket_read($sock, _MAX_LINE);	
		$BUF .= $RX;
		
		$RX_STX = substr($BUF, 0, 2);
		$CONT   = substr($BUF, 2, 1);
		$RX_ETX = substr($BUF, strlen($BUF)-2, 2);
		
		if( $RX_STX != $STX || $RX_ETX != $ETX ) {
			$CONT = "";
			continue;
		}

		$BUF = substr($BUF, 5, strlen($BUF)-7);
		$line = iconv("EUC-KR", "UTF-8", $BUF);
		
		$line = ereg_replace("0x([A-Fa-f0-9][A-Fa-f0-9][A-Fa-f0-9][A-Fa-f0-9])", "&#x\\1;", $line);	
		echo trim($line);
		
		$BUF = "OK";
		$LENGTH = sprintf("%c%c", ((strlen($BUF)>>7)&0x7f)|0x80, (strlen($BUF)&0x7f)|0x80);	
		$TX = $STX."F".$LENGTH.$BUF.$ETX;
		socket_write($sock, $TX);
		
		$BUF = "";
	} while( $CONT != "F" ); 
	$RX = $RX_BUF;
	
	socket_close($sock);
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
function print_globals($arr, $k_buf, $uid, &$k_list)
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
			print_globals($v, $k_buf, $uid, $k_list);
		} else {
			$list = array();
			for( $i=0 ; $i<3 ; $i++ ) {
				$str = ( isset($k_buf[$i]) ) ? $k_buf[$i] : "";
				array_push($list, $str);
			}
			
			$bg_color = "";
//			if( $list[1] ) $bg_color = "bgcolor='black'";
			
			echo "<table style='width:100%;' border=1 $bg_color>";
			echo "	<tr>";
			
			$cnt_k = 0;
			foreach( $list as $sub_k ) {				
				echo "	<td style='width:15%;'>";
				echo "		<input id='$uid.key.$cnt_k' type=text style='width:100%;' value=\"$sub_k\" />";
				echo "	</td>";
				
				$cnt_k++;
			}
			echo "	<td>";
			echo "		<input id='$uid.value' type=text style='width:100%;' value=\"$v\" />";
			echo "	</td>";

			if( $list[2] == "ENGINE" || $list[2] == "DIC" ) {
				echo "	<td style='text-align:center; width:20px;'>";
								
				if( file_exists($v) || is_dir($v) ) {
					if( is_dir($v) ) {
						$path = $v;
						
						$path = str_replace("/home_mt/", "file:////mt.etri.re.kr/", $path);
						
						echo "<img src=/img/folder_icon.png onmouseover=\"this.style.cursor='pointer'\" onclick=\"window.open('$path', '');\" />";	
					} else {
						echo "<img src=/img/file_o.png />";
					}
				} else {
					echo "	<img src=/img/file_x.png />";					
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
				echo "		<input id='$uid.key.$i' type=text style='width:100%;' value='' />";
				echo "	</td>";
			}
			echo "	<td>";
			echo "		<input id='$uid.value' type=text style='width:100%;' value='' />";
			echo "	</td>";
			
			echo "	</tr>";
			echo "</table>\n";
				
			echo "<hr>";	
		}
	}
} 
//------------------------------------------------------------------------------
function get_cluster_status()
{
	$list_as_tag = array();
	
	$cnt = 0;
	foreach( $GLOBALS["SERV_IP"] as $_host => $_ip ) {
		$cmd = $GLOBALS["SSH_PASS"]." ".$_ip." \" ps -ef | grep ^pub | tr -s ' ' | cut -d' ' -f1,2,3,8- 2>&1 \" ";
	
		$list = array();
		exec($cmd, $list);
	
		if( count($list) > 0 ) {
			$sorted_list = array();
			foreach( $list as $line ) {
				list($id, $pid, $gid, $cmd) = explode(" ", $line, 4);
	
				$cmd = substr(preg_replace("/\/\S+\//", "", $cmd), 0, 100);

				$tag = "";
				$tag = preg_replace("/^.+? -tag (\S+?) -.+$/", "$1", $cmd);
				
				if( $tag == "" || $cmd == $tag ) $tag = "ETC";
	
				if( strpos($cmd, "run.daemon.") === false ) $tag = "ETC";
				
				$list_as_tag[$tag][$pid] = "$_ip\t$id\t$pid\t$gid\t$cmd";
			}
		}
	}
	
//	ksort($list_as_tag);
	foreach( $list_as_tag as $tag => $list ) {
		$display = "";
		if( $tag == "ETC" ) $display = "display:none;";
		
		$uid = uniqid();

		echo "<table border=1 width=100%>";
		echo "	<tr>";
		echo "		<td bgcolor=lightgray>";
		if( $display != "" ) echo "	<img src=/img/open.png  style='cursor:hand;' onclick=\"dojo.byId('$uid').style.display='inline';\">";
		echo "			$tag";
		echo "		</td>";
		echo "	</tr>";	
		echo "</table>";
	
		echo "<div id='$uid' style='$display'>\n";
		foreach( $list as $pid => $line ) {
			list($_ip, $id, $pid, $gid, $cmd) = explode("\t", $line, 5);
	
			$uid = uniqid();
					
			echo "<table border=1 width=100%>";
				echo "<tr>";
	
				echo "	<td style='width:50px;'>$_ip</td>";
				echo "	<td>$cmd</td>";
	
				echo "	<td style='width:10px;'>";
				echo "		<span id='$uid'>";
				echo "			<img border=0 src=/img/delete.png alt='delete' onclick='delete_pid(\"$_ip\", \"$pid\", \"$uid\")' style='cursor:hand;'>";
				echo "		</span>";
				echo "	</td>";
	
				echo "</tr>";
			echo "</table>";
		}
		echo "</div>\n";
	}
}
//------------------------------------------------------------------------------
function delete_pid()
{
	$_pid     = $_POST["pid"];
	$_ip      = $_POST["ip"];

	$_sshpass = $GLOBALS["SSH_PASS"]." ".$_ip;

	$cmd = "$_sshpass \" kill -9 $_pid \"";
	system($cmd);
}
//------------------------------------------------------------------------------
?>