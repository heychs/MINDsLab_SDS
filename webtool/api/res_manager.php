<?
//------------------------------------------------------------------------------
function res_download_file($_post_data)
{
    $file_name = $_post_data["filename"];
    $file_dir = $_post_data["_RES_MANAGER_HOME_"]."/";
    
    if( strpos($_post_data["filename"], "..") !== false ) return;
    if( !is_file($file_dir.$file_name) ) return;

    // 다운로드 방식을 구한다. 
    $ext = array_pop(explode(".", $file_name)); 
    
    $file_type = null;
    
    if ($ext=="avi" || $ext=="asf")         $file_type = "video/x-msvideo"; 
    else if ($ext=="mpg" || $ext=="mpeg")   $file_type = "video/mpeg"; 
    else if ($ext=="jpg" || $ext=="jpeg")   $file_type = "image/jpeg"; 
    else if ($ext=="gif")                   $file_type = "image/gif"; 
    else if ($ext=="png")                   $file_type = "image/png"; 
    else if ($ext=="txt")                   $file_type = "text/plain"; 
    else if ($ext=="zip")                   $file_type = "application/x-zip-compressed"; 
    
    if( file_exists($file_dir.$file_name) ) { 
        $fp = fopen($file_dir.$file_name, "rb"); 
    
        if( $file_type ) { 
            header("Content-type: $file_type"); 
            Header("Content-Length: ".filesize($file_dir.$file_name));     
            Header("Content-Disposition: attachment; filename=$file_name");   
            Header("Content-Transfer-Encoding: binary"); 
            header("Expires: 0"); 
        } else { 
            if( eregi("(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)", $_SERVER["HTTP_USER_AGENT"]) ){ 
                Header("Content-type: application/octet-stream"); 
                Header("Content-Length: ".filesize($file_dir.$file_name));     
                Header("Content-Disposition: attachment; filename=$file_name");   
                Header("Content-Transfer-Encoding: binary");   
                Header("Expires: 0");   
            } else{ 
                Header("Content-type: file/unknown");     
                Header("Content-Length: ".filesize($file_dir.$file_name)); 
                Header("Content-Disposition: attachment; filename=$file_name"); 
                Header("Content-Description: PHP3 Generated Data"); 
                Header("Expires: 0"); 
            } 
        } 
    
    
        fpassthru($fp); 
        fclose($fp); 
    } 
    
    return; 
} 
//------------------------------------------------------------------------------
function res_move_directory($_post_data) {
    if( strpos($_post_data["source_path"], "..") !== false ) return;
    if( strpos($_post_data["target_path"], "..") !== false ) return;
    
    $source_path = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["source_path"];
    $target_path = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["target_path"];
    
    $post_result = array("msg" => "error");
    
    if( !rename($source_path, $target_path) ) {
        $err = error_get_last();
        if( $err["message"] != "" ) {
            $post_result["error_msg"] = $err["message"];
            $post_result["msg"] = preg_replace("/^.+:\s*/", "", $err["message"]);
        }

        echo json_encode($post_result);
        return;
    }

    $post_result["msg"] = "ok";

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function res_upload_file($_post_data) {
    error_reporting(E_ALL | E_STRICT);
    
    include_once("res_manager.upload.php");
    
    $options = array(
        "upload_dir" => isset($_post_data["upload_dir"]) ? $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["upload_dir"]."/" : $_post_data["_RES_MANAGER_HOME_"]."/",
        "param_name" => "files",
        "max_file_size" => null,
        "min_file_size" => 1,
        "accept_file_types" => "/.+$/i",
        "max_number_of_files" => null,
        "discard_aborted_uploads" => true
    );
    
    $upload_handler = new UploadHandler($options);
    
    header("Pragma: no-cache");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Content-Disposition: inline; filename=\"files.json\"");
    header("X-Content-Type-Options: nosniff");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size");

    $upload_handler->post();
}
//------------------------------------------------------------------------------
function res_delete_data_file($_post_data)
{
    if( strpos($_post_data["filename"], "..") !== false ) return;
    
    $filename = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["filename"];
    
    $post_result = array("msg" => "error");
    
    if( !is_file($filename) ) {
        $post_result["msg"] = "This is not file.";

        echo json_encode($post_result);
        return;
    }
    
    if( !unlink($filename) ) {
        $err = error_get_last();
        if( $err["message"] != "" ) {
            $post_result["error_msg"] = $err["message"];
            $post_result["msg"] = preg_replace("/^.+:\s*/", "", $err["message"]);
        }

        echo json_encode($post_result);
        return;
    }

    $post_result["msg"] = "ok";

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function res_add_directory($_post_data)
{
    if( strpos($_post_data["path"], "..") !== false ) return;
    
    $path = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"];

    $post_result = array("msg" => "ok", "path" => $path);     
    if( !mkdir($path) ) {
        $post_result["msg"] = "Failed to create folders...";
        
        $err = error_get_last();
        if( $err["message"] != "" ) {
            $post_result["error_msg"] = $err["message"];
            $post_result["msg"] = preg_replace("/^.+:\s*/", "", $err["message"]);
        }

        echo json_encode($post_result);
        return;   
    }    

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function res_delete_directory($_post_data)
{
    if( strpos($_post_data["path"], "..") !== false ) return;
    
    $path = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"];

    $post_result = array("msg" => "ok", "path" => $path);     
    if( !is_dir($path) || !rmdir($path) ) {
        $post_result["msg"] = "Failed to delete folders...";
        
        $err = error_get_last();
        if( $err["message"] != "" ) {
            $post_result["error_msg"] = $err["message"];
            $post_result["msg"] = preg_replace("/^.+:\s*/", "", $err["message"]);
        }

        echo json_encode($post_result);
        return;   
    }    

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function file_download_testing($_post_data)
{
    // set the header values 
    header("Content-Type: application/force-download\n");
    header("Content-Disposition: attachment; filename=".$_GET['fileName']);
     
    //set the value of the fields in Opened dailog box
    header('Content-Disposition: attachment; filename="'.$_GET['fileName'].'"');
     
    // echo the content to the client browser
    readfile($_GET['fileName']);
} 
//------------------------------------------------------------------------------
function res_read_file_list($_post_data)
{
    if( strpos($_post_data["path"], "..") !== false ) return;
    
    $path = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"];
    
    // read file
    $listFile = array();
    if( $handler = opendir($path) ) {
        while( ($sub = readdir($handler)) !== FALSE ) {
            if( $sub == "." || $sub == ".." ) continue;

            // hidden file
            if( strpos($sub, ".") !== false && strpos($sub, ".") == 0 ) continue;
            
            $filename = $path."/".$sub;
            if( !is_dir($filename) ){
                
                $size = filesize($filename);    
                $fileinfo = pathinfo($filename);
                 
                array_push($listFile, array(
                    "name" => $sub, 
                    "size" => $size, 
                    "dirname" => $_post_data["path"],
                    "extension" => $fileinfo["extension"]
                )); 
            }
        }
        
        closedir($handler); 
    } 

    echo json_encode($listFile);
}
//------------------------------------------------------------------------------
function res_tree_build_data($_post_data)
{
    if( strpos($_post_data["path"], "..") !== false ) return;
    
    $data_path = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"];
    
    $ret = get_directory_list($_post_data, $data_path);
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function res_tree_build_category($_post_data)
{
    $res_home = $_post_data["_RES_MANAGER_HOME_"];
    
    $ret = get_directory_list($_post_data, $res_home, array("data"=>"1"));
    echo json_encode($ret);
}
//------------------------------------------------------------------------------
function get_directory_list($_post_data, $dir, $filter = array(), $listDir = array())
{
    if( !is_dir($dir) ) return;
    
    $listDir = array();
    if( $handler = opendir($dir) ) {
        while( ($sub = readdir($handler)) !== FALSE ) {
            if( $sub == "." || $sub == ".." ) continue;

            // hidden file
            if( strpos($sub, ".") !== false && strpos($sub, ".") == 0 ) continue;
            
            if( is_dir($dir."/".$sub) ){
                if( isset($filter[$sub]) ) {
                    $listDir[$sub] = array();
                    break;   
                }
                
                $listDir[$sub] = get_directory_list($_post_data, $dir."/".$sub, $filter); 
            } 
        }
        
        closedir($handler); 
    } 
    
    return $listDir;    
}
//------------------------------------------------------------------------------
function res_delet_wiki_meta_data($_post_data)
{
    if( strpos($_post_data["path"], "..") !== false ) return;
    
    $fn = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"]."/meta.wiki";
    $fn_bak = $fn.".".date("Y-m-d_H_i_s");
        
    $post_result = array("msg" => "error");
    
    if( !is_file($fn) ) {
        $post_result["msg"] = "This is not file.";

        echo json_encode($post_result);
        return;
    }
    
    if( !rename($fn, $fn_bak) ) {
        $err = error_get_last();
        if( $err["message"] != "" ) {
            $post_result["error_msg"] = $err["message"];
            $post_result["msg"] = preg_replace("/^.+:\s*/", "", $err["message"]);
        }

        echo json_encode($post_result);
        return;
    }

    $post_result["msg"] = "ok";

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function res_read_wiki_meta_data($_post_data)
{
    if( strpos($_post_data["path"], "..") !== false ) return;
    
    $fn = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"]."/meta.wiki";
    
    $post_result = array("wiki_meta_data" => "", "dir_list" => "");
    
    if( file_exists($fn) ) {
        $contents = file_get_contents($fn, false);  
        
        // remove BOM  
        $post_result["wiki_meta_data"] = preg_replace("|^\xEF\xBB\xBF|", "", $contents);
    } else {
        // read tree folder
        $post_result["error"] = "$fn not exits.";
    }    
    
    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function res_get_new_wiki_meta_data($_post_data)
{
    $fn = $_post_data["_RES_MANAGER_HOME_"]."/default.wiki";
    $fn_target = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"]."/meta.wiki";
    
    $post_result = array("msg" => "ok");

    if( !copy($fn, $fn_target) ) {
        $post_result["msg"] = "error";

        $err = error_get_last();
        if( $err["message"] != "" ) {
            $post_result["error_msg"] = $err["message"];
            $post_result["msg"] = preg_replace("/^.+:\s*/", "", $err["message"]);
        }
    }

    echo json_encode($post_result);
}
//------------------------------------------------------------------------------
function res_save_wiki_meta_data($_post_data)
{
    if( strpos($_post_data["path"], "..") !== false ) return;
    
    $fn = $_post_data["_RES_MANAGER_HOME_"]."/".$_post_data["path"]."/meta.wiki";
    $fn_bak = $fn.".".date("Y-m-d_H_i_s");

    $contents = $_post_data["contents"];
    
    exec("sync; cp \"$fn\" \"$fn_bak\"; sync");
    
    file_put_contents($fn, $contents);
    
    res_read_wiki_meta_data($_post_data);
}
//------------------------------------------------------------------------------
?>