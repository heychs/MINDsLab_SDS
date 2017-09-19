//------------------------------------------------------------------------------
function onload() {
    var user_email = $.cookie("user_email");
    
    //console.log("user_email", user_email);

    if( user_email == null || user_email == "" ) {
        logoff();
    } else {
        login_ok();

        if( user_email == "ejpark" ) {
            $("#workbench_src").val("나는 학교에 가서 밥을 먹었다.");
        }        
    }
}
//------------------------------------------------------------------------------
function open_login_box(){
    // hide tab panel
    $("#main_panel").hide();
    
    var user_email = $.cookie("user_email");
    var user_pw    = $.cookie("user_pw");
    
    if( user_email == null ) {
        user_email = g_variable["DEFAULT_USER_NAME"];
        user_pw    = g_variable["DEFAULT_USER_PW"];
    } 
    
    // open window
    var buf = "";    
    buf += "<div id='login_box' style='padding: 5px; overflow: hidden; text-align: center;'>";
    buf += "	<div style='padding: 10px; background: #fff; border: 1px solid #ccc; text-align: center;'>";
    buf += "        <table>";
    buf += "            <tr><td>";
    buf += "                ID:";
    buf += "            </td><td>";
    buf += "                <input id='login_box_id' type='text' style='width: 150px;' value='"+user_email+"' />";
    buf += "            </td></tr>";
    buf += "            <tr><td>";
    buf += "                PW:";
    buf += "            </td><td>";
    buf += "                <input id='login_box_pw' type='password' style='width: 150px;' value='"+user_pw+"' onkeydown='login(window.event)' />";
    buf += "            </td></tr>";
    buf += "        </table>";
    buf += "	</div>";
    buf += "	<div style='text-align: center; padding: 5px 0;'>";
    buf += "		<a id='login_box_login_button' class='easyui-linkbutton' iconCls='icon-ok' href='javascript:void(0)' onclick='login(null)'>Login</a>";
    buf += "	</div>";
    buf += "</div>";

    var login_box = $(buf).appendTo("body");
    login_box.window({  
        "width": 240,  
        "height": dialog_act_tagger_get_window_heigth(125), 
        "modal": true,
        "inline": false,
        "noheader": true,
        "resizable": false,
        "onMove": function(left, top) {
			dialog_act_tagger_on_window_move(this, left, top);
        },
        "onClose": function(forceClose){
            $(this).remove();
        }       
    });
    
    $("#login_box_login_button").linkbutton({});    
}
//------------------------------------------------------------------------------
function logoff(){	
	$.cookie("user_name" , null);
	$.cookie("user_email", null);
	$.cookie("user_pw", null);
	$.cookie("level", null);
	$.cookie("env", null);
    $.cookie("project_name", null);
    
    $("#main_panel").panel("setTitle", "Workbench");
    
    open_login_box();
}
//------------------------------------------------------------------------------
function login_ok(){
    $("#login_box").window("close");
    
	$("#main_panel").slideDown("slow");
    
    // set engine info toolbar
    $("#main_panel").panel({
        "tools": [{
            "iconCls": "icon-logout",  
            "handler":function(){
                logoff();
            }  
        }]  
    }); 

	init_tab();
}
//------------------------------------------------------------------------------
function login(_event){
    if( _event && _event.keyCode != 13 ) return;
    
	var post_data = {
    	"request": "login",
    	"user_pw": $("#login_box_pw").val(),
    	"user_email": $("#login_box_id").val()
	};
	
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
       var ret = JSON.parse(data);
       
		if( ret.user_email != post_data.user_email ) {
			alert("login failed.");
            logoff();
            return;
		}
        
    	$.cookie("user_name" , ret.user_name);
    	$.cookie("user_email", ret.user_email);
    	$.cookie("user_pw", ret.user_pw);
    	$.cookie("level", ret.level);
    	$.cookie("env", ret.env);

        location.reload();

//        login_ok();
	});
}
//------------------------------------------------------------------------------
function init_tab(){
	if( $.cookie("env") != null ) {
    	g_variable["env"] = JSON.parse($.cookie("env"));

        var tb = $("#panel_tab").tabs("tabs");
        
        for( var i=tb.length-1 ; i>=0 ; --i ) {
            var title = tb[i].panel("options").title;

            console.log("title: ", title);

			if( title != "Dialog Act" && title != "Dialog Act Guide" ) {
	            if( g_variable["env"].tab[title] != 1 ) {
	                $("#panel_tab").tabs("close", title);
	                continue;   
	            }
	        }
            
			if( title == "Log" ) init_log_page();
			if( title == "Trac" ) init_trac_tab(g_variable["env"].tab_trac);
			if( title == "Bitext" ) init_bitext_page();
			if( title == "Corpus" ) init_corpus_page();	
			if( title == "Diff Dict" ) init_diff_dictionary_page();
			if( title == "Workbench" ) init_workbench();            
			if( title == "Dictionary" ) init_dictionary_page();	
			if( title == "Deamon Log" ) init_deamon_log();
			if( title == "Res Manager" ) init_res_manager();            

			if( title == "Dialog Act" ) {
				display_select_prj_win();
			}
        }
	}
}
//------------------------------------------------------------------------------
function init_trac_tab(tab_info) {
    for( var i in tab_info ) { 
		var title = decodeURIComponent(i);
		var url = decodeURIComponent(tab_info[i]);

    	var content = "<iframe class='trac_content' src='"+url+"'></iframe>";
    
    	$("#tab_trac").tabs("add",{
    		"title": title,
    		"content": content,
    		"iconCls": "icon-blank",
    		"closable": true
    	});
        
        var t = $("#tab_trac").tabs("getSelected");
        t.css("overflow", "hidden");
    }
}
//------------------------------------------------------------------------------
