//------------------------------------------------------------------------------
// Engine info를 Workbench 텝 안으로 이동
//------------------------------------------------------------------------------
function init_workbench() {
    g_variable["workbench_toolbar_tooltip_list"] = {
        "icon-workbench_change_engine_info": "Change Engine"
    };    
    
    // set engine info toolbar
    $("#main_panel").panel({
        "tools": [{
            "iconCls": "icon-workbench_change_engine_info",  
            "handler":function(){
                open_window_change_engine_info();
            }  
        }, {
            "iconCls": "icon-logout",  
            "handler":function(){
                logoff();
            }  
        }]  
    }); 

    // set workbench src panel toolbar
    $("#workbench_panel_src").panel({
        "tools": [{
            "iconCls": "icon-workbench_translate",  
            "handler":function(){
                $.post(g_variable["_POST_URL_"], {
                    "src": $("#workbench_src").val(),
                    "ref": $("#workbench_ref").val(),
                    "lang_type": g_variable["env"]["engine_info"]["lang_type"],
                    "engine": g_variable["env"]["engine_info"]["engine"],
                    "dictionary": g_variable["env"]["engine_info"]["dictionary"],
                    "request": "translate_sentence"
                }, function(data) {
                    display_translation_result(data);
                });
            }  
        }]  
    });

    // set workbench ref panel toolbar
    $("#workbench_panel_ref").panel({
        "tools": [{
            "iconCls": "icon-workbench_save_ref",  
            "handler":function(){
                workbench_save_ref();
            }  
        }]  
    });
 
    update_engine_info_title();
    
    set_workbench_toolbar_tooltip();   
}
//------------------------------------------------------------------------------
function update_engine_info_title() {
    var engine_info = g_variable["env"]["engine_info"];    
    var title = "";
    
    title  = "Lang: [" + get_engine_info("lang_type") + "], ";
    title += "Engine: [" + engine_info["engine"] + "], ";
    title += "Dictionary: [" + engine_info["dictionary"] + "]";
    
    $("#main_panel").panel("setTitle", title);
}
//------------------------------------------------------------------------------
function open_window_change_engine_info() {
    // make post data
	var post_data = {
    	"lang_type": g_variable["env"]["engine_info"]["lang_type"],
    	"request": "get_engine_info"
	};

	// get engine info.
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
    
        var buf = "";
        
    	buf += "<div>";
    	buf += "<table style='width: 100%;' border=1>";
    	buf += "	<tr><td>";
    	buf += "		Lang:";
    	buf += "	</td><td style='width: 200px;'>";
    	buf += "		<div id='lang_type' style='width: 200px;'></div>";
    	buf += "	</td></tr>";
    	buf += "	<td>";
    	buf += "		Engine:";
    	buf += "	</td><td>";
    	buf += "		<div id='engine' style='width: 200px;'></div>";
    	buf += "	</td></tr>";
    	buf += "	<td>";
    	buf += "		Dictionary:";
    	buf += "	</td><td>";
    	buf += "		<div id='dictionary' style='width: 200px;'></div>";
    	buf += "	</td></tr>";
    	buf += "</table>";
        buf += "    <br/>";
        buf += "    <center>";
        buf += "        <a href='#' id='btn_change_engine_win_ok' iconCls='icon-ok'>OK</a>";
        buf += "        <a href='#' id='btn_change_engine_win_cancel' iconCls='icon-cancel'>Cancel</a>";
        buf += "    </center>";
    	buf += "</div>";
        
        var win_result = $(buf).appendTo("body");
    
        // open window
        win_result.window({  
            "iconCls"   : "icon-ok", 
            "title"     : "Change Engine",  
            "width"     : 300,  
        	"height"    : dialog_act_tagger_get_window_heigth(180), 
            "modal"     : true,
            "inline"    : false,
            "collapsible" : false,
            "maximizable" : false,
            "minimizable" : false,
            "attribute" : [],
	        "onMove": function(left, top) {
				dialog_act_tagger_on_window_move(this, left, top);
	        },
            "onClose"   : function(forceClose){
                $(this).remove();
            }       
        });

        // make lang combobox
        var obj_lang = $("#lang_type");
        obj_lang.combobox({
            "valueField": "value", 
            "textField": "text", 
            "required": true, 
            "editable": false,
            "onSelect": function(record) {
                change_lang_type(record.value);
            } 
        });
        
        obj_lang.combobox("loadData", ret.lang);
        
        update_lang_type_combo_data(ret);
        
        // build link button
        $("#btn_change_engine_win_ok").linkbutton({});
        
        $("#btn_change_engine_win_ok").bind("click", function(){
            set_engine_info("lang_type", $("#lang_type").combobox("getValue"));
            set_engine_info("engine",    $("#engine").combobox("getValue"));
            set_engine_info("dictionary", $("#dictionary").combobox("getValue"));
            
            update_engine_info_title();
            
            win_result.window("close");
        });

        $("#btn_change_engine_win_cancel").linkbutton({});
        $("#btn_change_engine_win_cancel").bind("click", function(){
            win_result.window("close");
        });
	});
}
//------------------------------------------------------------------------------
function change_lang_type(lang_type) {
    // make post data
	var post_data = {
    	"lang_type": lang_type,
    	"request": "get_engine_info"
	};

	// get engine info.
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var ret = JSON.parse(data);
        update_lang_type_combo_data(ret);
	});
}
//------------------------------------------------------------------------------
function update_lang_type_combo_data(data) {
    // make engine combobox
    var obj_engine = $("#engine");
    obj_engine.combobox({"valueField": "value", "textField": "text", "required": true, "editable": false});
    obj_engine.combobox("loadData", data.engine);

    // make dictionary combobox
    var obj_dictionary = $("#dictionary");
    obj_dictionary.combobox({"valueField": "value", "textField": "text", "required": true, "editable": false});
    obj_dictionary.combobox("loadData", data.dictionary);
}
//------------------------------------------------------------------------------
function set_workbench_toolbar_tooltip() {
    // set toolbar's tooltip 
    var tooltip_title = g_variable["workbench_toolbar_tooltip_list"];  
    
    var all = document.getElementsByTagName("A");
    for(var i=0 ; i<all.length ; ++i){
        var tooltip = tooltip_title[$(all[i]).attr("class")];
        if( typeof(tooltip) == "undefined" ) continue;
        
        $(all[i]).attr("title", tooltip); 
    }    
}
//------------------------------------------------------------------------------
function display_translation_result(data) {
    var xml = JSON.parse(data);
    
    console.log(xml);

    // make mid result html
    var obj_result = $("#workbench_result");
    
	obj_result.empty();	
    obj_result.css("overflow", "scroll");
    obj_result.css("padding-right", "3px");

    // init portal
    var obj_portal = obj_result.portal({"fit": true, "border": false});        
    
    var cnt = 0;
    for( var tag in xml ) {
        if( tag.substr(0, 1) == "_" ) continue;
        
        var value = xml[tag];
        if( value == "" ) continue; 
                    
        if( tag == "MA" ) value = ma_to_html(value);
        
        var p = $("<div style='padding: 5px;'></div>").appendTo(obj_portal);
        p.panel({
            "title": tag,
            "inline": true,
            "iconCls": "icon-ok",
            "closable": true, 
            "collapsible": true, 
            "collapsed": (( cnt++ > 1 ) ? true : false), 
            "content": value
        });

        obj_portal.portal("add", {"panel": p, "columnIndex": cnt});
    }
    
    obj_portal.portal("resize");         
}
//------------------------------------------------------------------------------
function workbench_save_ref() {
    console.log("workbench_save_ref");
}
//------------------------------------------------------------------------------
function search_ma_word(obj, result_id) {
    var w = $(obj).text();
    
    var uid = uniqid();
    
	var post_data = {
    	"request" 	: "search_word",
    	"src" 		: w,
    	"lang_type" : g_variable["env"]["engine_info"]["lang_type"],
    	"engine" 	: g_variable["env"]["engine_info"]["engine"],
    	"dic" 		: g_variable["env"]["engine_info"]["dic"]
	};

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
        var tree_data = xml2json(data);
        
        var uid = uniqid();
        
        var buf = "";
        
        var win_id = "win_lexicon_tree_"+uid;
        var content_id = "win_content_"+uid;
        
        buf += "<div id='"+win_id+"' style='overflow: auto; padding: 10px;'>";
        buf += "    <div id='"+content_id+"'></div>";
        buf += "</div>";
        
        $("#"+result_id).empty();	
        $("#"+result_id).html(buf);
        
        // build dictionary
        if( tree_data[0].text == "LEXICONS" ) {
            $("#"+content_id).tree({ 
                "id": uid, 
                "data": tree_data, 
                "dnd": true, 
                "animate": true, 
                "onClick": function(node){
                    $(this).tree("toggle", node.target);
                },
                "onDblClick": function(node){
                    $(this).tree("beginEdit", node.target);
                }       
            });
        } else {
            var portal = $("#"+content_id).portal({"fit": true, "border": false});            
            update_dictionay_panel(portal, $.xml2json(data));
        }
        
        // open window
        $("#"+win_id).window({  
            "iconCls": 'icon-ok',
            "title": w,  
            "width": 600,  
	        "height": dialog_act_tagger_get_window_heigth(400), 
            "modal": true,
            "tag": result_id,
            "inline": false,
            "onClose":function(forceClose){
                $(this).remove();
            }       
        });
	});
}
//------------------------------------------------------------------------------
function update_dictionay_panel(_portal, obj) {
    var ret = [];
    for( var i in obj ) {
        if( jQuery.type(obj[i]) == "array" ) {
            update_dictionay_panel(_portal, obj[i]);
            continue;
        }
        
        var key_id = uniqid();
        var val_id = uniqid();
        
        var content = "";
        
        content += "<table style='width: 100%;'>";
        content += "    <tr><td style='width: 200px;'>";
        content += "        <textarea id='"+key_id+"' class='key'>"+obj[i].key+"</textarea>";
        content += "    </td><td>";
        content += "        <textarea id='"+val_id+"' class='val'>"+obj[i].val+"</textarea>";
        content += "    </td></tr>";
        content += "</table>";
        
        var item = { 
            "title": obj[i].file_name, 
            "content": content, 
            "iconCls": "icon-ok", 
            "closable": true, 
            "collapsible": true, 
            "maximizable": true,
            "key_id": key_id, 
            "val_id": val_id, 
            "tools": [{
                "iconCls": "icon-save",
                "handler": function(){
                }
            }],
            "onMaximize": function(){
                $(this).children().css("height", "100%");
            },
            "onClose": function(){
            }
        };
        
        var p = $("<div></div>").appendTo(_portal);                    
        _portal.portal("add", {"panel": p.panel(item), "columnIndex": i});
    }
    
    return ret;    
}
//------------------------------------------------------------------------------
function ma_to_html(content) {
	var buf = "";

	var list = content.split(/\s+/);
	for( var i in list ) {
		var w = list[i];
        var uid = uniqid();
        
        var onclick = "onclick=\"search_ma_word(this, '"+uid+"');\"";
        
		buf += "<span class='hand' "+onclick+">"+w+"</span> ";
		buf += "<span class='hide' id='"+uid+"'></span> ";
	}

	return buf;
}
//------------------------------------------------------------------------------
