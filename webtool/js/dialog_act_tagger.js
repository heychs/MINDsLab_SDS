//------------------------------------------------------------------------------
function init_dialog_act_tagger() {
    // set env.
    g_variable["utter_tagger_dialog_action_tagging_window_caption_list"] = {
        "utter": "User",
        "slot_tagged": "Slot",
        "machine_utter_str": "System",
        "dialog_action": "Dialog Act",
        "comment": "Comment"
    };

    g_variable["utter_tagger_dialog_action_tagging_window_toolbar_tooltip_list"] = {
        "icon-dialog_act_tagger_upload": "Upload Excel",
        "icon-dialog_act_tagger_download": "Download",
        "icon-dialog_act_tagger_note": "Make Comment",
        "icon-dialog_act_tagger_working_status": "Working Status",
        "icon-dialog_action_tagger_open_dialog_system": "Open Dialog System",
        "icon-dialog_act_tagger_edit_slot_structure": "Edit Slot Structure",
        "icon-dialog_act_tagger_edit_task_manager": "Edit Task Manager",
        "icon-change_domain_slot_tagger": "Change Domain",        
        "icon-run_slot_tagging-a": "Get Slot Tagging Result",        
        "icon-run_slot_tagging-b": "Update Dialog Act",        
        "icon-reset_slot_tagging": "Reset Slot Tagging Result",        
        "icon-search_slot_candidate": "Search Candidate",        
        "icon-save_slot_tagger": "Save Result"        
    };

    g_variable["utter_tagger_status_title_list"] = {
        "CLOSE": "문장이 닫힌 상태.",  
        "CHECK": "점검 요망.",  
        "COMPLETE": "작업 완료.",  
        "OPEN": "문장이 열린 상태."  
    };

    g_variable["utter_tagger_status_list"] = ["NONE", "OPEN", "CLOSE", "CHECK", "COMPLETE"];
    
    // set task manager toolbar
    $("#dialog_act_tagger_task_manager").empty();
    $("#dialog_act_tagger_task_manager").panel({  
        "tools": [{
            "iconCls": "icon-dialog_act_tagger_edit_task_manager",  
            "handler":function(){
                open_task_manager_edit_window();
            }  
        }, {  
            "iconCls": "layout-button-right",  
            "handler":function(){
                $("#layout_scenario").layout("collapse", "east");
            }  
        }], 
        "onResize": function(width, height) {
        	$.cookie("dialog_act_tagger_task_manager_width", width);
        	$.cookie("dialog_act_tagger_task_manager_height", height);        	
        } 
    });

    // set scenario toolbar
    $("#dialog_act_tagger_panel_utter_list").empty();
    $("#dialog_act_tagger_panel_utter_list").panel({  
        "tools": [{           
            "iconCls": "icon-dialog_act_tagger_db",  
            "handler":function(){    
                dialog_act_tagger_open_upload_win();
            }
         }, "-", {
            "iconCls": "icon-reload",  
            "handler":function(){                
                reset_db();
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
    
    // set dialog action tagging toolbar
    $("#dialog_act_tagger_tagged_result").empty();
    $("#dialog_act_tagger_tagged_result").panel({  
        "tools": [{
            "iconCls": "icon-run_slot_tagging-add",  
            "handler":function(){
                slot_tagger_add_sentance();
            }  
        }, {  
            "iconCls": "icon-run_slot_tagging-delete",  
            "handler":function(){
                slot_tagger_delete_sentance();
            }  
        }, "-", {  
            "iconCls": "icon-run_slot_tagging-a",  
            "handler":function(){
                run_utter_dialog_act_tagger(g_variable["_UID_"]);
            }  
        }, {  
            "iconCls": "icon-run_slot_tagging-b",  
            "handler":function(){
                update_dialog_action();
            }  
        }, {  
            "iconCls": "icon-reset_slot_tagging",  
            "handler":function(){
                clear_utter_tagging_result(g_variable["_UID_"]+"_slot_tagged");
                $("#dialog_act_tagger_candidate").empty();
                
                update_dialog_action();
            }  
        }, "-", {  
            "iconCls": "icon-search_slot_candidate",  
            "handler":function(){
                get_utter_tagging_candidate(g_variable["slot_tagged_data"]["no"], $("#"+g_variable["_UID_"]+"_slot_tagged").val()); 
            }  
        }, {  
            "iconCls": "icon-save_slot_tagger",  
            "handler":function(){
                save_utter_tagging_result(g_variable["_UID_"]);
            }  
        }]  
    }); 
    
    // reset slot structure
    $("#dialog_act_tagger_candidate").panel({
        "onResize": function(width, height) {
        	$.cookie("dialog_act_tagger_candidate_height", height);
        } 
    });
    
    $("#dialog_act_tagger_candidate").empty();
    $("#dialog_act_tagger_slot_structure").empty();
    
    // set toolbar's tooltip 
    var tooltip_title = g_variable["utter_tagger_dialog_action_tagging_window_toolbar_tooltip_list"];  
    
    var all = document.getElementsByTagName("A");
    for(var i=0 ; i<all.length ; ++i){
        var tooltip = tooltip_title[$(all[i]).attr("class")];
        if( typeof(tooltip) == "undefined" ) continue;
        
        $(all[i]).attr("title", tooltip); 
    }   
    
    init_dialog_act_tagger_refresh(); 
    
    //
    var dialog_act_tagger_candidate_height = $.cookie("dialog_act_tagger_candidate_height");
    if( dialog_act_tagger_candidate_height != null ) $("#dialog_act_tagger_candidate").panel("resize", {"height": dialog_act_tagger_candidate_height});

    var dialog_act_tagger_task_manager_width = $.cookie("dialog_act_tagger_task_manager_width");
    if( dialog_act_tagger_task_manager_width != null ) $("#dialog_act_tagger_task_manager").panel("resize", {"width": dialog_act_tagger_task_manager_width});

    // var dialog_act_tagger_task_manager_height = $.cookie("dialog_act_tagger_task_manager_height");
    // if( dialog_act_tagger_task_manager_height != null ) $("#dialog_act_tagger_panel_main_north").panel("resize", {"width": dialog_act_tagger_task_manager_height});
	
}
//------------------------------------------------------------------------------
function dialog_act_tagger_get_window_heigth(_height) {
	return ( window.innerHeight < _height ) ? window.innerHeight-50 : _height;
}
//------------------------------------------------------------------------------
function dialog_act_tagger_on_window_move(_obj, _left, _top) {
	var move_left = _left;
	var move_top = _top;

	if( _top < 0 )  move_top = 0;
	if( _left < 0 ) move_left = 0;
	
	if( window.innerHeight < _top ) move_top = window.innerHeight-$(_obj).height();
	if( window.innerWidth  < _left ) move_left = window.innerWidth-$(_obj).width();

	if( move_top != _top || move_left != _left ) $(_obj).window("move", {"left":move_left, "top":move_top});
}
//------------------------------------------------------------------------------
function init_dialog_act_tagger_refresh() {
	var project_name = $.cookie("project_name");
	
    if( project_name && project_name != "" ) $("#main_panel").panel("setTitle", "Project Name: "+project_name);

    // update utter list
    get_utter_list({
        "keyword": "", 
        "status": "NONE",
        "worker": "", 
        "page_number": 1, 
        "page_size": 10,
        "row_index": 0 
    });
    
    // update slot structure
    get_slot_structure($("#dialog_act_tagger_slot_structure"), "read_only");
    
    // display task_manager    
    $("#dialog_act_tagger_task_manager").html("<iframe src='ui/task_manager.php' class='task_manager_style'></iframe>"); 
}
//------------------------------------------------------------------------------
function display_select_prj_win() {
    var project_name = $.cookie("project_name");

    if( project_name != null ) {
		if( $.cookie("project_type") == "guided" ) {
			$("#panel_tab").tabs("close", "Dialog Act");
			init_dialog_act_tagger_guide();
		} else {
			$("#panel_tab").tabs("close", "Dialog Act Guide");
			init_dialog_act_tagger();
		}   
		return;        
    }
    
    //
    var post_data = {
        "request": "get_prj_list"
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);

		var prj_type = ["dynamic", "guided"];

        // open window
        var buf = "";
        
        buf += "<div style='overflow: hidden; padding: 5px;'>";

        buf += "    <center>";
        buf += "        Project Type: ";
        buf += "        <select id='project_type'>";
        for( var i in prj_type ) {
        	if( prj_type[i] == "empty" ) continue;
        	
            buf += "        <option value='"+prj_type[i]+"'>"+prj_type[i]+"</option>";
        }
        buf += "        </select>";
        buf += "    </center>";
        buf += "    <br>";
        buf += "    <center>";
        buf += "        Select Project : ";
        buf += "        <select id='project_name'>";
        for( var i in ret ) {
        	if( ret[i] == "empty" ) continue;
        	
            buf += "        <option value='"+ret[i]+"'>"+ret[i]+"</option>";
        }
        buf += "        </select>";
        buf += "    </center>";
        buf += "    <br>";
        buf += "    <center>";
        buf += "        <a id='close_select_project'>OK</a>";
        buf += "    </center>";
          
        buf += "</div>";
        
        var win_result = $(buf).appendTo("body");
        
        // open window
        win_result.window({  
            "iconCls"   : "icon-ok", 
            "title"     : "Select Project",  
            "width"     : 280,  
	        "height"    : dialog_act_tagger_get_window_heigth(140), 
            "modal"     : true,
            "inline"    : false,
            "closable"    : false,
            "resizable"   : true,
            "collapsible" : false,
            "minimizable" : false,
            "maximizable" : false,
            "tools" : [{
                "iconCls": "icon-add",  
                "handler":function(){
                    dialog_act_tagger_make_new_project(win_result);
                }
            }, {
                "iconCls": "icon-remove",  
                "handler":function(){
                    dialog_act_tagger_make_delete_project(win_result);
                }
            }],
	        "onMove": function(left, top) {
				dialog_act_tagger_on_window_move(this, left, top);
	        },
            "onClose": function(forceClose){
                $(this).remove();
            }       
        });
        
        $("#close_select_project").linkbutton({});
        $("#close_select_project").bind("click", function(){
            // display 
            var project_type = $("#project_type").val();
            
            $.cookie("project_name", $("#project_name").val());
            $.cookie("project_type", project_type);

            //callback_func();
			if( project_type == "guided" ) {
				$("#panel_tab").tabs("close", "Dialog Act");
				init_dialog_act_tagger_guide();
			} else {
				$("#panel_tab").tabs("close", "Dialog Act Guide");
				init_dialog_act_tagger();
			}         

            // close window
            win_result.window("close");
        });
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_make_delete_project(win_result) {
	$.messager.progress({
		"title": "Please wait..",	
		"msg": "Loading..."
	});
	
    $.messager.confirm("Delete Confirm", "\""+$("#project_name").val() + "\" will be deleted. <br />Are you sure?", function(r){
        if( !r ) {
            $.messager.progress("close");
            return;
        }
        
        var post_data = {
            "request": "dialog_act_tagger_make_delete_project",
            "project_name": $("#project_name").val()
        };
            
        $.post(g_variable["_POST_URL_"], post_data, function(data) {
            var ret = JSON.parse(data);
            
            // console.log(ret);
    
            win_result.window("close");
            $.messager.progress("close");
            
            display_select_prj_win();
        });
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_make_new_project(win_result) {
    $.messager.progress({
        "title": "Please wait..",   
        "msg": "Loading..."
    });
    
    $.messager.prompt("New Project", "Please Enter New Project Name: ", function(r){
        if( r ){
            var post_data = {
                "request": "dialog_act_tagger_make_new_project",
                "new_project_name": r
            };
                
            $.post(g_variable["_POST_URL_"], post_data, function(data) {
                var ret = JSON.parse(data);

                win_result.window("close");
                $.messager.progress("close");
                
                display_select_prj_win();
            });
        } else {
            $.messager.progress("close");
        }
    }); 
}
//------------------------------------------------------------------------------
function reset_db() {        
    var post_data = {
        "request": "reset_db"
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        alert(ret["msg"]);
    });
}
//------------------------------------------------------------------------------
function slot_tagger_add_sentance() {
    var data = {
        "utter": "",
        "slot_tagged": "",
        "dialog_action": "",
        "machine_utter_str": "",
        "comment": "",
        "no": -1,
        "m_date": "",
        "status": "OPEN",
        "worker": $.cookie("user_name")
    };
    
    display_tagged_result($("#dialog_act_tagger_tagged_result"), data);        
}
//------------------------------------------------------------------------------
function slot_tagger_delete_sentance() {
    var tagging_result = g_variable["slot_tagged_data"];
    
    tagging_result = get_utter_tagging_result(g_variable["_UID_"], tagging_result);
        
    var post_data = {
        "request": "slot_tagger_delete_sentance",
        "no": tagging_result["no"]
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);

        alert(ret["msg"]);
 
        get_utter_list({
            "keyword": $("#search_keyword").val(), 
            "status" : $("#search_status").val(), 
            "worker" : $("#search_worker").val() 
        });
           
        $("#dialog_act_tagger_tagged_result").empty();    
	});
}
//------------------------------------------------------------------------------
function run_utter_dialog_act_tagger(_uid) {
    if( g_variable["slot_tagged_data"] == null ) return;    
    
    var tagging_result = g_variable["slot_tagged_data"];
    
    tagging_result = get_utter_tagging_result(_uid, tagging_result);

    var utter = tagging_result["utter"];
    
    var post_data = {
        "utter": utter,
        // "cmd": "GET_INTENTION",
        "cmd": "GET_SLOT_TAGGING",
        "tagging_result": tagging_result,
        "request": "run_utter_dialog_act_tagger"
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        // console.log(data);
        var ret = JSON.parse(data);
        console.log(ret);
        
        for( var k in ret["result"] ) {
            if( k == "utter" ) continue;
            
            ret["result"][k] = ret["result"][k].replace(/<TAB>/g, "");
            ret["result"][k] = ret["result"][k].replace(/\\/g, "");
            
            tagging_result[k] = ret["result"][k];     
        }
        
        tagging_result["status"] = "OPEN";
        
        display_tagged_result($("#dialog_act_tagger_tagged_result"), tagging_result);

        get_utter_tagging_candidate(tagging_result["no"], $("#"+g_variable["_UID_"]+"_slot_tagged").val()); 
        
        slot_tagger_focus_item($("#"+g_variable["_UID_"]+"_slot_tagged_table"));
	});
}
//------------------------------------------------------------------------------
function slot_tagger_focus_item(obj) {
    var prev_style = obj.attr("style");
    
//    obj.css("font-weight", "bold");
    obj.css("border", "1px solid #FF0000");
    
    setTimeout(function(){
        obj.attr("style", prev_style);
	} , 1000);    
}
//------------------------------------------------------------------------------
function update_dialog_action() {
    var tagging_result = g_variable["slot_tagged_data"];
    
    tagging_result = get_utter_tagging_result(g_variable["_UID_"], tagging_result);
        
    var post_data = {
        "request": "run_utter_dialog_act_tagger",
        "cmd": "SET_SLOT_TAGGING",
        "utter": $("#"+g_variable["_UID_"]+"_utter").val(),
        "tagging_result": tagging_result,
        "slot_tagged": $("#"+g_variable["_UID_"]+"_slot_tagged").val()
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        g_variable["slot_tagged_data"]["dialog_action"] = ret["result"]["dialog_action"];
        $("#"+g_variable["_UID_"]+"_dialog_action").val(ret["result"]["dialog_action"]);

        slot_tagger_focus_item($("#"+g_variable["_UID_"]+"_dialog_action_table"));
	});
}
//------------------------------------------------------------------------------
function get_utter_tagging_candidate(no, tagging_result) {
    var post_data = {
        "request": "get_utter_tagging_candidate",
        "no": no,
        "tagging_result": tagging_result
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        display_utter_tagging_candidate($("#dialog_act_tagger_candidate"), ret);
	});
}
//------------------------------------------------------------------------------
function display_utter_tagging_candidate(obj_result, candidate) {
    obj_result.empty();
    obj_result.html("<img src=/imgs/loader-small.gif />");

    var buf = "";
    
    buf = "<br>";
    for( var tag in candidate ) {
        if( tag.substring(0, 1) == "_" ) continue;
        
        tag_html = htmlspecialchars(tag, "ENT_QUOTES");

        buf += "<div><b>TAG: "+tag_html+"</b></div><br>\n";
        for( var i in candidate[tag] ) {
            var no = candidate[tag][i]["no"];
            var worker = candidate[tag][i]["worker"];
            var query_result = candidate[tag][i]["slot_tagged"];
            
            if( typeof(query_result) == "undefined" ) query_result = "";

            query_result = htmlspecialchars(query_result, "ENT_QUOTES");
    
            query_result = query_result.replace(tag_html, "<font color='red'><b>"+tag_html+"</b></font>");
            
            buf += "<div>"+no+": "+query_result+" ("+worker+")</div>\n";
        }
        buf += "<hr>";
    }

    obj_result.html(buf);
}
//------------------------------------------------------------------------------
function htmlspecialchars (string, quote_style, charset, double_encode) {
    if( typeof(string) == "undefined" || string.substring(0, 1) == "_" ) return string;
    
    // http://kevin.vanzonneveld.net
    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nathan
    // +   bugfixed by: Arno
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +      input by: felix
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: charset argument not supported
    // *     example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
    // *     returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
    // *     example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);
    // *     returns 2: 'ab"c&#039;d'
    // *     example 3: htmlspecialchars("my "&entity;" is still here", null, null, false);
    // *     returns 3: 'my &quot;&entity;&quot; is still here'
    var optTemp = 0,
        i = 0,
        noquotes = false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }

    return string;
}
//------------------------------------------------------------------------------
function get_utter_list(param) {
    // default
    try {
        var grid = $("#datagrid_utter_list");
        if( grid.length != 0 ) {
            var opt = grid.datagrid("options");
            
            if( typeof(param.page_size) == "undefined" ) param.page_size = opt.pageSize;
            if( typeof(param.page_number) == "undefined" ) param.page_number = opt.pageNumber;
        }
    } catch(e) {
        console.log(e);
    }

    var post_data = {
        "keyword": param.keyword,
        "status": param.status,
        "worker": param.worker,
        "page_number": param.page_number,
        "page_size": param.page_size,
        "project_name": $.cookie("project_name"),
		"request": "get_utter_list"
    };
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        display_utter_list($("#dialog_act_tagger_panel_utter_list"), param, ret);
    });
}
//------------------------------------------------------------------------------
function display_utter_list(obj_result, param, ret) {
    obj_result.empty();
    
    var columns = [
  		{"editor": "text", "field": "no",       "title": "no",          "width": 50},
  		{"editor": "text", "field": "speaker",  "title": "speacker",    "width": 50},
		{"editor": "text", "field": "utter",    "title": "utter",       "width": 400},
  		{"editor": "text", "field": "status",   "title": "status",      "width": 90},
  		{"editor": "text", "field": "worker",   "title": "worker",      "width": 50}        
    ];
    
    var status_list = g_variable["utter_tagger_status_list"];
    
    var toolbar_status = '';
    
    toolbar_status += '<span>status: </span>';
    toolbar_status += '<select id="search_status">';
    for( var i in status_list ) {
        toolbar_status += ' <option>'+status_list[i]+'</option>';
    }
    toolbar_status += '</select>';
    
    var datagrid = $("<table id='datagrid_utter_list'></table>").appendTo(obj_result);
    
    datagrid.datagrid({	
        "lastIndex"     : -1,               
        "fitColumns"    : false,
        "rownumbers"    : false,  
        "singleSelect"  : true,
        "fit"           : true,
        "pagination"    : true,
        "pageNumber"    : param.page_number,
        "pageSize"      : param.page_size,
        "columns"       : [columns],
        "toolbar"       : [{
			"text":'<span>keyword: </span><input id="search_keyword" style="width: 100px; border: 1px solid #ccc;" />'
		}, {
			"text":'<span>worker: </span><input id="search_worker" style="width: 100px; border: 1px solid #ccc;" />'
		}, {
            "text": toolbar_status
		}, "-", {
			"text": "Search",
			"iconCls": "icon-search",
			"handler": function(){
                var opt = datagrid.datagrid("getPager").pagination("options");

                get_utter_list({
                    "keyword": $("#search_keyword").val(), 
                    "status" : $("#search_status").val(), 
                    "worker" : $("#search_worker").val(), 
                    "page_number": 1, 
                    "page_size" : opt.pageSize,
                    "row_index": 0
                });
			}
		}],
		"onClickRow": function(rowIndex){
            var row = $(this).datagrid("getRows")[rowIndex];
            get_utter_tagged_result(row);
		}
	}); 
    
    var p = datagrid.datagrid("getPager");
	$(p).pagination({
		"onChangePageSize": function(pageSize){
		},
		"onRefresh": function(pageNumber, pageSize){
            get_utter_list({
                "keyword": $("#search_keyword").val(), 
                "status" : $("#search_status").val(), 
                "worker" : $("#search_worker").val(), 
                "page_number": pageNumber, 
                "page_size" : pageSize,
                "row_index": param.row_index
            });
		},
		"onSelectPage": function(pageNumber, pageSize){
            get_utter_list({
                "keyword": $("#search_keyword").val(), 
                "status" : $("#search_status").val(), 
                "worker" : $("#search_worker").val(), 
                "page_number": pageNumber, 
                "page_size" : pageSize,
                "row_index": 0
            });
		}
	});           
    
    // fill up grid data
    datagrid.datagrid("loadData", {"total": ret.total, "rows": ret.rows}); 
    
    // set status
    $("#search_keyword").val(param.keyword);
    $("#search_status").val(param.status);
    $("#search_worker").val(param.worker);
    
    // clear slot tagging panel
    $("#dialog_act_tagger_tagged_result").empty();
    
    // set select row
    if( param.row_index > 0 ) {
        datagrid.datagrid("selectRow", param.row_index);
        
        var selected = datagrid.datagrid("getSelected");
        
        get_utter_tagged_result(selected);
    }
}
//------------------------------------------------------------------------------
function get_utter_tagged_result(row) {
    var post_data = {        
		"request": "get_utter_tagged_result",
        "no": row.no
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        display_tagged_result($("#dialog_act_tagger_tagged_result"), ret[0]);
        
        // get candidate
        get_utter_tagging_candidate(g_variable["slot_tagged_data"]["no"], g_variable["slot_tagged_data"]["slot_tagged"]);
    });
}
//------------------------------------------------------------------------------
function utter_status_change(obj) {
    var val = $(obj).val();
    
    var title_list = g_variable["utter_tagger_status_title_list"];
    
    for( var v in title_list ) {
        if( v != val ) continue;
        
        $(obj).attr("title", title_list[v]);        
        
        break;
    }     
}
//------------------------------------------------------------------------------
function display_tagged_result(obj_result, _rows) {
    g_variable["slot_tagged_data"] = _rows;
    
    obj_result.empty();
    
    var uid = g_variable["_UID_"] = uniqid();
    
    var buf = "";
    
    // info.
    buf += "<table style='width: 100%; text-align: center;' border=1>";
    buf += "    <tr><td style='width: 50px; background-color: #EFEFEF;'>";
    buf += "        NO";
    buf += "    </td><td style='width: 50px;'>";
    buf += _rows["no"];
    buf += "    </td><td style='width: 50px; background-color: #EFEFEF;'>";
    buf += "        Worker";
    buf += "    </td><td style='width: 100px;'>";
    buf += _rows["worker"];
    buf += "    </td><td style='width: 50px; background-color: #EFEFEF;'>";
    buf += "        Date";
    buf += "    </td><td>";
    buf += _rows["m_date"];
    buf += "    </td><td style='width: 100px; background-color: #EFEFEF;'>";
    buf += "        Status";
    buf += "    </td><td>";

    // set alias caption    
    var alias_caption = g_variable["utter_tagger_dialog_action_tagging_window_caption_list"];

    var title_list = g_variable["utter_tagger_status_title_list"];

    var status_list = g_variable["utter_tagger_status_list"];
    
    // init title
    var t = title_list[_rows["status"]];
    buf += "        <select id='"+uid+"_status' style='width: 100%;' onchange='utter_status_change(this)' title='"+t+"'>";
    
    for( var i in status_list ) {        
        if( _rows["status"] == status_list[i] ) {
            buf += "    <option selected='selected'>";
        } else {
            buf += "    <option>";
        }
        buf += status_list[i];
        buf += "        </option>";
    }

    buf += "        </select>";
    buf += "    </td></tr>";
    buf += "</table>";
    
    // display data
    for( var k in _rows ) {
        if( k.substring(0, 1) == "_" ) continue;
        
        if( k == "no" || k == "status" || k == "worker" || k == "m_date" ) continue;
        
        var id = uid+"_"+k;
        
        _rows[k] = $.trim(_rows[k]);
        
        if( k == "slot_tagged" && _rows[k] == "" ) _rows[k] = _rows["utter"]; 
        
        buf += "<table id='"+id+"_table' style='width: 100%;' border=1>";
        buf += "    <tr><td style='width: 100px; background-color: #EFEFEF;'>";
        if( typeof(alias_caption[k]) == "undefined" ) {
            buf += "&nbsp;"+k;
        } else {
            buf += "&nbsp;"+alias_caption[k];
        }
        buf += "    </td><td>";
        buf += "        <textarea id='"+id+"' style='width: 100%; height: 100%;'>"+_rows[k]+"</textarea>";
        buf += "    </td></tr>";
        buf += "</table>";
    }    
    obj_result.html(buf);
    
    // set textarea event
    $("#"+uid+"_slot_tagged").bind({
        "select": function(e) {
            var selection_obj_name = "slot_tagged";
            
            g_variable["_selection_obj_name_"] = selection_obj_name;
             
            g_variable["_"+selection_obj_name+"_selectionStart_"] = this.selectionStart; 
            g_variable["_"+selection_obj_name+"_selectionEnd_"] = this.selectionEnd;
            
            var text = get_selection_str();
            g_variable["_"+selection_obj_name+"_selectionText_"] = text; 
            
            $("#dialog_act_tagger_slot_structure").panel("setTitle", htmlspecialchars(g_variable["_"+selection_obj_name+"_selectionText_"], "ENT_QUOTES"));
            
            update_dialog_action();
        },
        "click": function(e) {
            var selection_obj_name = "slot_tagged";
            
            if( this.selectionStart - this.selectionEnd == 0 ) {
                g_variable["_"+selection_obj_name+"_selectionStart_"] = 0; 
                g_variable["_"+selection_obj_name+"_selectionEnd_"] = 0;
    
                g_variable["_"+selection_obj_name+"_selectionText_"] = ""; 
                
                $("#dialog_act_tagger_slot_structure").panel("setTitle", "Slot Structure");
            }

            update_dialog_action();
        }
    });    
    
    
    $("#"+uid+"_dialog_action").bind({
        "select": function(e) {
            var selection_obj_name = "dialog_action";
            
            g_variable["_selection_obj_name_"] = selection_obj_name;

            g_variable["_"+selection_obj_name+"_selectionStart_"] = this.selectionStart; 
            g_variable["_"+selection_obj_name+"_selectionEnd_"] = this.selectionEnd;
            
            var text = get_selection_str();
            g_variable["_"+selection_obj_name+"_selectionText_"] = text; 
            
            $("#dialog_act_tagger_slot_structure").panel("setTitle", htmlspecialchars(g_variable["_"+selection_obj_name+"_selectionText_"], "ENT_QUOTES"));
        },
        "click": function(e) {
            if( this.selectionStart - this.selectionEnd == 0 ) {
                var selection_obj_name = "dialog_action";
                
                g_variable["_"+selection_obj_name+"_selectionStart_"] = 0; 
                g_variable["_"+selection_obj_name+"_selectionEnd_"] = 0;
    
                g_variable["_"+selection_obj_name+"_selectionText_"] = ""; 
                
                $("#dialog_act_tagger_slot_structure").panel("setTitle", "Slot Structure");
            }
        }
    });    
        
}
//------------------------------------------------------------------------------
function get_selection_str() {
    var text = "";
    
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
    
    return text;
}
//------------------------------------------------------------------------------
function get_utter_tagging_result(_uid, tagging_result) {
    for( var k in tagging_result ) {
        var str = $("#"+_uid+"_"+k).val();
        
        if( typeof(str) != "undefined" ) {
            // str = str.replace(/\/\//gi, "");
            // console.log(str);
            tagging_result[k] = str;  
        } 
    }
    
    if( typeof(tagging_result["speaker"]) == "undefined" || tagging_result["speaker"] == "" ) tagging_result["speaker"] = "human";
    
    return tagging_result;
}
//------------------------------------------------------------------------------
function save_utter_tagging_result(_uid) {
    var tagging_result = g_variable["slot_tagged_data"];
    
    if( tagging_result == null ) return;
    
    tagging_result = get_utter_tagging_result(_uid, tagging_result);
    
    var user_email = $.cookie("user_email");
    
    if( typeof(user_email) != "undefinded" && user_email != "" ) tagging_result["worker"] = user_email;

    // post
    var post_data = {
        "request": "save_utter_tagging_result",
        "tagging_result": tagging_result
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        get_utter_list({
            "keyword": $("#search_keyword").val(), 
            "status" : $("#search_status").val(), 
            "worker" : $("#search_worker").val() 
        });
	});
}
//------------------------------------------------------------------------------
function replaceIt(obj, selectionStart, selectionEnd, newtxt) {
    obj.selectionStart = selectionStart;
    obj.selectionEnd   = selectionEnd;
    
    $(obj).val(
        $(obj).val().substring(0, obj.selectionStart)+newtxt+$(obj).val().substring(obj.selectionEnd)
    );
    
    obj.selectionStart = selectionStart;  
    obj.selectionEnd = selectionStart + newtxt.length;
    
    return (selectionStart + newtxt.length);
}

function clear_utter_tagging_result(_id) {
    var obj = $("#"+_id);
    
    var text = obj.val();
    
    var reg = /<[^=]+=([^>]+)>/mgi;
    var slot = (text.match(reg));
    
    text = text.replace(reg, "$1");
    
    if( slot != null ) obj.val(text);    
}
