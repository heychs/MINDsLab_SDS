/**
 * @author ejpark
 */
//------------------------------------------------------------------------------
var login_box = null;
var mission_box = null;

//------------------------------------------------------------------------------
function eval_logout() {
	$.cookie("user_name", null); 
	$.cookie("project_name", null);
	$.cookie("evaluation_mode", null);
	$.cookie("mission_complete_list", null);  
	
    $("#eval_main_panel").panel({"title": "Evaluation Dialog System"});
    $("#eval_main_panel").hide();
	
	location.reload();
}
//------------------------------------------------------------------------------
function eval_select_mission(_domain, _asr_type, _mission_tag) {
	g_variable["_POST_URL_"] = "/webtool/api/main.php";
	
    // open window
    var buf = "";    
    buf += "<div id='login_box' style='padding: 5px; overflow: hidden; text-align: center;'>";
    buf += "	<div style='padding: 10px; background: #fff; border: 1px solid #ccc; text-align: center;'>";
    buf += "        <table>";
    buf += "            <tr><td>";
    buf += "                Select Mission:";
    buf += "            </td><td>";
    buf += "                <input id='eval_mission_name'/>";
    buf += "            </td></tr>";
    buf += "        </table>";
    buf += "	</div>";
    buf += "	<div style='text-align: center; padding: 5px 0;'>";
    buf += "		<a id='eval_select_mission_ok_button' class='easyui-linkbutton' iconCls='icon-ok' href='javascript:void(0)' onclick=\"eval_open_dialog_system('"+_asr_type+"');\">OK</a>";
    buf += "	</div>";
    buf += "</div>";

    mission_box = $(buf).appendTo("body");
    mission_box.window({  
        "width": 400,  
        "height": dialog_act_tagger_get_window_heigth(110), 
        "modal": true,
        "inline": false,
        "noheader": true,
        "resizable": false,
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
    
    $("#eval_select_mission_ok_button").linkbutton({});    
    
    // _domain
    $.cookie("project_name", _domain);
    
    // get column 
	var post_data = {
		"request": "get_mission_column_list",
		"mission_tag": _mission_tag,
		"project_name": _domain
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
	    var columns = [];

		for( i in ret ) {
			var column_width = 80;
			
			if( ret[i] == "id" || ret[i] == "sub id" ) {
				column_width = 20;
			} else if( ret[i] == "description" ) {
				column_width = 120;
			}
			
			columns.push({
				"field": ret[i],
				"title": ret[i],
				"width": column_width
			});
		}
		
		$("#eval_mission_name").combogrid({  
		    "width": 250,
		    "panelWidth":450,  
		    "value":"1",  	   
		    "idField":"id",  
		    "textField":"description",
		    "fitColumns": true,  
		    "url": g_variable["_POST_URL_"],
		    "queryParams": {
		    	"request": "get_mission_list",
				"mission_tag": _mission_tag,
				"domain": _domain
			},  
		    "columns":[columns]  
		});      
	});
}
//------------------------------------------------------------------------------
function eval_open_dialog_system(_asr_type) {
	var grid = $("#eval_mission_name").combogrid("grid");	
	var row = grid.datagrid("getSelected");
	
	mission_box.window("close");

	g_utter_log = [];
	g_utter_log.push({"TYPE": "MISSION", "MISSION": row, "ASR_TYPE": _asr_type});
	
	dialog_action_tagger_open_dialog_system();	
}
//------------------------------------------------------------------------------
function eval_open_asr_server(_domain) {
	var post_data = {
		"request": "get_asr_server_log_filename"
	};
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
        g_deamon_log_file_name = ret[0];

		var uid = uniqid();
	
	    var buf = "";
	    
	    buf += "<div style='overflow: hidden; padding: 3px;'>";
	    buf += "   <textarea id='"+uid+"' style='width: 100%; height: 100%;'></textarea>";
	    buf += "</div>";
	    
	    var win_result = $(buf).appendTo("body");
	    
	    // open window
	    win_result.window({  
	        "iconCls"   : "icon-ok", 
	        "title"     : "ASR Server Daemon Log",  
	        "width"     : 500,  
	    	"height"    : dialog_act_tagger_get_window_heigth(350), 
	        "modal"     : false,
	        "inline"    : false,
	        "collapsible" : true,
	        "minimizable" : false,
	        "attribute" : [],
	        "tools": [{  
	            "iconCls": "icon-dialog_act_tagger_refresh_log",  
	            "handler": function(){
	            	$("#"+uid).val("");
	            	
					var post_data = {
						"request": "get_asr_server_log_filename"
					};
					
					$.post(g_variable["_POST_URL_"], post_data, function(data) {
				        var ret = jQuery.parseJSON(data);
				        g_deamon_log_file_name = ret[0];
				        
				        $("#"+uid).val(g_deamon_log_file_name);
				        
				        g_stop_tail_log = 0;
						setTimeout(function(){
					        tail_log(g_deamon_log_file_name, uid, 0, "tail_log_learn_slu", 1);
					    }, 1000);				        
				 	});
	            }              
	        }, "-",{  
	            "iconCls": "icon-dialog_action_tagget_start_dialog_system",  
	            "handler": function(){
	            	$("#"+uid).val("");
	            	// run asr server
					var post_data = {
						"request": "run_asr_server"
					};
				    
					$.post(g_variable["_POST_URL_"], post_data, function(data) {
		            	// run asr server
						var post_data = {
							"request": "is_running_asr_server"
						};
					    
						$.post(g_variable["_POST_URL_"], post_data, function(data) {
							var ret = jQuery.parseJSON(data);
							$("#"+uid).val(ret.join("\n"));
						});
					}); 
	            }              
	        }, "-",{  
	            "iconCls": "icon-dialog_act_tagger_log_statistics",  
	            "handler": function(){
	            	$("#"+uid).val("");
	            	
	            	// run asr server
					var post_data = {
						"request": "is_running_asr_server"
					};
				    
					$.post(g_variable["_POST_URL_"], post_data, function(data) {
						var ret = jQuery.parseJSON(data);
						$("#"+uid).val(ret.join("\n"));
						
						g_stop_tail_log = 1;
					});
	            }              
	        }, "-",{  
	            "iconCls": "icon-run_slot_tagging-delete",  
	            "handler": function(){
	            	$("#"+uid).val("");
	            	// run asr server
					var post_data = {
						"request": "reset_asr_server_daemon"
					};
				    
					$.post(g_variable["_POST_URL_"], post_data, function(data) {
						g_deamon_log_file_name = "";
						g_stop_tail_log = 1;
					});
	            }              
	        }, "-"],  
	        "onMove": function(left, top) {
				dialog_act_tagger_on_window_move(this, left, top);
	        },
	        "onClose": function(forceClose){
	            $(this).remove();
	        }       
	    });
	
		// if( g_deamon_log_file_name != "" ) {
			// setTimeout(function(){
		        // tail_log(g_deamon_log_file_name, uid, 0, "tail_log_learn_slu", 1);
		    // }, 1000);
		// } else {
			// $("#"+uid).val("There is no daemon log file name.\nStart first.");
		// }
	});
}
//------------------------------------------------------------------------------
function eval_open_dialog_system_for_dial_evaluation(_domain, _asr_type, _mission_tag) {
    // _domain
    $.cookie("project_name", _domain);
    
    // get mission list
	var post_data = {
		"request": "get_mission_list",
		"mission_tag": _mission_tag,
		"project_name": _domain
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
		g_utter_log = [];
		g_utter_log.push({"TYPE": "MISSION", "MISSION_LIST": ret, "ASR_TYPE": _asr_type});
		
		dialog_action_tagger_open_dialog_system();	
	});
}
//------------------------------------------------------------------------------
function eval_onload() {
    $("#eval_main_panel").panel({
		"tools": [{  
			"iconCls":'icon-logout',  
			"handler":function(){
				eval_logout();
			}  
		}]  
  	});

	var user_name = $.cookie("user_name");
	
	if( user_name ) {
	    $("#eval_main_panel").panel({"title": user_name + " 님 환영합니다."});
	} else {
		open_eval_login_box();
	}
}
//------------------------------------------------------------------------------
function open_eval_login_box(){
    // hide tab panel
    $("#eval_main_panel").hide();
    
    // open window
    var buf = "";    
    buf += "<div id='login_box' style='padding: 5px; overflow: hidden; text-align: center;'>";
    buf += "	<div style='padding: 10px; background: #fff; border: 1px solid #ccc; text-align: center;'>";
    buf += "        <table>";
    buf += "            <tr><td>";
    buf += "                Your Name:";
    buf += "            </td><td>";
    buf += "                <input id='eval_login_box_user_name' type='text' style='width: 100px;' value='' onkeydown='eval_login(window.event)' />";
    buf += "            </td></tr>";
    buf += "        </table>";
    buf += "	</div>";
    buf += "	<div style='text-align: center; padding: 5px 0;'>";
    buf += "		<a id='login_box_login_button' class='easyui-linkbutton' iconCls='icon-ok' href='javascript:void(0)' onclick='eval_login(null)'>Login</a>";
    buf += "	</div>";
    buf += "</div>";

    login_box = $(buf).appendTo("body");
    login_box.window({  
        "width": 240,  
        "height": dialog_act_tagger_get_window_heigth(110), 
        "modal": true,
        "inline": false,
        "noheader": true,
        "resizable": false,
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
    
    $("#login_box_login_button").linkbutton({});    
}
//------------------------------------------------------------------------------
function eval_login(_event){
    if( _event && _event.keyCode != 13 ) return;
    
    var user_name = $("#eval_login_box_user_name").val();
    
	$.cookie("user_name", user_name); 
	$.cookie("evaluation_mode", "yes");  

    $("#eval_main_panel").panel({"title": user_name + " 님 환영합니다."});
    $("#eval_main_panel").show("slow");

	login_box.window("close");
}
//------------------------------------------------------------------------------
function eval_open_questionnaire_form(_domain){
    // open window
    var buf = "";    
    buf += "<div style='padding: 0px; overflow: hidden;'>";

	// if( _domain == "dialog" ) {
	    buf += "	<iframe src='/webtool/eval.questionnaire_form.dialog.php' class='survey_window_frame_style'></iframe>";
	// } else {
	    // buf += "	<iframe src='/webtool/eval.questionnaire_form.php' class='survey_window_frame_style'></iframe>";
	// }    

    buf += "</div>";

    var win_result = $(buf).appendTo("body");
    win_result.window({  
        "iconCls"   : "icon-ok",
        "title"     : "Dialog System Questionnaire Form",  
        "width": 500,  
        "height": dialog_act_tagger_get_window_heigth(400), 
        "modal": true,
        "inline": false,
        "noheader": false,
        "resizable": true,
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
}
//------------------------------------------------------------------------------
function eval_search_utter_log(_id, _domain){
	var search_cond = {
		"user_name": $("#"+_id+"_user_name").combobox("getValue"),
		"mission_id": $("#"+_id+"_mission_id").combobox("getValue")
	};
	
	// console.log(search_cond);

	eval_get_utter_log_list(_id, _domain, search_cond);
}
//------------------------------------------------------------------------------
function eval_form_get_user_name_list(_id){
	var post_data = {
		"request": "get_eval_form_user_name_list"
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
		var data = [];
		for( i in ret ) {
			 data.push({"id": i, "user_name": ret[i].user_name});
		}

        var combo = $("#"+_id);
		combo.combobox({  
		    "valueField": "user_name",  
		    "textField": "user_name",
		    "readonly": true,
		    "data": data
		});
	});
}
//------------------------------------------------------------------------------
function eval_form_get_mission_list(_id){
	var post_data = {
		"request": "get_eval_form_mission_list"
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
		var data = [];
		for( i in ret ) {
			 data.push({"id": i, "mission_id": ret[i].mission_id});
		}

        var combo = $("#"+_id);
		combo.combobox({  
		    "valueField": "mission_id",  
		    "textField": "mission_id",
		    "readonly": true,
		    "data": data
		});
	});
}
//------------------------------------------------------------------------------
function eval_form_download_survay(){
	var post_data = {
		"request": "eval_form_download_survay"
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
        console.log(ret);

		// open url
		window.open(ret.url);        
	});
}
//------------------------------------------------------------------------------
function eval_form_download_utter_log(){
	var post_data = {
		"request": "eval_form_download_utter_log"
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);

		// open url
		window.open(ret.url);        
	});
}
//------------------------------------------------------------------------------
function eval_open_evaluation_form(_domain){
    // open window
    var uid = uniqid();
    
    var buf = "";
    
    buf += "<div id='"+uid+"' style='overflow: hidden;'>";

    buf += "<div id='"+uid+"_layout' fit='true'>";
    
    buf += "   <div region='north' id='"+uid+"_search_box' title='Search Box' noheader='false' split='true' style='height: 100px; overflow: hidden;'>";
    buf += "		<span>mission id:</span><input id='"+uid+"_mission_id' type=text style='width: 200px;' value=\"\" />";    
    buf += "		<span>user name:</span><input id='"+uid+"_user_name' type=text style='width: 200px;' value=\"\" />";    
    buf += "		<button onclick=\"eval_search_utter_log('"+uid+"', '"+_domain+"');\">Search</button>";    
    buf += "   </div>";    
    buf += "   <div region='center' id='"+uid+"_dialog_list' title='Dialog List' noheader='false' split='true' style=''>";    
    buf += "   </div>";    
    buf += "   <div region='south' id='"+uid+"_evaluation_form' title='Evaluation Form' noheader='false' split='true' style='height: 300px; overflow: hidden;'>";
    buf += "   </div>";    

    buf += "</div>";    

    buf += "</div>";
    
    var win_result = $(buf).appendTo("body");

    win_result.window({  
        "title"     : "Evaluation Form",
        "fit"		: true,  
        // "width"     : 800,  
        // "height"    : dialog_act_tagger_get_window_heigth(700), 
        "modal"     : false,
        "inline"    : false,
        "iconCls"   : "icon-ok", 
        "closable"  : true,
        "resizable" : true,
        "collapsible" : false,
        "minimizable" : false,
        "maximizable" : true,
		"tools": [{			
            "iconCls": "icon-dialog_act_tagger_page_down",  
            "handler": function(){
            	eval_form_download_survay();
            }  
        }, {  
            "iconCls": "icon-dialog_act_tagger_download",  
            "handler": function(){
            	eval_form_download_utter_log();
            }  
        }, "-", {  
            "iconCls": "icon-dialog_act_tagger_refresh_log",  
            "handler": function(){
            	eval_form_get_user_name_list(uid+"_user_name");
            	eval_form_get_mission_list(uid+"_mission_id");
            }  
        }, "-", {  
            "iconCls": "icon-dialog_act_tagger_delete_record",  
            "handler": function(){
            	eval_delete_evaluation_form_data(uid, _domain);
            }  
        }, {  
            "iconCls": "icon-save",  
            "handler": function(){
            	eval_save_evaluation_form_data(uid);
            }  
        }, "-"],  
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });    
            
    $("#"+uid+"_layout").layout({});
    
    $("#"+uid+"_evaluation_form").panel({
       "tools": [{  
            // "iconCls": "icon-dialog_act_tagger_delete_record",  
            // "handler": function(){
            	// eval_delete_evaluation_form_data(uid, _domain);
            // }  
        // },{  
            "iconCls": "icon-save",  
            "handler": function(){
            	eval_save_evaluation_form_data(uid);
            }  
        }]  
    });

	// eval_get_utter_log_list(uid, _domain);
	
	eval_form_get_user_name_list(uid+"_user_name");
	eval_form_get_mission_list(uid+"_mission_id");
}
//------------------------------------------------------------------------------
function eval_open_evaluation_summary_window(){
		
}
//------------------------------------------------------------------------------
function eval_save_evaluation_form_data(_target_layout_id){	
	var eval_form_grid = $("#"+_target_layout_id+"_evaluation_form_grid");
	var dialog_list_grid = $("#"+_target_layout_id+"_dialog_list_grid");

	// get grid data	
	var eval_form_grid_data = eval_form_grid.datagrid("options").grid_data;
	var dialog_list_grid_data = dialog_list_grid.datagrid("options").grid_data;
	
	// get selected list
	var dialog_list_selected = dialog_list_grid.datagrid("getSelected");
	
	// make post data
	var post_data = {
		"request": "eval_save_evaluation_form_data",
		"utter_result": []
	};

	for( var k in dialog_list_selected ) {
		post_data[k] = dialog_list_selected[k];
	}
	
	for( var i=0 ; i<eval_form_grid_data.length ; i++ ) {
		post_data["utter_result"].push({
			"utter_id": eval_form_grid_data[i]["utter_id"],	
			"utter_result": eval_form_grid_data[i]["utter_result"],	
			"sub_mission_result": eval_form_grid_data[i]["sub_mission_result"],	
			"utter_result_comment": eval_form_grid_data[i]["utter_result_comment"]
		});
	}
	
	// post
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
        // console.log(post_data, ret);
        
		$.messager.show({
			"title": "Save Evaluation Result",
			"msg": ret.msg,
			"timeout": 2000,
			"showType": "slide"
		});
	});
}
//------------------------------------------------------------------------------
function eval_delete_evaluation_form_data(_target_layout_id, _domain){	
    $.messager.confirm("Delete Utterance log", "선택된 대화를 삭제하시겠습니까?", function(r){
		if( r ){
			var dialog_list_grid = $("#"+_target_layout_id+"_dialog_list_grid");
		
			// get grid data	
			var dialog_list_grid_data = dialog_list_grid.datagrid("options").grid_data;
			
			// get selected list
			var dialog_list_selected = dialog_list_grid.datagrid("getSelected");
			
			// make post data
			var post_data = {
				"request": "eval_delete_evaluation_form_data",
				"dialog_id": dialog_list_selected["dialog_id"],
				"mission": dialog_list_selected["mission"],
				"domain": dialog_list_selected["domain"],
				"user_name": dialog_list_selected["user_name"]
			};
		
			// post
			$.post(g_variable["_POST_URL_"], post_data, function(data) {
		        var ret = jQuery.parseJSON(data);
		
				$.messager.show({
					"title": "delete evaluation form",
					"msg": ret.msg,
					"timeout": 2000,
					"showType": "slide"
				});
				
				// eval_get_utter_log_list(_target_layout_id, _domain);
			});
        }
	});  
}
//------------------------------------------------------------------------------
function eval_get_utter_log_list(_target_layout_id, _domain, _query_cond){
	$("#"+_target_layout_id+"_evaluation_form").empty();
	
	var post_data = {
		"request": "eval_get_utter_log_list", 
		"query_cond": _query_cond, 
		"project_name": _domain
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
        eval_display_utter_log_list(_target_layout_id, ret, _domain);
	});
}
//------------------------------------------------------------------------------
function eval_display_utter_log_list(_target_layout_id, _data, _domain){
    var buf = "";    
    buf += "<table id='"+_target_layout_id+"_dialog_list_grid'></table>";
    
    var _target = $("#"+_target_layout_id+"_dialog_list");
    
    _target.empty();
    _target.html(buf);

    var grid = $("#"+_target_layout_id+"_dialog_list_grid");

    var columns = [[{
        "field": "domain", 
        "title": "Domain", 
        "width": 100,
        "editor": "text",
        "rowspan": 2  
    },{
        "field": "user_name", 
        "title": "User Name", 
        "width": 100,
        "editor": "text",
        "rowspan": 2  
    },{
        "field": "dialog_id", 
        "title": "Dialog ID", 
        "width": 50,
        "editor": "text",
        "rowspan": 2  
    },{
        "field": "mission", 
        "title": "Mission", 
        "width": 40,
        "editor": "text",
        "rowspan": 2   
    },{
    	"title": "Eval. (Success/Fail)",
    	"colspan":2
    },{
        "title": "Comment", 
    	"colspan":2
    }],[{
        "field": "success", 
        "title": "Success", 
        "width": 80,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_log_list_formatter(value, row, index, _target_layout_id+"_dialog_list_grid", "S");
        } 
    },{
        "field": "fail", 
        "title": "Fail", 
        "width": 80,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_log_list_formatter(value, row, index, _target_layout_id+"_dialog_list_grid", "F");
        } 
    },{
        "field": "eval_result_comment", 
        "title": "Comment", 
        "width": 150,
        "align": "center",
        "editor": "text"
    },{
        "field": "add_comment", 
        "title": "Edit", 
        "width": 40,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_log_list_edit_comment_formatter(value, row, index, _target_layout_id+"_dialog_list_grid");
        } 
    }]];

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : true,
        "rownumbers"    : true,  
        "singleSelect"  : true,
        "pagination"    : false,
        "showHeader"	: true,
        "columns"       : columns,
        "grid_data"		: _data,
		"onClickRow":function(rowIndex){
			var row = $(this).datagrid("getRows")[rowIndex];
			
			eval_get_utter_log(_target_layout_id, row.dialog_id, _domain);
		}
	});

    var rows = _data;    
    grid.datagrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
function eval_evaluation_log_list_edit_comment_formatter(value, row, index, grid_id){
	return "<button onclick=\"eval_evaluation_log_list_edit_comment('"+grid_id+"', '"+index+"');\">Edit</button>";
}
//------------------------------------------------------------------------------
function eval_evaluation_log_list_edit_comment(grid_id, index){
	var grid_data = $("#"+grid_id).datagrid("options").grid_data;
 	
    $.messager.prompt("Edit Comment", "Comment: ", function(r){
        if( r ){
        	grid_data.eval_result_comment = r;
        	
			$("#"+grid_id).datagrid("options").grid_data = grid_data;

			var row = $("#"+grid_id).datagrid("getSelected");
			row.eval_result_comment = r;
			
			$("#"+grid_id).datagrid("updateRow", {
				"index": index,
				"row": row
			});
		}
	}); 	
}
//------------------------------------------------------------------------------
function eval_evaluation_log_list_formatter(value, row, index, grid_id, check_value){
    var col = "", checked = "";
    
    if( row.eval_result == check_value ) checked = "checked='checked'";
    col = "<input type='radio' name='" + grid_id + "_" + index + "_" + row.utter_id+"' onclick=\"eval_update_evaluation_log_list_value('"+grid_id+"', '"+index+"', '"+check_value+"');\" value='"+row.utter_result+"' "+checked+" />";
    
    return col;
}
//------------------------------------------------------------------------------
function eval_update_evaluation_log_list_value(grid_id, index, value){
	var grid_data = $("#"+grid_id).datagrid("options").grid_data;
 	
	grid_data[index].eval_result = value;
	$("#"+grid_id).datagrid("options").grid_data = grid_data;
}
//------------------------------------------------------------------------------
function eval_get_utter_log(_target_layout_id, _dialog_id, _domain){
	var post_data = {
		"request": "eval_get_utter_log",
		"dialog_id": _dialog_id,
		"project_name": _domain
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);
        
        // console.log(ret);
        
        eval_display_utter_log(_target_layout_id, ret);
	});
}
//------------------------------------------------------------------------------
function eval_display_utter_log(_target_layout_id, _data){
    var buf = "";    
    buf += "<table id='"+_target_layout_id+"_evaluation_form_grid'></table>";
    
    var _target = $("#"+_target_layout_id+"_evaluation_form");
    
    _target.empty();
    _target.html(buf);
    
    var grid = $("#"+_target_layout_id+"_evaluation_form_grid");

    var columns = [[{
        "field": "utter_id", 
        "title": "ID", 
        "width": 30,
        "editor": "text",
        "rowspan": 2  
    },{
        "field": "speaker", 
        "title": "speaker", 
        "width": 80,
        "editor": "text",
        "rowspan": 2  
    },{
        "field": "utter", 
        "title": "Utterence", 
        "width": 250,
        "editor": "text",
        "rowspan": 2 
    },{
    	"title": "System",
    	"colspan":3
    },{
    	"title": "User",
    	"colspan":2
    },{
    	// "title": "Mission",
    	// "colspan":2
    // },{
        "title": "ASR", 
        "colspan": 2
    },{
        "title": "Comment", 
    	"colspan":2
    // },{
        // "field": "sub_mission", 
        // "title": "Sub Mission", 
        // "width": 250,
        // "editor": "text",
        // "rowspan": 2 
    }],[{
        "field": "utter_result_ss", 
        "title": "S", 
        "width": 40,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid", "SS", "utter_result");
        } 
    },{
        "field": "utter_result_sf", 
        "title": "F", 
        "width": 40,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid", "SF", "utter_result");
        } 
    },{
        "field": "utter_result_se", 
        "title": "E", 
        "width": 40,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid", "SE", "utter_result");
        } 
    },{
        "field": "utter_result_us", 
        "title": "S", 
        "width": 40,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid", "US", "utter_result");
        } 
    },{
        "field": "utter_result_uf", 
        "title": "F", 
        "width": 40,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid", "UF", "utter_result");
        } 
    },{
        // "field": "sub_mission_result_ms", 
        // "title": "S", 
        // "width": 40,
        // "align": "center",
        // "formatter": function(value, row, index){
        	// return eval_evaluation_form_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid", "MS", "sub_mission_result");
        // } 
    // },{
        // "field": "sub_mission_result_mf", 
        // "title": "F", 
        // "width": 40,
        // "align": "center",
        // "formatter": function(value, row, index){
        	// return eval_evaluation_form_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid", "MF", "sub_mission_result");
        // } 
    // },{
        "field": "asr_result", 
        "title": "result", 
        "width": 150,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_asr_text_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid");
        }  
    },{
        "field": "asr_file_name", 
        "title": " ", 
        "width": 30,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_asr_file_name_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid");
        } 
    },{
        "field": "utter_result_comment", 
        "title": "Comment", 
        "width": 150,
        "align": "center",
        "editor": "text"
    },{
        "field": "add_comment", 
        "title": "Edit", 
        "width": 40,
        "align": "center",
        "formatter": function(value, row, index){
        	return eval_evaluation_form_edit_comment_formatter(value, row, index, _target_layout_id+"_evaluation_form_grid");
        } 
    }]];

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : false,
        "rownumbers"    : false,  
        "singleSelect"  : true,
        "pagination"    : false,
        "showHeader"	: true,
        "grid_data"		: _data,
        "columns"       : columns,
		"onClickRow":function(rowIndex){
			var row = $(this).datagrid("getRows")[rowIndex];	
			
			// console.log(row);	
			$.messager.show({
				"title": "Detail",
				"msg": row.utter,
				"timeout": 5000,
				"showType": "slide"
			});	
		}
	});

    var rows = _data;    
    grid.datagrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
function eval_evaluation_form_edit_comment_formatter(value, row, index, grid_id){
	return "<button onclick=\"eval_evaluation_form_edit_comment('"+grid_id+"', '"+index+"');\">Edit</button>";
}
//------------------------------------------------------------------------------
function eval_evaluation_form_edit_comment(grid_id, index){
	var grid_data = $("#"+grid_id).datagrid("options").grid_data;
 	
    $.messager.prompt("Edit Comment", "Comment: ", function(r){
        if( r ){
			console.log(grid_data);
			
        	grid_data.utter_result_comment = r;
        	
			$("#"+grid_id).datagrid("options").grid_data = grid_data;

			var row = $("#"+grid_id).datagrid("getSelected");
			row.utter_result_comment = r;
			
			$("#"+grid_id).datagrid("updateRow", {
				"index": index,
				"row": row
			});
		}
	}); 	
}
//------------------------------------------------------------------------------
function eval_evaluation_form_asr_text_formatter(value, row, index, grid_id){
    if( row.speaker == "USER" ) return value;
    
    return "";
}
//------------------------------------------------------------------------------
function eval_evaluation_form_asr_file_name_formatter(value, row, index, grid_id){
    var col = "";
    
    if( row.asr_file_name && row.asr_file_name != "" && row.speaker == "USER" ) {
    	var uid = uniqid();
    	
	    col = "<a href='#' onclick=\"eval_play_file('"+row.asr_file_name+"', '"+row.domain+"', '"+uid+"')\"><img src='/imgs/Button-Play-icon.png' border=0></a>";
	    col += "<div id='"+uid+"' style='display: hidden;'></div>";
    }
    
    return col;
}
//------------------------------------------------------------------------------
function eval_play_file(asr_file_name, _domain, player_id){
	var post_data = {
		"request": "eval_play_asr_file",
		"project_name": _domain,
		"asr_file_name": asr_file_name
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = jQuery.parseJSON(data);

		if( typeof(ret.url) != "undefined" && ret.url != "" ) {
			$("#"+player_id).html("<embed src='"+ret.url+"' hidden=true autostart=true loop=false>");
		}
	});
}
//------------------------------------------------------------------------------
function eval_evaluation_form_formatter(value, row, index, grid_id, check_value, type){
    var col = "", checked = "";
    
    if( type == "utter_result" ) {
    	if( row.speaker == "SYSTEM" ) {
    		if( check_value == "US" || check_value == "UF" ) return "";
    	} else {
    		if( check_value == "SS" || check_value == "SF" || check_value == "SE" ) return "";
    	}
    		
	    if( row.utter_result == check_value ) checked = "checked='checked'";
	    col = "<input type='radio' name='"+type+"_"+row.utter_id+"' onclick=\"eval_update_evaluation_form_value('"+grid_id+"', '"+index+"', '"+check_value+"', '"+type+"');\" value='"+row.utter_result+"' "+checked+" />";
		} else {
			if( index <= 0 ) return "";
						
			var rows = $("#"+grid_id).datagrid("getRows");

			var prev_row = rows[index-1];
			var next_row = null;
			
			if( index < rows.length ) next_row = rows[index+1];
			
			if( prev_row.sub_mission == "" ) return "";
			if( next_row != null && row.sub_mission == next_row.sub_mission ) return "";
			
	    if( row.sub_mission_result == check_value ) checked = "checked='checked'";
	    col = "<input type='radio' name='"+type+"_"+row.utter_id+"' onclick=\"eval_update_evaluation_form_value('"+grid_id+"', '"+index+"', '"+check_value+"', '"+type+"');\" value='"+row.sub_mission_result+"' "+checked+" />";
		}
		    
    return col;
}
//------------------------------------------------------------------------------
function eval_update_evaluation_form_value(grid_id, index, value, type){
	var grid_data = $("#"+grid_id).datagrid("options").grid_data;
 	
  if( type == "utter_result" ) {
		grid_data[index].utter_result = value;
	} else {
		grid_data[index].sub_mission_result = value;
	}

	$("#"+grid_id).datagrid("options").grid_data = grid_data;
}
//------------------------------------------------------------------------------

    