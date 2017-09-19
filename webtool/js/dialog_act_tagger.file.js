//------------------------------------------------------------------------------
function dialog_act_tagger_open_upload_win() {
    // open window
    var buf = "";
    
    buf += "<div id='dialog_act_tagger_db_convert_win' style='overflow: auto; padding: 5px;'></div>";
    
    $(buf).appendTo("body");
    
    // open window
    $("#dialog_act_tagger_db_convert_win").window({  
        "iconCls"   : "icon-ok", 
        "title"     : "File Upload & Download",  
        "width"     : 500,  
        "height"    : dialog_act_tagger_get_window_heigth(300), 
        "modal"     : true,
        "inline"    : false,
        "collapsible" : false,
        "minimizable" : false,
        "maximizable" : false,
        "closable"    : true,
        "resizable"   : true,
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
    
    dialog_act_tagger_read_file_list($("#dialog_act_tagger_db_convert_win"));
}
//------------------------------------------------------------------------------
function dialog_act_tagger_db_convert(_name) {
    var file_list = $("#dialog_act_tagger_file_list");
    
    var index = file_list.datagrid("getRowIndex", _name);
    var row   = file_list.datagrid("getRows")[index];
    
    var filename = row.dirname;
    if( filename != "" ) filename += "/";
    filename += row.name;
    
    var post_data = {
    	"request": "dialog_act_tagger_db_convert",
        "project_name": $.cookie("project_name"),
        "project_type": $.cookie("project_type"),
        "user_name": $.cookie("user_name"),
        "filename": filename,
        "type": row.type
	};
    
	$.messager.progress({
		"title": "Please waiting",	
		"msg": "Converting file..."
	});    

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        console.log(ret);
        
        $.messager.progress("close");
        
        dialog_act_tagger_read_file_list($("#dialog_act_tagger_db_convert_win"));
    });     
}
//------------------------------------------------------------------------------
function dialog_act_tagger_download_file(_name) {
    var file_list = $("#dialog_act_tagger_file_list");
    
    var index = file_list.datagrid("getRowIndex", _name);
    var row   = file_list.datagrid("getRows")[index];
    
    var filename = row.dirname;
    if( filename != "" ) filename += "/";
    filename += row.name;
    
    var inputs = '';
    
    inputs += '<input type="hidden" name="request" value="dialog_act_tagger_download_file" />';
    inputs += '<input type="hidden" name="filename" value="'+ filename +'" />';

    jQuery('<form action="api/main.php" method="post">'+inputs+'</form>').appendTo('body').submit().remove();
}
//------------------------------------------------------------------------------
function dialog_act_tagger_file_formatter(value, row, index){    
	var del  = '';
    
    var project_name = $.cookie("project_name");    
//    if( row.name != project_name+"_DB.sqlite3" ) {
        del = '<img src="/imgs/delete-icon.16px.png" class="hand" title="delete" onclick="dialog_act_tagger_delete_data_file(\''+row.name+'\')" />&nbsp;';
//    }
    
	var down = '<img src="/imgs/fm/_down.png" class="hand" title="download" onclick="dialog_act_tagger_download_file(\''+row.name+'\')" />&nbsp;';
	
	var convert = '';
	
    if( row.name.indexOf(".bin") < 0 && row.name.indexOf(".svm") < 0 ) {
		convert = '<img src="/imgs/blue-document-convert-icon.png" class="hand" title="convert" onclick="dialog_act_tagger_db_convert(\''+row.name+'\')" />&nbsp;';
	}
    
	return "<div style='text-align: center;'>"+convert+down+del+"</div>";
}
//------------------------------------------------------------------------------
function dialog_act_tagger_delete_data_file(_name) {
    var file_list = $("#dialog_act_tagger_file_list");
    
    var index = file_list.datagrid("getRowIndex", _name);
    var row   = file_list.datagrid("getRows")[index];
    
    dialog_act_tagger_delete_file(row.dirname, row.name, function() {
        $("#dialog_act_tagger_file_list").datagrid("deleteRow", index);
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_delete_file(_dirname, _filename, _func_callback) {
    $.messager.confirm("Delete Confirm", "\""+_filename + "\" will be deleted. <br />Are you sure?", function(r){
		if( !r ) return;
        
    	var post_data = {
        	"request": "dialog_act_tagger_delete_data_file",
            "filename": _dirname + "/" + _filename	
    	};
    
    	$.post(g_variable["_POST_URL_"], post_data, function(data) {
            var ret = JSON.parse(data);
            
            if( ret.msg != "ok" ) {
                $.messager.alert("Error", ret.msg, "error");
                return;   
            }
            
            if( typeof(_func_callback) == "function" ) _func_callback();
        });
    });
}
//------------------------------------------------------------------------------
function dialog_act_tagger_read_file_list(_obj_result) {
    var post_data = {
    	"request": "dialog_act_tagger_read_file_list",
        "project_name": $.cookie("project_name")
	};

    _obj_result.empty();
    _obj_result.html(g_variable["gif_loader"]);    
	
	// console.log(g_variable["_POST_URL_"]);
	// console.log(post_data);
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		// build tree
        var ret = JSON.parse(data);     
        
        _obj_result.empty();
        
        var grid = $("<table id='dialog_act_tagger_file_list'></table>").appendTo(_obj_result); 
        
        var rows = [];
        for( var i in ret ) {
            rows.push({
                "name": ret[i].name, 
                "dirname": ret[i].dirname, 
                "type": ret[i].extension, 
                "size": commify(ret[i].size),
                "checked": false
            });                    
        }
        
        grid.datagrid({	
            "lastIndex"     : -1,               
            "singleSelect"  : false,
            "width"  : "100%",
            "height"  : 200,
            "idField"       : "name",
            "columns"       : [[
        		{"field": "checked",   "title": "",           "checkbox": true},
        		{"field": "name",      "title": "file name",  "width": 200},
        		{"field": "size",      "title": "size",       "width": 50},
        		{"field": "type",      "title": "type",       "width": 30},
        		{"field": "action",    "title": "action",     "width": 80, "formatter": dialog_act_tagger_file_formatter},
        		{"field": "progress",  "title": "progress",   "width": 80}
            ]],
            "fitColumns"    : false
    	});            
        
        // fill up grid data
        grid.datagrid("loadData", {"rows": rows});
        
        // file upload 
        $('<br />').appendTo(_obj_result);        

        var res_upload_progress_all = $('<div></div>').appendTo(_obj_result);        
        res_upload_progress_all.progressbar({"width": "100%"}); 

        var obj_fileupload = $('<input type="file" name="files[]" multiple />').appendTo(_obj_result); 
        
        obj_fileupload.fileupload({
            "dataType": "json",
            "url": g_variable["_POST_URL_"],
            "formData": function (form) {
                form.push({
                        "name": "request", 
                        "value": "dialog_act_tagger_upload_file"
                });
                return form;
            },
            "progress": function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                
                var progress_id = "progress_id_"+data.files[0].name.replace(/\./g, "_");

                $("#"+progress_id).progressbar("setValue", progress);
            },
            "progressall": function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                res_upload_progress_all.progressbar("setValue", progress);

                if( progress == 100 ) {
                    setTimeout(function() {
                        dialog_act_tagger_read_file_list(_obj_result);   
                    }, 1000);                        
                }
            },
            "add": function (e, data) {
                $.each(data.files, function (index, file) {
                    var progress_id = "progress_id_"+file.name.replace(/\./g, "_");
                    
                    var row = {
                        "name":file.name, 
                        "type": file.type, 
                        "checked": false, 
                        "size": commify(file.size),
                        "progress": '<div id="'+progress_id+'"></div>'
                    };
                    
                    grid.datagrid("appendRow", row);
                    
                    // build progress bar
                    $("#"+progress_id).progressbar({"width": "100%"});
                });
                
                var jqXHR = data.submit();
            }
        });        
	});    
}
//------------------------------------------------------------------------------
