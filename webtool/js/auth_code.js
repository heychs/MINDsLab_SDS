//------------------------------------------------------------------------------
function delete_pid(_ip, _pid, _icon_id) {
	if( _icon_id ) $("#"+_icon_id).html(g_variable["gif_loader"]);
    
    var post_data = {
		"request" : "delete_pid",
		"ip"      : _ip,
		"pid"     : _pid
    };

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		if( _icon_id ) $("#"+_icon_id).html(data);
	});
}
//------------------------------------------------------------------------------
function get_value_at_rows(_rows, _needle) 
{
    for( var i in _rows ) {
        if( _rows[i].tag == _needle ) return _rows[i].val; 
    }
    
    return "";
}
//------------------------------------------------------------------------------
function open_deamon_log(obj_self, deamon_log) 
{
    if( deamon_log == "" ) return;
    
    // display loading img
    var org_class = $(obj_self).attr("class");
    if( org_class.indexOf("easyui-linkbutton") < 0 ) $(obj_self).attr("class", "icon-loading");

    var content_id = uniqid();
    var win_result = $("<div class='deamon_log_content'><textarea id='"+content_id+"' class='deamon_log_result'></textarea></div>").appendTo("body");
    
    // open window
    win_result.window({  
        "iconCls"   : "icon-ok",
        "title"     : "daemon log: " + deamon_log,  
        "width"     : 600,  
        "height"    : dialog_act_tagger_get_window_heigth(400), 
        "modal"     : true,
        "inline"    : false,
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose"   : function(forceClose){
            $(this).remove();
        }       
    });
    
    if( org_class.indexOf("easyui-linkbutton") < 0 ) $(obj_self).attr("class", org_class);
    
    win_result.css("overflow", "hidden");    

	tail_log(deamon_log, content_id, 0);
}
//------------------------------------------------------------------------------
//var g_stop_tail_log = 1;
function tail_log(file_name, result_id, start_line_num, _request, _unlimit_line) {
	if( file_name == "" ) return;
    
    if( typeof(_request) == "undefined" ) _request = "tail_log";
    
    var obj_result = $("#"+result_id);
    if( obj_result == null || typeof(obj_result) == "undefined" || obj_result.length == 0 ) return;

    // post data
	var post_data = {
        "type"          : "XML",	
    	"request"      : _request,
    	"file_name"    : file_name,
    	"start_line_num": start_line_num
	};
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		var ret = { "max_cnt": -1, "msg": "", "result": "" };        
        if( post_data.type == "XML" ) {
            // get max cnt
            data.match(/<max_cnt><!\[CDATA\[(-?\d+)\]\]><\/max_cnt>/i);
            ret.max_cnt = RegExp.$1;

            // get msg
            var msg = data;
            msg = msg.replace(/^.+?<msg><!\[CDATA\[/i, "");
            msg = msg.replace(/\]\]><\/msg>.*$/i, "");

            ret.msg = msg;
            
            // get result
            var result = data;
            result = result.replace(/^.+?<result><!\[CDATA\[/i, "");
            result = result.replace(/\]\]><\/result>.*$/i, "");
            
            ret.result = result;
        } else {
            ret = JSON.parse(data);
        }
        
        if( obj_result == null || typeof(obj_result) == "undefined" || obj_result.length == 0 ) return;
        
        if( ret.msg != null && ret.msg.indexOf("ERROR:") == 0 ) {
            obj_result.val(ret.msg);
            return;
        }
        
		if( ret.result != null ) {
            if( ret.result != "" ) {
    			var prev_log = obj_result.val();
    			
    			if( typeof(_unlimit_line) == "undefined" || _unlimit_line != 1 ) {
	    			if( prev_log.length > 10000 ) prev_log = "";
    			}			
    			
    			obj_result.val(prev_log + "\n" + ret.result);
    			
    			document.getElementById(result_id).scrollTop = document.getElementById(result_id).scrollHeight;
            }
            
    		setTimeout(function(){
    				tail_log(file_name, result_id, eval(ret.max_cnt++), _request, _unlimit_line);
    			} , 1000);
		}
	});
}
//------------------------------------------------------------------------------
function get_daemon_status(obj_self, _deamon_tag, _obj_result) 
{
    // display loading img
    var org_class = $(obj_self).attr("class");
    if( org_class.indexOf("easyui-linkbutton") < 0 ) {
        $(obj_self).attr("class", "icon-loading");   
    }
    
    // post
	var post_data = {
    	"request"      : "get_daemon_status",	
    	"deamon_tag"   : _deamon_tag
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
	    var win_result = null;
        if( typeof(_obj_result) == "undefined" ) {
            win_result = _obj_result = $("<div style='width: 560px;'></div>").appendTo($("body"));
        } else {
            _obj_result.empty();
            _obj_result.css("overflow", "scroll");
        } 
        
        var ret = JSON.parse(data);
        
        for( var i in ret ) {                    
            var content = ret[i].cmd;            
            content = str_replace_all(content, " -", "<br />&nbsp;&nbsp;&nbsp;&nbsp;-");
            
            var p = $("<div></div>").appendTo(_obj_result);
            
            p.panel({
                "ip"        : ret[i].ip, 
                "pid"       : ret[i].pid, 
                "title"     : ret[i].tag, 
                "content"   : content, 
                "iconCls"   : "icon-ok", 
                "closable"  : true, 
                "collapsed" : false,
                "collapsible": true, 
                "maximizable": false,
                "onClose": function(){
                    var opt = $(this).panel("options");                    
                    delete_pid(opt.ip, opt.pid, null);
                }
            });
            
            $("<div class='panel_margin'></div>").appendTo(_obj_result);
        }
        
        // open window
        if( win_result ) {
            win_result.window({  
                "iconCls"   : "icon-ok",
                "title"     : "daemon status: " + _deamon_tag,  
                "width"     : 600,  
        		"height"    : dialog_act_tagger_get_window_heigth(400), 
                "modal"     : true,
                "inline"    : false,
		        "onMove": function(left, top) {
					dialog_act_tagger_on_window_move(this, left, top);
		        },
                "onClose"   : function(forceClose){
                    $(this).remove();
                }       
            });
        }
        
        if( org_class.indexOf("easyui-linkbutton") < 0 ) $(obj_self).attr("class", org_class);
	});
}
//------------------------------------------------------------------------------
function unlock_mid_result(obj_self, result_id)
{
    // display loading img
    var class_name = $(obj_self).attr("class");
    
    if( class_name == "icon-unlock" ) {
        $(obj_self).attr("title", "Display Mid Result");   
        $(obj_self).attr("class", "icon-lock");   
    } else {
        $(obj_self).attr("title", "Hide Mid Result");   
        $(obj_self).attr("class", "icon-unlock");   
    }
}
//------------------------------------------------------------------------------
function make_datagrid(obj_result, title, _tools, _columns, _rows) 
{
    // make panel 
    var data_grid_id = uniqid();
    
    var panel = $("<div style='padding: 5px;'></div>").appendTo(obj_result);
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

    $("<div class='panel_margin'></div>").appendTo(obj_result);

    // add panel at portal
//    obj_result.portal("add", {"panel": panel, "columnIndex": eval(obj_result.length + 1)});                    

    // make data grid
    var datagrid = $("<table id='"+data_grid_id+"'></table>").appendTo(panel);    
    datagrid.datagrid({	
        "lastIndex"     : -1,               
        "singleSelect"  : true,
        "columns"       : [_columns],
        "singleSelect": true,
        "fitColumns": true,
		"onClickRow" : function(rowIndex){
            var dg = $(this);
		    var lastIndex = dg.datagrid("options").lastIndex;
            
			if( lastIndex != rowIndex ){
				dg.datagrid("endEdit", lastIndex);
				dg.datagrid("beginEdit", rowIndex);
			}
            
			dg.datagrid("options").lastIndex = rowIndex;
		}
	});            
    
    // fill up grid data
    datagrid.datagrid("loadData", {"rows": _rows}); 
}
//------------------------------------------------------------------------------
