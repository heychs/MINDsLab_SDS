//------------------------------------------------------------------------------
var g_dialog_library = null;
//------------------------------------------------------------------------------
function open_task_manager_edit_window() {
    // open windows
    var buf = "";
      
    buf += "<div id='task_manager_edit_win_layout_top' fit='true'>";

    buf += "   <div region='west' title='' noheader='true' split='true' style='width: 350px; overflow: hidden;'>";
    buf += "        <div id='task_manager_edit_win_panel' style='overflow: hidden;'>";
    buf += "            <iframe name='task_manager_edit_win' src='ui/task_manager.php' class='task_manager_style'></iframe>";
    buf += "        </div>";    
    buf += "   </div>";    
    buf += "   <div region='center' title='' noheader='true' split='true' style='overflow: hidden;'>";
    
    // tab
    buf += "        <div id='task_manager_edit_win_layout_tab' fit='true' border='false'>";
    buf += "            <div title='Property' closable='false' id='task_manager_edit_win_detail'></div>";
    buf += "            <div title='Slot' closable='false' id='task_manager_edit_win_slot_structure'></div>";
    buf += "            <div title='DA Type' closable='false' id='task_manager_edit_win_da_type'></div>";
    buf += "        </div>";
        
    buf += "        <div id='task_manager_task_info_tmenu' style='width: 100px; display: none;'>";
    buf += "            <div iconCls='icon-ok'>add</div>";
    buf += "            <div iconCls='icon-ok'>delete</div>";
    buf += "            <div iconCls='icon-ok'>update</div>";
    buf += "        </div>";

    buf += "        <div id='dialog_act_tagger_slot_structure_context_menu' style='width: 100px;'>";
    buf += "            <div iconCls='icon-add'>add</div>";
    buf += "            <div iconCls='icon-edit'>edit</div>";
    buf += "			<div class='menu-sep'></div>";
    buf += "            <div iconCls='icon-add'>add class</div>";
    buf += "            <div iconCls='icon-edit'>edit class</div>";
    buf += "        </div>";

    buf += "   </div>";

    buf += "</div> <!-- task_manager_edit_win_layout_top -->";

	task_manager_edit_win_layout_top = buf;
    buf = "";
	
	// task_manager_edit_win_layout
    buf += "<div id='task_manager_edit_win_layout' fit='true'>";

    buf += "   <div region='north' title='' noheader='true' split='true' style='height: 230px; overflow: hidden;'>";
  
    buf += task_manager_edit_win_layout_top;
    
    buf += "   </div>";
    buf += "   <div region='center' title='' noheader='true' split='true' style='overflow: hidden;'>";
    // tab
    buf += "        <div id='task_manager_edit_win_layout_dialog_lib_tab' fit='true' border='false'>";
    buf += "            <div title='transaction' closable='false' id='task_manager_edit_win_dialog_library_transaction' selected='true'></div>";
    buf += "            <div title='progress' closable='false' id='task_manager_edit_win_dialog_library_progress'></div>";
    buf += "            <div title='response' closable='false' id='task_manager_edit_win_dialog_library_response'></div>";
    buf += "        </div>";

    buf += "   </div>";
    
    buf += "</div>";  

	task_manager_edit_win_layout = buf;
    buf = "";

    // main tab
    buf += "<div id='task_manager_edit_win_main_tab' fit='true' border='false'>";
    buf += "    <div title='task manager' closable='false' style='overflow: hidden;'>";
    
    buf += task_manager_edit_win_layout;

    buf += "    </div>";
    
    // task information layout 
    buf += "    <div title='knowledge' closable='false' style='overflow: hidden;'>";
    buf += "        <div id='dialog_act_tagger_extra_db_layout' fit='true'>";
    buf += "            <div region='west' id='dialog_act_tagger_extra_db_list' title='Table List' style='width: 200px;' split='true'></div>";
    buf += "            <div region='center' id='dialog_act_tagger_extra_db' title='TABLE_NAME' style='' split='true'></div>";
    buf += "        </div>";
    buf += "    </div>";

    // buf += "    <div title='user dictionary' closable='false' style='overflow: hidden;'>";
    // buf += "        <div id='dialog_act_tagger_user_dict_layout' title='User Dictionary'>";
    // buf += "        </div>";
    // buf += "    </div>";

    buf += "</div>";
    
    var win_result = $("<div style='overflow: hidden;'>"+buf+"</div>").appendTo("body");
    
    // open window
    win_result.window({  
        "iconCls"   : "icon-ok", 
        "title"     : "Edit Task Manager",  
        "width"     : 700,  
        "height"    : dialog_act_tagger_get_window_heigth(550), 
        "modal"     : false,
        "inline"    : false,
        "collapsible" : false,
        "minimizable" : false,
        "attribute" : [],
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            // display task_manager    
            $("#task_manager").html("<iframe src='ui/task_manager.php' class='task_manager_style'></iframe>");
            get_slot_structure($("#dialog_act_tagger_slot_structure"), "read_only");
            
            $(this).remove();
        }       
    });
    
    // build main tab
    $("#task_manager_edit_win_main_tab").tabs({});

    // build task manager tab
    $("#task_manager_edit_win_layout").layout({});    
    $("#task_manager_edit_win_layout_top").layout({});
    
    $("#task_manager_edit_win_layout_tab").tabs({});  
    
    
    $("#task_manager_edit_win_layout_dialog_lib_tab").tabs({  
        "tools": [{  
            "iconCls": "icon-add",  
            "handler": function(){  
                task_manager_add_new_dialog_library(window, $("#task_manager_edit_win_layout_dialog_lib_tab"));
            }
        }]  
    });
    
    $("#task_manager_edit_win_panel").panel({
        "fit": true,
        "title": "task manager",
        "tools": [{           
            "iconCls": "icon-reload",  
            "handler":function(){
                task_manager_update_edited_task();
            }
        }, {
            "iconCls": "icon-add",  
            "handler":function(){
                task_manager_add_task();
            }
        }, {
            "iconCls": "icon-remove",  
            "handler":function(){
                task_manager_delete_task();
            }
        }, {
            "iconCls": "icon-save",  
            "handler":function(){
                task_manager_save_task_info();
            }
        }] 
    });
        
    // slot structure
    get_slot_structure($("#task_manager_edit_win_slot_structure"), "edit");    
    
	$("#dialog_act_tagger_slot_structure_context_menu").menu({
		"onClick": function(item){
            slot_structure_context_menu(item.text);
		}
	});
    
    // da type
    get_da_type($("#task_manager_edit_win_da_type"));

    // create memnu
	$("#task_manager_task_info_tmenu").menu({
		"onClick": function(item){
            task_manager_task_info_propertygrid_tmenu(item.text);
		}
	});

    // build extra db tab
    $("#dialog_act_tagger_extra_db_layout").layout({});

    $("#dialog_act_tagger_user_dict_layout").panel({});

//    task_manager_init_user_dictionary();

    $("#dialog_act_tagger_extra_db_list").empty();
    $("#dialog_act_tagger_extra_db_list").panel({}); 

    dialog_act_tagger_get_db_table_list();
}
//------------------------------------------------------------------------------
function task_manager_init_user_dictionary() {
    var buf = "";

    buf += "<div>";
    buf += "    <select id='user_dict_name'>";
    buf += "        <option value='ADDRESS' selected='selected'>ADDRESS</option>";
    buf += "        <option value='POI'>POI</option>";
    buf += "    </select>";
    buf += "    <span> Key: </span><input id='user_dict_key' type=text value='' />";
    buf += "    <button onclick='task_manager_search_user_dict()'>Search</button>";
    buf += "</div>";
    buf += "<div id='user_dict_result'></div>";


    $("#dialog_act_tagger_user_dict_layout").html(buf);
}
//------------------------------------------------------------------------------
function task_manager_search_user_dict() {
    var post_data = {
        "request": "task_manager_search_user_dict",
        "user_dict_name": $("#user_dict_name").val(),
        "user_dict_key": $("#user_dict_key").val(),
    };

    $("#user_dict_result").panel({'title': 'search result: ' + post_data['user_dict_key']});

    $.post(g_variable["_POST_URL_"], post_data, function(data) {
        $("#user_dict_result").html(post_data['user_dict_key']);
    });
}
//------------------------------------------------------------------------------
function task_manager_clear_task_info() {
    $("#task_manager_edit_win_detail").empty();
}
//------------------------------------------------------------------------------
function task_manager_display_task_info(_win, _idx) {
    var buf = "";
    
    buf += "<table id='task_manager_task_info'></table>";
    
    $("#task_manager_edit_win_detail").empty();
    $("#task_manager_edit_win_detail").html(buf);
    
    // create propertygrid
    var task = _win.g_task_info[_idx];
    
    var task_property = $("#task_manager_task_info");
    
    task_property.propertygrid({
        "fit": true,
        "task_id": task.task_id,
        "showGroup": true,  
        "onAfterEdit": function(rowIndex, rowData, changes) {
            task_manager_update_edited_task();
        },
        "onRowContextMenu": function(e, rowIndex, rowData){
        	e.preventDefault();
			
			if( rowData.group == "common information" ) {
	        	dialog_act_tagger_show_menu($("#task_manager_task_info_tmenu"), e);
	       }
        }
    });

    // make row data
    
    // reordering
    var disp_order = {
        "task-task_name": 1, 
        "task-task_goal": 1, 
        "task-fill_slot": 1, 
        "task-task_type": 1,
        "task-reset_slot": 1,
        "task-related_slot": {
        	"group": "response information"
        }, 
        "task-next_task": {
        	"disp_order": {"task_name":1, "controller":1, "condition":1}
        }
    };
    
    var editors = {
		"task_type": [
            {"val": "essential", "name": "essential"},
            {"val": "optional",  "name": "optional"}
	   	], 
		"controller": [
			{"val": "system", "name": "system"},
			{"val": "user",   "name": "user"}
  	   	], 
		"reset_slot": [
			{"val": "yes", "name": "yes"},
			{"val": "no",  "name": "no"}
  	   	]
    };
    
    // task
    var rows = [];
    task_info_to_griddata(_win, _idx, disp_order, editors, rows);
    
    task_property.propertygrid("loadData", {"total": rows.length, "rows": rows} );
}
//------------------------------------------------------------------------------
function task_manager_task_info_propertygrid_tmenu(type) {
    var grid = $("#task_manager_task_info");
    
	var row = grid.propertygrid("getSelected");
    
    if( type == "add" ) {
		if( row == null ) return;

		var index = grid.propertygrid("getRowIndex", row);
		grid.propertygrid("endEdit", index);
        
        $.messager.prompt("Enter Name", "Enter Name: ", function(r){
    		if( r ){
                var new_key = row.key;
                new_key.pop();
                new_key.push(r);
                
                // add propertygrid                
				grid.propertygrid("insertRow", {
				    "index": index,
                    "row": {
    					"key": new_key,
    					"name": r,
    					"value": "NEW_VALUE",  
                        "group": "common information",  
                        "editor": "text"
                    }
				});
    		}
    	}); 
    } else if( type == "delete" ) {
		if( row == null ) return;
        
		var index = grid.propertygrid("getRowIndex", row);
		grid.propertygrid("deleteRow", index);
        
        task_manager_update_edited_task();
    } else if( type == "update" ) {
        task_manager_update_edited_task();
    }
}
//------------------------------------------------------------------------------
function task_info_to_griddata(_win, _idx, disp_order, _editors, ret) {
    var task = _win.g_task_info[_idx];

    for( var i in disp_order ) {
        var str_name = i;
        str_name = str_name.replace("task-", ""); 
        
        if( typeof(task[i]) == "object" ) {
            // next task
            if( str_name == "next_task" ) {
                var next_task_item = task[i]["next_task_item"];
                
                if( $.isArray(next_task_item) ) {
                	var sub_disp_order = {};
                	if( typeof(disp_order[i]["disp_order"]) != "undefined" ) {
                		sub_disp_order = disp_order[i]["disp_order"];
                	}
                	
                    for( var k in next_task_item ) {
                    	if( typeof(disp_order[i]["disp_order"]) == "undefined" ) {
                        	sub_disp_order = next_task_item[k];
                    	}
                    	
                        for( var j in sub_disp_order ) {
                            ret.push({  
                                "key": [i, "next_task_item", k, j],  
                                "name": j,  
                                "value": next_task_item[k][j],  
                                "group": next_task_item[k]["task_name"],  
                                "editor": task_manager_property_get_editor(_editors, j)  
                            });          
                        }
                    }
                } else {
                    for( var j in next_task_item ) {
                        ret.push({  
                            "key": [i, "next_task_item", j],  
                            "name": j,  
                            "value": next_task_item[j],  
                            "group": next_task_item["task_name"],  
                            "editor": task_manager_property_get_editor(_editors, j)  
                        });          
                    }
                }
            }
            
            // fill slot
            if( str_name == "fill_slot" ) {
                var slot = task[i]["slot"];
                
                if( !$.isArray(slot) ) {
                    slot = task[i]["slot"] = [slot];
                    _win.g_task_info[_idx] = task;
                }                
                    
                for( var j in slot ) {
                    ret.push({  
                        "key": [i, "slot", j],  
                        "name": "slot",  
                        "value": slot[j],  
                        "group": "progress information",  
                        "editor": task_manager_property_get_editor(_editors, j)  
                    });          
                }
            }
            
            continue;
        }
        
        var group = "common information";
        if( typeof(disp_order[i]["group"]) != "undefined" ) group = disp_order[i]["group"]; 

        ret.push({  
            "key": [i],  
            "name": str_name,  
            "value": task[i],  
            "group": group,  
            "editor": task_manager_property_get_editor(_editors, str_name)  
        });          
    }
}
//------------------------------------------------------------------------------
function task_manager_property_get_editor(_editors, _name) {
	var editor = "text";
	
    if( typeof(_editors[_name]) != "undefined" ) {
        if( $.isArray(_editors[_name]) ) {
            editor = {  
                "type":"combobox",  
                "options":{  
                    "textField": "name",
                    "valueField": "val",
                    "data": _editors[_name],
                    "required": true
                }  
            }; 
        } else {
        	editor = _editors[_name];
        }
    }
    
    return editor;
}
//------------------------------------------------------------------------------
function get_task_list_combobox_data(task_info) {
    var task_list = [];
    
    for( var i in task_info ) {
        var task_name = task_info[i]["task-task_name"];
        
        task_list.push({
            "val": task_name, 
            "name": task_name
        });
    }
    
    return task_list;
}
