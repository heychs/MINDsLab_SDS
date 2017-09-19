<?
	$user_email = $user_pw = "";

	$remote_ip = getenv('REMOTE_ADDR');
	if( isset($AUTO_LOGIN["ALIAS_IP"][$remote_ip]["PASSWD"]) ) {
		$user_pw = $AUTO_LOGIN["ALIAS_IP"][$remote_ip]["PASSWD"];
	}

	if( isset($AUTO_LOGIN["ALIAS_IP"][$remote_ip]["USER_EMAIL"]) ) {
		$user_email = $AUTO_LOGIN["ALIAS_IP"][$remote_ip]["USER_EMAIL"];
	}

//	$user_email = $user_pw = "carvatar";
	$user_email = $user_pw = "carvatar";
?>
<!DOCTYPE html>
<html>
    <head>
    	<meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
    	<title>웹 워크벤치</title>

    	<link rel="stylesheet" type="text/css" href="/jquery/easyui/themes/default/easyui.css" />
    	<link rel="stylesheet" type="text/css" href="/jquery/easyui/themes/icon.css" />

    	<link rel="stylesheet" type="text/css" href="style/style.css" />
        <link rel="stylesheet" type="text/css" href="style/dialog_act_tagger.css" />

    	<link rel="stylesheet" type="text/css" href="style/icon_extension.css" />        
    	<link rel="stylesheet" type="text/css" href="style/icon.css" />        
    	<link rel="stylesheet" type="text/css" href="style/xml_dic.css" />
        <link rel="stylesheet" type="text/css" href="style/wiki_meta.css" />
        
        <link rel="stylesheet" type="text/css" href="style/workbench.css" />
        <link rel="stylesheet" type="text/css" href="style/res_manager.css" />
        <link rel="stylesheet" type="text/css" href="style/admin.css" />
        <link rel="stylesheet" type="text/css" href="style/trac.css" />

        <link rel="stylesheet" type="text/css" href="style/dialog_act_tagger.css" />
        <link rel="stylesheet" type="text/css" href="style/dialog_act_tagger_guide.css" />

        <!-- jquery include -->
        <script src="/jquery/jquery-1.7.1.js"></script>
        <script src="/jquery/jquery.jsoncookie.js"></script>
        <script src="/jquery/jquery.xml2json.js"></script>
        <script src="/jquery/jquery.globalstylesheet.js"></script>

    	<script src="/jquery/easyui/jquery.easyui.min.js"></script>
        
        <!-- fileupload include -->
        <script src="/jquery/blueimp/js/vendor/jquery.ui.widget.js"></script>
        <script src="/jquery/blueimp/js/jquery.iframe-transport.js"></script>
        <script src="/jquery/blueimp/js/jquery.fileupload.js"></script>
        <script src="/jquery/blueimp/js/jquery.fileupload-ui.js"></script>
        
        <script src="/jquery/blueimp/js/tmpl.min.js"></script>
        <script src="/jquery/blueimp/js/locale.js"></script>
        
        <!-- include -->
    	<script src="js/xml2treejson.js"></script>
        
        <!-- include -->
        <script src="js/workbench.js"></script>
        <script src="js/auth_code.js"></script>
        <script src="js/admin.js"></script>
        <script src="js/res_manager.js"></script>

        <script src="js/GoogleCodeWikiParser.js"></script>
  
        <script src="js/common.js"></script>
        <script src="js/session.js"></script>
		<script src="js/user_auth.js"></script>
        
        <!-- dialog_act_tagger -->
        <script src="js/dialog_act_tagger.js"></script>
		<script src="js/dialog_act_tagger.task_manager.js"></script>
		<script src="js/dialog_act_tagger.task_manager_edit.js"></script>
		<script src="js/dialog_act_tagger.web_dialog_system.js"></script>
		<script src="js/dialog_act_tagger.slot_structure.js"></script>        
		<script src="js/dialog_act_tagger.extra_db.js"></script>
		<script src="js/dialog_act_tagger.dialog_library.js"></script>   
		<script src="js/dialog_act_tagger.file.js"></script>   
		<script src="js/dialog_act_tagger.da_type.js"></script>   
		<script src="js/sprintf.js"></script>        

        <script src="js/dialog_act_tagger_guide.js"></script>
		
        <!-- include -->
		<script type="text/javascript" src="js/g_variable.js"></script>
		<script type="text/javascript">
            g_variable["DEFAULT_USER_NAME"] = "<? echo $user_email; ?>"; 
            g_variable["DEFAULT_USER_PW"]   = "<? echo $user_pw; ?>"; 
        </script>

        <!-- ASR -->        
        <script src="/jquery/swfobject.js"></script>        
        <script src="js/recorder.js"></script>     

        <script>
        function test() {
        	$.cookie('user_name', 'gnb'); 
        	$.cookie('project_name', 'gnb'); 
        	
        	g_variable["_POST_URL_"] = "http://mt.etri.re.kr/webtool/api/main.php";
        	
        	dialog_action_tagger_open_dialog_system();
        }
        </script>     

    </head>
<body onload="test();">

        
</body>
</html>