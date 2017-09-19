<?
//------------------------------------------------------------------------------
function get_auth_code($_post_data)
{
	include_once $_post_data["_AUTH_CODE_FILE_NAME_"];
    
    echo json_encode($_AUTH_CODE);
}
//------------------------------------------------------------------------------
function tail_log($_post_data)
{
	$fn = $_post_data["file_name"];
	$type = $_post_data["type"];
	$start_line_num = $_post_data["start_line_num"];

	// get line count
	$result = array("max_cnt" => -1, "msg" => "OK", "result" => "");

    if( file_exists($fn) ) {
    	$ret = array();	
    	exec("wc -l $fn", $ret);
    
    	list($max_cnt) = explode(" ", trim($ret[0]));
    	$current = $max_cnt - $start_line_num;
        
        $result["max_cnt"] = $max_cnt;

    	if( $current > 1000 ) $current = 1000;
        
        if( $current > 0 ) {
        	// tail log file 
    		$ret = array();
// 		// $cmd = "tail -n $current $fn 2>&1";
    		$cmd = "grep -n '' $fn | tail -n $current 2>&1";
            
    		exec($cmd, $ret);
    		
    		$result["result"] = join("\n", $ret);
        }
    } else {
		$result["msg"] = "ERROR: $fn is not exists.";
    }
    
    // echo result
    if( isset($type) && $type == "XML" ) {
        echo array2xml_cdata($result);
    } else {
    	echo json_encode($result);
    }		
}
//------------------------------------------------------------------------------
function array2xml_cdata($array) {
	$buf = "";
	foreach( $array as $key=>$item ) {
		$buf .= "<$key><![CDATA[$item]]></$key>";
	}

	return $buf;
}
//------------------------------------------------------------------------------
function save_auth_code($_post_data, $_auth_code)
{
	$rows = $_post_data["rows"];
    
    if( !isset($rows) || $rows == "" ) return;
    
    $list = json_decode($rows, true);
    
    // merge auth code
    $b_save = false;
    foreach( $list as $obj ) {
        $auth_code  = $obj["auth_code"];
        $request    = $obj["request"];
        $tag        = $obj["tag"];
        $val        = $obj["val"];
        
        if( !isset($_auth_code[$auth_code][$request][$tag]) || $_auth_code[$auth_code][$request][$tag] != $val ) $b_save = true;
        
        $_auth_code[$auth_code][$request][$tag] = $val;
    }
    
    // save auth code
    if( $b_save == false ) return;
    
    $buf = "";
    foreach( $_auth_code as $auth_code => $request_list ) {
        foreach( $request_list as $request => $tag_list ) {
            foreach( $tag_list as $tag => $val ) {
                $buf .= "\$_AUTH_CODE[\"$auth_code\"][\"$request\"][\"$tag\"] = \"$val\";\n";
            }
            
            $buf .= "\n";
        }
        $buf .= "\n\n";
    }    
    
	$ret = array();
	
	$fn = $_post_data["_AUTH_CODE_FILE_NAME_"];
	$fn_target = $_post_data["_AUTH_CODE_BACKUP_PATH_"]."/".basename($_post_data["_AUTH_CODE_FILE_NAME_"]).".".date("Y-m-d_H_i_s").".php";
	
	$cmd = "cp $fn $fn_target";
	exec($cmd, $ret);
	
	$fh = fopen($fn, "w");
	fwrite($fh, "<?\n".$buf."\n?>");
	fclose($fh);
}
//------------------------------------------------------------------------------
function delete_pid($_post_data)
{
	$_pid = $_post_data["pid"];
	$_ip  = $_post_data["ip"];

	$cmd = "\$SSH_PASS $_ip \" kill -9 $_pid \"";
	exec($cmd);
}
//------------------------------------------------------------------------------
function get_daemon_status($_post_data)
{
	$deamon_tag = ( $_post_data["deamon_tag"] != "" ) ? $_post_data["deamon_tag"] : "^pub";
    
	$ip_list = ( isset($_post_data["ip_list"]) ) ? $_post_data["ip_list"] : $_post_data["_SERV_LIST_"];
		
    $result = array();
        
	foreach( explode(",", $ip_list) as $ip ) {
		$ret = array();
		
		$cmd_shell = "\$PS | grep '$deamon_tag' | tr -s ' ' 2>&1";
        
		$cmd = "\$SSH_PASS ".$ip." \"$cmd_shell\" ";		
		exec($cmd, $ret);
        
		foreach( $ret as $line ) {
			if( strpos($line, "grep ") !== false ) continue;
			if( strpos($line, "apache2 ") !== false ) continue;
//			if( strpos($line, "run.daemon.farm.pl") === false ) continue;
	
			$uid = uniqid();
			
			$list = explode(" ", $line, 3);
			
			$pid = $list[1];
			$str = $list[2];
            
            $tag = preg_replace("/^.+? -tag (\S+?) -.+$/", "$1", $str);

			if( strpos($str, "sshd: pub@notty") !== false ) continue;
			if( strpos($str, "ps ") !== false ) continue;
			if( strpos($str, "tr ") !== false ) continue;
			if( strpos($str, "smbd ") !== false ) continue;
            
            array_push($result, array("ip" => $ip, "pid" => $pid, "cmd" => $str, "tag" => $tag) );
		}
	}
    
    echo json_encode($result);
}
//------------------------------------------------------------------------------
?>