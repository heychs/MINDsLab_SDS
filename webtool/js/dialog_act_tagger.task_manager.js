//------------------------------------------------------------------------------
var g_task_info = null;
//------------------------------------------------------------------------------
function init_task_manager() {      
    if( $.cookie("project_name") == null ) return;
    
    g_variable["_POST_URL_"] = "../api/main.php";
    
    var post_data = {
        "request": "get_task_manager_info",
        "project_name": $.cookie("project_name")
    };
    
	if( $.cookie("project_type") == "guided" ) {
	    post_data = {
	        "request": "dialog_act_tagger_guide_get_task_info",
	        "project_name": $.cookie("project_name")
	    };
	}    
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        window.g_task_info = JSON.parse(data);

        console.log("window.g_task_info", window.g_task_info);

        // ready
        task_manager_update_task(window);
    });
}
//------------------------------------------------------------------------------
function init_jsPlumb(_doc, _jsPlumb) {
	_jsPlumb.importDefaults({
		"Endpoint" : [
            "Dot", {"radius": 5}
        ],
		"HoverPaintStyle" : {
            "lineWidth": 2,
		    "strokeStyle":"#42a62c" 
        },
		"ConnectionOverlays" : [
			[ "Arrow", { 
				"id": "arrow",
                "length": 5,
				"location": 1,
                "foldback": 0.9
			}]
		]
	});

    // initialise draggable elements.  note: jsPlumb does not do this by default from version 1.3.4 onwards.
	_jsPlumb.draggable(_jsPlumb.getSelector(".task"));

    _jsPlumb.makeTarget(_jsPlumb.getSelector(".task"), {
		"anchor": "Continuous",			
		"dropOptions":{ "hoverClass": "dragHover" }
	});            
    
	// initEndpoints
    $(_doc).find(".task_endpoint").each(function(i,e) {
		var p = $(e).parent();
		_jsPlumb.makeSource($(e), {
			"parent": p,
			"anchor": "Continuous",
			"connector": [ 
                "StateMachine", { 
                    "curviness": 10 
                } 
            ],
			"connectorStyle": { 
			    "strokeStyle": "#000000", 
                "lineWidth": 2 
            },
			"maxConnections": -1
		});
	});		            
}
//------------------------------------------------------------------------------
function task_manager_update_task(_win) {    
    var _jsPlumb = _win.jsPlumb;
    
    _jsPlumb.unload();
    
    _jsPlumb.bind("ready", function() {
        var _doc = _win.document;
        
    	// chrome fix.
    	_doc.onselectstart = function () { return false; };				

        // reset event
        if( typeof(_jsPlumb.clearListeners) == "function" ) {
            _jsPlumb.clearListeners("jsPlumbConnection");
            _jsPlumb.clearListeners("jsPlumbConnectionDetached");
        }
        _jsPlumb.reset();
        
        var task_info = trim_array(_win.g_task_info);
        
        // make html task node
    	var contents = "";        
        for( var i in task_info ) {
            task_info[i]["task_id"] = "task_"+i;
            
            contents += '<div class="task" id="'+task_info[i]["task_id"]+'">';
            contents += '   <span class="task_endpoint"></span>';
            contents += '   &nbsp;'+task_info[i]["task-task_name"];
            contents += '</div>';
        }
        
        // update task_info        
        _win.g_task_info = task_info;
        
        // save
        $(_doc).find("#task_manager").empty();
        $(_doc).find("#task_manager").html(contents);

        _jsPlumb.setRenderMode(_jsPlumb.SVG);    
        init_jsPlumb(_doc, _jsPlumb);
        
    
        // bind a click listener to each connection; the connection is deleted.
    	_jsPlumb.bind("dblclick", function(c) { 
            if( !task_manager_disconnect_task(_win, c) ) return;
                
            _jsPlumb.detach(c); 
    	});
        

        var task_info = trim_array(_win.g_task_info);

        // set position
        var task_index = {};
        for( var i in task_info ) {
        	var task_name = $.trim(task_info[i]["task-task_name"]);
            task_info[i]["task-_position"]["task_id"] = task_index[task_name] = task_info[i]["task_id"];
            
            set_move_task(_win, task_info[i]["task-_position"]);
        }

        // set connection
        for( var i in task_info ) {
            var task_id = task_info[i]["task_id"];
            
            var next_task = task_info[i]["task-next_task"];
            for( var j in next_task ) {
                var task_name = $.trim(next_task[j]["task_name"]);
                
                if( task_name ) {
                    // one link
                    if( task_index[task_name] ) {
                		_jsPlumb.connect({
                            "source": task_id, 
                            "target": task_index[task_name]
                        });
                    }
                } else {
                    // multi link
                    for( var k in next_task[j] ) {
                        var task_name = $.trim(next_task[j][k]["task_name"]);
                        
                        if( task_index[task_name] ) {
                            try{
                        		_jsPlumb.connect({
                                    "source": task_id, 
                                    "target": task_index[task_name]
                                });
                            } catch(e) {                                
                                console.log(e, task_name, task_id, task_index[task_name]);
                            }
                        }
                    }
                }
            }
        }
        
        // set connection event
        _jsPlumb.bind("jsPlumbConnection", function(conn) {
            task_manager_connect_task(_win, conn);
        });
    
//        _jsPlumb.bind("jsPlumbConnectionDetached", function(conn) { });
    });                  
}
//------------------------------------------------------------------------------
function task_manager_disconnect_task(_win, conn) {
    var idx = conn.sourceId.split("_")[1];
    var idx_target = conn.targetId.split("_")[1];
    
    // check task_info    
    var task_info = _win.g_task_info;    
    if( task_info == null ) return;
    
    // delete next task
    var next_task = task_info[idx]["task-next_task"];
    
    if( typeof(next_task["next_task_item"]) == "undefined" ) return;
    
    var source_task_name = $.trim(task_info[idx]["task-task_name"]);
    var target_task_name = $.trim(task_info[idx_target]["task-task_name"]);
    
    // confirm 
    var msg = source_task_name + " -> " + target_task_name + " 간의 연결을 삭제하시겠습니까?";
    if( !confirm(msg) ) {
        return false;
    }    
    
    // disconnect task    
    var next_task_item = [];
    if( $.isArray(next_task["next_task_item"]) ) {
        for( var i in next_task["next_task_item"] ) {
            var task = next_task["next_task_item"][i];
            
            var source_task_name = $.trim(task["task_name"]); 
            if( source_task_name == target_task_name ) continue;   
            
            next_task_item.push(task);
        } 
        
        if( next_task_item.length == 1 ) {
            next_task_item = next_task_item[0];
        }               
    } else {
        var source_task_name = $.trim(next_task["next_task_item"]["task_name"]);
        
        if( source_task_name != target_task_name ) {
            next_task_item = next_task["next_task_item"];
        }
    }
    
    _win.g_task_info[idx]["task-next_task"]["next_task_item"] = next_task_item;
    
    return true;
}
//------------------------------------------------------------------------------
function task_manager_connect_task(_win, conn) {
    conn.connection.setPaintStyle({"strokeStyle": "#000000"});
    
    var idx = conn.sourceId.split("_")[1];
    var idx_target = conn.targetId.split("_")[1];
    
    var task_info = _win.g_task_info;    
    if( task_info == null ) return;
    
    // add next task
    var next_task = task_info[idx]["task-next_task"];
    
    if( typeof(next_task) == "undefined" ) next_task = {};
    
    var task = {
        "task_name": task_info[idx_target]["task-task_name"],
        "condition": 0,
        "controller": "system"
    };
    
    if( typeof(next_task["next_task_item"]) == "undefined" ) {
        if( typeof(task_info[idx]["task-next_task"]) == "undefined" ) {
            task_info[idx]["task-next_task"] = {"next_task_item" : task};   
        } else {
            task_info[idx]["task-next_task"]["next_task_item"] = task;
        }
    } else {
        var next_task_item = [];
        
        if( typeof(next_task["next_task_item"]) == "undefined" ) {
        	next_task["next_task_item"] = {};
        } 
        
        if( $.isArray(next_task["next_task_item"]) ) {
            next_task_item = next_task["next_task_item"];
        } else if( typeof(next_task["next_task_item"]["task_name"]) != "undefined" ){
            next_task_item.push(next_task["next_task_item"]);
        }
    
        next_task_item.push(task);
    
        task_info[idx]["task-next_task"]["next_task_item"] = next_task_item;
    } 
    
    _win.g_task_info = task_info;
}
//------------------------------------------------------------------------------
function task_manager_delete_task() {    
    var _win = task_manager_get_edit_win();
    
    var all_div = $(_win.document).find("div");
    
    if( _win.g_task_info == null ) return;
    
    for( var i in all_div ) {
        var obj = all_div[i];
        
        var task_id = $(obj).attr("id");        
        if( typeof(task_id) == "undefined" || task_id == "" || task_id.indexOf("task_") != 0 ) continue;
        
        var str_color = $(obj).css("background-color");
        
        if( str_color == "" || str_color == "rgba(0, 0, 0, 0)" ) continue;

        // remove link
        task_manager_delete_one_task(_win, task_id);
        
        // refresh
        task_manager_update_task(_win);        
        
        break;        
    }
}
//------------------------------------------------------------------------------
function task_manager_delete_one_task(_win, task_id) {
    var idx = task_id.split("_")[1];
    
    var task_info = _win.g_task_info;
    var task_name = trim(task_info[idx]["task-task_name"]);
    
//    console.log(task_info[idx]);

    // delete all link
    for( var i in task_info ) {
        var next_task_item = task_info[i]["task-next_task"]["next_task_item"];
        
        if( typeof(next_task_item) == "undefined" ) continue;
        
        if( $.isArray(next_task_item) ) {
            for( var j in next_task_item ) {
            	if( typeof(next_task_item[j]) == "undefined" ) continue;
            	
                if( trim(next_task_item[j]["task_name"]) == task_name ) {
                    delete task_info[i]["task-next_task"]["next_task_item"][j];
                }
            }
        } else {
            if( trim(next_task_item["task_name"]) == task_name ) {
                delete task_info[i]["task-next_task"]["next_task_item"];
            }
        }
    }
    
    delete task_info[idx];
    _win.g_task_info = trim_array(task_info);
}
//------------------------------------------------------------------------------
function trim_array(obj) {
    var ret = [];
    
    var cnt = 0;
    for( var i in obj ) {
        if( typeof(obj[i]) == "undefined" || obj[i] == null || obj[i] == "undefined" ) continue;
        
        cnt++;
        obj[i]["task-_idx"] = cnt;
        
        ret.push(obj[i]);
    }
    
    return ret;    
}
//------------------------------------------------------------------------------
function task_manager_update_edited_task() {  
    var _win = task_manager_get_edit_win();
    
    var task_info = _win.g_task_info;
    if( task_info == null ) return;
        
    var opt = $("#task_manager_task_info").propertygrid("options");    
    var idx = opt.task_id.split("_")[1];

    var changed = $("#task_manager_task_info").propertygrid("getChanges");
    var changed_deleted = $("#task_manager_task_info").propertygrid("getChanges", "deleted");    
    
    var task = task_info[idx];    
    for( var i in changed ) {
        // convert array to [][]
        var cmd = "task" + get_key_str_cmd(changed[i].key);

        var v = changed[i].value.replace(/\"/g, "\\\"");
        
        cmd += " = \""+v+"\";";        
        eval(cmd);      
    }    
    
    for( var i in changed_deleted ) {
        // convert array to [][]
        var cmd = "delete task" + get_key_str_cmd(changed[i].key);
        eval(cmd);      
    }    
    task_info[idx] = task;
    
    _win.g_task_info = task_info;
    task_manager_update_task(_win);
}
//------------------------------------------------------------------------------
function get_key_str_cmd(k) {
    var cmd = "";
    for( var j in k ) {
        var str = k[j].replace(/\"/g, "\\\"");
        if( isNaN(parseInt(str)) ) str = "\"" + str + "\"";                
        
        cmd += "[" + str + "]";
    }
    
    return cmd;
}
//------------------------------------------------------------------------------
function task_manager_get_edit_win() { 
    return window.frames["task_manager_edit_win"];
}
//------------------------------------------------------------------------------
function task_manager_add_task() {  
    var _win = task_manager_get_edit_win();

    var task_info = _win.g_task_info;    
    if( task_info == null ) return;
    
    task_info.push({
        "task-task_name": "NEW_TASK",
        "task-related_slot": "",
        "task-task_goal": "1",
        "task-fill_slot": {"slot": [""]},
        "task-task_type": "essential",
        "task-reset_slot": "no",
        "task-next_task": {},
        "task-previous_task": {},

        "task-_idx": task_info.length,
        "task-_position": {"top": 0, "left": 0, "width": 100}
    });
    
    _win.g_task_info = task_info;
    
    // update
    task_manager_update_task(_win);
}
//------------------------------------------------------------------------------
function trim_array2(obj) {
    var ret = {};
    
    if( $.isArray(obj) ) ret = [];
        
    var cnt = 0;
    for( var i in obj ) {
        if( typeof(obj[i]) == "undefined" || obj[i] == null || obj[i] == "undefined" ) continue;
        
        if( $.isArray(obj) ) {
        	ret.push(obj[i]);
        } else {
        	ret[i] = obj[i];
        }
    }
    
//    console.log(obj, ret);
    
    return ret;    
}
//------------------------------------------------------------------------------
function task_manager_save_task_info() {
    // save task information
    var _win = task_manager_get_edit_win();
    
//    console.log(_win.g_task_info.length);
    
    for( var i=0 ; i<_win.g_task_info.length ; i++ ) {
//    	console.log(i);
    	
    	if( typeof(_win.g_task_info[i]) == "undefined" ) continue;
    	
	    var next_task_item = _win.g_task_info[i]["task-next_task"].next_task_item;
	    
	    next_task_item = trim_array2(next_task_item);
	    
//	    console.log(_win.g_task_info[i]["task-task_name"], "next_task_item", next_task_item);    
	    _win.g_task_info[i]["task-next_task"].next_task_item = next_task_item;    
    }
    
    var task_info = _win.g_task_info;    
    if( task_info == null ) return;
    
    var post_data = {
        "request": "task_manager_save_task_info",
        "task_info": task_info
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        alert("OK");        
    });
}
//------------------------------------------------------------------------------
function set_move_task(_win, param) {
    // set position
    var obj_task = $(_win.document).find("#"+param.task_id);
            
    if( param.top    ) obj_task.css("top", param.top);
    if( param.left   ) obj_task.css("left", param.left);
    if( param.width  ) obj_task.css("width", param.width);
//    if( param.height ) obj_task.css("width", param.height);

    obj_task.bind("dragstop", function(e, ui) {
        var position = {
            "top": $(this).css("top"), 
            "left": $(this).css("left"), 
            "width": $(this).css("width"), 
            "height": $(this).css("height")
        };
        
        var task_info = _win.g_task_info;
        if( task_info == null ) return;
        
        var idx = $(this).attr("id").split("_")[1];        
        task_info[idx]["task-_position"] = position;
        
        _win.g_task_info = task_info;
    });

    obj_task.bind("click", function(e) {
        // get selected task
        var task_info = _win.g_task_info;
        if( task_info == null ) return;
        
        // unclick all task
        for( var i in task_info ) {
            $(_win.document).find("#task_"+i).css("background-color", "");
        }        

        // check selected task
        $(this).css("background-color", "silver");

        // position
        var position = {
            "top": $(this).css("top"), 
            "left": $(this).css("left"), 
            "width": $(this).css("width"), 
            "height": $(this).css("height")
        };

        // save position
        var idx = $(this).attr("id").split("_")[1];            
        task_info[idx]["task-_position"] = position;
        
        _win.g_task_info = task_info;
        
        parent.task_manager_clear_task_info();
        parent.task_manager_clear_dialog_library();
        
        // when guide
        parent.task_clicked(task_info[idx]);        
    });
    
    obj_task.bind("dblclick", function(e) {
        // save position
        var idx = $(this).attr("id").split("_")[1];            

        parent.task_manager_display_task_info(_win, idx);
        parent.task_manager_display_dialog_library(_win, idx);
    });
}
//------------------------------------------------------------------------------
