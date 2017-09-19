//------------------------------------------------------------------------------
var g_slot_structure_class_list = [];
//------------------------------------------------------------------------------
function get_slot_structure(_target, _mode) {
    var post_data = {
		"request": "get_slot_structure",
        "project_name": $.cookie("project_name")
    };
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var slot_structure = JSON.parse(data);
        
        display_slot_structure(_target, _mode, slot_structure);
    });
}
//------------------------------------------------------------------------------
function display_slot_structure(obj_result, _mode, _slot_structure) {
    // convert json to tree data
    var tree_data = [];
    var description_list = {};

    
    var cnt = 0;
    
    delete g_slot_structure_class_list;
    g_slot_structure_class_list = [];

    for( var class_name in _slot_structure ) {
        var child = [];

        for( var slot_name in _slot_structure[class_name] ) {
            if( slot_name == "" ) continue;
            
            cnt++;
            var id = "slot_structure_"+cnt;
            
            description_list[id] = _slot_structure[class_name][slot_name]["description_korean"];
            
            child.push({
                "attributes": _slot_structure[class_name][slot_name], 
                "id": id, 
                "state": "open", 
                "text": slot_name, 
                "iconCls": "icon-ok"
            });
        }    
        
        if( class_name == "" ) continue;
        
        g_slot_structure_class_list.push(class_name);
        
        tree_data.push({
            "attributes": _slot_structure[class_name][""], 
            "id": "slot_structure_"+eval(++cnt), 
            "state": "open", 
            "text": class_name, 
            "iconCls": "icon-folder",
            "children": child 
        });
    }    
    
    // display tree
    var uid = uniqid();

    var buf = "<div id='"+uid+"' style='height: 100%; overflow: auto;'></div>";
    
    obj_result.html(buf);

	obj_tree = $("#"+uid);
    
	obj_tree.tree({
		"slot_info"	: _slot_structure,
		"checkbox"	: false,
		"fit"		: true,
        "data"		: tree_data,
        "slot_structure_data": _slot_structure,
        "onClick": function(node){
            if( _mode == "read_only" ) slot_structure_click(node);
		},
        "onContextMenu": function(e, node){
            e.preventDefault();

            if( _mode == "read_only" ) return;
            obj_tree.tree("select", node.target);
            
            var menu = $("#dialog_act_tagger_slot_structure_context_menu");            
            if( menu ) {
                menu.menu("show", {
                    "left": e.pageX,
                    "top": e.pageY
                });
            }
        }
	});

    // set tooltip for slot description 
//    for( var id in description_list ){
//        var node = obj_tree.tree("find", id);
//        $(node.target).attr("title", description_list[id]);        
//    }
}
//------------------------------------------------------------------------------
function slot_structure_context_menu(type) {
	var tree = $("#task_manager_edit_win_slot_structure");
	
    if( type == "add" ) {
    	dialog_act_tagger_open_slot_structure_edit_win(tree, "add", "slot"); 
    } else if( type == "edit" ) {
    	dialog_act_tagger_open_slot_structure_edit_win(tree, "edit", "slot");
    } else if( type == "add class" ) {
    	dialog_act_tagger_open_slot_structure_edit_win(tree, "add", "class");
    } else if( type == "edit class" ) {
    	dialog_act_tagger_open_slot_structure_edit_win(tree, "edit", "class");
    }
}
//------------------------------------------------------------------------------
function dialog_act_tagger_open_slot_structure_edit_win(_slot_tree, mode, type) {
	// type is class or slot
	var data = {};
	var node = null;

	if( _slot_tree ) {
        node = _slot_tree.tree("getSelected");   
    	if( node ) data = node.attributes;
    }
	
    if( mode == "add" ) {
    	var class_name = ( typeof(data["class-name"]) != "undefined" ) ? data["class-name"] : "";
    	
    	if( type == "class" ) class_name = "NEW CLASS NAME";
    	
    	data = {
	    	"class-_idx"       : -1,
    		"class-_position"  : "",
    		"class-description": "",
    		"class-key"   : "",
    		"class-name"  : class_name,
    		"class-slots" : "",
    		"class-source": "",
    		"slot-_idx"       : -1,
    		"slot-_position"  : "",
    		"slot-description": "",
    		"slot-name"       : "",
    		"slot-preceding_slots": "",
    		"slot-source" : "system",
    		"slot-type"   : "string",
    		"slot-value_define": ""    	
    	};
    } 
    
    if( data == null || typeof(data) == "undefined" ) return; 
    
    if( typeof(type) == "undefined" || type == "" ) {
    	type = "slot";

    	if( _slot_tree && node != null ) {
            if( !_slot_tree.tree("isLeaf", node.target) ) type = "class";
        }
    }
    
    // open window
    var win_result = $("<div></div>").appendTo("body");

    win_result.window({  
        "iconCls"   : "icon-ok",
        "title"     : "Edit Slot Structure.",  
        "width"     : 600,  
        "height"    : dialog_act_tagger_get_window_heigth(400), 
        "modal"     : true,
        "inline"    : false,
        "collapsible" : false,
        "minimizable" : false,
        "tools" : [{
            "iconCls": "icon-delete_slot",  
            "handler":function(){
            	dialog_act_tagger_delete_slot_structure(win_result);
            }
        }, "-", {
            "iconCls": "icon-save",  
            "handler":function(){
            	dialog_act_tagger_slot_structure_save_propertygrid(win_result);
            }
        }, "-"],
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
        	var grid = $("#dialog_act_tagger_slot_structure_property");
        	dialog_act_tagger_end_edit_all_grid(grid, "propertygrid");
        	
        	grid.remove();
			
        	get_slot_structure($("#task_manager_edit_win_slot_structure"), "edit");  

            $(this).remove();
        }       
    });
    
    disp_slot_structure_contents(win_result, data, type);
}
//------------------------------------------------------------------------------
function disp_slot_structure_contents(_target, _data, type) {
    var class_editor = [];
    for( var i in g_slot_structure_class_list ) {
    	class_editor.push({
    		"name": g_slot_structure_class_list[i],
    		"val":  g_slot_structure_class_list[i]
    	});
    }
    
    var disp_order = {};
    
    if( type == "class" ) {
        disp_order = {
            "class-name": {
            	"group" : "class information",
            	"editor": class_editor
            },
            "class-description": {
            	"group": "class information"
            },
            "class-key": {
            	"group": "class information"
            },
            "class-source": {
            	"group" : "class information",
                "editor": [
                    {"val": "system", "name": "system"},
                    {"val": "user",   "name": "user"}
                ]            
            }
        };
    } else {
        disp_order = {
            "class-name": {
            	"group" : "class information",
            	"editor": class_editor
            },
            "slot-name": {
            	"group": "slot information"
            },
            "slot-source": {
            	"group" : "slot information",
                "editor": [
                    {"val": "system", "name": "system"},
                    {"val": "user",   "name": "user"},
                    {"val": "KB", "name": "KB"},
                    {"val": "dialog_user", "name": "dialog_user"},
                    {"val": "dialog_system", "name": "dialog_system"}
                ]            
            },
            "slot-type": {
            	"group": "slot information",
                "editor": [
                    {"val": "int",    "name": "int"},
                    {"val": "float",  "name": "float"},
                    {"val": "string", "name": "string"},
                    {"val": "time",   "name": "time"},
                    {"val": "date",   "name": "date"},
                    {"val": "event",  "name": "event"}
                ]            
            },
            "slot-description": {
            	"group": "slot information"
            },
            "slot-preceding_slots": {
            	"group": "slot information"
            },
            "slot-sense": {
                "group": "slot information"
            },
            "slot-value_define": 1
        };
    }

    // make propertygrid
	var buf = "";
	
    buf += "<table id='dialog_act_tagger_slot_structure_property'></table>";
    buf += "<div id='dialog_act_tagger_slot_structure_property_context_menu' style='width: 100px; display: none;'>";
    buf += "	<div iconCls='icon-add'>add</div>";
    buf += "	<div iconCls='icon-remove'>delete</div>";
    buf += "</div>";

    _target.empty();
    _target.html(buf);
    
    // build menu
	$("#dialog_act_tagger_slot_structure_property_context_menu").menu({
		"onClick": function(item){
			dialog_act_tagger_slot_structure_property_context_menu_click(item);
		}
	});
    
	// build propertygrid
    var grid = $("#dialog_act_tagger_slot_structure_property");

    grid.propertygrid({	
        "fit": true, 
        "showGroup": true,
        "scrollbarSize": 0,
        "slot_data" : _data,
        "disp_order": disp_order,
        "onRowContextMenu": function(e, rowIndex, rowData){
            e.preventDefault();
            
            var row = $(this).propertygrid("getSelected");
            
            if( row == null || !$.isArray(row.key) ) return;
            
            dialog_act_tagger_end_edit_all_grid($(this), "propertygrid");
            
            var menu = $("#dialog_act_tagger_slot_structure_property_context_menu");
            if( menu ) {
                menu.menu("show", {
                    "left": e.pageX,
                    "top": e.pageY
                });
            }
        }
	}); 
    
    display_slot_structure_edit_propertygrid(grid);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_slot_structure_property_context_menu_click(item) {
    var grid = $("#dialog_act_tagger_slot_structure_property");
    
    var row  = grid.propertygrid("getSelected");
	var rows = grid.propertygrid("getRows");
	
    var slot_data = grid.propertygrid("options").slot_data;
    var define = slot_data["slot-value_define"]["define"];
    
    if( item.text == "add" ) {
	    slot_data = dialog_action_update_propertygrid_slot_data(grid, slot_data);
    
        define.push({
            "condition": [""],
            "target_slot": [""],
            "action": [""]
        });
    } else {
        var idx = row.group.split(" ")[1];
        
        delete define[idx];
	}
    
    grid.propertygrid("options").slot_data["slot-value_define"]["define"] = define;

    display_slot_structure_edit_propertygrid(grid);
}
//------------------------------------------------------------------------------
function display_slot_structure_edit_propertygrid(_grid) {
	var slot_data   = _grid.propertygrid("options").slot_data;
	var _disp_order = _grid.propertygrid("options").disp_order;    

    var rows = [];     
    for( var k in _disp_order ) {
        var str_k = k;
        
        if( k == "class-name" ) str_k = "class";
        
        str_k = str_k.replace("class-", "");
        str_k = str_k.replace("slot-", "");
        
        var editor = "text";
        if( typeof(_disp_order[k]) == "object" && $.isArray(_disp_order[k]["editor"]) ) {
            editor = {  
                "type":"combobox",  
                "options":{  
                    "textField": "name",
                    "valueField": "val",
                    "editable": true,
                    "data": _disp_order[k]["editor"],
                    "required": true,
                    "onSelect": function(rec){
                        if( typeof(rec["func"]) == "function" ) rec["func"](rec);
                    }
                }  
            }; 
        }       
        
		if( str_k == "value_define" ) {
		    if( slot_data[k] == null ) {
		        slot_data[k] = {"define": null};
		    }
		    
			var define = slot_data[k]["define"];
			
			// empty fill default
			if( define == null || typeof(define) == "undefined" ) {
				define = [{
					"condition": "",
					"target_slot": "",
					"action": ""
				}];
				
				_grid.propertygrid("options").slot_data[k] = {"define" : define};
			}
			
			// check isarray
			if( !$.isArray(define) ) {
			    define = [define];

                _grid.propertygrid("options").slot_data[k] = {"define" : define};
			}
			
			for( var i in define ) {
//			    if( !$.isArray(define[i]) ) define[i] = [define[i]];

		    	for( var kk in define[i] ) {
		    		if( $.isArray(define[i][kk]) ) {
			           	rows.push({
			                "key"   : [k, "define", i, kk],  
			                "name"  : kk,  
			                "value" : define[i][kk][0],  
			                "group" : str_k+" "+i,  
			                "editor": editor
			            });
		    		} else {
			           	rows.push({
			                "key"   : [k, "define", i, kk],  
			                "name"  : kk,  
			                "value" : define[i][kk],  
			                "group" : str_k+" "+i,  
			                "editor": editor
			            });
		    		}
		    	}
			}
		} else {
            var group = "";
            if( typeof(_disp_order[k]["group"]) != "undefined" ) group = _disp_order[k]["group"]; 
            
            rows.push({
                "key"   : k,  
                "name"  : str_k,  
                "value" : slot_data[k],  
                "group" : group,  
                "editor": editor
            });
        }
    }
    
    _grid.propertygrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
function dialog_action_update_propertygrid_slot_data(_grid, _slot_data) {
	var changed = _grid.propertygrid("getChanges");
//	var deleted = _grid.propertygrid("getChanges", "deleted");
	
	for( var i in changed ) {
		var key = changed[i].key;
		var value = changed[i].value;
		
		if( !$.isArray(key) && key == "" ) continue;

		if( typeof(key) == "string" ) {
			delete _slot_data[key];
			_slot_data[key] = value;
		} else {
			value = value.replace(/\"/g, "\\\"");
			
			var cmd = "_slot_data" + get_key_str_cmd(key) + " = \"" + value + "\"";
			eval(cmd);
		}
	}
	
	return _slot_data;
}
//------------------------------------------------------------------------------
function dialog_act_tagger_slot_structure_save_propertygrid(_win) {
    var grid = $("#dialog_act_tagger_slot_structure_property");
    
    dialog_act_tagger_end_edit_all_grid(grid, "propertygrid");
    
    var slot_data = grid.propertygrid("options").slot_data;
    var prev_slot_name = slot_data["slot-name"];
        
    slot_data = dialog_action_update_propertygrid_slot_data(grid, slot_data);
    
    console.log(slot_data);
    
    var new_slot_name = slot_data["slot-name"];
    
    var post_data = {
		"request": "slot_structure_save",
        "slot_data": slot_data,
        "prev_slot_name": prev_slot_name
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
		if( typeof(ret["msg"]) != "undefined" ) $.messager.alert("알림", ret["msg"], "info");
        _win.window("close");
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_delete_slot_structure(_win) {
    var grid = $("#dialog_act_tagger_slot_structure_property");
    var slot_data = grid.propertygrid("options").slot_data;

    $.messager.confirm("슬롯 삭제 확인", slot_data["slot-name"]+"와 태스크 정보를 삭제하시겠습니까?", function(r){
		if( r ){
		    // slot_structure_delete
		    var post_data = {
				"request": "slot_structure_delete",
		        "slot_data": slot_data
		    };
		    
			$.post(g_variable["_POST_URL_"], post_data, function(data) {
		        var ret = JSON.parse(data);
		        
				if( typeof(ret["msg"]) != "undefined" ) {
					$.messager.alert("알림", ret["msg"], "info");
					_win.window("close");
				}
		    });
		}
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_end_edit_all_grid(_grid, _type) {
	var rows = null;
	if( _type == "propertygrid" ) {
		rows = _grid.propertygrid("getRows");
	} else if( _type == "treegrid" ) {
		rows = _grid.treegrid("getRows");
	} else if( _type == "datagrid" ) {
		rows = _grid.datagrid("getRows");
	}
	
	for( var i=0 ; i<rows.length ; i++ ) {
		if( _type == "propertygrid" ) {
			_grid.propertygrid("endEdit", i);
		} else if( _type == "treegrid" ) {
			_grid.treegrid("endEdit", i);
		} else if( _type == "datagrid" ) {
			_grid.datagrid("endEdit", i);
		}
	}
}
//------------------------------------------------------------------------------
function slot_structure_click(node) {
    if( node == null ) return;
    
    var selection_obj_name = g_variable["_selection_obj_name_"];
    
    var text = g_variable["_"+selection_obj_name+"_selectionText_"];
    
    var selectionStart = g_variable["_"+selection_obj_name+"_selectionStart_"];
    var selectionEnd   = g_variable["_"+selection_obj_name+"_selectionEnd_"];
    
    if( typeof(text) == "undefined" || text == "" ) return;
    if( typeof(selectionEnd) == "undefined" ) return;
    if( typeof(selectionStart) == "undefined" ) return;
        
    var obj = $("#"+g_variable["_UID_"]+"_"+selection_obj_name);
    
    if( selection_obj_name == "slot_tagged" ) {
        g_variable["_"+selection_obj_name+"_selectionEnd_"] = replaceIt(obj, selectionStart, selectionEnd, "<" + node.text + "=" + text + ">");   
    
        // update candidate
        get_utter_tagging_candidate(g_variable["slot_tagged_data"]["no"], obj.val());
        
        // update dialog_action
        update_dialog_action(); 
    } else {
        g_variable["_"+selection_obj_name+"_selectionEnd_"] = replaceIt2(obj, selectionStart, selectionEnd, node.text);   
    }
}
//------------------------------------------------------------------------------
function replaceIt2(obj, selectionStart, selectionEnd, newtxt) {
    obj.selectionStart = selectionStart;
    obj.selectionEnd   = selectionEnd;
    
    $(obj).val(
        $(obj).val().substring(0, obj.selectionStart)+newtxt+$(obj).val().substring(obj.selectionEnd)
    );
    
    obj.selectionStart = selectionStart;  
    obj.selectionEnd = selectionStart + newtxt.length;
    
    return (selectionStart + newtxt.length);
}
//------------------------------------------------------------------------------
