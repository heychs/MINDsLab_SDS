//------------------------------------------------------------------------------
// TO DO
// * User List 에서 환경 설정을 새 창에서 하는 것으로 변경
//------------------------------------------------------------------------------
function open_build_path(rev, base_dir, build_target_id) {
	var path = base_dir+"/"+get_combobox_value($("#"+build_target_id))+"."+rev+"/";
	window.open(path, "");
}
//------------------------------------------------------------------------------
function add_service(rev, build_target_id, result_id, link_id) {
	var post_data = {
    	"request": "add_service",	
    	"rev": rev,	
    	"build_target": get_combobox_value($("#"+build_target_id)),	
    	"user_email": $.cookie("user_email"),		
    	"lang_type": get_engine_info("lang_type"),    	
    	"svn_home": get_combobox_value($("#"+build_target_id))+"."+rev	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		var ret = JSON.parse(data);
		
		set_text_by_id(link_id, ret.fn_engine);
		set_text_by_id(result_id, ret.msg);
	});
}
//------------------------------------------------------------------------------
function start_tail_log(file_name, result_id) {
	if( file_name == "" ) {
		set_text_by_id(result_id, "DONE");
		return;	
	}
	
	set_text_by_id(result_id, "");	
	
	var result_log = result_id+'_tail_log_result';
	
	if( result_id != "" ) {	
		var obj = document.getElementById(result_log);
		if( obj == null ) {
			var html = "";
			
			html += "<textarea id='"+result_log+"' style='word-wrap: break-word; width: 100%; height: 300px;'></textarea>";
			
			set_text_by_id(result_id, html);
		}		
	}
	
	setTimeout(function(){
        tail_log(file_name, result_log, 0);
    } , 1000);
}
//------------------------------------------------------------------------------
function build_engine(svn_url, rev, build_target_id, result_id, user_home)
{
	set_loading_img(document.getElementById(result_id));

	var post_data = {
    	"request": "build_engine",	
    	"user_email": $.cookie("user_email"),	
    	"rev": rev,	
    	"svn_url": svn_url,
    	"user_home": user_home,
    	"build_target": get_combobox_value($("#"+build_target_id)),	
    	"lang_type": get_engine_info("lang_type"),	
    
    	"svn_home": get_combobox_value($("#"+build_target_id))+"."+rev,		
    	"build_log": get_combobox_value($("#"+build_target_id))+"."+rev+".log"
	};
	
	setTimeout(function(){
        start_tail_log(user_home + "/" + post_data.build_log, result_id, 0);
    } , 1000);
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
//        var ret = JSON.parse(data);        
//        start_tail_log(ret.log_file_name, result_id);    
	});
}
//------------------------------------------------------------------------------
function get_svn_info(obj, result_id, linked_id, max_cnt)
{
	// toggle result
	if( obj.flag_selected && obj.flag_selected == 1 ) {
		obj.flag_selected = 0;
	
		set_text_by_id(result_id, "");
		hide(result_id);
		return;	
	}
	obj.flag_selected = 1;

	set_loading_img(document.getElementById(result_id));

	var post_data = {
    	"request": "get_svn_info",	
    	"user_email": $.cookie("user_email"),	
    	"lang_type": get_engine_info("lang_type"),	
    	"linked_id": linked_id,	
    	"max_cnt": max_cnt
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var obj_result = $("#"+result_id);
        obj_result.empty();	

        var ret = JSON.parse(data);

        // build option
        var makefile_opt_id = uniqid();

        var item = {
            "rev": 0,
            "title": "Makefile Option",
            "svn_url": "",
            "comment": "",
            "makefile": ret.makefile,
            "makefile_opt_id": makefile_opt_id     
        }; 
        
        make_panel_svn_list(obj_result, item, []);
        
        var buf = "";
        for( var i in ret ) {
            var item = ret[i];
            
            item.user_home = ret.user_home;
            item.makefile_opt_id = makefile_opt_id;
            
            var tools = [];
            tools.push({
                "iconCls": "icon-admin_svncheckout",
                "handler": function(){
                    var opt = $(this.parentNode.parentNode.parentNode.children.item(1)).panel("options");

                	set_loading_img(document.getElementById(opt.panel_id));
                    
                	var post_data = {
                    	"request": "svn_checkout",	
                    	"user_email": $.cookie("user_email"),	
                    	"rev": opt.rev,	
                    	"svn_url": opt.svn_url,
                    	"build_target": opt.makefile_opt_id,	
                    	"lang_type": get_engine_info("lang_type"),
                        "user_home": opt.user_home,    
                    	"svn_home": get_combobox_value($("#"+opt.makefile_opt_id))+"."+opt.rev,	
                    	"build_log": get_combobox_value($("#"+opt.makefile_opt_id))+"."+opt.rev+".log"
                	};
                    
                	setTimeout(function(){
                        start_tail_log(opt.user_home + "/" + post_data.build_log, opt.panel_id, 0);
                    } , 1000);
                    
                	$.post(g_variable["_POST_URL_"], post_data, function(data) {});                    
                }
            },{
                "iconCls": "icon-admin_build_engine",
                "handler": function(){
                    var opt = $(this.parentNode.parentNode.parentNode.children.item(1)).panel("options");

                    build_engine(opt.svn_url, opt.rev, opt.makefile_opt_id, opt.panel_id, opt.user_home);
                }
            },{
                "iconCls": "icon-admin_cp_engine_service",
                "handler": function(){
                    var opt = $(this.parentNode.parentNode.parentNode.children.item(1)).panel("options");

                    add_service(opt.rev, opt.makefile_opt_id, opt.panel_id, opt.linked_id);
                }
            },{
                "iconCls": "icon-admin_delete_svn",
                "handler": function(){
                    var opt = $(this.parentNode.parentNode.parentNode.children.item(1)).panel("options");
                    
                	var post_data = {
                    	"request": "clear_build",	
                    	"rev": opt.rev,	
                    	"build_target": opt.makefile_opt_id,	
                    	"user_email": $.cookie("user_email"),		
                    	"lang_type": g_variable["env"]["engine_info"]["lang_type"],
                        "user_home": opt.user_home,
                    	"svn_home": get_combobox_value($("#"+opt.makefile_opt_id))+"."+opt.rev
                	};
                
                	$.post(g_variable["_POST_URL_"], post_data, function(data) {
                		console.log(data);
                	});
                }
            },{
                "iconCls": "icon-blank"
            });

            make_panel_svn_list(obj_result, item, tools);
    	}
            
	});
}
//------------------------------------------------------------------------------
function make_panel_svn_list(obj_result, item, _tools) 
{
    obj_result.css("overflow", "scroll");
    
    // make panel body
    var panel_body = "";            
    
    if( typeof(item.makefile) != "undefined" ) {
        panel_body += "<span id='"+item.makefile_opt_id+"'></span>&nbsp;";   
    }
    
    panel_body += "<span>"
    panel_body += item.comment;
    panel_body += "</span>"

    // make panel 
    var panel_id = uniqid();
    
    var panel = $("<div id='"+panel_id+"' style='padding: 5px;'>"+panel_body+"</div>").appendTo(obj_result);
    panel.panel({
        "title"     : item.title,
        "iconCls"   : "icon-ok",
        "inline"    : true,
        "closable"  : false, 
        "collapsed" : false, 
        "collapsible": true, 
        "maximizable": false,
        "rev": item.rev,
        "panel_id": panel_id,
        "svn_url": item.svn_url,
        "user_home": item.user_home,
        "makefile_opt_id": item.makefile_opt_id,
        "tools"     : _tools
    });    

    // make makefile option list
    if( typeof(item.makefile) != "undefined" ) {
        var obj_makefile_opt = $("#"+item.makefile_opt_id);    
        obj_makefile_opt.combobox({
            "valueField": "value", 
            "textField": "text", 
            "required": true, 
            "editable": false
        });
        
        obj_makefile_opt.combobox("loadData", item.makefile);
    }
}
//------------------------------------------------------------------------------
function get_array_string(post_data, key_list)
{
	var buf_key_list = "";
    
	var list = key_list.split(",");
	for( var i=0 ; i<list.length ; i++ ) {
		var buf_k = "";		
		for( var j=0 ; j<3 ; j++ ) {
			var v = get_text_by_id(list[i]+"_key_"+j);
			
			if( v != "" ) {
				if( buf_k != "" ) buf_k += ",";
				buf_k += v;
			}
		}	

		var v = get_text_by_id(list[i]+"_value");
		if( v != "" ) {
			post_data[buf_k] = v;
			
			if( buf_key_list != "" ) buf_key_list += "\n";
			buf_key_list += buf_k;
		}
	}
    
    post_data.key_list = buf_key_list;

	return post_data;
}
//------------------------------------------------------------------------------
function get_user_list(obj_self, result_id)
{
    var obj_result = $("#"+result_id);
    
    obj_result.empty();

    var post_data = {
		"request": "get_user_list"
    };
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        var columns = [
    		{"editor": "text", "field": "user_email", "title": "user_email",  "width": 100},
    		{"editor": "text", "field": "user_name",  "title": "user_name",   "width": 100},
    		{"editor": "text", "field": "user_pw",    "title": "user_pw",     "width": 100},
    		{"editor": "text", "field": "m_date",     "title": "m_date",      "width": 100},
    		{"editor": "text", "field": "level",      "title": "level",       "width": 100},
    		{"editor": "text", "field": "comment",    "title": "comment"}
        ];
        
        // make data grid
        var tools = [];
        tools.push({
            "iconCls": "icon-add",
            "handler": function(){
                var p = this.parentNode.parentNode.parentNode.children.item(1);
                var dg = $("#"+$(p).panel("options").data_grid_id);
                
    			dg.datagrid("appendRow", {
    			     "user_email": "", 
                     "user_name": "", 
                     "user_pw":"", 
                     "m_date":"", 
                     "level":"", 
                     "comment":"", 
                     "env": '{"default":{"lang_type":"ek","ek":{"engine":"ek_mt.3770","dictionary":"dicek.wbs.2012","dictionary_fix":"1","engine_fix":"1"}},"tab":{"Dialog Act":"1"},"lang_type":{"ck":"0","ek":"1","ke":"1","kc":"0"}}' 
                });
    			dg.datagrid("endEdit", dg.datagrid("options").lastIndex);
    
                var lastIndex = dg.datagrid("getRows").length-1;
                
    			dg.datagrid("selectRow", lastIndex);
    			dg.datagrid("beginEdit", lastIndex);
                
                dg.datagrid("options").lastIndex = lastIndex;
            }
        },{
            "iconCls": "icon-remove",
            "handler": function(){
                var p = this.parentNode.parentNode.parentNode.children.item(1);
                var dg = $("#"+$(p).panel("options").data_grid_id);
                
    			delete_user_list(this, dg.datagrid("getSelected"));         
            }
        },{
            "iconCls": "icon-save",
            "handler": function(){
                var p = this.parentNode.parentNode.parentNode.children.item(1);
                var dg = $("#"+$(p).panel("options").data_grid_id);
                
    			dg.datagrid("endEdit", dg.datagrid("options").lastIndex);   
                
                save_user_list(this, dg.datagrid("getSelected"));            
            }
        },{
            "iconCls": "icon-blank"
        });
                
        make_datagrid_user_list(obj_result, "User Information", tools, columns, ret);
	});
}
//------------------------------------------------------------------------------
function delete_user_list(obj_self, _row) 
{
    // display loading img
    var org_class = $(obj_self).attr("class");
    if( org_class.indexOf("easyui-linkbutton") < 0 ) $(obj_self).attr("class", "icon-loading");

    var post_data = {
        "request"    : "update_user",
    	"request_type" : "delete",
    	"user_email" : _row.user_email,
    	"user_name"  : _row.user_name,
    	"user_pw" 	 : _row.user_pw,
    	"comment"	 : _row.comment,
    	"level" 	 : _row.level,
    	"env" 		 : _row.env
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {	   
		var ret = JSON.parse(data);
		
        if( ret["msg"] != "" ) {
            alert(ret["msg"]);   
        } else {
            alert("ERROR:"+ret["msg"])
        }
        if( org_class.indexOf("easyui-linkbutton") < 0 ) $(obj_self).attr("class", org_class);
    });
}
//------------------------------------------------------------------------------
function save_user_list(obj_self, _row) 
{
    // display loading img
    var org_class = $(obj_self).attr("class");
    if( org_class.indexOf("easyui-linkbutton") < 0 ) $(obj_self).attr("class", "icon-loading");

    var post_data = {
        "request"    : "update_user",
    	"user_email" : _row.user_email,
    	"user_name"  : _row.user_name,
    	"user_pw" 	 : _row.user_pw,
    	"comment"	 : _row.comment,
    	"level" 	 : _row.level,
    	"env" 		 : _row.env
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {	   
		var ret = JSON.parse(data);
		
        if( ret["msg"] != "" ) {
            alert(ret["msg"]);   
        } else {
            alert("ERROR:"+ret["msg"])
        }
        if( org_class.indexOf("easyui-linkbutton") < 0 ) $(obj_self).attr("class", org_class);
    });
}
//------------------------------------------------------------------------------
function convert_env_to_treegride(_obj) {
    var ret = [];

    if( typeof(_obj) != "object" ) return ret;
        
    for( var k in _obj ) {
        var v = _obj[k];        

        var child = [];
        child = convert_env_to_treegride(v);

        var item = {"id": uniqid(), "key": k, "value": v};
        if( child.length > 0 ) {
            item = {"id": uniqid(), "key": k, "children": child};
        }       
        
        ret.push(item);
    }
    
    return ret;
}
//------------------------------------------------------------------------------
function convert_treegride_to_env(_obj) {
    var ret = {};

    if( typeof(_obj) != "object" ) return ret;
        
    for( var k in _obj ) {
        var v = _obj[k];        
        
        var child = convert_treegride_to_env(v.children);

        var key = v.key;
        var value = v.value;
            
        if( JSON.stringify(child) != "{}" ) {
            eval('item = { "'+key+'" : '+JSON.stringify(child)+' };');
            $.extend(ret, item);
        } else if( typeof(value) != "undefined" && value != "" ) {
            ret[key] = value;
        } else {
            console.log("ERROR");
        }
    }
    
    return ret;
}
//------------------------------------------------------------------------------
function make_datagrid_user_list(obj_result, title, _tools, _columns, _rows) 
{
    // make panel 
    var data_grid_id = uniqid();
    
    obj_result.css("overflow", "scroll");
    
    var buf = "";
    
    buf += "<div style='overflow: auto; padding: 2px;'>";
    
    buf += "<div id='user_list_context_menu' style='width: 100px; display: none;'>";
    buf += "    <div iconCls='icon-edit'>edit</div>";
    buf += "</div>";
    
    buf += "</div>";
    
    var panel = $(buf).appendTo(obj_result);
    panel.panel({
        "title"     : title,
        "iconCls"   : "icon-ok",
        "inline"    : true,
        "closable"  : true, 
        "collapsed" : false, 
        "collapsible": true, 
        "maximizable": true,
        "data_grid_id": data_grid_id,
        "rows"      : _rows,
        "tools"     : _tools
    });

    // make menu 
    $("#user_list_context_menu").menu({
        "onClick": function(item){
            if( item.text == "edit" ) {
                open_user_option($("#"+data_grid_id), data_grid_id);
            }
        }
    });

    // make data grid
    var datagrid = $("<table id='"+data_grid_id+"'></table>").appendTo(panel);    
    datagrid.datagrid({	
        "columns"       : [_columns],
        "lastIndex"     : -1,               
        "fitColumns"    : false,
        "rownumbers"    : true,  
        "singleSelect"  : true,
        "data_grid_id"  : data_grid_id,
		"onClickRow": function(rowIndex){
            var dg = $(this);
		    var lastIndex = dg.datagrid("options").lastIndex;
            
			if( lastIndex != rowIndex ){
				dg.datagrid("endEdit", lastIndex);
				dg.datagrid("beginEdit", rowIndex);
			}
			dg.datagrid("options").lastIndex = rowIndex;
		}, 
		"onRowContextMenu": function(e, rowIndex, rowData){
            var dg = $(this);
            dg.datagrid("selectRow", rowIndex);            

            e.preventDefault();            

            var _menu = $("#user_list_context_menu");
            if( _menu ) {
                _menu.menu("show", {
                    "left": e.pageX,
                    "top" : e.pageY
                });
            }
		}
	});            
    
    // fill up grid data
    datagrid.datagrid("loadData", {"rows": _rows}); 
}
//------------------------------------------------------------------------------
function open_user_option(obj, data_grid_id) 
{
    // open window
    var buf = "";
    
    buf += "<div style='overflow: hidden; padding: 2px;'>";
    
    buf += "<div id='user_option_context_menu' style='width: 100px; display: none;'>";
    buf += "    <div iconCls='icon-add'>add</div>";
    buf += "    <div iconCls='icon-remove'>delete</div>";
    buf += "</div>";
    
    buf += "</div>";
    
    var win_result = $(buf).appendTo("body");
    
    // open window
    win_result.window({  
        "iconCls"   : "icon-ok", 
        "title"     : "User Property",  
        "width"     : 400,  
        "height"    : dialog_act_tagger_get_window_heigth(500), 
        "modal"     : true,
        "inline"    : false,
        "closable"    : true,
        "resizable"   : true,
        "collapsible" : false,
        "minimizable" : false,
        "maximizable" : false,
        "tools" : [{
            "iconCls": "icon-add",  
            "handler":function(){
            }
        }, {
            "iconCls": "icon-remove",  
            "handler":function(){
            }
        }],
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
    
    // make menu 
    $("#user_option_context_menu").menu({
        "onClick": function(item){
            if( item.text == "add" ) {
            } else if( item.text == "delete" ) {
            }
        }
    });
    
//    var index = $(obj).datagrid("getSelected");
    var row = $(obj).datagrid("getSelected");

    // make grid
    var opt = $(obj).datagrid("options");
    
    var grid = $("<table></table>").appendTo(win_result);            
    grid.treegrid({  
        "lastIndex" : -1, 
        "fit": true,  
        "fitColumns": true,  
        "rownumbers": true,  
        "singleSelect": true,  
        "parent_id" : data_grid_id, 
        "loadMsg"   : "",  
        "height"    : "auto",  
        "idField"   : "id",                
        "treeField" : "key",                
        "columns"   : [[  
            {"editor": "text", "field": "key",    "title": "key",   "width": 100},  
            {"editor": "text", "field": "value",  "title": "value", "width": 100}  
        ]],
        "onAfterEdit": function(rowIndex, rowData, changes){
            var opt = $(this).treegrid("options");
            var subgrid_data = $(this).treegrid("getData");
            
            var env = convert_treegride_to_env(subgrid_data);
            
            var row = $(obj).datagrid("getSelected");
            var index = $(obj).datagrid("getRowIndex", row);
             
            var parent_row = $("#"+opt.parent_id).datagrid("getRows")[index];
             
            env = JSON.stringify(env);
            if( parent_row.env != env ) {
                $("#"+opt.parent_id).datagrid("getRows")[index].env = env;                           
            }
        },
        "onClickRow": function(row){
            var tg = $(this);
            var lastIndex = tg.treegrid("options").lastIndex;
            
            if( lastIndex != row.id ){
                tg.treegrid("endEdit", lastIndex);
                tg.treegrid("beginEdit", row.id);
            }
            tg.treegrid("options").lastIndex = row.id;
        }
    });  

    var env = JSON.parse(row.env);    
    var child = convert_env_to_treegride(env);

    grid.treegrid("loadData", child);
}
//------------------------------------------------------------------------------
