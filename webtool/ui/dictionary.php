<style type="text/css">
	#tab_dic_body { margin:0; padding:0; width:100%; height:100%; }
	
	#dic_result, #dic_result_ex  { margin:0; padding:0; font-size:130%; }
	
	#pane_dic_top { margin:0; padding:0; font-size:130%; }

	#tbl_search_dic { width:100%; margin:0; padding:0; }
	#word, #val { width:100%;  border: 1px black solid; padding: 0; margin: 0; }
</style>

<div id="tab_dic_body" class="easyui-layout" fit="true">
    <div id="pane_dic_top" class="easyui-accordion" region="north" split="true">
    	
		<table id="tbl_search_dic">
		    <tr><td style="width:250px">
		
				<table style="width:100%;">
				    <tr><td style="width:50px;" align=center>
			        	사 전:
				   	</td><td style="width:200px">
						<select id="db" style="width:100%" onchange="on_change_dic(this, 'img_conv')"></select>
				    </td></tr>
				</table>
		
			</td><td id="panel_sub_search_dic">
		
				<table style="width:100%;">
				    <tr><td style="width:60px;">
				    	<img id='img_conv' src='/imgs/conv.png' id="btn_dic_conv_db" style="display:none;" onmouseover="this.style.cursor='pointer'" onclick="conv_db(this, 'db')" title="GDBM -> sqlite">
						<img src='/imgs/refresh.jpg' onmouseover="this.style.cursor='pointer'" onclick="init_dictionary_page();" title="Update DB List">
				    </td><td style="width:50px;" align=center>
				        키:
				    </td><td>
				        <input id="word" type=text onkeydown="keyword_keydown('btn_search')">
				    </td><td style="width:50px" align=center>
				        값:
				    </td><td>
				        <input id="val" type=text onkeydown="keyword_keydown('btn_search')">
				    </td><td style="width:10px;">
				    	<img src="/imgs/search2.png" id="btn_search" onmouseover="this.style.cursor='pointer'" onclick="search_gdbm(this, 'db', 'word', 'val', 'dic_result', 'dic_result_ex', 0)" title="Search">
				    </td></tr>
				</table>
		
	        </td></tr>
		</table>

    </div>
    <div region="center">
        <div class="easyui-layout" fit="true">
            <div id="dic_result" title="" class="easyui-accordion panel_top" region="west" split="true">
            </div>
            <div id="dic_result_ex" title="" id="" region="center">
            </div>
        </div>
    </div>
</div>
