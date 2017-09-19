<?
//------------------------------------------------------------------------------
include_once "common.php";
// include_once "admin.php";
// include_once "workbench.php";
include_once "auth_code.php";
// include_once "res_manager.php";
include_once "dialog_act_tagger.php";
include_once "dialog_act_tagger.guide.php";
include_once "dialog_act_tagger.da_type.php";
include_once "dialog_act_tagger.dialog_library.php";
include_once "dialog_act_tagger.extra_db.php";
include_once "dialog_act_tagger.slot_structure.php";
include_once "dialog_act_tagger.task_manager.php";
include_once "dialog_system.php";
include_once "ChromeLogger.php";
//------------------------------------------------------------------------------
wb_main($_POST);
//------------------------------------------------------------------------------
// function
//------------------------------------------------------------------------------
function wb_main($_post_data) {
	header('Content-Type: text/html; charset=utf-8');

	if( !isset($_post_data["request"]) || $_post_data["request"] == "" ) exit();

	$request = $_post_data["request"];
	
//	print_r($_post_data);	
	include_once "env.php";
	
	// common
	if( $request == "delete_log_db" ) delete_log_db($_post_data);
	if( $request == "change_my_passwd" ) change_my_passwd($_post_data);

	if( $request == "get_file_list" ) echo join("\n", get_file_list($_post_data["upload_path"], $_post_data["file_header"]));
	// if( $request == "get_corpus_list" ) get_corpus_list($_post_data);
	// if( $request == "get_engine_info" ) get_engine_info($_post_data);

	// login & logoff
	if( $request == "login" ) login($_post_data);
	if( $request == "logoff" ) logoff($_post_data);

	// workbench
	if( $request == "save_report" ) save_report($_post_data);
	if( $request == "delete_report" ) delete_report($_post_data);
	if( $request == "translate_sentence" ) echo json_encode(translate_sentence($_post_data));

	if( $request == "tail_log" ) tail_log($_post_data);	
	
	if( $request == "show_deamon_log" ) show_deamon_log($_post_data);

	if( $request == "delete_pid" ) delete_pid($_post_data);
	
    // dialog_act_tagger
    $_post_data["_WWW_DATA_PATH_"] = $_post_data["_ROOT_PATH_"]."/www_data";

    if( !isset($_post_data["project_name"]) ) {
        $_post_data["project_name"] = "";
        
        if( isset($_COOKIE["project_name"]) ) {
            $_post_data["project_name"] = str_replace("\"", "", $_COOKIE["project_name"]);   
        }
    } 
    
    //$_post_data["_DIALOG_SYSTEM_PRJ_PATH_"] = $_post_data["_ROOT_PATH_"]."/www_data/project";
	$_post_data["_DIALOG_SYSTEM_PRJ_PATH_"] = $_post_data["_ROOT_PATH_"]."/www_data/dialog_domain"; // tghong
    
    //$_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"]      = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"]."/".$_post_data["project_name"];
	$_post_data["_DIALOG_SYSTEM_ENGINE_PATH_"]      = $_post_data["_ROOT_PATH_"]."/www_data"; // tghong
    //$_post_data["_DIALOG_SYSTEM_ENGINE_"]           = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"]."/".$_post_data["project_name"]."/dial";
	$_post_data["_DIALOG_SYSTEM_ENGINE_"]           = $_post_data["_WWW_DATA_PATH_"]."/dial"; // tghong
    //$_post_data["_DIALOG_SYSTEM_DICTIONARY_HOME_PATH_"]  = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"]."/".$_post_data["project_name"]; // tghong
	$_post_data["_DIALOG_SYSTEM_DICTIONARY_HOME_PATH_"]  = $_post_data["_WWW_DATA_PATH_"]; // tghong
    $_post_data["_DIALOG_SYSTEM_LOG_PATH_"]         = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"]."/".$_post_data["project_name"]."/log";
    $_post_data["_DIALOG_SYSTEM_BIN_PATH_"]         = $_post_data["_WWW_DATA_PATH_"]."/bin";
	
    $_post_data["_DIALOG_SYSTEM_EVAL_DATA_PATH_"]   = $_post_data["_WWW_DATA_PATH_"];
	
    $_post_data["_DIALOG_SYSTEM_DAEMON_"]           = "perl ".$_post_data["_WWW_DATA_PATH_"]."/bin/run.daemon.dial.pl";
    $_post_data["_DIALOG_SYSTEM_SERV_IP_"]          = "127.0.0.1";
	$_post_data["_DIALOG_SYSTEM_SSHPASS_"]          = "sshpass -p ".$_post_data["_SSH_PASS_"]." ssh -T -oStrictHostKeyChecking=no -p 122 -l ".$_post_data["_SSH_ID_"]." ";
    $_post_data["_DIALOG_SYSTEM_SERV_PORT_"]        = 50101;
    
    //$_post_data["_DIALOG_SYSTEM_DATA_PATH_"]        = $_post_data["_DIALOG_SYSTEM_DICTIONARY_HOME_PATH_"]."/data";
	$_post_data["_DIALOG_SYSTEM_DATA_PATH_"]        = $_post_data["_DIALOG_SYSTEM_PRJ_PATH_"]."/".$_post_data["project_name"]; // tghong
    $_post_data["_DIALOG_SYSTEM_DB_FILE_NAME_"]     = $_post_data["project_name"]."_DB.sqlite3"; 
    $_post_data["_DIALOG_SYSTEM_UTTER_LOG_FILE_NAME_"] = "dialog_system_utter_log.sqlite3"; 
       
    $_post_data["_DB_TABLE_NAME_DA_TYPE_"]   		= "da_type";
    $_post_data["_DB_TABLE_NAME_SCENARIO_"]         = "scenario";
    $_post_data["_DB_TABLE_NAME_GUIDE_MAP_"] 		= "map";
    $_post_data["_DB_TABLE_NAME_TASK_MANAGER_"]     = "task_manager";
    $_post_data["_DB_TABLE_NAME_SLOT_STRUCTURE_"]   = "slot_structure";
    $_post_data["_DB_TABLE_NAME_DIALOG_LIBRARY_"]   = "dialog_lib";
    $_post_data["_DB_TABLE_NAME_TASK_INFORMATION_"] = "task_information";

    if( $request == "get_utter_list" ) get_utter_list($_post_data);
    if( $request == "get_utter_tagged_result" ) get_utter_tagged_result($_post_data);
    if( $request == "get_slot_structure" ) get_slot_structure($_post_data);
    if( $request == "save_utter_tagging_result" ) save_utter_tagging_result($_post_data);    
    if( $request == "get_utter_tagging_candidate" ) get_utter_tagging_candidate($_post_data);    
    if( $request == "run_utter_dialog_act_tagger" ) run_utter_dialog_act_tagger($_post_data);    
    if( $request == "slot_structure_save" ) slot_structure_save($_post_data);    
    if( $request == "get_task_manager_info" ) get_task_manager_info($_post_data);    
    if( $request == "dialog_act_tagger_search_extra_db" ) dialog_act_tagger_search_extra_db($_post_data);    
	if( $request == "dialog_act_tagger_get_db_table_list" ) dialog_act_tagger_get_db_table_list($_post_data);  
	if( $request == "dialog_act_tagger_get_extra_db_column_list" ) dialog_act_tagger_get_extra_db_column_list($_post_data);
	if( $request == "dialog_act_tagger_save_extra_db" ) dialog_act_tagger_save_extra_db($_post_data);
	if( $request == "dialog_act_tagger_delete_extra_db_record" ) dialog_act_tagger_delete_extra_db_record($_post_data);
	     
	if( $request == "run_asr_korean" ) run_asr_korean($_post_data);
	if( $request == "run_asr_english" ) run_asr_english($_post_data);
	
	if( $request == "task_manager_save_task_info" ) task_manager_save_task_info($_post_data);
	if( $request == "task_manager_get_dialog_library" ) task_manager_get_dialog_library($_post_data);    
	if( $request == "save_dialog_library_detail" ) save_dialog_library_detail($_post_data);    
    
	if( $request == "slot_tagger_delete_sentance" ) slot_tagger_delete_sentance($_post_data);

	if( $request == "reset_db" ) reset_db($_post_data);
	if( $request == "get_prj_list" ) get_prj_list($_post_data);
	if( $request == "get_da_type" ) get_da_type($_post_data);
	
	if( $request == "delete_dialog_library_detail" ) delete_dialog_library_detail($_post_data);

	if( $request == "dialog_act_tagger_upload_file" ) dialog_act_tagger_upload_file($_post_data);    
	if( $request == "dialog_act_tagger_read_file_list" ) dialog_act_tagger_read_file_list($_post_data);
	if( $request == "dialog_act_tagger_delete_data_file" ) dialog_act_tagger_delete_data_file($_post_data);    
	if( $request == "dialog_act_tagger_download_file" ) dialog_act_tagger_download_file($_post_data);    
	if( $request == "dialog_act_tagger_db_convert" ) dialog_act_tagger_db_convert($_post_data);
	if( $request == "dialog_act_tagger_make_new_project" ) dialog_act_tagger_make_new_project($_post_data);	
    if( $request == "dialog_act_tagger_make_delete_project" ) dialog_act_tagger_make_delete_project($_post_data);

	if( $request == "dialog_act_tagger_save_da_type" ) dialog_act_tagger_save_da_type($_post_data);	
	
	if( $request == "slot_structure_delete" ) slot_structure_delete($_post_data);	
	
	
	// dialog guide
	if( $request == "dialog_act_tagger_guide_get_task_detail" ) dialog_act_tagger_guide_get_task_detail($_post_data);	

	if( $request == "dialog_act_tagger_guide_get_task_info" ) dialog_act_tagger_guide_get_task_info($_post_data);	
	
    // dialog system
	if( $request == "learn_slu" ) learn_slu($_post_data);
	if( $request == "init_dialog_system" ) init_dialog_system($_post_data);
	if( $request == "stop_dialog_system" ) stop_dialog_system($_post_data);
	if( $request == "dialog_system_user_utter" ) dialog_system_user_utter($_post_data);   
	if( $request == "dialog_system_update_engine_list" ) dialog_system_update_engine_list($_post_data); 
	if( $request == "dialog_system_save_utter_log" ) dialog_system_save_utter_log($_post_data); 	  
     
	if( $request == "tail_log_learn_slu" ) tail_log_learn_slu($_post_data);
	
	if( $request == "eval_get_utter_log_list" ) eval_get_utter_log_list($_post_data);
	if( $request == "eval_get_utter_log" ) eval_get_utter_log($_post_data);		

	if( $request == "get_mission_list" ) get_mission_list($_post_data);
	if( $request == "get_mission_column_list" ) get_mission_column_list($_post_data);	
	
	if( $request == "submit_survey" ) submit_survey($_post_data);	
	
	if( $request == "eval_play_asr_file" ) eval_play_asr_file($_post_data);	
	if( $request == "eval_save_evaluation_form_data" ) eval_save_evaluation_form_data($_post_data);	
	if( $request == "eval_delete_evaluation_form_data" ) eval_delete_evaluation_form_data($_post_data);		

	if( $request == "read_log_file" ) read_log_file($_post_data);
	
	if( $request == "run_asr_server" ) run_asr_server($_post_data);	
	if( $request == "get_asr_server_log_filename" ) get_asr_server_log_filename($_post_data);
	if( $request == "is_running_asr_server" ) is_running_asr_server($_post_data);
	if( $request == "reset_asr_server_daemon" ) reset_asr_server_daemon($_post_data);
	if( $request == "get_eval_form_user_name_list" ) get_eval_form_user_name_list($_post_data);	
	if( $request == "get_eval_form_mission_list" ) get_eval_form_mission_list($_post_data);	
	if( $request == "eval_form_download_utter_log" ) eval_form_download_utter_log($_post_data);
	if( $request == "eval_form_download_survay" ) eval_form_download_survay($_post_data);

	if( $request == "task_manager_search_user_dict" ) task_manager_search_user_dict($_post_data);

}
//------------------------------------------------------------------------------
?>