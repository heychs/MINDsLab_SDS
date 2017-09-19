//------------------------------------------------------------------------------
var asr_file_name;
var g_deamon_log_file_name = "";
var g_help_window_info = {};
var g_utter_log = [];
var g_asr_result = null;
var g_sub_mission = "";
//------------------------------------------------------------------------------
function dialog_action_tagger_open_dialog_system() {
    var uid = uniqid();
    
    g_help_window_info["result_id"] = uid+"_result";
    g_help_window_info["user_utter_id"] = uid+"_user_utter";
    g_help_window_info["chk_guide_id"] = uid+"_chk_guide";
    
    var buf = "";
    
    buf += "<div id='wami' style='height: 0px;'></div>";
    
    buf += "<div id='dialog_act_tagger_web_dialog_system_"+uid+"_layout' fit='true'>";
    buf += "	<div region='center' id='dialog_act_tagger_web_dialog_system_panel' title='' noheader='true' split='true'>";

    buf += "<table style='width: 100%; height: 100%;' border=1>";
    buf += "   <tr><td style='height: 15px;'>";

    buf += "<table style='height: 100%; text-align: center;'>";
    buf += "   <tr><td>";
    buf += "       engine";
    buf += "   </td><td style='width: 150px;'>";
    buf += "       <select id='"+uid+"_engine' style='width: 100%; height: 100%;'>";
    buf += "           <option value='dial' selected='selected'>dial</option>";
    buf += "       </select>";
    buf += "   </td><td>";
    buf += "       channel";
    buf += "   </td><td style='width: 80px;'>";
    buf += "       <select id='"+uid+"_channel' style='width: 100%; height: 100%;'>";
    buf += "           <option value='0' selected='selected'>0</option>";
    
    for( var i=1 ; i<20 ; i++ ) {
        buf += "       <option value='"+i+"'>"+i+"</option>";
    }
    
    buf += "       </select>";
    buf += "   </td><td>";
    buf += "       <button id='"+uid+"_start_button' onclick=\"init_dialog_system('"+uid+"')\"> Start </button>";
    buf += "       <button id='"+uid+"_stop_button' onclick=\"stop_dialog_system('"+uid+"')\" disabled='disabled'> Stop </button>";

	if( $.cookie("evaluation_mode") == "yes" && $.cookie("project_name") != "dialog" ) {
	    buf += "       <input type=checkbox id='"+g_help_window_info["chk_guide_id"]+"' checked='checked'> guide";
	} else {
	    buf += "       <button id='"+uid+"_complete_button' onclick=\"complete_dialog_system('"+uid+"')\" disabled='disabled'> Complete </button>";
	    buf += "       <input type=checkbox id='"+g_help_window_info["chk_guide_id"]+"'> guide";
	}
    
    buf += "   </td></tr>";
    buf += "</table>";

    buf += "   </td></tr>";
    buf += "   <tr><td>";
    buf += "       <textarea id='"+g_help_window_info["result_id"]+"' style='width: 100%; height: 100%;'></textarea>";
    buf += "   </td></tr>";
    buf += "   <tr><td style='height: 50px;'>";

    buf += "<table style='width: 100%; height: 100%;' border=1>";
    buf += "   <tr><td style='height: 50px;'>";
    buf += "       <textarea id='"+g_help_window_info["user_utter_id"]+"' style='width: 100%; height: 100%;' onkeydown=\"dialog_system_user_utter_keydown('"+uid+"')\" disabled='disabled'></textarea>";
    buf += "   </td><td style='width: 50px;'>";
    buf += "       <button id='"+uid+"_asr_button' style='width: 100%; height: 100%;' onclick=\"run_asr(this, '"+uid+"')\" disabled='disabled'>Init<br> ASR</button>";
    buf += "   </td><td style='width: 50px;'>";
    buf += "       <button id='"+uid+"_speak_button' style='width: 100%; height: 100%;' onclick=\"dialog_system_user_speak('"+uid+"');\" disabled='disabled'>Speak</button>";
    buf += "   </td></tr>";
    buf += "</table>";

    buf += "   </td></tr>";
    buf += "</table>";


    
    buf += "	</div>";
    // buf += "	<div region='south' title='log' style='height: 150px; overflow: hidden;' split='true'>";
    // buf += "	</div>";    buf += "</div>";
    

    // open windows  
    var win_result = $("<div id='"+uid+"_win' style='padding: 0px; overflow: hidden;'>"+buf+"</div>").appendTo("body");
    
    win_result.window({  
        "iconCls"   : "icon-ok",
        "title"     : "Dialog System Test",  
        "width"     : 550,  
        "height"    : dialog_act_tagger_get_window_heigth(400), 
        "modal"     : false,
        "inline"    : false,
        "collapsible" : true,
        "minimizable" : false,
        "tools": [{  
            "iconCls": "icon-dialog_act_tagger_open_deamon_log",  
            "handler": function(){
            	dialog_system_open_daemon_log_window();
            }
        },{
            "iconCls": "icon-dialog_act_tagger_open_user_guide_win",  
            "handler": function(){
			    if( $.cookie("evaluation_mode") == "yes" && $.cookie("project_name") == "dialog" ) {
					dialog_system_open_user_guide_window_for_dialog_evaluation(uid);
				} else {
					dialog_system_open_user_guide_window(uid);
				}		
            } 
        }, "-"],  
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose"   : function(forceClose){
			if( $.cookie("evaluation_mode") == "yes" ) {
				g_help_window_info["obj_recommend_win"].window("close");
			}    

            $(this).remove();
        }       
    });

    // build loyout     
    $("#dialog_act_tagger_web_dialog_system_"+uid+"_layout").layout();    
    
//    $("#dialog_act_tagger_web_dialog_system_layout").layout("collapse", "south");    
    
    // get dial engine list
    dialog_system_update_engine_list(uid+"_engine");   
    
    // open task manager window
//    dialog_system_open_task_manager(win_result);

	if( $.cookie("evaluation_mode") == "yes" ) {
		win_result.window({
			"left" : 0,
			"top": 0
		});
		
	    if( $.cookie("project_name") == "dialog" ) {
			dialog_system_open_user_guide_window_for_dialog_evaluation(uid);
		} else {
			dialog_system_open_user_guide_window(uid);
		}		
	}
}
//------------------------------------------------------------------------------
function dialog_system_open_user_guide_window_for_dialog_evaluation(_uid) {
    // open windows  
    result_id = "dialog_act_tagger_web_dialog_system_panel_log_"+_uid;
    
    g_help_window_info["recommend_info_id"] = _uid+"_recommend_info"; 
    g_help_window_info["paraphrase_id"] 	= _uid+"_paraphrase";
    g_help_window_info["mission_table_id"] 	= _uid+"_mission";
    g_help_window_info["description_id"] 	= _uid+"_description";
    g_help_window_info["mission_table_id"] 	= _uid+"_mission_table"; 
    
    var buf = "";
    
    buf += "<div style='overflow: hidden; padding: 3px;'>";
    buf += "	<div id='dialog_act_tagger_web_dialog_system_user_guide_"+_uid+"_layout' fit='true'>";

    buf += "		<div region='north' id='"+g_help_window_info["mission_table_id"]+"' title='Mission' noheader='false' split='true' style='height: 150px;'>";
    buf += "		</div>";
    buf += "		<div region='center' id='"+g_help_window_info["description_id"]+"' title='Description' noheader='false' split='true' style=''>";
    buf += "		</div>";

    buf += "	</div>";
    buf += "</div>";
    
    var win_result = $(buf).appendTo("body");

    g_help_window_info["obj_recommend_win"] = win_result; 

    // open window
    win_result.window({  
        "iconCls"   : "icon-ok", 
        "title"     : "User Guide",  
        "width"     : 300,  
    	"height"    : dialog_act_tagger_get_window_heigth(400), 
        "modal"     : false,
        "inline"    : false,
        "collapsible" : true,
        "minimizable" : false,
        "attribute" : [],
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
    
	win_result.window({
		"left" : 550,
		"top": 0
	});
    
    $("#dialog_act_tagger_web_dialog_system_user_guide_"+_uid+"_layout").layout();
    
    dialog_system_update_mission_table_for_dialog_evaluation($("#"+g_help_window_info["mission_table_id"]));
}
//------------------------------------------------------------------------------
function dialog_system_open_user_guide_window(_uid) {
    // open windows  
    result_id = "dialog_act_tagger_web_dialog_system_panel_log_"+_uid;
    
    g_help_window_info["paraphrase_id"] 	= _uid+"_paraphrase";
    g_help_window_info["description_id"] 	= _uid+"_description";
    g_help_window_info["mission_table_id"] 	= _uid+"_mission";
    g_help_window_info["recommend_info_id"] = _uid+"_recommend_info"; 
    g_help_window_info["mission_table_id"] 	= _uid+"_mission_table"; 
    
    var buf = "";
    
    buf += "<div style='overflow: hidden; padding: 3px;'>";
    buf += "	<div id='dialog_act_tagger_web_dialog_system_user_guide_"+_uid+"_layout' fit='true'>";

    buf += "		<div region='north' id='"+g_help_window_info["mission_table_id"]+"' title='Mission' noheader='false' split='true' style='height: 120px;'>";
    buf += "		</div>";
    buf += "		<div region='center' id='"+g_help_window_info["recommend_info_id"]+"' title='Recommendation for User' noheader='false' split='true' style=''>";
    buf += "		</div>";
    buf += "		<div region='south' id='"+g_help_window_info["paraphrase_id"]+"' title='Paraphrase' noheader='false' split='true' style='height: 120px;'>";
    buf += "		</div>";

    buf += "	</div>";

    buf += "</div>";
    
    var win_result = $(buf).appendTo("body");

    g_help_window_info["obj_recommend_win"] = win_result; 

    // open window
    win_result.window({  
        "iconCls"   : "icon-ok", 
        "title"     : "User Guide",  
        "width"     : 300,  
    	"height"    : dialog_act_tagger_get_window_heigth(400), 
        "modal"     : false,
        "inline"    : false,
        "collapsible" : true,
        "minimizable" : false,
        "attribute" : [],
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
    
	if( $.cookie("evaluation_mode") == "yes" ) {
		win_result.window({
			"left" : 550,
			"top": 0
		});
	}    
    
    $("#dialog_act_tagger_web_dialog_system_user_guide_"+_uid+"_layout").layout();

	dialog_system_update_mission_table($("#"+g_help_window_info["mission_table_id"]));
}
//------------------------------------------------------------------------------
function dialog_system_update_mission_table_for_dialog_evaluation(_target) {
	g_help_window_info["mission_table_id"] = uniqid();
	
    _target.empty();
    _target.html("<table id='"+g_help_window_info["mission_table_id"]+"'></table>");

    var grid = $("#"+g_help_window_info["mission_table_id"]);

	var mission_list = g_utter_log[0].MISSION_LIST;
//	console.log(g_help_window_info["mission_table_id"], "mission list", mission_list);

    var columns = [{
        "field": "ok", 
        "title": "ok", 
        "width": 20,
        "editor": "text" 
	},{
        "field": "domain", 
        "title": "domain", 
        "width": 80,
        "editor": "text" 
	},{
        "field": "description", 
        "title": "description", 
        "width": 150,
        "editor": "text" 
    }];

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : true,
        "rownumbers"    : true,  
        "singleSelect"  : true,
        "pagination"    : false,
        "showHeader"	: true,
        "columns"       : [columns],
		"onClickRow":function(rowIndex){
			g_utter_log[0].MISSION = g_utter_log[0].MISSION_LIST.rows[rowIndex]; 
			
			$("#"+g_help_window_info["description_id"]).html("<h2>"+g_utter_log[0].MISSION.description+"</h1>");
		}
	});
	
	//update complete list status
	if( $.cookie("mission_complete_list") == null ) $.cookie("mission_complete_list", "");
	var mission_complete_list = $.cookie("mission_complete_list").split(",");

	for( var i in mission_list.rows ) {
		for( var j in mission_complete_list ) {
			if( mission_list.rows[i].id == mission_complete_list[j] ) {
				mission_list.rows[i].ok = "O";
			}
		}
	}
	
    grid.datagrid("loadData", {"total": mission_list.rows.length, "rows": mission_list.rows});
}
//------------------------------------------------------------------------------
function dialog_system_update_mission_table(_target) {
    _target.empty();
    _target.html("<table id='"+g_help_window_info["mission_table_id"]+"'></table>");

    var grid = $("#"+g_help_window_info["mission_table_id"]);

    var columns = [{
        "field": "key", 
        "title": "key", 
        "width": 100,
        "editor": "text" 
    },{
        "field": "value", 
        "title": "value", 
        "width": 120,
        "editor": "text" 
    }];

    if( $.cookie("project_name") == "carvatar" ) {
		columns = [{
	        "field": "value", 
	        "title": "value", 
	        "width": 120,
	        "editor": "text" 
	    }];
    }

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : true,
        "rownumbers"    : true,  
        "singleSelect"  : true,
        "pagination"    : false,
        "showHeader"	: false,
        "columns"       : [columns],
		"onClickRow":function(rowIndex){
			if( $.cookie("project_name") == "carvatar" ) {
				var row = $(this).datagrid("getRows")[rowIndex];
				
				var editor = $("#"+g_help_window_info["result_id"]);
				var str = editor.val();
				
				str += "Sub Mission: " + row.value + "\n";
				
				editor.val(str);

				g_sub_mission = row.value;
			}
		}
	});
	
	var mission = g_utter_log[0].MISSION;

    var rows = [];
    
    if( $.cookie("project_name") == "carvatar" ) {
    	var list = mission["description"].split(/\n/);
    	
	    for( var k in list ) {
	    	if( list[k] == "" ) continue;
    	
	        rows.push({
	            "key": k,  
	            "value": list[k],  
	            "editor": "text"
	        });
	    }
    } else {
	    for( var k in mission ) {
	        rows.push({
	            "key": k,  
	            "value": mission[k],  
	            "editor": "text"
	        });
	    }
    }
    
    grid.datagrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
function dialog_system_open_daemon_log_window() {
	var uid = uniqid();

    // open windows  
    result_id = "dialog_act_tagger_web_dialog_system_panel_log_"+uid;
    
    var buf = "";
    
    buf += "<div style='overflow: hidden; padding: 3px;'>";
    buf += "   <textarea id='"+result_id+"' style='width: 100%; height: 100%;'></textarea>";
    buf += "</div>";
    
    var win_result = $(buf).appendTo("body");
    
    // open window
    win_result.window({  
        "iconCls"   : "icon-ok", 
        "title"     : "Dialog System Log",  
        "width"     : 500,  
    	"height"    : dialog_act_tagger_get_window_heigth(350), 
        "modal"     : false,
        "inline"    : false,
        "collapsible" : true,
        "minimizable" : false,
        "attribute" : [],
        "tools": [{  
            "iconCls": "icon-dialog_act_tagger_clear_log",  
            "handler": function(){
            	console.log(result_id);
            	$("#"+result_id).val("");
            }              
        }, "-"],  
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });

	if( g_deamon_log_file_name != "" ) {
		setTimeout(function(){
	        tail_log(g_deamon_log_file_name, result_id, 0, "tail_log_learn_slu", 1);
	    }, 1000);
	} else {
		$("#"+result_id).val("There is no daemon log file name.\nStart first.");
	}
}
//------------------------------------------------------------------------------
function dialog_system_open_task_manager(result_obj) {
    var buf = "<iframe src='ui/task_manager.php' class='task_manager_style'></iframe>";
 
    // open windows  
    var win_result = $("<div style='padding: 0px; overflow: hidden;'>"+buf+"</div>").appendTo(result_obj);
    
    // open window
    win_result.window({  
        "iconCls"   : "icon-ok",
        "title"     : "Task Manager",  
        "top"       : 66,
        "left"      : 380,
        "width"     : 300,  
        "height"    : dialog_act_tagger_get_window_heigth(200), 
        "modal"     : false,
        "inline"    : true,
        "collapsed"   : true,
        "collapsible" : true,
        "minimizable" : false,
        "maximizable" : false,
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose"   : function(forceClose){
            $(this).remove();
        }       
    });    
}
//------------------------------------------------------------------------------
function dialog_system_update_engine_list(uid) {
	var post_data = {
		"request": "dialog_system_update_engine_list"
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
        $("#"+uid+"_engine").empty();
        for( var i in ret ) {
            $("#"+uid+"_engine").append("<option value='"+ret[i]+"'>"+ret[i]+"</option>");
        }
	});
}
//------------------------------------------------------------------------------
function learn_slu(result_id) {
	var post_data = {
		"request" : "learn_slu",
		"project_type" : $.cookie("project_type"),
		"project_name" : $.cookie("project_name"),
        "dialog_system_engine" : "dial"
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		var ret = JSON.parse(data);
		
		console.log(post_data, ret);
				
	    if( result_id == "" ) {
	        result_id = uniqid();
	        
	        var buf = "";
	        
	        buf += "<div style='overflow: hidden; padding: 3px;'>";
	        buf += "   <textarea id='"+result_id+"' style='width: 100%; height: 100%;'></textarea>";
	        buf += "</div>";
	        
	        var win_result = $(buf).appendTo("body");
	        
	        // open window
	        win_result.window({  
	            "iconCls"   : "icon-ok", 
	            "title"     : "SLU Learning Log",  
	            "width"     : 500,  
	        	"height"    : dialog_act_tagger_get_window_heigth(350), 
	            "modal"     : false,
	            "inline"    : false,
	            "attribute" : [],
	            "collapsible" : false,
	            "minimizable" : false,
		        "onMove": function(left, top) {
					dialog_act_tagger_on_window_move(this, left, top);
		        },
	            "onClose": function(forceClose){
	                $(this).remove();
	            }       
	        });

			// open window
			setTimeout(function(){
		        tail_log(ret.log_file_name, result_id, 0, "tail_log_learn_slu");
		    }, 1000);
		}
	});
}
//------------------------------------------------------------------------------
function run_asr(obj, uid) {
    var str = $(obj).text();

    if( str == "Init ASR" ) {
        $(obj).text("ASR");
        
        // init recoder
    	Wami.setup("wami");
    	
    	g_asr_result = null;
    } else if( str == "Stop" ) {
        // stop recording
        Wami.stopRecording();

        // change caption
        $(obj).text("ASR");
        
        // start asr
    	var post_data = {
    		"request": "run_asr_korean",
    		"project_name": $.cookie("project_name"),
    		"asr_file_name": asr_file_name,
        	"dialog_system_channel" : $("#"+uid+"_channel").val()
    	};
    	
    	if( g_utter_log[0]["ASR_TYPE"] == "asr_english" ) post_data["request"] = "run_asr_english";
        
		$.messager.progress({
			"title": "Please waiting",	
			"msg": "English ASR Running..."
		});    
        
        g_asr_result = null;
        
    	$.post(g_variable["_POST_URL_"], post_data, function(data) {
            var ret = jQuery.parseJSON(data);
            
            $.messager.progress("close");
            
            $("#"+uid+"_user_utter").val(ret.asr_result[0]);
            
            console.log(ret);
            
            g_asr_result = ret;
        });    
    } else {
        // clear old user utter
        $("#"+uid+"_user_utter").val("");
        
        // define asr file name
        asr_file_name = "asr/"+uniqid_date()+"."+uniqid()+".wav";
        
    	Wami.startRecording("/webtool/api/record.php?name="+asr_file_name);
        
        // change caption
        $(obj).text("Stop");
        
        g_asr_result = null;
    }    
}
//------------------------------------------------------------------------------
function stop_dialog_system(_uid) {
	var post_data = {
		"request" 	: "stop_dialog_system",
        "dialog_system_channel" : $("#"+_uid+"_channel").val()
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var str = $("#"+_uid+"_result").val();

        $("#"+_uid+"_result").val(str+"\nDialog End. Thanks.");
        
        $("#"+_uid+"_engine").removeAttr("disabled");
        $("#"+_uid+"_channel").removeAttr("disabled");
        
        $("#"+_uid+"_start_button").removeAttr("disabled"); 
        $("#"+_uid+"_stop_button").attr("disabled", "disabled"); 

        if( $.cookie("evaluation_mode") == "yes" && $.cookie("project_name") == "dialog" ) {
	        $("#"+_uid+"_complete_button").attr("disabled", "disabled");
	    } 

        $("#"+_uid+"_asr_button").attr("disabled", "disabled"); 
        $("#"+_uid+"_user_utter").attr("disabled", "disabled"); 
        $("#"+_uid+"_speak_button").attr("disabled", "disabled");    
        
        g_deamon_log_file_name = "";     
        
    	$("#"+g_help_window_info["paraphrase_id"]).empty();
		$("#"+g_help_window_info["recommend_info_id"]).empty(); 
    });
    
    // save utter log data
	g_utter_log.push({"TYPE": "STOP_SYSTEM"});
	
	dialog_system_save_utter_log();
}
//------------------------------------------------------------------------------
function complete_dialog_system(_uid) {
	var post_data = {
		"request" 	: "stop_dialog_system",
        "dialog_system_channel" : $("#"+_uid+"_channel").val()
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var str = $("#"+_uid+"_result").val();

        $("#"+_uid+"_result").val(str+"\nDialog End. Thanks.");
        
        $("#"+_uid+"_engine").removeAttr("disabled");
        $("#"+_uid+"_channel").removeAttr("disabled");
        
        $("#"+_uid+"_start_button").removeAttr("disabled"); 
        $("#"+_uid+"_stop_button").attr("disabled", "disabled");
        
        if( $.cookie("evaluation_mode") == "yes" && $.cookie("project_name") == "dialog" ) {
        	$("#"+_uid+"_complete_button").attr("disabled", "disabled");
        } 

        $("#"+_uid+"_asr_button").attr("disabled", "disabled"); 
        $("#"+_uid+"_user_utter").attr("disabled", "disabled"); 
        $("#"+_uid+"_speak_button").attr("disabled", "disabled");    
        
        g_deamon_log_file_name = "";     
        
    	$("#"+g_help_window_info["paraphrase_id"]).empty();
		$("#"+g_help_window_info["recommend_info_id"]).empty(); 
    });
    
    // save utter log data
	g_utter_log.push({"TYPE": "COMPLETE_SYSTEM"});
	
	dialog_system_save_utter_log();
	
	// change status ok
	var grid = $("#"+g_help_window_info["mission_table_id"]);
	var row = grid.datagrid("getSelected");
	
	row.ok = "O";
	
	var index = grid.datagrid("getRowIndex", row);
	
	// update cookie
	var mission_complete_list = $.cookie("mission_complete_list").split(",");
	mission_complete_list.push(row.id);	
	$.cookie("mission_complete_list", mission_complete_list.join(","));
	
	grid.datagrid("updateRow", {
		"index": index,
		"row": g_utter_log[0].MISSION
	});
}
//------------------------------------------------------------------------------
function dialog_system_save_utter_log() {
	var post_data = {
		"request" 	: "dialog_system_save_utter_log",
        "utter_log" : g_utter_log
	};
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
//		var ret = jQuery.parseJSON(data);
    });
    
    var mission = g_utter_log[0];
    
    g_utter_log = [mission];
}
//------------------------------------------------------------------------------
function dialog_system_user_utter_keydown(_uid) {
	if( event.keyCode == 13 ) dialog_system_user_speak(_uid);
}
//------------------------------------------------------------------------------
var g_asr_err_cnt = 0;
function dialog_system_user_speak(_uid) {
    var user_utter = $("#"+_uid+"_user_utter").val();
    
	var post_data = {
		"request" 	: "dialog_system_user_utter",
		"user_name" : $("#user_name").val(),
		"user_utter" : user_utter,
        "dialog_system_channel" : $("#"+_uid+"_channel").val()
	};
    
    var str = $("#"+_uid+"_result").val();
    
    str += "user: " + user_utter + "\n";
    
    $("#"+_uid+"_result").val(str);
    
    g_asr_err_cnt = 0;

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
		ret.system = ret.system.replace("system: ", "");
		
		// if not query test
		if( user_utter.substr(0, 1) == "#" ) {
			ret.system = +"\n" + ret.system;
		} else {
			ret.system = str_replace_all(ret.system, "\n", " ");
			ret.system = str_replace_all(ret.system, "<BR>", " ");
			ret.system = str_replace_all(ret.system, "<TAB>", " ");

			ret.system = ret.system.replace(/\s+/g, " ");
			
			if( $.cookie("evaluation_mode") == "yes" ) {
				ret.system = ret.system.replace(/\[.+?\]/g, "");
			}
		}

        if( g_utter_log[0]["ASR_TYPE"] == "asr_english" ) {
        	ret.org_system = ret.system;
	    	if( ret.slu_weight < 0 || ret.slu_weight > 0.59 ) {
	    		g_asr_err_cnt++;
	    		ret.system = "I can't catch you. Please try to speak recommended expressions.";
			} else if( ret.slu_weight > 0.2 ) {
	    		ret.system = "I guess you mean that \""+ret.slu_similar_str+"\".\n";
	    		ret.system += "        " + ret.org_system;
	    	} else if( $.trim(ret.system) == "" ) {
	    		g_asr_err_cnt++;
	    		ret.system = "I can't catch you. Please try to speak recommended expressions.";
	    	} 
        }

        var str = $("#"+_uid+"_result").val();

	    if( $.trim(ret.system) != "" ) {
	        str += "system: " + ret.system + "\n\n";
	    }
	    
        $("#"+_uid+"_result").val(str);

		var asr_text = "", asr_file_name = "";
		if( g_asr_result ) {
			asr_text = g_asr_result.asr_result[0];
			asr_file_name = g_asr_result.asr_file_name;
		}
    	g_asr_result = null;

		// save utter log
    	g_utter_log.push({
    		"TYPE": "SPEAK", 
    		"USER": user_utter, 
    		"SYSTEM": ret.system, 
    		"SUB_MISSION": g_sub_mission,
    		"POST_DATA": post_data, 
    		"POST_RESULT": ret, 
    		"ASR_TEXT": asr_text,
    		"ASR_FILE_NAME": asr_file_name
    	});
    	
		// scroll buttom
		document.getElementById(""+_uid+"_result").scrollTop = document.getElementById(_uid+"_result").scrollHeight;
    
    	// reset user utterance box
        $("#"+_uid+"_user_utter").val(""); 
        
        if( ret.IsDialogEnd == "1" ) stop_dialog_system(_uid);
        
        if( g_asr_err_cnt > 0 ) {
        	get_recommandation_info(_uid);
        } else {
			$("#"+g_help_window_info["paraphrase_id"]).empty();
			$("#"+g_help_window_info["recommend_info_id"]).empty(); 
        }
    });
}
//------------------------------------------------------------------------------
function init_dialog_system(_uid) {
	$("#"+_uid+"_result").val("loading...");
	
	g_deamon_log_file_name = $.cookie("user_name") + "." + _uid;

	var guide_mode = $("#"+g_help_window_info["chk_guide_id"]).attr("checked");
	if( typeof(guide_mode) != "undefined" && guide_mode == "checked" ) {
		guide_mode = "yes";
	} else {
		guide_mode = "no";
	}
	
	var post_data = {
		"request" 	: "init_dialog_system",
		"user_name" : $.cookie("user_name"),
		"project_name": $.cookie("project_name"),
		"deamon_log_file_name": g_deamon_log_file_name,
        "dialog_system_engine" : $("#"+_uid+"_engine").val(),
        "dialog_system_channel" : $("#"+_uid+"_channel").val(),
        "guide_mode": guide_mode
	};
	
	g_asr_err_cnt = 0;
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
		ret.system = ret.system.replace("system: ", "");
		ret.system = str_replace_all(ret.system, "\n", " ");
        
    	g_utter_log.push({"TYPE": "INIT_SYSTEM", "SYSTEM": ret.system, "POST_RESULT": ret, "POST_DATA": post_data});
    	
        if( typeof(ret.deamon_log_file_name) != "undefined" ) {
        	g_deamon_log_file_name = ret.deamon_log_file_name;
        }

        var str = "";
        
	    if( $.trim(ret.system) == "" ) {
	    	str += "ERROR: Check Log File..\n";	
	        $("#"+_uid+"_result").val(str);
	        
	    	return;
	    } else {
	        str += "system: " + ret.system + "\n\n";
	    }

        $("#"+_uid+"_result").val(str);

        // disabled
        $("#"+_uid+"_engine").attr("disabled", "disabled");
        $("#"+_uid+"_channel").attr("disabled", "disabled");   
        
        $("#"+_uid+"_start_button").attr("disabled", "disabled");  
        $("#"+_uid+"_stop_button").removeAttr("disabled");
        
        if( $.cookie("evaluation_mode") == "yes" && $.cookie("project_name") == "dialog" ) {
	        $("#"+_uid+"_complete_button").removeAttr("disabled");
        }
        
        $("#"+_uid+"_user_utter").removeAttr("disabled");   
        $("#"+_uid+"_asr_button").removeAttr("disabled");   
        $("#"+_uid+"_speak_button").removeAttr("disabled");   
        
        // get recommandation info.
        if( g_asr_err_cnt > 0 ) get_recommandation_info(_uid);
	});
}
//------------------------------------------------------------------------------
function get_recommandation_info(_uid) {
	$("#"+g_help_window_info["paraphrase_id"]).empty();
	$("#"+g_help_window_info["recommend_info_id"]).empty(); 
	
	var post_data = {
		"request" 	: "dialog_system_user_utter",
		"user_name" : $("#user_name").val(),
		"user_utter" : "help",
        "dialog_system_channel" : $("#"+_uid+"_channel").val()
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
        ret.recommend_info = ret.system.replace(/^system: /, "");
        ret.system = "";
        
        dialog_act_tagger_web_dialog_system_display_recommend_info(_uid, ret.recommend_info);
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_web_dialog_system_display_recommend_info(_uid, recommend_info) {
    // build data grid
    var _target = $("#"+g_help_window_info["recommend_info_id"]); 
    
    var uid = uniqid();
    
    var buf = "";    
    buf += "<table id='"+uid+"' style='background-color: lightblue;'></table>";
    
    _target.empty();
    _target.html(buf);

    var grid = $("#"+uid);

    var columns = [{
        "field": "utter", 
        "title": "utter", 
        "width": 220,
        "editor": "text" 
    }];

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : true,
        "rownumbers"    : true,  
        "singleSelect"  : true,
        "pagination"    : false,
        "showHeader"	: false,
      	"bodyCls"		: "eval_recommend_info",
        "columns"       : [columns],
		"onClickRow":function(rowIndex){
			var row = $(this).datagrid("getRows")[rowIndex];
			
			var utter = row.utter;
						
			utter = utter.replace("&gt;", ">");    	
			utter = utter.replace("&lt;", "<");    	
			
			$("#"+g_help_window_info["user_utter_id"]).val(utter);

			// get paraphrase
			dialog_act_tagger_get_paraphrase(_uid, row.utter);
		}
	});
    
    var list = recommend_info.split(/\n/);

    var rows = [];
    for( var i=0 ; i < list.length ; i++ ) {
    	var utter = list[i];
    	
    	if( utter == "" ) continue;
    	    	
    	utter = utter.replace(">", "&gt;");    	
    	utter = utter.replace("<", "&lt;");    	

        rows.push({
            "utter"  : utter,  
            "editor" : "text"
        });
    }
    
    grid.datagrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
function dialog_act_tagger_get_paraphrase(_uid, utter) {
	utter = utter.replace(/&gt;/g, ">");    	
	utter = utter.replace(/&lt;/g, "<");    	
	
	var post_data = {
		"request" 	: "dialog_system_user_utter",
		"user_name" : $("#user_name").val(),
		"user_utter" : "get_paraphrase<TAB>"+utter,
        "dialog_system_channel" : $("#"+_uid+"_channel").val()
	};
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);

        ret.paraphrase = ret.system.replace(/^system: /, "");
        ret.system = "";
        
        dialog_act_tagger_display_paraphrase(_uid, ret.paraphrase);
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_display_paraphrase(_uid, paraphrase) {
    // g_help_window_info["paraphrase_id"]; 

    // build data grid
    var _target = $("#"+g_help_window_info["paraphrase_id"]); 
    
    var uid = uniqid();
    
    var buf = "";    
    buf += "<table id='"+uid+"'></table>";
    
    _target.empty();
    _target.html(buf);

    var grid = $("#"+uid);

    var columns = [{
        "field": "utter", 
        "title": "utter", 
        "width": 220,
        "editor": "text" 
    }];

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : true,
        "rownumbers"    : true,  
        "singleSelect"  : true,
        "pagination"    : false,
        "showHeader"	: false,
      	"bodyCls"		: "eval_paraphrase",
        "columns"       : [columns],
		"onClickRow":function(rowIndex){
			var row = $(this).datagrid("getRows")[rowIndex];

			var utter = row.utter;
						
			utter = utter.replace("&gt;", ">");    	
			utter = utter.replace("&lt;", "<");    	
			
			$("#"+g_help_window_info["user_utter_id"]).val(utter);
		}
	});
    
    var list = paraphrase.split(/\n/);

    var rows = [];
    for( var i=0 ; i < list.length ; i++ ) {
    	var utter = list[i];
    	
    	if( utter == "" ) continue;
    	    	
    	utter = utter.replace(">", "&gt;");    	
    	utter = utter.replace("<", "&lt;");    	

        rows.push({
            "utter"  : utter,  
            "editor" : "text"
        });
    }
    
    grid.datagrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
