//------------------------------------------------------------------------------
// TODO
//  * file list
//   - group delete
//   - group download
//------------------------------------------------------------------------------
function init_res_manager() {
    res_category_refresh();
}
//------------------------------------------------------------------------------
function res_data_tree_refresh() {
    var node = $("#res_category").tree("getSelected");
    var path = res_get_path(node.attributes);
    
    res_tree_build_data(path);
}
//------------------------------------------------------------------------------
function res_category_refresh() {
    res_tree_build_category($('#res_category'), $('#res_manager_result'), $('#res_manager_main_panel'));
}
//------------------------------------------------------------------------------
function res_rename_node(_tree, _func_callback) {
    if( typeof(_tree) == "string" ) _tree = $("#"+_tree);
    
    var node = _tree.tree("getSelected");
    if( node == null && _tree.hasClass("tree") == false) {
        return;   
    }    
    
    $.messager.prompt("Rename Directory", "Give me Directory Name:", function(r){
		if( r ){
            var dirname = res_get_path(node.attributes, true);
            
            var source_path = dirname + "/" + node.attributes.name;
            var target_path = dirname + "/" + r;
            
            res_move_directory(source_path, target_path, _func_callback);
		}
	});    
}
//------------------------------------------------------------------------------
function res_add_category(_tree) {
    if( typeof(_tree) == "string" ) _tree = $("#"+_tree);
    
    var node = _tree.tree("getSelected");
    if( node == null && _tree.hasClass("tree") == false) {
        return;   
    }    
    
    $.messager.prompt("Add Directory", "Give me Directory Name:", function(r){
		if( r ){
            res_add_directory(_tree, r, node);
		}
	});    
}
//------------------------------------------------------------------------------
function res_delete_category(_tree) {
    if( typeof(_tree) == "string" ) _tree = $("#"+_tree);
    
    var node = _tree.tree("getSelected");    
    if( node == null ) return;    
    
    $.messager.confirm("Delete Confirm", "\"" + node.text + "\" will be deleted. <br />Are you sure?", function(r){
		if( r ){
            res_delete_directory(_tree, node);
		}
	});    
}
//------------------------------------------------------------------------------
function res_delete_directory(_tree, _node) {
    var path = res_get_path(_node.attributes);
    
	var post_data = {
    	"request": "res_delete_directory",
        "path": path	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        if( ret.msg != "ok" ) {
            $.messager.alert("Error", ret.msg, "error");
            console.log(ret.error_msg);
            return;   
        }

        _tree.tree("remove", _node.target);
    });
}
//------------------------------------------------------------------------------
function res_add_directory(_tree, text, _node, _func_callback) {
    var path = res_get_path(_node.attributes);
    
	var post_data = {
    	"request": "res_add_directory",
        "path": path + "/" + text	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        
        if( ret.msg != "ok" ) {
            $.messager.alert("Error", ret.msg, "error");
            console.log(ret.error_msg);
            return;   
        }
        
        var dirname = _node.attributes.dirname;
        if( dirname != "" ) dirname += "/";
        dirname += _node.attributes.name;
        
        _tree.tree("append", {  
            "parent": ( _node ) ? _node.target : null,  
            "data": [{
                "id": uniqid(), 
                "state": "closed", 
                "text": text, 
                "iconCls": "tree-folder",
                "attributes": {
                    "name": text,
                    "dirname": dirname,
                    "category": _node.attributes.category
                }
            }]  
        });
        
        if( typeof(_func_callback) == "function" ) _func_callback();
    });
}
//------------------------------------------------------------------------------
function res_hide_category() {
    $("#res_manager_layout").layout("collapse", "west");
    
    var west_panel = $("#res_manager_layout").layout("panel", "west");    
    west_panel.panel({
        "onExpand":function(){
    		$("#res_manager_result").empty();
    	}
    });
}
//------------------------------------------------------------------------------
function res_get_new_wiki_meta_data(_tree, _result) {
    // get full path            
    var node = _tree.tree("getSelected");    
    var path = res_get_path(node.attributes);
    
	var post_data = {
    	"request": "res_get_new_wiki_meta_data",
        "path": path	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);

        if( ret.msg != "ok" ) {
            $.messager.alert("Error", ret.msg, "error");
            console.log(ret.error_msg);
            return;   
        }
        
        // clear
        _result.empty();
    
        res_read_wiki_meta_data(_result, path);
	});
}
//------------------------------------------------------------------------------
function res_edit_wiki_meta_data(_obj_panel, _path) {
	var post_data = {
    	"request": "res_read_wiki_meta_data",
        "path": _path	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        var wiki_meta_data = ret.wiki_meta_data;
        
        if( wiki_meta_data == "" ) return;
        
        var org_html = _obj_panel.html();
        _obj_panel.empty();
                
        _obj_panel.css("overflow", "hidden");

        var editor = $("<textarea class='res_wiki_editor'></textarea>").appendTo(_obj_panel);        
        editor.val(wiki_meta_data);
        
        var org_tools = _obj_panel.panel("options").tools;

        // change icon
        _obj_panel.panel({
            "tools": [{
                "iconCls": "icon-save",
                "handler": function() {
                    res_save_wiki_meta_data(_obj_panel, org_tools, editor, _path);
                }
            },{
                "iconCls": "icon-undo",
                "handler": function() {
                    undo_wiki_meta_data(_obj_panel, org_tools, org_html);
                }
            }]
        })
	});
}
//------------------------------------------------------------------------------
function res_save_wiki_meta_data(_obj_panel, _org_tools, _editor, _path) {
    if( _org_tools ) {
        _obj_panel.panel({ "tools": _org_tools });   
    }
    
	var post_data = {
    	"request": "res_save_wiki_meta_data",
    	"contents": _editor.val(),
        "path": _path	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        var wiki_meta_data = ret.wiki_meta_data;
        
        if( wiki_meta_data == "" ) return;

        res_display_wiki_meta_data(_obj_panel, wiki_meta_data, _path);      
    });    
}
//------------------------------------------------------------------------------
function undo_wiki_meta_data(_obj_panel, _org_tools, _org_html) {
    if( _org_tools ) {
        _obj_panel.panel({ "tools": _org_tools });   
    }
    
    _obj_panel.html(_org_html);
}
//------------------------------------------------------------------------------
function res_display_tree(_obj_result, _data) {
    var obj_panel = $("<div></div>").appendTo(_obj_result);
    
    obj_panel.panel({
        "fit": true, 
        "title": "directory tree",
        "noheader": false,
        "tools": [{
            "iconCls": "icon-add",
            "handler": function() {
            }
        }]
    });

    var tree_data = json_to_treedata(_data, "", {
        "state": "open",
        "iconCls": "tree-folder", 
        "leafCls": "tree-folder"
    }, full_path = []);
    
    var tree = $("<div></div>").appendTo(obj_panel);
    
    tree.tree({ 
        "id": uniqid(), 
        "data": tree_data, 
        "dnd": true, 
        "fit": true,
        "animate": false
    });
}
//------------------------------------------------------------------------------
function res_delet_wiki_meta_data(_obj_result, _path, _func_callback) {
    $.messager.confirm("Delete Confirm", "Meta file will be deleted. <br />Are you sure?", function(r){
		if( !r ) return;
        
    	var post_data = {
        	"request": "res_delet_wiki_meta_data",
            "path": _path	
    	};
    
    	$.post(g_variable["_POST_URL_"], post_data, function(data) {
            var ret = JSON.parse(data);
            
            if( ret.msg != "ok" ) {
                $.messager.alert("Error", ret.msg, "error");
                console.log(ret.error_msg);
                return;   
            }
            
            _obj_result.empty();
            
            if( typeof(_func_callback) == "function" ) _func_callback();
        });
    });
}
//------------------------------------------------------------------------------
function res_read_wiki_meta_data(_obj_result, _path) {
	var post_data = {
    	"request": "res_read_wiki_meta_data",
        "path": _path	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        var wiki_meta_data = ret.wiki_meta_data;
        
        var title = _path;
        var tools = [];
        
        if( wiki_meta_data == "" ) {
        } else {
            title = "meta information";
            tools.push({
                "iconCls": "icon-edit",
                "handler": function() {
                    res_edit_wiki_meta_data(obj_panel, _path);
                }
            },{
                "iconCls": "icon-remove",
                "handler": function() {
                    res_delet_wiki_meta_data(obj_panel, _path);
                }
            });
        }
        
        // build wiki panel
        _obj_result.css("overflow", "hidden");
        
        var obj_panel = $("<div></div>").appendTo(_obj_result);
        
        // show panel header
        obj_panel.panel({
            "fit": true, 
            "title": title,
            "noheader": false,
            "tools": tools
        });

        obj_panel.addClass("wiki_meta_data");
        
        res_display_wiki_meta_data(obj_panel, wiki_meta_data, _path);
	});
}
//------------------------------------------------------------------------------
function res_display_wiki_meta_data(_obj_panel, _wiki_meta_data, _path) {
    var p = new GoogleCodeWikiParser();
    var html_meta = p.parse( _wiki_meta_data );

    _obj_panel.html(html_meta);

    _obj_panel.css("overflow", "auto");        
}
//------------------------------------------------------------------------------
function json_to_treedata(_obj, _category, _attr, _dirname) {
    var ret = [];

    if( typeof(_obj) != "object" ) return ret;
    
    for( var title in _obj ) {
        _dirname.push(title);        
        var child = json_to_treedata(_obj[title], _category, _attr, _dirname);        
        _dirname.pop();

        // iconCls        
        var iconCls = _attr.leafCls;
                
        // change iconCls as keyword
        if( _attr.alias_icon && _attr.alias_icon[title] ) iconCls = _attr.alias_icon[title];
        
        var state = "closed";
        var item = {
            "id": uniqid(), 
            "state": state, 
            "text": title, 
            "iconCls": iconCls, 
            "attributes": {
                "name": title,
                "dirname": _dirname.join("/"),
                "category": _category
            } 
        };
        
        if( child.length > 0 ) {
            state = "closed";
            if( _attr.state ) state = _attr.state;
        
            if( _attr.iconCls ) iconCls = _attr.iconCls;

            // change iconCls as keyword
            if( _attr.alias_icon && _attr.alias_icon[title] ) iconCls = _attr.alias_icon[title];

            item = {
                "id": uniqid(), 
                "state": state, 
                "text": title, 
                "iconCls": iconCls, 
                "attributes": {
                    "name": title,
                    "dirname": _dirname.join("/"),
                    "category": _category
                }, 
                "children": child
            };
        }       
        
        ret.push(item);
    }
    
    return ret;
}
//------------------------------------------------------------------------------
function res_move_directory(_source_path, _target_path, _func_callback) {
    var post_data = {
    	"request": "res_move_directory",
        "source_path": _source_path,
        "target_path": _target_path	
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);   
        
        if( ret.msg != "ok" ) {
            $.messager.alert("Error", ret.msg, "error");
            console.log(ret.error_msg);
            
            res_category_refresh();
            return;   
        }
        
        if( typeof(_func_callback) == "function" ) _func_callback();  
    });
}
//------------------------------------------------------------------------------
function res_tree_build_category(_category, _result, _main_panel) {    
    var post_data = {
    	"request": "res_tree_build_category"	
	};

    // clean
    _result.empty();

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);   

        var tree_data = json_to_treedata(ret, "", {
            "iconCls": "tree-folder", 
            "leafCls": "tree-folder", 
            "alias_icon": {
                "workspace": "icon-res_manager_database", 
                "corpus": "icon-res_manager_database", 
                "data": "icon-res_manager_database", 
                "upload": "icon-res_manager_upload_data"
            }
        }, full_path = []);
        
        _category.tree({ 
            "id": uniqid(), 
            "data": tree_data, 
            "dnd": true, 
            "animate": false,
            "onContextMenu": function(e, node){
                e.preventDefault();
                _category.tree("select", node.target);
                
                $("#res_category_context_menu").menu("show", {
                    "left": e.pageX,
                    "top": e.pageY
                });
            },             
            "onDrop": function(target, source, point) {
                var source_path = res_get_path(source.attributes);

                var target_node = _category.tree("getNode", target);
                var target_path = res_get_path(target_node.attributes);
                
                if( target_path == "" ) return;
                
                res_move_directory(source_path, target_path + "/" + source.attributes.name, res_category_refresh);
            },
            "onClick": function(node) {
                // get full path                
                var path = res_get_path(node.attributes);
                
                // clear
                _result.empty();
                
                // check data & upload
                if( node.text == "data" || node.text == "corpus" || node.text == "workspace" ) {
                    res_build_layout_data(_result, node);
                    res_tree_build_data(path);
                } else if( get_root_node(_category, node) == "upload" ) {
                    // check root is upload
                    res_read_file_list(_result, path);
                } else {
                    res_read_wiki_meta_data(_result, path);
                }
            
                // toggle state
                var child = $(this).tree("getChildren", node.target);
            
                if( child.length > 0 ) {
                    // expend all children
                    $(this).tree("expandAll", node.target);
                     
//                    $(this).tree("toggle", node.target);   
                }                
            }
        });
	});    
}
//------------------------------------------------------------------------------
function get_root_node(_tree, _node) {
    if( _tree.hasClass("tree") == false ) {
        return "";        
    }
    
    var parent = null;    

    var ret = "";
    if( _node ) {
        parent = _tree.tree("getParent", _node.target);
        ret = _node.text;
    }
    
    while( parent ) {
        ret = parent.text;
        parent = _tree.tree("getParent", parent.target);
    }
    
    return ret;
}
//------------------------------------------------------------------------------
function res_build_layout_data(_obj_result, _node) {
    _obj_result.empty();
    _obj_result.css("overflow", "hidden");    
    _obj_result.css("padding", "1px"); 
    
    _obj_result.removeClass("wiki_meta_data");
    
    // hide panel header
    _obj_result.panel({"noheader": true});
    
    // build data layout
    var html = "";
    
    html += '<div style="" fit="true">';  
    html += '   <div region="west" split="true" noheader="true" style="width:200px;">';  
    html += '       <div id="res_data_tree"></div>';  
    html += '   </div>';
    html += '   <div region="center" noheader="true" style="padding: 1px;">';
    html += '       <div id="res_data_file_list"></div>';  
    html += '   </div>';  
    html += '</div>';

    $(html).appendTo(_obj_result).layout();
    
    // build data tree panel
    $("#res_data_tree").panel({
        "fit": true,
        "title": _node.text,
        "tools": [{
            "iconCls": "icon-reload",
            "handler": function(){
                res_data_tree_refresh();
            }
        }]
    });
    
    // build file list panel
    $("#res_data_file_list").panel({
        "fit": true,
        "title": "file list"
    });    
}
//------------------------------------------------------------------------------
function res_tree_build_data(_path) {
	var post_data = {
    	"request": "res_tree_build_data",
        "path": _path	
	};
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        res_hide_category();
        
        var ret = JSON.parse(data);        
        
        var child = json_to_treedata(ret, _path, {
            "state": "open",
            "iconCls": "tree-folder", 
            "leafCls": "tree-folder"
        }, full_path = []);
        
        var tree_tittle = _path.split("/").reverse().shift();
        
        var tree_data = [];
        
        tree_data.push({
            "id": uniqid(), 
            "state": "closed", 
            // "text": "data", 
            "text": tree_tittle, 
            "iconCls": "icon-res_manager_database", 
            "attributes": {
                "name": "",
                "dirname": "",
                "category": _path
            }, 
            "children": child
        });
        
        var obj_data_tree = $("#res_data_tree"); 
        
        obj_data_tree.tree({ 
            "id": uniqid(), 
            "data": tree_data, 
            "dnd": true, 
            "animate": false,
            "onlyLeafCheck": false,
            "category": _path,
            "onDrop": function(target, source, point) {
                var source_path = res_get_path(source.attributes);

                var target_node = obj_data_tree.tree("getNode", target);
                var target_path = res_get_path(target_node.attributes);
                
                if( target_path == "" ) return;
                
                res_move_directory(source_path, target_path + "/" + source.attributes.name, res_data_tree_refresh);
            },
            "onContextMenu": function(e, node){
                e.preventDefault();
                obj_data_tree.tree("select", node.target);
                
                $("#res_data_tree_context_menu").menu("show", {
                    "left": e.pageX,
                    "top": e.pageY
                });
            },             
            "onClick": function(node){
                var path = res_get_path(node.attributes);
                $("#res_data_file_list").panel("setTitle", path);
                res_read_file_list($("#res_data_file_list"), path);
            }       
        });
	});
}
//------------------------------------------------------------------------------
function commify(n) {
    var reg = /(^[+-]?\d+)(\d{3})/;   // 정규식
    n += '';                          // 숫자를 문자열로 변환
    
    while( reg.test(n) ) {
        n = n.replace(reg, '$1' + ',' + '$2');
    }
    
    return n;
}
//------------------------------------------------------------------------------
function res_delete_file(_dirname, _filename, _func_callback) {
    $.messager.confirm("Delete Confirm", "\""+_filename + "\" will be deleted. <br />Are you sure?", function(r){
		if( !r ) return;
        
    	var post_data = {
        	"request": "res_delete_data_file",
            "filename": _dirname + "/" + _filename	
    	};
    
    	$.post(g_variable["_POST_URL_"], post_data, function(data) {
            var ret = JSON.parse(data);
            
            if( ret.msg != "ok" ) {
                $.messager.alert("Error", ret.msg, "error");
                console.log(ret.error_msg);
                return;   
            }
            
            if( typeof(_func_callback) == "function" ) _func_callback();
        });
    });
}
//------------------------------------------------------------------------------
function res_delete_data_file(_name) {
    var file_list = $("#res_file_list_result");
    
    var index = file_list.datagrid("getRowIndex", _name);
    var row   = file_list.datagrid("getRows")[index];
    
    res_delete_file(row.dirname, row.name, function() {
        $("#res_file_list_result").datagrid("deleteRow", index);
    });
}
//------------------------------------------------------------------------------
function res_download_data_file(_name) {
    var file_list = $("#res_file_list_result");
    
    var index = file_list.datagrid("getRowIndex", _name);
    var row   = file_list.datagrid("getRows")[index];
    
    var filename = row.dirname;
    if( filename != "" ) filename += "/";
    filename += row.name;
    
    console.log(filename);

    var inputs = '';
    
    inputs += '<input type="hidden" name="request" value="res_download_file" />';
    inputs += '<input type="hidden" name="filename" value="'+ filename +'" />';

    jQuery('<form action="api/main.php" method="post">'+inputs+'</form>').appendTo('body').submit().remove();
}
//------------------------------------------------------------------------------
function action_res_file_formatter(value, row, index){
	var del  = '<img src="/imgs/fm/_editdelete.png" class="hand" title="delete" onclick="res_delete_data_file(\''+row.name+'\')" />';
	var down = '<img src="/imgs/fm/_down.png" class="hand" title="download" onclick="res_download_data_file(\''+row.name+'\')" />';
    
	return "<div style='text-align: center;'>"+del+down+"</div>";
}
//------------------------------------------------------------------------------
function res_get_path(_attribute, _dirname) {
    var path = _attribute.category;
    
    if( _attribute.dirname != "" ) {
        if( path != "" ) path += "/";
        path += _attribute.dirname;
    }

    if( typeof(_dirname) == "undefined" || _dirname != true ) {
        if( path != "" ) path += "/";
        path += _attribute.name;
    }
    
    return path;
}
//------------------------------------------------------------------------------
function res_read_file_list(_obj_result, _path) {
    var post_data = {
    	"request": "res_read_file_list",
        "path": _path
	};

    _obj_result.empty();
    _obj_result.html(g_variable["gif_loader"]);    
  
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        // build tree
        var ret = JSON.parse(data);        
        
        _obj_result.empty();
        
        var obj_datagrid = $('<table id="res_file_list_result"></table>').appendTo(_obj_result); 
        
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
        
        obj_datagrid.datagrid({	
            "lastIndex"     : -1,               
            "singleSelect"  : false,
            "idField"       : "name",
            "columns"       : [[
        		{"field": "checked",   "title": "",           "checkbox": true},
        		{"field": "name",      "title": "file name",  "width": 300},
        		{"field": "size",      "title": "size",       "width": 50},
        		{"field": "type",      "title": "type",       "width": 50},
        		{"field": "progress",  "title": "progress",   "width": 100},
        		{"field": "action",    "title": "action",     "width": 50, "formatter": action_res_file_formatter}
            ]],
            "fitColumns"    : false
    	});            
        
        // fill up grid data
        obj_datagrid.datagrid("loadData", {"rows": rows});
        
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
                        "name": "request", "value": "res_upload_file"
                    },{
                        "name": "upload_dir", "value": _path
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
                        res_read_file_list(_obj_result, _path);   
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
                    
                    obj_datagrid.datagrid("appendRow", row);
                    
                    // build progress bar
                    $("#"+progress_id).progressbar({"width": "100%"});
                });
                
                var jqXHR = data.submit();
            }
        });        
	});    
}
//------------------------------------------------------------------------------
