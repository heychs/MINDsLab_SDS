//------------------------------------------------------------------------------
// xls -> json
// json -> xml
// slu learning
// add & delete task graph
// save task graph
// 
//------------------------------------------------------------------------------
function init_dialog_act_tagger_guide() {
    // set scenario toolbar
//    $("#dialog_act_tagger_guide_task_manager").empty();
    $("#dialog_act_tagger_guide_task_manager").panel({  
        "tools": [{           
            "iconCls": "icon-dialog_act_tagger_db",  
            "handler":function(){    
                dialog_act_tagger_open_upload_win();
            }
         }, "-", {
            "iconCls": "icon-add",  
            "handler":function(){               
                
            }
         }, {
            "iconCls": "icon-remove",  
            "handler":function(){               
                
            }
         }, "-", {
            "iconCls": "icon-dialog_act_tagger_build_slu",  
            "handler":function(){                
                learn_slu("");
            }
         }, "-", {
    
            "iconCls": "icon-dialog_action_tagger_open_dialog_system",  
            "handler":function(){
                dialog_action_tagger_open_dialog_system();
            }
        }]  
    });
    
    init_dialog_act_tagger_guide_refresh();
}
//------------------------------------------------------------------------------
function init_dialog_act_tagger_guide_refresh() {
	var project_name = $.cookie("project_name");
	
    if( project_name && project_name != "" ) $("#main_panel").panel("setTitle", "Project Name: "+project_name);

	// build panel	
	$("#dialog_act_tagger_guide_mission").panel({
		"collapsible": true,
		"collapsed": false
	});
	
	$("#dialog_act_tagger_guide_slot_values").panel({
		"collapsible": true,
		"collapsed": false
	});

	$("#dialog_act_tagger_guide_slot_structure").panel({
		"collapsible": true,
		"collapsed": false
	});

    // update slot structure
    get_slot_structure($("#dialog_act_tagger_guide_slot_structure"), "read_only");
    
    // display task_manager    
    $("#dialog_act_tagger_guide_task_manager").html("<iframe src='ui/task_manager.php' class='task_manager_style'></iframe>"); 
	
}
//------------------------------------------------------------------------------
function task_clicked(_task_info) {
	if( $.cookie("project_type") != "guided" ) return;
	
	var task_name = _task_info["task-task_name"];
	
	// clear
	$("#dialog_act_tagger_guide_tagged_result").empty();
	
	// get utter list
	dialog_act_tagger_guide_get_task_detail(task_name);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_get_task_detail(task_name) {
   // post
    var post_data = {
        "request": "dialog_act_tagger_guide_get_task_detail",
        "task_name": task_name
    };

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        dialog_act_tagger_guide_build_tree(ret);
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_build_tree_data(tree_data, data){
    for( var i=0 ; i < tree_data.length ; i++ ) {
    	dialog_act_tagger_guide_build_tree_data(tree_data[i].children, data);

    	if( tree_data[i]["id"] == data["parent_id"] ) {
    		var utter = data["utter_list"].split(/\n/);
    		
	    	utter[0] = utter[0].replace(">", "&gt;");    	
	    	utter[0] = utter[0].replace("<", "&lt;");    	

    		tree_data[i].children.push({
                "id": data["id"], 
                "state": "open", 
                "text": utter[0], 
                "iconCls": "icon-ok",
                "children": []
    		});
    		
    		return;
    	}
	}
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_mark_end(tree_data){
	for( var i=0 ; i < tree_data.length ; i++ ) {
    	dialog_act_tagger_guide_mark_end(tree_data[i].children);
    	
    	if( tree_data[i].children.length == 0 ) {
    		tree_data[i].children.push({
                "id": uniqid(), 
                "state": "closed", 
                "text": "END", 
                "iconCls": "icon-folder",
                "children": []
    		});
    	}
	}
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_build_tree(guide_data) {
    // g_guide_data = guide_data;
	// build tree
    var tree_data = [];

    tree_data.push({
        "id": "-1", 
        "state": "open", 
        "text": "START", 
        "iconCls": "icon-folder",
        "children": []
    });
    
    var cnt = 0;
    
    for( var i=0 ; i < guide_data.length ; i++ ) {
    	// trace tree_data
    	dialog_act_tagger_guide_build_tree_data(tree_data, guide_data[i]);
    }
    
    dialog_act_tagger_guide_mark_end(tree_data);
    
    // display tree
    var uid = uniqid();

    var buf = "<div id='"+uid+"' style='height: 100%; overflow: auto;'></div>";
    
    var obj_result = $("#dialog_act_tagger_guide_utter_structure");  
    
    obj_result.html(buf);

	obj_tree = $("#"+uid);
    
	obj_tree.tree({
		"checkbox"	: false,
		"fit"		: true,
        "data"		: tree_data,
        "onClick": function(node){
        	// display para list
		    dialog_act_tagger_guide_utter_tree_clicked(node.id, guide_data);
		},
        "onContextMenu": function(e, node){
        }
	});
	
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_utter_tree_clicked(node_id, guide_data) {
	var data = null;	
    for( var i=0 ; i < guide_data.length ; i++ ) {
    	if( node_id != guide_data[i].id ) continue;
    	
    	data = guide_data[i];    	
    	break;
    } 

    var slot = data["slot"];
    var mission = data["mission"];
    var utter_list = data["utter_list"].split(/\n/);
    	
    console.log(data, "slot", slot, "mission", mission);
    	
    // display utterance list
    dialog_act_tagger_guide_display_paraphrase(utter_list);
    
    // display related slots 
    dialog_act_tagger_guide_display_related_slots($("#dialog_act_tagger_guide_slot_values"), slot);

    // display mission 
	mission = mission.replace(/^Mission:/, "");
    dialog_act_tagger_guide_display_related_slots($("#dialog_act_tagger_guide_mission"), mission);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_parse_slot_form(slot) {	
	// slot = visiting_place={"Times Square", "the Empire State Building", "Chinatown", "Centeral Park", "Rockefeller Center"}

	var data = {};
	
	if( slot == null || slot == "" ) return;
	
	slot.replace(
	    new RegExp("([^=]+)=\{([^}]+)\}", "g"),
	    function($0, $1, $2) { 
			var k = $1;
			var v = $2;
			
			data[k] = v.split(/,/);
			for( var i=0 ; i < data[k].length ; i++ ) {
				data[k][i] = data[k][i].replace(/^\s*\"|\"\s*$/g, ""); 
			} 
		}
	);
	
	return data;	
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_display_related_slots(obj_result, slot) {
	var data = dialog_act_tagger_guide_parse_slot_form(slot);

	var tree_data = [];
	
	for( var slot_name  in data ) {
		var child = [];
		
		for( var i in data[slot_name] ) {
			child.push({
		        "state": "open", 
		        "text": data[slot_name][i], 
		        "iconCls": "icon-ok",
		        "children": []
			});
		}
		
	    tree_data.push({
	        "id": "-1", 
	        "state": "open", 
	        "text": slot_name, 
	        "iconCls": "icon-folder",
	        "children": child
	    });
	}


    var uid = uniqid();

    var buf = "<div id='"+uid+"' style='height: 100%; overflow: auto;'></div>";

	obj_result.html(buf);

	obj_tree = $("#"+uid);
    
	obj_tree.tree({
		"checkbox"	: false,
		"fit"		: true,
        "data"		: tree_data,
        "onClick": function(node){
        	// display para list
		},
        "onContextMenu": function(e, node){
        }
	});
}
//------------------------------------------------------------------------------
function dialog_act_tagger_guide_display_paraphrase(utter_list) {
    // data gride
    var _target = $("#dialog_act_tagger_guide_tagged_result"); 
    
    var buf = "";
    
    buf += "<table id='dialog_act_tagger_guide_para_list_datagrid'></table>";
    buf += "<div id='dialog_act_tagger_guide_para_list_datagrid_context_menu' style='width: 130px; display: none;'>";
    buf += "	<div iconCls='icon-add'>add</div>";
    buf += "	<div iconCls='icon-remove'>delete</div>";
    buf += "	<div iconCls='icon-edit'>edit</div>";
    buf += "	<div class='menu-sep'></div>";
    buf += "	<div iconCls='icon-save'>save</div>";
    buf += "</div>";
    
    _target.empty();
    _target.html(buf);
    
	// build menu
	$("#dialog_act_tagger_guide_para_list_datagrid_context_menu").menu({
		"onClick": function(item){
		}
	});
    
    var grid = $("#dialog_act_tagger_guide_para_list_datagrid");

    var columns = [{
        "field": "type", 
        "title": "type", 
        "width": 80,
        "editor": "text" 
    },{
	    "field": "utter",  
        "title": "utter", 
        "width": 700,
        "editor": "text" 
    }];

    grid.datagrid({	
        "lastIndex"     : -1,
        "fit"           : true,
        "fitColumns"    : false,
        "rownumbers"    : true,  
        "singleSelect"  : true,
        "pagination"    : false,
        "columns"       : [columns],
		"onDblClickRow": function(rowIndex){
		},
		"onClickRow":function(rowIndex){
		},
        "onHeaderContextMenu": function(e, field){
            end_edit_datagrid($(this), e);
            dialog_act_tagger_show_menu($("#dialog_act_tagger_guide_para_list_datagrid_context_menu"), e);            
        },
        "onRowContextMenu": function(e, rowIndex, rowData){
            end_edit_datagrid($(this), e);
            dialog_act_tagger_show_menu($("#dialog_act_tagger_guide_para_list_datagrid_context_menu"), e);            
        }
	});
    
    var rows = [];
    for( var i=0 ; i < utter_list.length ; i++ ) {
    	var list = utter_list[i].split(/:/);
    	
    	var type = list[0];
    	var utter = list[1];
    	
    	utter = utter.replace(">", "&gt;");    	
    	utter = utter.replace("<", "&lt;");    	

        rows.push({
            "type"   : type,  
            "utter"  : utter,  
            "editor" : "text"
        });
    }
    
    grid.datagrid("loadData", {"total": rows.length, "rows": rows});

}
//------------------------------------------------------------------------------
