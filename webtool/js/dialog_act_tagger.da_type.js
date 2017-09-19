//------------------------------------------------------------------------------
function get_da_type(_target) {
    var post_data = {
		"request": "get_da_type",
        "project_name": $.cookie("project_name")
    };
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        display_da_type(_target, ret);
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_da_type_datagrid_context_menu_click(_item) {
	var grid = $("#dialog_act_tagger_da_type_datagrid");

	if( _item.text == "add" ) {
		grid.datagrid("appendRow", {
        	"key": {
        		"type": [-1, "define-DA_type"],
        		"class": [-1, "define-DA_class"]
        	},
            "type"     : "",  
            "class"    : "default",  
            "editor"   : "text"
		});
	} else if( _item.text == "delete" ) {
		var row = grid.datagrid("getSelected");
		var rowIndex = grid.datagrid("getRowIndex", row);
		
		grid.datagrid("deleteRow", rowIndex);
	} else if( _item.text == "edit" ) {
		edit_datagrid(grid, -1);
	} else if( _item.text == "save" ) {
		dialog_act_tagger_save_da_type(grid);
	}
}
//------------------------------------------------------------------------------
function dialog_action_update_datagrid_da_type(_grid, _da_type_data) {
	var changed = _grid.datagrid("getChanges");
	var deleted = _grid.datagrid("getChanges", "deleted");
	
	for( var i in changed ) {
		var t = changed[i]["type"];
		var c = changed[i]["class"];

		if( t == "" ) continue;
		
		if( changed[i].key["class"][0] < 0 ) {
			_da_type_data.push({
				"define-DA_class": c,
				"define-DA_type": t,
				"define-_idx": -1,
				"define-_position": ""
			});			
		} else {
			var cmd = "";
			
			// change type
			cmd = "_da_type_data"+get_key_str_cmd(changed[i].key["type"]);
			eval(cmd + " = \""+t+"\"");

			// change class
			cmd = "_da_type_data"+get_key_str_cmd(changed[i].key["class"]);
			eval(cmd + " = \""+c+"\"");
		}
	}
	
	for( var i in deleted ) {
		var t = changed[i]["type"];

		if( t == "" ) continue;
		
		if( changed[i].key["class"][0] < 0 ) continue;

		var idx = changed[i].key["class"][0];		
		_da_type_data.splice(idx, 1);
	}

	return _da_type_data;
}
//------------------------------------------------------------------------------
function dialog_act_tagger_save_da_type(_grid) {
	var da_type_data = _grid.datagrid("options").da_type_data;
	
	da_type_data = dialog_action_update_datagrid_da_type(_grid, da_type_data);
	
    var post_data = {
        "request": "dialog_act_tagger_save_da_type",
        "da_type_data": da_type_data
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		var ret = JSON.parse(data);
		
		if( typeof(ret["msg"]) != "undefined" ) {
			$.messager.alert("알림", ret["msg"], "info");
			
			get_da_type($("#task_manager_edit_win_da_type"));
		}
    });	
}
//------------------------------------------------------------------------------
function display_da_type(_target, _data) {
    var buf = "";
    
    buf += "<table id='dialog_act_tagger_da_type_datagrid'></table>";
    buf += "<div id='dialog_act_tagger_da_type_datagrid_context_menu' style='width: 130px; display: none;'>";
    buf += "	<div iconCls='icon-add'>add</div>";
    buf += "	<div iconCls='icon-remove'>delete</div>";
    buf += "	<div iconCls='icon-edit'>edit</div>";
    buf += "	<div class='menu-sep'></div>";
    buf += "	<div iconCls='icon-save'>save</div>";
    buf += "</div>";
    
    _target.empty();
    _target.html(buf);
    
	// build menu
	$("#dialog_act_tagger_da_type_datagrid_context_menu").menu({
		"onClick": function(item){
			dialog_act_tagger_da_type_datagrid_context_menu_click(item);
		}
	});
    
    var grid = $("#dialog_act_tagger_da_type_datagrid");

    var columns = [{
        "field": "class", 
        "title": "class", 
        "width": 100,
        "editor": {  
            "type":"combobox",  
            "options":{  
                "textField": "name",
                "valueField": "val",
                "editable": false,
                "data": [
                    {"name": "default", "val": "default"},
                    {"name": "inform",  "val": "inform"},
                    {"name": "request", "val": "request"},
                    {"name": "confirm", "val": "confirm"},
                    {"name": "confreq", "val": "confreq"},
                    {"name": "select",  "val": "select"},
                    {"name": "affirm",  "val": "affirm"},
                    {"name": "negate",  "val": "negate"},
                    {"name": "hello",   "val": "hello"},
                    {"name": "bye",     "val": "bye"},
                    {"name": "command", "val": "command"}
                ],
                "required": true,
                "onSelect": function(rec){
                    if( typeof(rec["func"]) == "function" ) rec["func"](rec);
                }
            }  
        } 
    },{
	    "field": "type",  
        "title": "type", 
        "width": 80,
        "editor": "text" 
    }];

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : false,
        "rownumbers"    : false,  
        "singleSelect"  : true,
        "pagination"    : false,
        "da_type_data"  : _data,
        "columns"       : [columns],
		"onDblClickRow": function(rowIndex){
			edit_datagrid($(this), rowIndex);
		},
		"onClickRow":function(rowIndex){
			end_edit_datagrid($(this), null);
		},
        "onHeaderContextMenu": function(e, field){
            end_edit_datagrid($(this), e);
            dialog_act_tagger_show_menu($("#dialog_act_tagger_da_type_datagrid_context_menu"), e);            
        },
        "onRowContextMenu": function(e, rowIndex, rowData){
            end_edit_datagrid($(this), e);
            dialog_act_tagger_show_menu($("#dialog_act_tagger_da_type_datagrid_context_menu"), e);            
        }
	});
    
    var rows = [];
    for( var i in _data ) {
        var k = _data[i]["define-DA_class"];
        var v = _data[i]["define-DA_type"];

        rows.push({
        	"key": {
        		"type": [i, "define-DA_type"],
        		"class": [i, "define-DA_class"]
        	},
            "type"     : v,  
            "class"    : k,  
            "editor"   : "text"
        });
    }
    
    grid.datagrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
