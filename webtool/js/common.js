//------------------------------------------------------------------------------
// common functions
//------------------------------------------------------------------------------
function commify(n) {
	var reg = /(^[+-]?\d+)(\d{3})/;   // 정규식
	n += '';                          // 숫자를 문자열로 변환

	while( reg.test(n) ) {
		n = n.replace(reg, '$1' + ',' + '$2');
	}

	return n;
}

function uniqid_date() {
    var date = new Date();
    var year  = date.getFullYear();
    var month = date.getMonth() + 1; // 1월=0,12월=11이므로 1 더함
    var day   = date.getDate();
    var hour  = date.getHours();
    var min   = date.getMinutes();
    var sec   = date.getSeconds();

    if (("" + month).length == 1) { month = "0" + month; }
    if (("" + day).length   == 1) { day   = "0" + day;   }
    if (("" + hour).length  == 1) { hour  = "0" + hour;  }
    if (("" + min).length   == 1) { min   = "0" + min;   }
    if (("" + sec).length   == 1) { sec   = "0" + min;   }

    return (year + "-" + month + "-" + day + "_" + hour + "-" + min + "-" + sec)
}
//------------------------------------------------------------------------------
function uniqid(prefix, more_entropy) {
    if( prefix == undefined ) prefix = "";
    if( typeof prefix == undefined ) prefix = "";

    var retId;
    var formatSeed = function (seed, reqWidth) {
        seed = parseInt(seed,10).toString(16); // to hex str
        if (reqWidth < seed.length) { // so long we split
            return seed.slice(seed.length - reqWidth);
        }
        if (reqWidth > seed.length) { // so short we pad
            return Array(1 + (reqWidth - seed.length)).join('0')+seed;
        }
        return seed;
    };

    // BEGIN REDUNDANT
    if (!this.php_js) {
        this.php_js = {};
    }
    // END REDUNDANT
    if (!this.php_js.uniqidSeed) { // init seed with big random int
        this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
    }
    this.php_js.uniqidSeed++;

    retId  = prefix; // start with prefix, add current milliseconds hex string
    retId += formatSeed(parseInt(new Date().getTime()/1000,10),8);
    retId += formatSeed(this.php_js.uniqidSeed,5); // add seed hex string

    if (more_entropy) {
        // for more entropy we add a float lower to 10
        retId += (Math.random()*10).toFixed(8).toString();
    }

    return retId;
}
//------------------------------------------------------------------------------
function get_engine_info(type) {
    return g_variable["env"]["engine_info"][type];
}
//------------------------------------------------------------------------------
function set_engine_info(type, value) {
    g_variable["env"]["engine_info"][type] = value;
    return g_variable["env"]["engine_info"][type];
}
//------------------------------------------------------------------------------
function get_combobox_value(_obj) {
    var ret = ( _obj.size() > 0 ) ? _obj.combobox("getValue") : "";
//    if( _obj.size() > 0 ) console.log(ret);
    return ret;
}
//------------------------------------------------------------------------------
function delete_pid(_ip, _pid, _icon_id) {
	if( _icon_id ) $("#"+_icon_id).html("<img src=/imgs/loader-small.gif />");
    
    var post_data = {
		"request": "delete_pid",
		"ip": _ip,
		"pid": _pid
    };

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		if( _icon_id ) $("#"+_icon_id).html(data);
	});
}
//------------------------------------------------------------------------------
function trim(str) {
	if( typeof(str) != "string" ) return str;
	
    return str.replace(/^\s\s*/, "").replace(/\s\s*$/, "");
}
//------------------------------------------------------------------------------
function init_corpus_page(obj_self) 
{
	var org = set_loading_img(obj_self);
	
    var post_data = {
		"request": "get_corpus_list"
    };

	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		restore_loading_img(obj_self, org);
		
		var obj = JSON.parse(data);

		if( obj.ck ) g_variable["corpus_list_ck"] = obj.ck.split(",");			
		if( obj.ek ) g_variable["corpus_list_ek"] = obj.ek.split(",");			
		if( obj.ke ) g_variable["corpus_list_ke"] = obj.ke.split(",");			
		if( obj.kc ) g_variable["corpus_list_kc"] = obj.kc.split(",");		
		
		var obj_select = document.getElementById("corpus_file_name");
		clear_option(obj_select);
		
		append_option(obj_select, "Select", "");

		append_option(obj_select, "--------------------", "");
		append_option_as_list(obj_select, g_variable["corpus_list_ck"]);

		append_option(obj_select, "--------------------", "");
		append_option_as_list(obj_select, g_variable["corpus_list_ek"]);

		append_option(obj_select, "--------------------", "");
		append_option_as_list(obj_select, g_variable["corpus_list_ke"]);

		append_option(obj_select, "--------------------", "");
		append_option_as_list(obj_select, g_variable["corpus_list_kc"]);
	});
}
//------------------------------------------------------------------------------
function delete_option(_obj) 
{
	if( _obj == null || _obj.nodeName != "SELECT" ) return;
	
	_obj.remove(_obj.options.selectedIndex);
}
//------------------------------------------------------------------------------
function clear_option(_obj) 
{
	if( _obj == null || _obj.nodeName != "SELECT" ) return;
	
	for( var i=_obj.length ; i>=0 ; i-- ) {
		_obj.remove(i);	
	}
}
//------------------------------------------------------------------------------
function append_option(_obj, _text, _val) {
	var new_option = document.createElement('option');

	new_option.text = _text;
	new_option.value = _val;
	
	_obj.add(new_option);
}
//------------------------------------------------------------------------------
function append_option_as_list(_obj, _list) {
	if( g_variable["corpus_list_ck"] == null ) return;
	
	for( var i=0 ; i<_list.length ; i++ ) {
		append_option(_obj, _list[i], _list[i]);
	}
}
//------------------------------------------------------------------------------
function get_selection() 
{
//	var obj_selection = document.selection.createRange();	
//	if( obj_selection != null ) return obj_selection.text;
	
	var selection = GetSelection();
//    console.log("selection:["+selection+"]");
    
	if( selection != "" ) return selection;
		
	if( g_variable["selected_search_word"] != null ) {
		return get_text(g_variable["selected_search_word"]);
	}
	
	if( g_variable["selected_search_corpus"] != null ) {
		return get_text(g_variable["selected_search_corpus"]);
	}

	return "";
}
//------------------------------------------------------------------------------
function GetSelection() 
{
    var text = "";
    if( window.getSelection ){
        text = window.getSelection();
    }else if( document.getSelection ){
        text = document.getSelection();
    }else if( document.selection ){
        text = document.selection.createRange().text;
    }
    text=text.toString();
    
    return text;
}
//------------------------------------------------------------------------------
function keyword_keydown(btn_id) 
{	
	if( event.keyCode == 13 ) {		
		document.getElementById(btn_id).click();
	}
}
//------------------------------------------------------------------------------
function set_select_obj(obj_self, tag) 
{
	if( obj_self.nodeName != "INPUT" ) {
		// reset color
		if( g_variable["selected_"+tag] != null ) {
			g_variable["selected_"+tag].style.color = "";
			g_variable["selected_"+tag].style.fontWeight = "";
		}
		
		// set color
		obj_self.style.color = "red";
		obj_self.style.fontWeight = "bold";
	}
	
	g_variable["selected_"+tag] = obj_self;
}
//------------------------------------------------------------------------------
function set_text(obj, str, append_mode, display) 
{
	if( obj == null || obj == undefined ) return;
	
	if( display == null || display == undefined ) obj.style.display = "";
	
	var org_html = "";
	
	if( obj.nodeName == "INPUT" || obj.nodeName == "TEXTAREA" ) {
		org_html = obj.value;
		
		if( append_mode == undefined ) obj.value = "";
		obj.value += str;
		
		return org_html;
	}
	
	if( obj.nodeName == "SELECT" ) {
		org_html = obj.value;

		if( append_mode == null || append_mode == undefined ) clear_option(obj);
		
		var uniq = Array();
	
		var list = str.split("\n");
		for( var i=0 ; i<list.length ; i++ ) {
			if( list[i] == "" ) continue;
			
			if( uniq[list[i]] == 1 ) continue;
			uniq[list[i]] = 1;
			
			append_option(obj, list[i], list[i]);
		}

		return org_html;
	}

	org_html = obj.innerHTML;
	obj.innerHTML = str;	
	
	return org_html;
}
//------------------------------------------------------------------------------
function set_text_by_id(uid, str, append_mode, display) 
{
	return set_text(document.getElementById(uid), str, append_mode, display);
}
//------------------------------------------------------------------------------
function get_text(obj) 
{
	if( obj == null || obj == undefined ) return "";

	if( obj.nodeName == "INPUT" && obj.type == "checkbox" ) {
		if( obj.checked ) return obj.value;
		
		return "";
	}

	if( obj.nodeName == "INPUT" || obj.nodeName == "TEXTAREA" || obj.nodeName == "SELECT" ) {
		return obj.value;
	}
	
	return obj.innerHTML;	
}
//------------------------------------------------------------------------------
function get_text_by_id(uid)
{
	return get_text(document.getElementById(uid));
}
//------------------------------------------------------------------------------
function reg_replace(_str, _org_pattern, _target) {
  var re = new RegExp(_org_pattern, "g");
  
  return _str.replace(re, _target);
}
//------------------------------------------------------------------------------
function dump(arr,level) 
{
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == "object") { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == "object") { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}

	return dumped_text;
}
//------------------------------------------------------------------------------
function restore_loading_img(obj, org_html) 
{
	if( obj == null ) return;
	
	if( obj.nodeName == "IMG" ) {
		obj.src = org_html;
	} else if( obj.nodeName == "INPUT" ) {
		return;
	} else {
		obj.innerHTML = org_html;		
	}
}
//------------------------------------------------------------------------------
function set_loading_img(obj) 
{
	if( obj == null ) return;
	
	obj.style.display = "";
	
	var org_html = "";
	if( obj.nodeName == "IMG" ) {
		org_html = obj.src;
		
		obj.src = g_variable["img_loader"];
	} else if( obj.nodeName == "INPUT" ) {
		return;
	} else {
		org_html = obj.innerHTML;

		obj.innerHTML = g_variable["gif_loader"];		
	}
	
	return org_html;
}
//------------------------------------------------------------------------------
function show(obj_id) 
{
	if( document.getElementById(obj_id) == undefined ) return;
	
	document.getElementById(obj_id).style.display = "";
}
//------------------------------------------------------------------------------
function hide(obj_id) 
{
	if( document.getElementById(obj_id) == undefined ) return;
	
	document.getElementById(obj_id).style.display = "none";
}
//------------------------------------------------------------------------------
function change_my_passwd()
{
    var post_data = {
		"user_email": $.cookie("user_email"),
		"user_pw": get_text_by_id("ui.passwd.new.user_wd"),
		"request": "change_my_passwd"
    };
    
	$.post(g_variable["_POST_URL_"], post_data, function(data) {
		set_text_by_id("pane_passwd_result", data);
	});
}
//------------------------------------------------------------------------------
function merge_json(o1, o2) {
    var tempNewObj = o1;

    //if o1 is an object - {}
    if (o1.length === undefined && typeof o1 !== "number") {
        $.each(o2, function(key, value) {
            if (o1[key] === undefined) {
                tempNewObj[key] = value;
            } else {
                tempNewObj[key] = merge_json(o1[key], o2[key]);
            }
        });
    }

    //else if o1 is an array - []
    else if (o1.length > 0 && typeof o1 !== "string") {
        $.each(o2, function(index) {
            if (JSON.stringify(o1).indexOf(JSON.stringify(o2[index])) === -1) {
                tempNewObj.push(o2[index]);
            }
        });
    }

    //handling other types like string or number
    else {
        //taking value from the second object o2
        //could be modified to keep o1 value with tempNewObj = o1;
        tempNewObj = o2;
    }
    return tempNewObj;
}
//------------------------------------------------------------------------------
function json2tree(_obj) {
    var ret = [];

    if( typeof(_obj) != "object" ) return ret;
    
    for( var title in _obj ) {
        var child = json2tree(_obj[title]);        

        var item = {};
        if( child.length > 0 ) {
            item = {
                "id": uniqid(), 
                "state": "open", 
                "text": title, 
                "iconCls": "", 
                "children": child
            };
        } else {
            var val = _obj[title];
            if( typeof(val) != "object" && val != "" ) {
                val = val.replace(/\</g, "&lt;");
                val = val.replace(/\>/g, "&gt;");
            }
            
            item = {
                "id": uniqid(), 
                "state": "open", 
                "text": title, 
                "iconCls": "",
                "children": [{
                    "id": uniqid(), 
                    "state": "open", 
                    "text": val, 
                    "iconCls": ""
                }]
            };
        }      
        
        ret.push(item);
    }
    
    return ret;
}
//------------------------------------------------------------------------------
// function tree2json(_tree, _child) {
//     var ret = {};
    
//     for( var i in _child ) {
//         var isLeaf = _tree.tree("isLeaf", _child[i].target);
        
//         if( !isLeaf ) continue;
        
//         // trace until root
//         var node = _child[i];
        
//         var history = [node];
//         while( null != (node = _tree.tree("getParent", node.target) ) ) {
//             history.push(node);
//         }
        
//         // reverse
//         history.reverse();

//         var str = "";
        
//         var v = history.pop().text; 
//         var k = history.pop().text; 
        
//         v = v.replace(/\"/g, "'");
//         k = k.replace(/\"/g, "'");

//         v = v.replace(/\&lt\;/g, "<");
//         v = v.replace(/\&gt\;/g, ">");
        
//         var buf = "{\"" + k + "\":\"" + v + "\"}";
//         while( "undefined" != typeof(str = history.pop()) ) {
//             k = str.text.replace(/\"/g, "'");
//             buf = "{\"" + k + "\": " + buf + "}";
//         }
        
//         // convert
//         var oo = {};
//         eval("oo = " + buf);
        
//         // merge result
//         ret = merge_json(ret, oo);
//     }
    
//     return ret;    
// }
//------------------------------------------------------------------------------
function str_replace_all(str,orgStr,repStr) 
{
	return str.split(orgStr).join(repStr);
} 	
//------------------------------------------------------------------------------
