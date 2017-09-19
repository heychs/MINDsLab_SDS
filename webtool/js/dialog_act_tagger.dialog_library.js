//------------------------------------------------------------------------------
function display_dialog_library(_win, dialog_library, type) {
    var obj = $("#task_manager_edit_win_dialog_library_"+type);
    obj.empty();
    
    if( typeof(dialog_library) == "undefined" ) return;
//    if( dialog_library.length <= 0 ) return;
    
    var columns = [];
    
    if( type == "response" ) {
        columns = [
              {"editor": "text", "field": "no",        "title": "no",         "width": 50},
              {"editor": "text", "field": "talker",    "title": "talker",     "width": 50},
            {"editor": "text", "field": "condition", "title": "condition",  "width": 25},
              {"editor": "text", "field": "DA",        "title": "DA" }        
        ];        
    } else if( type == "progress" ) {
        columns = [
            {"editor": "text", "field": "no",        "title": "no",         "width": 50},
              {"editor": "text", "field": "talker",    "title": "talker",     "width": 50},
            {"editor": "text", "field": "condition", "title": "condition",  "width": 25},
              {"editor": "text", "field": "progress_slot", "title": "progress_slot" }        
        ];
    } else if( type == "transaction" ) {
        columns = [
              {"editor": "text", "field": "no",       "title": "no",         "width": 50},
              {"editor": "text", "field": "talker",   "title": "talker",     "width": 50},
              {"editor": "text", "field": "task",     "title": "task" }        
        ];
    } else {
        return;
    }
    
    var grid = $("<table></table>").appendTo(obj);
   
    // make datagrid
    grid.datagrid({    
        "fit"           : true,
        "fitColumns"    : false,
        "rownumbers"    : false,  
        "pagination"    : false,
        "singleSelect"  : true,
        "columns"       : [columns],
        "dialog_library_type": type,
        "onDblClickRow": function(rowIndex){
            var row = $(this).datagrid("getRows")[rowIndex];
            var type = $(this).datagrid("options")["dialog_library_type"];
            
            dialog_library_item_click(_win, type, row["no"]);
        }
    }); 
    
    var rows = [];
    
    for( var i in dialog_library ) {
        var row = {};
        var data = dialog_library[i];

        if( type == "response" ) {
            data = dialog_library[i]["dialog_node-request_utterance"];

            row = {
                "no"    : i,
                "idx"   : dialog_library[i]["dialog_node-_idx"],
                "DA"    : data["DA"],
                "talker": data["talker"],
                "condition": data["condition"]
            };
        } else if( type == "progress" ) {
            row = {
                "no"    : i,
                "idx"   : data["dialog_node-_idx"],
                "talker": data["dialog_node-talker"],   
                "condition": data["dialog_node-condition"],   
                "progress_slot": data["dialog_node-progress_slot"]   
            };
        } else if( type == "transaction" ) {
            row = {
                "no"    : i,
                "idx"   : data["dialog_node-_idx"],
                "talker": data["dialog_node-talker"],   
                "task"  : data["dialog_node-task"]   
            };
        } else {
            return;
        }

        rows.push(row);
    }
    
    // fill up grid data
    grid.datagrid("loadData", {"total": rows.length, "rows": rows}); 
}
//------------------------------------------------------------------------------
function dialog_library_item_click(_win, type, no) {
    // open window
    var uid = uniqid();
    
    var buf = "";
    
    buf += "<div id='"+uid+"' style='overflow: hidden;'>";

    buf += "<div id='dialog_library_detail_layout' fit='true'>";

    buf += "   <div region='north' title='' noheader='true' split='true' style='height: 350px; overflow: hidden;'>";
    buf += "        <div id='dialog_library_detail_layout_north' fit='true'>";

    // north
    buf += "   <div id='dialog_library_detail_common' region='north' title='common information' noheader='false' split='true' style='height: 130px; overflow: hidden;'>";    
    buf += "   </div>";    

    buf += "   <div region='center' title='' noheader='true' split='true' style='overflow: hidden;'>";
    buf += "        <div title='dialog type' id='dialog_library_detail_dailog_type' style=''></div>";
    buf += "   </div>";    
        
    buf += "           </div>";    
    buf += "   </div>";    
    
    
    buf += "   <div region='center' title='' noheader='true' split='true' style='height: 240px; overflow: hidden;'>";
    buf += "        <div title='utterance set' id='dialog_library_detail_utterance_set'></div>";
    buf += "   </div>";   
     

    buf += "<div id='dialog_library_detail_utterance_set_context_menu' style='width: 100px; display: none;'>";
    buf += "    <div iconCls='icon-add'>add</div>";
    buf += "    <div iconCls='icon-remove'>delete</div>";
    buf += "</div>";

    buf += "</div>";    

    buf += "</div>";
    
    var win_result = $(buf).appendTo("body");

    win_result.window({  
        "title"     : "Edit Dialog Library",  
        "width"     : 500,  
        "height"    : dialog_act_tagger_get_window_heigth(600), 
        "modal"     : false,
        "inline"    : false,
        "iconCls"   : "icon-ok", 
        "closable"  : true,
        "resizable" : true,
        "collapsible" : false,
        "minimizable" : false,
        "maximizable" : true,
        "tools": [{  
            "iconCls": "icon-delete",  
            "handler": function(){
                delete_dialog_library_detail($("#"+uid), type, no);
            }  
        }, {
            "iconCls": "icon-save",  
            "handler": function(){
                // end all 
                dialog_act_tagger_end_edit_all_grid($("#dialog_library_detail_common_propertygrid"), "propertygrid");
                dialog_act_tagger_end_edit_all_grid($("#dialog_library_detail_dailog_type_propertygrid"), "propertygrid");
                
                dialog_act_tagger_end_edit_all_grid($("#dialog_library_detail_utterance_set_treegrid"), "treegrid");                
                
                save_dialog_library_detail($("#"+uid), type, no);
            }  
        }, "-"],  
        "onMove": function(left, top) {
            dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });    
            
    $("#dialog_library_detail_layout").layout({});
    $("#dialog_library_detail_layout_north").layout({});    
    
    $("#dialog_library_detail_dailog_type").panel({
        "fit": true
    });

    $("#dialog_library_detail_utterance_set").panel({
        "fit": true,
        "tools": [{  
            "iconCls": "icon-add",  
            "handler": function(){
                add_utterance_set($("#dialog_library_detail_utterance_set"), _win, type, no);
            }  
        },{  
            "iconCls": "icon-remove",  
            "handler": function(){  
                delete_utterance_set($("#dialog_library_detail_utterance_set"), _win, type, no);
            }  
        }]  
    });
    
    // build menu
    $("#dialog_library_detail_utterance_set_context_menu").menu({
        "onClick": function(item){
            if( item.text == "add" ) {
                add_utterance_set($("#dialog_library_detail_utterance_set"), _win, type, no);
            } else if( item.text == "delete" ) {
                delete_utterance_set($("#dialog_library_detail_utterance_set"), _win, type, no);
            }
        }
    });
    
    // display dialog library detail common
    display_dialog_library_detail_common(_win, type, no);
    display_dialog_library_detail_dailog_type(_win, type, no, g_dialog_library[type][no]["dialog_node-dialog_type"]);
    display_dialog_library_detail_utterance_set(_win, type, no);
//    display_dialog_library_detail_utterance_set2(_win, type, no);
}
//------------------------------------------------------------------------------
function display_dialog_library_detail_common(_win, type, no) {    
    $("#dialog_library_detail_common").html("<table id='dialog_library_detail_common_propertygrid'></table>");
    
    var grid = $("#dialog_library_detail_common_propertygrid");

    var disp_order = {
        "dialog_node-talker": {
            "editor": [
                {"val": "system",  "name": "system"},
                {"val": "user",    "name": "user"}
            ]            
        },
        "dialog_node-dialog_type": {
            "editor": [{
                "val" : "transaction",  
                "name": "transaction", 
                "func": function(rec){ 
                    display_dialog_library_detail_dailog_type(_win, type, no, rec.val); 
                }
            }, {
                "val" : "progress",     
                "name": "progress", 
                "func": function(rec){ 
                    display_dialog_library_detail_dailog_type(_win, type, no, rec.val); 
                }
            }, {
                "val" : "response",     
                "name": "response", 
                "func": function(rec){ 
                    display_dialog_library_detail_dailog_type(_win, type, no, rec.val); 
                }
            }]
        }
    };
    
    grid.propertygrid({    
        "fit"       : true, 
        "showGroup" : false,
        "onAfterEdit": function(rowIndex, rowData, changes) {
            g_dialog_library[type][no]["dialog_node-"+rowData.name] = rowData.value;
        }
    }); 
    
    display_dialog_library_propertygrid(grid, g_dialog_library[type][no], disp_order);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_show_menu(_menu, _e) {
    _e.preventDefault();
    
    if( _menu ) {
        _menu.menu("show", {
            "left": _e.pageX,
            "top" : _e.pageY
        });
    }
}
//------------------------------------------------------------------------------
function display_dialog_library_propertygrid(_grid, data, disp_order) {
    var rows = [];     
    for( var k in disp_order ) {
        var str_k = k;
        str_k = str_k.replace("dialog_node-", "");
        
        var editor = "text";
        if( typeof(disp_order[k]) == "object" && $.isArray(disp_order[k]["editor"]) ) {
            editor = {  
                "type":"combobox",  
                "options":{  
                    "data"      : disp_order[k]["editor"],
                    "required"  : true,
                    "editable"  : false,
                    "textField" : "name",
                    "valueField": "val",
                    "onSelect"  : function(rec){
                        if( typeof(rec["func"]) == "function" ) rec["func"](rec);
                    }
                }  
            }; 
        }       
        
        var group = "";
        if( typeof(disp_order[k]["group"]) != "undefined" ) group = disp_order[k]["group"]; 
        
        if( str_k == "request_utterance" ) {
            if( typeof(data[k]["talker"]) == "undefined" ) {
                data[k] = {
                    "DA"    : "",
                    "talker": "user",         
                    "condition": "1"
                };
            }
            
            for( var i in data[k] ) {
                rows.push({
                    "name"  : i,  
                    "value" : data[k][i],  
                    "group" : group,  
                    "editor": editor
                });
            }
        } else {
            rows.push({
                "name"  : str_k,  
                "value" : data[k],  
                "group" : group,  
                "editor": editor
            });
        }
    }
    
    _grid.propertygrid("loadData", {"total": rows.length, "rows": rows});
}
//------------------------------------------------------------------------------
function display_dialog_library_detail_dailog_type(_win, type, no, dialog_type) {
    var panel = $("#dialog_library_detail_dailog_type").panel("setTitle", dialog_type);

    var showGroup = false;
    var disp_order = {};

    if( dialog_type == "response" ) {
        showGroup = true;

        disp_order = {
            "dialog_node-related_task": {
                "editor": task_list
            },
            "dialog_node-request_utterance": {
                "group": "request utterance"
            }
        };
    } else if( dialog_type == "progress" ) {
        var task_list = get_task_list_combobox_data(_win.g_task_info);

        disp_order = {
            "dialog_node-related_task": {
                "editor": task_list
            },
            "dialog_node-progress_slot": 1,
            "dialog_node-condition": 1
        };
    } else if( dialog_type == "transaction" ) {
        disp_order = {
            "dialog_node-task": 1,
            "dialog_node-subtype": {
                "editor": [
                    {"val": "start",   "name": "start"},
                    {"val": "restart", "name": "restart"}
                ]
            }
        };
    }

    panel.empty();
    panel.html("<table id='dialog_library_detail_dailog_type_propertygrid'></table>");
    
    var grid = $("#dialog_library_detail_dailog_type_propertygrid");

    grid.propertygrid({    
        "fit": true, 
        "showGroup" : showGroup,
        "onAfterEdit": function(rowIndex, rowData, changes) {
            if( rowData.group == "request utterance" ) {
                var request_utterance = g_dialog_library[type][no]["dialog_node-request_utterance"];
                
                request_utterance[rowData.name] = rowData.value;
                
                g_dialog_library[type][no]["dialog_node-request_utterance"] = request_utterance;
            } else {
                g_dialog_library[type][no]["dialog_node-"+rowData.name] = rowData.value;
            }
        }
    }); 
    
    display_dialog_library_propertygrid(grid, g_dialog_library[type][no], disp_order);
}
//------------------------------------------------------------------------------
function display_dialog_library_detail_utterance_set2(_win, type, no) {
    var buf = "";
    
    buf += "<div id='dialog_library_detail_utterance_set_layout' fit='true'>";
    
    buf += "   <div region='west' id='' title='' noheader='true' split='true' style='width: 200px; overflow: hidden;'>";    
    buf += "           <div id='dialog_library_detail_utterance_set_tree'>";    
    buf += "           </div>";    
    buf += "   </div>";    

    buf += "   <div region='center' title='' noheader='true' split='true' style='overflow: hidden;'>";
    buf += "           <table id='dialog_library_detail_utterance_set_value'>";    
    buf += "           </table>";    
    buf += "   </div>";
        
    buf += "</div>";    
    
    
    $("#dialog_library_detail_utterance_set").html(buf);
    
    // build layout
    $("#dialog_library_detail_utterance_set_layout").layout();
}
//------------------------------------------------------------------------------
function display_dialog_library_detail_utterance_set(_win, type, no) {
    console.log("display_dialog_library_detail_utterance_set");

    $("#dialog_library_detail_utterance_set").html("<table id='dialog_library_detail_utterance_set_treegrid'></table>");

    var grid = $("#dialog_library_detail_utterance_set_treegrid"); 

    var columns = [
        {"field": "name",  "title": "Name",  "editor": "text", "width": 200},
        {"field": "value", "title": "Value", "editor": "text", "width": 200}
    ];    
    
    grid.treegrid({
        "lastIndex"     : -1,
        "idField"       : "id",
        "treeField"     : "name",
        "fit"           : true,
        "fitColumns"    : true,
        "rownumbers"    : true,  
        "pagination"    : false,
        "singleSelect"  : true,
        "columns"       : [columns],
        "onContextMenu": function(e, row){
            dialog_act_tagger_show_menu($("#dialog_library_detail_utterance_set_context_menu"), e);
        },
        "onClickRow": function(row){
            end_edit_treegrid($(this), type, no);
        },
        "onDblClickRow": function(row){
            edit_treegrid($(this));
        },
        "onBeforeEdit": function(row) {
            if( !$.isArray(row.value) ) {
                row.value = row.value.replace(/\&lt\;/g, "<");
                row.value = row.value.replace(/\&gt\;/g, ">");            
            }
        },
        "onAfterEdit": function(row, changes) {
            var utterance = g_dialog_library[type][no]["dialog_node-utterance_set"]["utterance"];

            var flag = 0;
            if( !$.isArray(utterance) ) {
                flag = 1;
                utterance = [utterance];
            } 
            
            var v = row.value;

            v = v.replace(/\"/g, "\\\"");

            v = v.replace(/\&lt\;/g, "<");
            v = v.replace(/\&gt\;/g, ">");
            
            var cmd = "utterance" + get_key_str_cmd(row.key) + " = \"" + v + "\";";
            eval(cmd);

            if( flag ) {
                g_dialog_library[type][no]["dialog_node-utterance_set"]["utterance"] = utterance[0];
            } else {   
                g_dialog_library[type][no]["dialog_node-utterance_set"]["utterance"] = utterance;
            }             

            // refresh
            row.value = row.value.replace(/\</g, "&lt;");
            row.value = row.value.replace(/\>/g, "&gt;");            

            $(this).treegrid("refresh", row.id);
        }
    }); 
    
    var utterance = g_dialog_library[type][no]["dialog_node-utterance_set"]["utterance"];

    if( !$.isArray(utterance) ) utterance = [utterance];

    dialog_act_tagger_update_utterance_set(grid, utterance, type, no);
}
//------------------------------------------------------------------------------
function make_utterance_tree_data(tree_data, utterance, idx) {
/*
{
    "condition": "1",
    "actoin": "...",
    "template": [{
        "weight": "1.0",
        "intention": [{
            "DA": "",
            "pattern": [""]
        }]
    }] 
}
 */

    tree_data.push({
        "id"    : idx, 
        "key"   : [idx, "condition"],
        "name"  : "utterance", 
        "value" : utterance["condition"] 
    });
    
    if( typeof(utterance["action"]) == "undefined" ) utterance["action"] = "";
    tree_data.push({
        "id"    : idx+"_action", 
        "key"   : [idx, "action"],
        "name"  : "action", 
        "value" : utterance["action"] 
    });
    
    // template
    var template = utterance["template"];
    if( !$.isArray(template) ) template = [template];
    
    for( var i in template ) {
        tree_data.push({
            "id"    : idx+"_"+i, 
            "key"   : [idx, "template", i, "weight"], 
            "name"  : "template", 
            "value" : template[i]["weight"], 
            "_parentId": idx
        });

        // intention
        var intention = template[i]["intention"]; 
        if( !$.isArray(intention) ) intention = [intention];
        
        for( var j in intention ) {
            tree_data.push({
                "id"    : idx+"_"+i+"_"+j, 
                "key"   : [idx, "template", i, "intention", j, "DA"], 
                "name"  : "intention", 
                "value" : intention[j]["DA"], 
                "_parentId": idx+"_"+i
            });

            // pattern
            var pattern = intention[j]["pattern"]; 
            if( !$.isArray(pattern) ) pattern = [pattern];
            
            for( var k in pattern ) {
                var str_pattern = pattern[k];
                
                str_pattern = str_pattern.replace(/\</g, "&lt;");
                str_pattern = str_pattern.replace(/\>/g, "&gt;");
            
                tree_data.push({
                    "id"    : idx+"_"+i+"_"+j+"_"+k, 
                    "key"   : [idx, "template", i, "intention", j, "pattern", k], 
                    "name"  : "pattern", 
                    "value" : str_pattern, 
                    "_parentId": idx+"_"+i+"_"+j
                });
            }
            
            intention[j]["pattern"] = pattern;
        }
        
        template[i]["intention"] = intention;        
    }

    utterance["template"] = template;    
    
    return utterance;
}
//------------------------------------------------------------------------------
function end_edit_treegrid(_grid, type, no) {
    var lastIndex = _grid.treegrid("options").lastIndex;

    if( lastIndex < 0 ) return;

    try {
        _grid.treegrid("options").lastIndex = -1;
        _grid.treegrid("endEdit", lastIndex);
    } catch(e) {
        var utterance = g_dialog_library[type][no]["dialog_node-utterance_set"]["utterance"];
    
        if( !$.isArray(utterance) ) utterance = [utterance];
        
        dialog_act_tagger_update_utterance_set(_grid, utterance, type, no);
    }    
}
//------------------------------------------------------------------------------
function edit_treegrid(_grid) {
    var lastIndex = _grid.treegrid("options").lastIndex;

    if( lastIndex > 0 ) {
        _grid.treegrid("endEdit", lastIndex);
        _grid.treegrid("options").lastIndex = -1;
       }

    var node = _grid.treegrid("getSelected");
    if( node ) {
        _grid.treegrid("beginEdit", node.id);        
        _grid.treegrid("options").lastIndex = node.id;

//        console.log(node.id, lastIndex, _grid.treegrid("options").lastIndex);
   }
}
//------------------------------------------------------------------------------
function save_dialog_library_detail(obj_win, type, no) {
    var data = g_dialog_library[type][no];
    
    // post
    var post_data = {
        "request": "save_dialog_library_detail",
        "dialog_library_data": data
    };

    $.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
//        console.log("save_dialog_library_detail", post_data, ret);
        
        alert("OK");        
        obj_win.window("close");

        //        
        var _win = g_dialog_library["_win"];
        var _idx = g_dialog_library["_idx"];
        
        task_manager_display_dialog_library(_win, _idx);
    });
}
//------------------------------------------------------------------------------
function delete_dialog_library_detail(obj_win, type, no) {
    var data = g_dialog_library[type][no];
    
    // post
    var post_data = {
        "request": "delete_dialog_library_detail",
        "dialog_library_data": data
    };

    $.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);

        alert("OK");        
        obj_win.window("close");
        
        //        
        var _win = g_dialog_library["_win"];
        var _idx = g_dialog_library["_idx"];
        
        task_manager_display_dialog_library(_win, _idx);
    });
}
//------------------------------------------------------------------------------
function task_manager_display_dialog_library(_win, _idx) {
    var task = _win.g_task_info[_idx];
    
    // post
    var post_data = {
        "request": "task_manager_get_dialog_library",
        "task_name": task["task-task_name"]
    };

    $.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        g_dialog_library = {
            "_win"  : _win, 
            "_idx"  : _idx, 
            "task_name" : task["task-task_name"], 
            "response"  :[], 
            "progress"  :[], 
            "transaction": []
        };
        
        for( var i in ret.rows ) {
            var type = ret.rows[i]["dialog_node-dialog_type"];
            
            if( typeof(g_dialog_library[type]) == "undefined" ) g_dialog_library[type] = [];
            g_dialog_library[type].push(ret.rows[i]);
        }
        
       // console.log("g_dialog_library", g_dialog_library);
        
        for( var type in g_dialog_library ) {
            display_dialog_library(_win, g_dialog_library[type], type);
        } 
    });
}
//------------------------------------------------------------------------------
function task_manager_add_new_dialog_library(_win, obj_tab) {
    var type = obj_tab.tabs("getSelected").panel("options").title;
    
    var new_item = {
        "dialog_node-_idx"      : "-1",
        "dialog_node-_position" : "",
        "dialog_node-condition" : "",
        "dialog_node-dialog_type"   : type,
        "dialog_node-progress_slot" : "",
        "dialog_node-related_task"  : "",
        "dialog_node-request_utterance" : {},
        "dialog_node-subtype"   : "start",
        "dialog_node-talker"    : "system",
        "dialog_node-task"      : "",
        "dialog_node-utterance_set": {
            "utterance": {
                "condition" : "",
                "action" : "",
                "template"  : {
                    "weight"    : "1.0",
                    "intention" : {
                        "DA": "",
                        "pattern": ""
                    }
                }
            }
        },
        "utterance": []
    };   
    
    var task_name = g_dialog_library["task_name"];
    
    if( type == "transaction" ) {
        new_item["dialog_node-task"] = task_name;
    } else {
        new_item["dialog_node-related_task"] = task_name;
    }

    var no = g_dialog_library[type].length;    
    g_dialog_library[type][no] = new_item;
    
    dialog_library_item_click(_win, type, no);
}
//------------------------------------------------------------------------------
function task_manager_clear_dialog_library() {
    delete g_dialog_library;
    g_dialog_library = null;
    
    var type_list = ["progress", "response", "transaction"];
    for( var i in type_list ) {
        $("#task_manager_edit_win_dialog_library_"+type_list[i]).empty();
    }
}
//------------------------------------------------------------------------------
function delete_utterance_set(_obj, _win, type, no) {
    var grid = $("#dialog_library_detail_utterance_set_treegrid");
    
    var node = grid.treegrid("getSelected");
     
    var utterance = g_dialog_library[type][no]["dialog_node-utterance_set"]["utterance"];
     
    if( node.name == "utterance" ) {
        delete utterance[node.key[0]];
        utterance = removeUndefined(utterance);
        
        if( utterance.length <= 0 ) {
            utterance = [{
                "condition": "",
                "action": "",
                "template": [{
                    "weight": "1.0",
                    "intention": [{
                        "DA": "",
                        "pattern": [""]
                    }]
                }]
            }];
        }
    } else if( node.name == "template" ) {
        delete utterance[node.key[0]][node.key[1]][node.key[2]];

        var template = utterance[node.key[0]][node.key[1]];
        template = removeUndefined(template);
        
        if( template.length <= 0 ) {
            template = [{
                "weight"   : "1.0",
                "intention": [{
                    "DA"     : "",
                    "pattern": [""]
                }]
            }];
        }
        
        utterance[node.key[0]][node.key[1]] = template;
    } else if( node.name == "intention" ) {
        delete utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]][node.key[4]];

        var intention = utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]];
        intention = removeUndefined(intention);
        
        if( intention.length <= 0 ) {
            intention = [{
                "DA"     : "",
                "pattern": [""]
            }];
        }
        
        utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]] = intention;
    } else if( node.name == "pattern" ) {
        //  ["0", "template", "0", "intention", "0", "pattern", "1"] 
        delete utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]][node.key[4]][node.key[5]][node.key[6]];

        var pattern = utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]][node.key[4]][node.key[5]];
        pattern = removeUndefined(pattern);
        
        if( pattern.length <= 0 ) {
            pattern = [""];
        }
        
        utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]][node.key[4]][node.key[5]] = pattern;
    }

    dialog_act_tagger_update_utterance_set(grid, utterance, type, no);
}
//------------------------------------------------------------------------------
function add_utterance_set(_obj, _win, type, no) {
    var grid = $("#dialog_library_detail_utterance_set_treegrid");
    
    var node = grid.treegrid("getSelected");
    
    // reload data
    var utterance = g_dialog_library[type][no]["dialog_node-utterance_set"]["utterance"];

    if( !$.isArray(utterance) ) utterance = [utterance];

    if( node.name == "utterance" ) {
        utterance.push({
            "condition": "1",
            "action": "",
            "template" : [{
                "weight"   : "1.0",
                "intention": [{
                    "DA"     : "",
                    "pattern": [""]
                }]
            }] 
        });
    } else if( node.name == "template" ) {
        var template = utterance[node.key[0]][node.name];
        
        if( !$.isArray(template) ) template = [template];
        
        template.push({
            "weight"   : "1.0",
            "intention": [{
                "DA"     : "",
                "pattern": [""]
            }]
        });
        
        utterance[node.key[0]][node.name] = template;
    } else if( node.name == "intention" ) {
        var intention = utterance[node.key[0]][node.key[1]][node.key[2]][node.name];
        
        if( !$.isArray(intention) ) intention = [intention];
        
        intention.push({
            "DA"     : "",
            "pattern": [""]
        });
        
        utterance[node.key[0]][node.key[1]][node.key[2]][node.name] = intention;        
    } else if( node.name == "pattern" ) {
        var pattern = utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]][node.key[4]][node.name];
        
        if( !$.isArray(pattern) ) pattern = [pattern];
        
        pattern.push("");
        
        utterance[node.key[0]][node.key[1]][node.key[2]][node.key[3]][node.key[4]][node.name] = pattern;        
    }

    // update
    dialog_act_tagger_update_utterance_set(grid, utterance, type, no);
}
//------------------------------------------------------------------------------
function dialog_act_tagger_update_utterance_set(_grid, _utterance, _type, _no)
{
    var tree_data = [];    
    for( var i in _utterance ) {
        _utterance[i] = make_utterance_tree_data(tree_data, _utterance[i], i);
    }    
    
    g_dialog_library[_type][_no]["dialog_node-utterance_set"]["utterance"] = _utterance;        
    
    _grid.treegrid("loadData", {
        "rows" : tree_data,
        "total": tree_data.length 
    });
}
//------------------------------------------------------------------------------
function removeUndefined(array)
{
    ret = [];
    
    for( var i=0 ; i < array.length ; i++ ) {
        if( typeof array[i] === 'undefined' ) continue;
        
        ret.push(array[i]);
    }

    return ret;
}
//------------------------------------------------------------------------------
function end_edit_datagrid(_grid, e) {
    if( e ) e.preventDefault();

    var lastIndex = _grid.datagrid("options").lastIndex;
    
    if( lastIndex < 0 ) return;
    
    try {
        _grid.datagrid("options").lastIndex = -1;
        _grid.datagrid("endEdit", lastIndex);    
    } catch(e) {
        
    }
}

function edit_datagrid(_grid, rowIndex) {
    if( typeof(rowIndex) == "undefined" || rowIndex < 0 ) {
        var row = _grid.datagrid("getSelected");
        rowIndex = _grid.datagrid("getRowIndex", row);
    }
    
    var lastIndex = _grid.datagrid("options").lastIndex;
    
    _grid.datagrid("endEdit", lastIndex);
    _grid.datagrid("beginEdit", rowIndex);
    
    _grid.datagrid("options").lastIndex = rowIndex;
}
