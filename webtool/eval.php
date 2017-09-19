<!DOCTYPE html>
<html>
    <head>
    	<meta http-equiv="Content-Type" content="text/html;" charset="UTF-8" />
    	<title>대화 시스템 평가 도구</title>

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

        <!-- ASR -->        
        <script src="/jquery/swfobject.js"></script>        
        <script src="js/recorder.js"></script>     


        <script src="js/eval.js"></script>     
        
        <style>
        	.button_space {
        		height: 2px;
        	}
        	.caption {
        		padding-left: 5px;
        		width: 180px;
        	}
        </style>     

    </head>
<body onload="eval_onload()">

<div class="easyui-panel" id="eval_main_panel" title="Evaluation Dialog System" fit="true" border="false" style="padding: 5px; overflow: hidden;">  
	<div class="button_space"></div>
	
	<table border=1>
<!--
		<tr><td class="caption">
			City Tour
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('city_tour', 'asr_english');"> #1 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('city_tour', 'asr_english');"> #2 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_evaluation_form('city_tour');"> Evaluation </a>
		</td></tr>

		<tr><td class="caption">
			Immigration
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('immigration', 'asr_english');"> #1 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('immigration', 'asr_english');"> #2 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_evaluation_form('immigration');"> Evaluation </a>
		</td></tr>

		<tr><td class="caption">
			Hotel Reservation
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('hotel_reservation', 'asr_english');"> #1 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('hotel_reservation', 'asr_english');"> #2 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_evaluation_form('hotel_reservation');"> Evaluation </a>
		</td></tr>

		<tr><td class="caption">
			Hotel Check In
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('hotel_check_in', 'asr_english');"> #1 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('hotel_check_in', 'asr_english');"> #2 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_evaluation_form('hotel_check_in');"> Evaluation </a>
		</td></tr>

		<tr><td class="caption">
			Carvatar
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('carvatar', 'asr_korean');"> #1 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_select_mission('carvatar', 'asr_korean');"> #2 </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_evaluation_form('carvatar');"> Evaluation </a>
		</td></tr> 
-->		
		<tr><td class="caption">
			dialog
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_dialog_system_for_dial_evaluation('dialog', 'asr_korean', 'group_a');"> group a </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_dialog_system_for_dial_evaluation('dialog', 'asr_korean', 'group_b');"> group b </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_dialog_system_for_dial_evaluation('dialog', 'asr_korean', 'group_c');"> group c </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_dialog_system_for_dial_evaluation('dialog', 'asr_korean', 'group_d');"> group d </a>
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_evaluation_form('dialog');"> Evaluation </a>
		</td></tr> 
		
	</table>
	
	<div class="button_space"></div>

	<table border=0>
		<tr><td>
			Questionnaire : 
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_questionnaire_form('dialog');"> Questionnaire </a>
		</td></tr>
	</table>	
	
	<table border=0>
		<tr><td>
			ASR Server : 
		</td><td>
			<a href="#" class="easyui-linkbutton" iconCls="" onclick="eval_open_asr_server();"> ASR Server </a>
		</td></tr>
	</table>	
	
	<div class="button_space"></div>

</div>
        
</body>
</html>