//------------------------------------------------------------------------------
console.log("lib.js");

var post_time_out = 10000;
var act_url='/build_farm/bleu.act.php';

// info
var dic_info = Array();
var engine_info = Array();
//------------------------------------------------------------------------------
function include(filename)
{
	var head = document.getElementsByTagName('head')[0];
	
	script = document.createElement('script');
	script.src = filename;
	script.type = 'text/javascript';
	
	head.appendChild(script)
}
//------------------------------------------------------------------------------
function clear_option(_obj) 
{
	if( _obj == null ) return;
	
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
function get_lang_pair() 
{
	var str_src_lang = '', str_trg_lang = '', str_engine = '';
	
	if( dojo.byId('src_lang') != null ) str_src_lang = dojo.byId('src_lang').value;
	if( dojo.byId('trg_lang') != null ) str_trg_lang = dojo.byId('trg_lang').value;
			
	str_engine = str_src_lang+str_trg_lang;
	if( str_engine != '' ) {		
		if( str_engine == 'kren' ) str_engine = 'ke';
		if( str_engine == 'krcn' ) str_engine = 'kc';
		if( str_engine == 'enkr' ) str_engine = 'ek';
		if( str_engine == 'cnkr' ) str_engine = 'ck';	
	}
	
	return str_engine;
}
//------------------------------------------------------------------------------

function get_exc_name() 
{
	var str_domain = '';	
	if( dojo.byId('domain') != null ) str_domain = dojo.byId('domain').value;
			
	var str_engine = get_lang_pair();
	if( str_engine != '' && str_domain != '' ) {		
		// alias
		// ek : ek_mt
		// ke_msg : ke_tour
		// kc_msg : kc_drama
		// ck_msg : ck_e-mail
		// ck_dail : ke_tour
		
		if( str_engine == 'ek' ) {		
			str_engine = 'ek_mt';
		} else {
			str_engine += '_' + str_domain;
			
			if( str_engine == 'ke_dial' ) str_engine = 'ke_tour';
			if( str_engine == 'kc_dial' ) str_engine = 'kc_drama';
			
			if( str_engine == 'ke_msg' ) str_engine = 'ke_tour';
			if( str_engine == 'kc_msg' ) str_engine = 'kc_drama';
			if( str_engine == 'ck_msg' ) str_engine = 'ck_e-mail';
		}
	}
	
	return str_engine;
}
//------------------------------------------------------------------------------
function update_exc_list() 
{
	// restrict exc
	var str_engine = get_exc_name();
	
	// update exc list
	var obj_select = dojo.byId("exc_list");
	
	clear_option(obj_select);

//		alert(str_engine);
	
	for( var exc in engine_info ) {
		// filtering
		if( str_engine != '' && exc != str_engine ) continue;
		
		append_option(obj_select, exc, exc);
	}
	
	update_rev_list();
	update_dic_list();
}
//------------------------------------------------------------------------------

function update_rev_list() 
{
	var obj_select_exc 	= dojo.byId('exc_list');	
	var obj_select 		= dojo.byId("rev_list");
	
	clear_option(obj_select);
	
	// update dic list
	var uniq = Array();
	var list2 = Array();
		
	for( var i=0 ; i<obj_select_exc.length ; i++ ) {
		if( obj_select_exc.options[i].selected == false ) continue;
	
		var exc = obj_select_exc.options[i].value;
		
		var list = engine_info[exc].split(",");
		for( var j=0 ; j<list.length ; j++ ) {
			if( uniq[list[j]] != undefined ) continue;
			
			uniq[list[j]] = 1;
			list2.push(list[j]);
		}
	}

	list2.sort(compNumberReverse);		
	for( var j=0 ; j<list2.length ; j++ ) {
		append_option(obj_select, list2[j], list2[j]);
	}
}
//------------------------------------------------------------------------------

function compNumberReverse(a, b) {
  return b - a;
}
//------------------------------------------------------------------------------

function update_dic_list() 
{
	var obj_select_exc 	= dojo.byId('exc_list');
	var obj_select 		= dojo.byId("dic_list");
	
	clear_option(obj_select);

	// update dic list
	var uniq = Array();
		
	for( var i=0 ; i<obj_select_exc.length ; i++ ) {
		if( obj_select_exc.options[i].selected == false ) continue;
	
		var exc = obj_select_exc.options[i].value;
		
		var domain = "dicke";
		if( exc.indexOf("ck_") == 0 ) domain = "dicck";
		if( exc.indexOf("ek_") == 0 ) domain = "dicek";
		

		var list = dic_info[domain].split(",");
		for( var j=0 ; j<list.length ; j++ ) {
			if( uniq[list[j]] != undefined ) continue;
			
			uniq[list[j]] = j;
			
			append_option(obj_select, list[j], list[j]);
		}
	}
}
//------------------------------------------------------------------------------
function get_engine_info() 
{
	dojo.xhrPost( {
		url: act_url,
		handleAs: "text",
		content: {
			request: 'get_engine_info'
		},
		timeout: post_time_out, 
		load: function(response, ioArgs) {
			engine_info = Array();
			
			var list = response.split("\n");
			for( var i=0 ; i<list.length ; i++ ) {
				var token = list[i].split("\t");
				
				var exc = token[0];
				var rev = token[1];
				
				if( exc == "" || rev == undefined ) continue;

				if( exc.indexOf("c:") != -1 ) continue;
				
				if( engine_info[exc] ) engine_info[exc] += ",";
				else engine_info[exc] = "";
				
				engine_info[exc] += rev;
			}

			update_exc_list();

			return response;
		},
		error: function(response, ioArgs) {
			return response;
		}
	});
}
//------------------------------------------------------------------------------
function get_dic_info() 
{
	dojo.xhrPost( {
		url: act_url,
		handleAs: "text",
		content: {
			request: 'get_dic_info'
		},
		timeout: post_time_out, 
		load: function(response, ioArgs) {
			dic_info = Array();
			
			var list = response.split("\n");
			for( var i=0 ; i<list.length ; i++ ) {
				var token = list[i].split(".");
				
				var domain = token[0];
				
				if( dic_info[domain] ) dic_info[domain] += ",";
				else dic_info[domain] = "";
				
				dic_info[domain] += list[i];
			}

			update_dic_list();

			return response;
		},
		error: function(response, ioArgs) {
			return response;
		}
	});
}
//------------------------------------------------------------------------------
function post_to_url(url, params) { 
	var new_win = window;

    var form = new_win.document.createElement("form"); 

    //move the submit function to another variable 
    //so that it doesn't get over written 
    form._submit_function_ = form.submit; 

    form.setAttribute("method", "POST"); 
    form.setAttribute("action", url); 
    form.setAttribute("target", "_blank"); 

    for(var key in params) { 
        var hiddenField = new_win.document.createElement("input"); 
        hiddenField.setAttribute("type", "hidden"); 
        hiddenField.setAttribute("name", key); 
        hiddenField.setAttribute("value", params[key]); 

        form.appendChild(hiddenField); 
    } 

    new_win.document.body.appendChild(form); 
    
    form._submit_function_(); //call the renamed function 
    
    new_win.document.body.removeChild(form); 
} 
//------------------------------------------------------------------------------

function start_tail_file(_fn) {
	if( dojo.byId('tail_result') == null ) {
		var body = document.getElementsByTagName('body')[0];	

		var div = document.createElement('div');

		div.innerHTML = "<div id=tail_result name=tail_result style='display:none; overflow:auto; width:100%; height: 300px; border-width:1px;'>&nbsp;</div>";
		
		body.appendChild(div);
	}
	
	if( dojo.byId('tail_file_name') == null ) {
		var body = document.getElementsByTagName('body')[0];	

		var div = document.createElement('div');

		div.innerHTML = "<div id=tail_file_name style='width:100%; border-width:1px; display:none;'></div>";
		
		body.appendChild(div);
	}
	
	dojo.byId('tail_result').innerHTML = "";	
	dojo.byId('tail_file_name').innerHTML = _fn;
	
	if( _fn == "" ) {	
		dojo.byId('tail_result').style.display = 'none';
	} else {
		dojo.byId('tail_result').innerHTML = '&nbsp;<img src=loader-small.gif /> ' + _fn;
//		dojo.byId('tail_result').style.height = '80%';
		dojo.byId('tail_result').style.height = document.body.clientHeight - 300;	
		dojo.byId('tail_result').style.width  = document.body.clientWidth - 10;		 
		dojo.byId('tail_result').style.display = 'inline';
	}
	
	if( _fn != "" ) setTimeout("tail_file()", 2000);
}
//------------------------------------------------------------------------------

function tail_file() {
	var fn = dojo.byId('tail_file_name').innerHTML;
	
	if( fn == "" ) return;
	
	dojo.xhrPost( {
		url: act_url,
		handleAs: "text",
		content: { 
			request: 'tail_file',
			file_name: fn
		},
		timeout: post_time_out, 
		load: function(response, ioArgs) {
			var objDiv = dojo.byId('tail_result');
			
			objDiv.innerHTML = "<pre>" + response + "</pre><br>"
								+ "<input type=button value='Stop' onclick='start_tail_file(\"\")'> ";
			
			// scroll buttom			
			objDiv.scrollTop = objDiv.scrollHeight;			

			setTimeout("tail_file()", 2000);
			return response;
		},
		error: function(response, ioArgs) {
			dojo.byId('tail_result').innerHTML = "ERROR: " + ioArgs.xhr.status;
			return response;
		}
	});
}
//------------------------------------------------------------------------------

function getContents(docs) {
	var c = Array();
	
	for( var i=0 ; i<docs.all.length ; i++ ){
		var obj = docs.all.item(i);
		
		var nodeName = obj.nodeName.toUpperCase();
		
//		if( nodeName != "INPUT".toUpperCase() && nodeName != "SELECT".toUpperCase() ) continue;
		var id = obj.id;
		var v  = obj.value;
		
		if( obj.type == "checkbox" ) v = obj.checked;
		
		if( nodeName == "SELECT".toUpperCase() ) {
			var str = '';
			for( var j=0 ; j<obj.length ; j++ ){				
				if( obj.options[j].selected == true ) {
					if( str != '' ) str += ',';
					str += obj.options[j].value;
				}
			}
			
			v = str;
		}
		
		c[id] = v;
	}
	
	return c;
}
//------------------------------------------------------------------------------

function uniqid(prefix, more_entropy) {
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kankrelune (http://www.webfaktory.info/)
    // %        note 1: Uses an internal counter (in php_js global) to avoid collision
    // *     example 1: uniqid();
    // *     returns 1: 'a30285b160c14'
    // *     example 2: uniqid('foo');
    // *     returns 2: 'fooa30285b1cd361'
    // *     example 3: uniqid('bar', true);
    // *     returns 3: 'bara20285b23dfd1.31879087'

    if (typeof prefix == 'undefined') {
        prefix = "";
    }

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

function show_hide(_id, _btn, exc) {
	if( dojo.byId(_id).style.display == 'none' ) {
		dojo.byId(_id).style.display = 'inline';
		
		_btn.value = 'hide ' + exc;
	} else {
		dojo.byId(_id).style.display = 'none';

		_btn.value = 'show ' + exc;
	}
}
//------------------------------------------------------------------------------

function align_src_tst() {
//	alert(dojo.byId('tst'));
//	alert(dojo.byId('src'));
	if( dojo.byId('tst') == null || dojo.byId('src') == null ) return;

	var obj_src = dojo.byId('src');
	var obj_tst = dojo.byId('tst');

	var list_src = obj_src.value.split("\n");
	var list_tst = obj_tst.value.split("\n");

	var td_style = "style='border:#000000 1px solid;'";

	var max = list_src.length;
	if( list_tst.length > max ) max = list_tst.length;

	var result = "";
	
	var msg = "";
	if( list_src.length != list_tst.length ) {
		msg += "<div style='color: red;'>* ERROR: mismatch src count ("+list_src.length+") & tst count ("+list_tst.length+")</div>";
	}	
	
	if( list_src.length == 1 ) msg += "<div style='color: red;'>* ERROR: src is empty.</div>";
	if( list_tst.length == 1 ) msg += "<div style='color: red;'>* ERROR: tst is empty.</div>";

	var msg_empty_line = "";

	result += "<table style='width:100%; border:#000000 1px solid;'>";	
	for( var i=0 ; i<max ; i++ ) {
		var str_src = list_src[i];
		var str_tst = list_tst[i];
		
		if( !str_src ) str_src = "";
		if( !str_tst ) str_tst = "";
		
		str_src = str_src.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		str_tst = str_tst.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		
		if( str_src == "" && str_tst == "" ) continue;
		
		var tr_color = "";
		
		if( str_src == "" || str_tst == "" ) {
			if( msg_empty_line != "" ) msg_empty_line += ", ";
			msg_empty_line += "#"+i;
			
			tr_color = "style='background-color: #D3D3D3;'";	
		}
		
		result += "<tr "+tr_color+">";
		result += "  <td "+td_style+">"+i+"</td>";
		result += "  <td "+td_style+">" + str_src + "&nbsp;</td>";
		result += "  <td "+td_style+">" + str_tst + "&nbsp;</td>";
		result += "</tr>";	
	}	
	result += "</table>";

	if( msg_empty_line != "" ) msg += "<div style='color: red;'>* ERROR: Line "+msg_empty_line+" is empty. Please check it first.</div>";
	
	result = msg + result;
	
	if( msg == "" ) {
		dojo.byId("align_checked").value = "yes";	
	} else {
		dojo.byId("align_checked").value = "";
	}
	
	if( dojo.byId('div_result') != null ) dojo.byId('div_result').innerHTML = result;
}
//------------------------------------------------------------------------------

function trans_text() {
	var c = getContents(document);
	
	c['request'] = 'trans_text';
	
	start_tail_file('');

	if( dojo.byId('div_tst') != null ) 	  dojo.byId('div_tst').innerHTML = '&nbsp;<img src=loader-small.gif />';
	if( dojo.byId('div_result') != null ) dojo.byId('div_result').innerHTML = '';
	
	dojo.xhrPost( {
		url: act_url,
		handleAs: "text",
		content: c,
		timeout: post_time_out * 100, 
		load: function(response, ioArgs) {
			if( dojo.byId('div_tst') != null ) dojo.byId('div_tst').innerHTML = response;
			
			align_src_tst();

			start_tail_file("");
			return response;
		},
		error: function(response, ioArgs) {
			if( dojo.byId('div_tst') != null ) dojo.byId('div_tst').innerHTML = "ERROR: " + ioArgs.xhr.status;
			return response;
		}
	});
}
//------------------------------------------------------------------------------
function delete_pid(_ip, _pid, _icon_id) {
	start_tail_file("");
	
	var icon = dojo.byId(_icon_id);
	
	if( icon != null ) icon.innerHTML = '<img src=/img/loader-small.gif />';

	dojo.xhrPost( {
		url: act_url,
		handleAs: "text",
		content: { 
			request: 'delete_pid',
			ip: _ip,
			pid: _pid
		},
		timeout: post_time_out, 
		load: function(response, ioArgs) {
			if( icon != null ) icon.innerHTML = response;
			return response;
		},
		error: function(response, ioArgs) {
			if( icon != null ) icon.innerHTML = "ERROR: " + ioArgs.xhr.status;
			return response;
		}
	});
}
//------------------------------------------------------------------------------
function get_cluster_status(result_id) {
	start_tail_file("");
	
	dojo.byId(result_id).innerHTML = '&nbsp;<img src=/img/loader-small.gif /> status loading...';

	dojo.xhrPost( {
		url: act_url,
		handleAs: "text",
		content: { 
			request: 'get_cluster_status'
		},
		timeout: post_time_out, 
		load: function(response, ioArgs) {
			dojo.byId(result_id).innerHTML = response;
			return response;
		},
		error: function(response, ioArgs) {
			console.log("ERROR: " + ioArgs.xhr.status);
			return response;
		}
	});
}
//------------------------------------------------------------------------------

	
