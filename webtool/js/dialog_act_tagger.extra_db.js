

var db_recode = null;
function dialog_act_tagger_search_extra_db() {
    var keyword = $("#dialog_act_tagger_extra_db_keyword").searchbox("getValue");
    
    var slot_type = $("#dialog_act_tagger_extra_db_keyword_menu").val();
    var page_number = $("#dialog_act_tagger_extra_db_page_no").val();

    var post_data = {
        "request": "dialog_act_tagger_search_extra_db",
        "slot_type": slot_type,
        "keyword": keyword,
        "page_number": page_number,
        "page_size": 50,
        "project_name": $.cookie("project_name"),
        "table_name": dialog_act_tagger_get_table_name()
    };
    
    if( post_data.table_name == "TABLE_NAME" ) {
        return;
    }
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        var column_list = new Array();        
        for( var slot in ret.column_list ) {
            column_list.push(slot);            
        }

        db_recode = ret;
        db_recode.column_list = column_list;
        
        display_extra_db_record_list();
	});
}


function dialog_act_tagger_get_db_table_list() {
    $("#dialog_act_tagger_extra_db").empty();
    
    var post_data = {
        "project_name": $.cookie("project_name"),
        "request": "dialog_act_tagger_get_db_table_list"
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        display_extra_db_table_list(ret);
	});
}


function display_extra_db_table_list(data) {
    var disp_list = {
        "SLOT_MAPPING" : 1,
        "task_information" : 1,
        "canonical_form" : 1,
        "generation_form" : 1
    };
    
    var buf = "";
    for( var i in data ) {
        var tbl_name = data[i].tbl_name;
        
        if( typeof(disp_list[tbl_name]) == "undefined" ) continue;
        
        buf += "<div class='db_table_item' onclick=\"open_extra_db_table('"+tbl_name+"')\">" + tbl_name + "</div>\n";   
    }    
    buf = "<div style='padding: 5px;'>" + buf + "</div>"
    
    $("#dialog_act_tagger_extra_db_list").html(buf);
}


function open_extra_db_table(tbl_name) {
    // set title
    dialog_act_tagger_set_table_name(tbl_name); 
    
    // get column list
    var post_data = {
        "request": "dialog_act_tagger_get_extra_db_column_list",
        "project_name": $.cookie("project_name"),
        "table_name": tbl_name
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        var buf_menu = "";
        
        buf_menu = "<select id='dialog_act_tagger_extra_db_keyword_menu' style='width: 120px;'> ";                
        buf_menu += "<option value=''>ALL</option>";            
        for( var slot in ret ) {
            buf_menu += "<option value='" + slot + "'>" + slot + "</option>";            
        }
        buf_menu += "</select>";

        // display search box
        var buf = "";
        buf += "<table border=1 style='width: 100%;'>";
        buf += "    <tr><td style='background: #EFEFEF;'>";

        // search box
        buf += "<table>";
        buf += "    <tr><td>";
        buf += buf_menu;
        buf += "    </td><td>";
        buf += "        <input id='dialog_act_tagger_extra_db_keyword' value='' />";
        buf += "    </td><td>";
        buf += "        <span class='icon-dialog_act_tagger_pagination-prev' onclick='dialog_act_tagger_extra_db_prev_page()'></span>";
        buf += "    </td><td>";
        buf += "        Page <input type=text id='dialog_act_tagger_extra_db_page_no' value='0' style='width: 40px;'>";
        buf += "    </td><td>";
        buf += "        <span class='icon-dialog_act_tagger_pagination-next' onclick='dialog_act_tagger_extra_db_next_page()'></span>";
        buf += "    </td></tr>";
        buf += "</table>";
        
        buf += "    </td></tr>";
        
        buf += "    <tr><td id='dialog_act_tagger_extra_db_search_result'>";
        // search result
        buf += "&nbsp;";
        buf += "    </td></tr>";
        buf += "</table>";
          
        $("#dialog_act_tagger_extra_db").html(buf);
        
        $("#dialog_act_tagger_extra_db_keyword").searchbox({  
            "width": 200,  
            "searcher": function(value, name){
                dialog_act_tagger_search_extra_db();
            },  
            "prompt": "Please Input Search Keyword"  
        });  
        
        dialog_act_tagger_search_extra_db();  
	});
}


function dialog_act_tagger_extra_db_next_page() {
    var page_no = $("#dialog_act_tagger_extra_db_page_no").val();
    
    $("#dialog_act_tagger_extra_db_page_no").val(++page_no);
    
    dialog_act_tagger_search_extra_db();
}


function dialog_act_tagger_extra_db_prev_page() {
    var page_no = $("#dialog_act_tagger_extra_db_page_no").val();
    
    if( --page_no < 0 ) page_no = 0;
    
    $("#dialog_act_tagger_extra_db_page_no").val(page_no);
    
    dialog_act_tagger_search_extra_db();
}


function open_win_edit_db_record(i) {
	// open windows  
    var win_result = $("<div style='padding: 5px;'></div>").appendTo("body");
    
    // open window
    win_result.window({  
        "iconCls"   : "icon-ok",
        "title"     : "Edit DB Record",  
        "width"     : 600,  
        "height"    : dialog_act_tagger_get_window_heigth(400), 
        "modal"     : true,
        "inline"    : false,
        "collapsible" : false,
        "minimizable" : false,
        "tools" : [{
            "iconCls": "icon-new_extra_db_record",  
            "handler":function(){
            	dialog_act_tagger_edit_db_record_grid_clear(win_result);
            }
        },{
            "iconCls": "icon-delete_extra_db_record",  
            "handler":function(){
            	dialog_act_tagger_delete_extra_db_record(win_result);
            }
        }, "-",{
            "iconCls": "icon-add",  
            "handler":function(){
            	dialog_act_tagger_edit_db_record_grid_add();
            }
        },{
            "iconCls": "icon-save",  
            "handler":function(){
                dialog_act_tagger_save_extra_db(win_result);
            }
        }, "-"],
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose"   : function(forceClose){
            dialog_act_tagger_search_extra_db();
            $(this).remove();
        }       
    });
    
    display_one_db_record(win_result, i);
}


function dialog_act_tagger_edit_db_record_grid_clear(_win) {
	_win.empty();
	
    display_one_db_record(_win, -1);
}


function dialog_act_tagger_edit_db_record_grid_add() {
	var grid = $("#dialog_act_tagger_edit_db_record_grid");

	grid.datagrid("appendRow", {
		"name": "", 
		"value": "NEW_SLOT_VALUE"
	});
}


function dialog_act_tagger_edit_db_record_context_menu_click(_target, _item) {
	var grid = $("#dialog_act_tagger_edit_db_record_grid");

	if( _item.text == "add" ) {
		dialog_act_tagger_edit_db_record_grid_add();
	} else if( _item.text == "delete" ) {
		var row = grid.datagrid("getSelected");
		var rowIndex = grid.datagrid("getRowIndex", row);
		
		grid.datagrid("deleteRow", rowIndex);
	} else if( _item.text == "edit" ) {
		edit_datagrid(grid, -1);
	} else if( _item.text == "add new slot" ) {
		dialog_act_tagger_open_slot_structure_edit_win($("#task_manager_edit_win_slot_structure"), "add");
	}
}


function display_one_db_record(_target, _idx) {
	var buf = "";
	
    buf += "<table id='dialog_act_tagger_edit_db_record_grid'></table>";
    buf += "<div id='dialog_act_tagger_edit_db_record_context_menu' style='width: 130px; display: none;'>";
    buf += "	<div iconCls='icon-add'>add</div>";
    buf += "	<div iconCls='icon-remove'>delete</div>";
    buf += "	<div iconCls='icon-edit'>edit</div>";
    buf += "	<div class='menu-sep'></div>";
    buf += "	<div iconCls='icon-add'>add new slot</div>";
    buf += "</div>";
	
	// display 
	_target.empty();
	_target.html(buf);    

	// build menu
	$("#dialog_act_tagger_edit_db_record_context_menu").menu({
		"onClick": function(item){
			dialog_act_tagger_edit_db_record_context_menu_click(_target, item);
		}
	});

	// make editor data
    var editor_data = [];
    for( var i in column_list ) {
    	if( column_list[i].indexOf("_") == 0 ) continue;
    	
    	editor_data.push({
    		"name": column_list[i],
    		"value": column_list[i]
    	});
    }
    
    var columns = [{
		"editor": {                
			"type":"combobox",  
            "options":{  
                "textField": "name",
                "valueField": "value",
                "editable": false,
                "data": editor_data,
                "required": true
            }  
		}, 
		"field": "name",  
		"title": "slot name",  
		"width": 200
	}, {
		"editor": "text", 
		"field": "value", 
		"title": "slot value", 
		"width": 370
 	}];        
    
	var grid = $("#dialog_act_tagger_edit_db_record_grid");
    
    var item = {
		"_idx": -1
    };
    
    if( _idx >= 0 ) item = db_recode.data[_idx];
    
    // make datagrid
    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : false,
        "rownumbers"    : false,  
        "singleSelect"  : true,
        "pagination"    : false,
        "db_record"		: item,
        "columns"       : [columns],
		"onDblClickRow": function(rowIndex){
            edit_datagrid($(this), rowIndex);
		},
		"onClickRow": function(rowIndex){
            end_edit_datagrid($(this), null);
		},
        "onHeaderContextMenu": function(e, field){
            end_edit_datagrid($(this), e);
            dialog_act_tagger_show_menu($("#dialog_act_tagger_edit_db_record_context_menu"), e);            
        },
        "onRowContextMenu": function(e, rowIndex, rowData){
            end_edit_datagrid($(this), e);
            dialog_act_tagger_show_menu($("#dialog_act_tagger_edit_db_record_context_menu"), e);            
        }
	});     

    var rows = [];    
    for( var i in item ) {
    	if( i.indexOf("_") == 0 ) continue;
    	
        rows.push({
        	"key": i,
        	"name": i,
        	"value": item[i]
        });
    }
    
    // fill up grid data
    grid.datagrid("loadData", {"total": rows.length, "rows": rows}); 
}


function dialog_act_tagger_delete_extra_db_record(_win) {
    $.messager.confirm("Delete DB Record", "현재 레코드를 삭제하시겠습니까?", function(r){
		if( r ){
			var grid = $("#dialog_act_tagger_edit_db_record_grid");
			var db_record = grid.datagrid("options").db_record;	
			
            // console.log("db_record", db_record, db_record["_idx"]);
			var post_data = {
	            "request": "dialog_act_tagger_delete_extra_db_record",
	            "db_record_idx": db_record["_idx"],
                "project_name": $.cookie("project_name"),
	            "table_name": dialog_act_tagger_get_table_name()
	        };

            // console.log(post_data);
	        
	    	$.post(g_variable["_POST_URL_"], post_data, function(data) {
	    		var ret = JSON.parse(data);
                // console.log(ret);
	    		
	    		if( typeof(ret["msg"]) != "undefined" ) $.messager.alert("알림", ret["msg"], "info");
	    		
	    		_win.window("close");
	        });
        }
	});  
}


function dialog_action_update_datagrid_db_record(_grid, _db_record) {
	var changed = _grid.datagrid("getChanges");
	var deleted = _grid.datagrid("getChanges", "deleted");
	
	for( var i in changed ) {
		var key = changed[i].key;
		var name = changed[i].name;
		var value = changed[i].value;
		
		if( key == "" || name == "" ) continue;
		
		delete _db_record[key];
		_db_record[name] = value;
	}
	
	for( var i in deleted ) {
		var key = changed[i].key;
		var name = changed[i].name;
		
		if( key == "" || name == "" ) continue;
		
		delete _db_record[key];
	}

	return _db_record;
}


function dialog_act_tagger_save_extra_db(_win) {
	var grid = $("#dialog_act_tagger_edit_db_record_grid");

	end_edit_datagrid(grid, null);
	
	var db_record = grid.datagrid("options").db_record;	
	db_record = dialog_action_update_datagrid_db_record(grid, db_record);
	
    var post_data = {
        "request": "dialog_act_tagger_save_extra_db",
        "db_record": db_record,
        "project_name": $.cookie("project_name"),
        "table_name": dialog_act_tagger_get_table_name()
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		var ret = JSON.parse(data);
		
		if( typeof(ret["msg"]) != "undefined" ) $.messager.alert("알림", ret["msg"], "info");
		
		_win.window("close");
    });
}


function display_extra_db_record_list() {
    column_list = db_recode.column_list; 
    data = db_recode.data;
    
    var buf = "";
    
    // display search box & page index

    var value_type = "table";
    for( var i in data ) {
        var item = data[i];
        
        var buf_value = ""; 
        
        if( value_type == "logical_form" ) {
            for( var k in item ) {
                if( buf_value != "" ) buf_value += ", ";
                buf_value += k + "=\"" + item[k] + "\"";
            }        
            
        } else { // table
            var max_column = 3;
            var cnt = 0;
            
            buf_value = "<table border=1 style='width: 100%;'>";
            buf_value += "<tr>";
            
            for( var k in item ) {
            	if( k.indexOf("_") == 0 ) continue;
            	
                if( (cnt++ % max_column) == 0 ) {
                    buf_value += "</tr>"
                }
                
                var str_val = item[k];
                if( str_val.length > 20 ) str_val = str_val.substr(0, 20)+" ...";
                
                buf_value += "<td style='width: 15%; background-color: #D0D0D0;'>" + k + "</td>";
                buf_value += "<td style='width: 15%;'>" + str_val + "</td>";
            }       
             
            buf_value += "</tr>";
            buf_value += "</table>";
        }       
        
        buf += "<table class='db_record' onclick=\"open_win_edit_db_record('"+i+"')\">";
        buf += "    <tr><td>";

        if( value_type == "logical_form" ) {
            buf += "    # db_record(" +buf_value + ")";
        } else {
            buf += buf_value;
        }

        buf += "    </td></tr>";
        buf += "</table>";
    }

    $("#dialog_act_tagger_extra_db_search_result").html(buf);
}


function dialog_act_tagger_set_table_name(tbl_name) {
    $("#dialog_act_tagger_extra_db").panel("setTitle", tbl_name);
}


function dialog_act_tagger_get_table_name() {
    return $("#dialog_act_tagger_extra_db").panel("options").title;
}

