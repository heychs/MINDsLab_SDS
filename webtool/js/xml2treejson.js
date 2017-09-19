//------------------------------------------------------------------------------
function xml2json(xml) {
    var xmlDoc = $.parseXML( xml );
    
	var _root = $(xmlDoc).children(':first-child');
    
	var child = retrive($(_root));
    
    var uid = uniqid();
    
	var ret = [{ "id":uid, "text":_root[0].nodeName, "state":"open", "iconCls":"icon-save" }];
    if( JSON.stringify(child) != "[]" ) {
        ret = [{ "id":uid, "text":_root[0].nodeName, "children": child, "state":"open", "iconCls":"icon-save" }];
    }
    
    return ret;
}
//------------------------------------------------------------------------------
function getAttribute(attributes, nodeName){
    var value = "";
    
	if( null != attributes && attributes.length > 0 ){
		for(var i=0; i<attributes.length; i++){
			if( nodeName == attributes[i].nodeName ) return attributes[i].nodeValue;
		}
	}
    
	return value;
}
//------------------------------------------------------------------------------
function retrive(node){
    var ret = [];
    
	var _ch = $(node).children();

	for(var i=0; i<_ch.length; i++){
		var child = retrive(_ch[i]);
        
        // nodeType : https://developer.mozilla.org/ko/Migrate_apps_from_Internet_Explorer_to_Mozilla
        //      3	Text Node
        //      4	CDATA Section Node
        var firstChild = _ch[i].firstChild;
        
        var uid = uniqid();
        
        if( null == firstChild ) {
			var new_item = { "id":uid, "text":_ch[i].nodeName, "iconCls":"icon-save" };
			if( JSON.stringify(child) != "[]" ) {
    			new_item = { "id":uid, "text":_ch[i].nodeName, "children": child, "state":"open", "iconCls":"icon-save" };
			}

            ret.push(new_item);                    
        } else {
            var data = _ch[i].nodeName;
            
            var str = "";
            if( 3 == firstChild.nodeType ) { // Text Node
                str = firstChild.textContent;
            } else { // CDATA Node
                str = firstChild.nodeValue;   
            }

            // remove tab & space
            if( str ) {
                str = str.replace(/\s+|\t+/g, "");   
                if( str != "" ) data += ": " + str;
            }
            
            // display title
            var title = getAttribute(_ch[i].attributes, "title");
            if( title != "" ) data += ": " + title;

            var state = "closed";
            if( _ch[i].nodeName == "syntax" ) state = "open";
            
            var new_item = { "id":uid, "text":data, "iconCls":"icon-save" };
			if( JSON.stringify(child) != "[]" ) {
   				new_item = { "id":uid, "text":data, "children": child, "state":state, "iconCls":"icon-save" };
			}
            
            ret.push(new_item);
		}
	}
        
    return ret;
}
//------------------------------------------------------------------------------
var _dictionary_tree = null;

function build_dictionary_tree(xml, result_id) {
    var xmlDoc = $.parseXML( xml );

	_dictionary_tree = null;
	_dictionary_tree = new xml2treejson(xmlDoc, result_id);
	_dictionary_tree.doProcess();
}

function parseXml(xmlStr) {
	if( window.DOMParser ) {
		return new window.DOMParser().parseFromString(xmlStr, "text/xml");
	} else if( typeof window.ActiveXObject != "undefined" && new window.ActiveXObject("Microsoft.XMLDOM") ) {
	        var xmlDoc = new window.ActiveXObject("Microsoft.XMLDOM");
	        xmlDoc.async = "false";
	        xmlDoc.loadXML(xmlStr);
	        return xmlDoc;
	}else{
		return $(xmlStr);
	}
}		

function xml2treejson(xmlDoc, result_id) {
	this.xmlDoc = xmlDoc;
	this.result_id = result_id;
	this.treeEl = $("#"+result_id);
	this.vsuid = 0;
}

xml2treejson.prototype.initTree = function(data){
    this.treeEl.tree({ 
        "id":this.vsuid, 
        "data": data, 
        "dnd": true, 
        "animate": true, 
        "onClick":function(node){
            _dictionary_tree.treeEl.tree('toggle', node.target);
        },
        "onDblClick":function(node){
            _dictionary_tree.treeEl.tree('beginEdit', node.target);
        }
    });
}

xml2treejson.prototype.doProcess = function(){
	//Find root:
	var _root = $(this.xmlDoc).children(':first-child');
    
	var child = this.vsTraverse($(_root));
    
	var _treedata = [{ "id":this.vsuid, "text":_root[0].nodeName, "state":"open", "iconCls":"icon-save" }];
    if( child != "" ) {
        _treedata = [{ "id":this.vsuid, "text":_root[0].nodeName, "children": JSON.parse("["+child+"]"), "state":"open", "iconCls":"icon-save" }];
    }

	this.initTree(_treedata);
    
    this.vsuid++;
}

xml2treejson.prototype.vsTraverse = function(node){
    var ret = "";
    
	var _ch = $(node).children();

	for(var i=0; i<_ch.length; i++){
		var child = this.vsTraverse(_ch[i]);
        
		var _a_att = this.vsTraverseAtt(_ch[i]);

        // nodeType : https://developer.mozilla.org/ko/Migrate_apps_from_Internet_Explorer_to_Mozilla
        //      3	Text Node
        //      4	CDATA Section Node
        var firstChild = _ch[i].firstChild;
        
        if( null != firstChild ) {
            if( 3 == firstChild.nodeType || 4 == firstChild.nodeType ) {
                var data = _ch[i].nodeName;
                                    
                var str = "";
                if( 3 == firstChild.nodeType ) { // Text Node
                    str = firstChild.textContent;
                } else { // CDATA Node
                    str = firstChild.nodeValue;   
                }
                
                // remove tab & space
                str = str.replace(/\s+|\t+/g, "");
                if( str != "" ) data += ": " + str;
                
                // display title
                var title = this.getAttribute(_ch[i].attributes, "title");
                if( title != "" ) data += ": " + title;

                var state = "closed";
                if( _ch[i].nodeName == "syntax" ) state = "open";
                
                var new_item = { "id":this.vsuid, "text":data, "iconCls":"icon-save" };
    			if( child != "" ) {
       				new_item = { "id":this.vsuid, "text":data, "children": JSON.parse("["+child+"]"), "state":state, "iconCls":"icon-save" };
    			}
                
                if( ret != "" ) ret += ",";
                ret += JSON.stringify(new_item);                    
            }
		} else {
			var new_item = { "id":this.vsuid, "text":_ch[i].nodeName, "iconCls":"icon-save" };
			if( child != "" ) {
    			new_item = { "id":this.vsuid, "text":_ch[i].nodeName, "children": JSON.parse("["+child+"]"), "state":"open", "iconCls":"icon-save" };
			}

            if( ret != "" ) ret += ",";
            ret += JSON.stringify(new_item);                    
		}
        
		this.vsuid++;
	}
        
    return ret;
}

xml2treejson.prototype.getAttribute = function(attributes, nodeName){
    var value = "";
    
	if( null != attributes && attributes.length > 0 ){
		for(var i=0; i<attributes.length; i++){
			if( nodeName == attributes[i].nodeName ) return attributes[i].nodeValue;
		}
	}
    
	return value;
}

xml2treejson.prototype.vsTraverseAtt = function(node){
	var _a_atts = null;
    var attributes = node.attributes;
    
	if( null != attributes && attributes.length > 0 ){
		_a_atts = new Array();
		for(var i=0; i<attributes.length; i++){
			_a_atts.push(attributes[i].nodeName + ": " + attributes[i].nodeValue);
		}
	}
    
	return _a_atts;
}

