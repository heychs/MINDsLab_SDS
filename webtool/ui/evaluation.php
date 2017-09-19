<style type="text/css">
	#eval_main_container { margin:0; padding:0; width:100%; height:100%; }
	#eval_set, #eval_top { margin:0; padding:0; overflow:auto; width:100%; height:150px; font-size:110%; }

	#eval_src { margin:0; padding:0; width:100%; height:100%; font-size:110%; }

	#eval_tbl_option, #eval_tbl_src { margin:0; padding:0; width:100%; height:100%; font-size:110%; }
</style>

<div id="eval_main_container" class="easyui-layout" fit="true">
    <div id="eval_top" class="easyui-accordion" region="north" split="true">

		<table id="eval_tbl_option">
		    <tr><td style="height:20px;">
				&nbsp;Tag:
				<select id="eval_tag" onchange="eval_onchange_tag_name(this.value, 'eval_pane_input')"></select>

				&nbsp;A:
				<select id="eval_engine_a" style="width:15%"></select>
				<select id="eval_dic_a" style="width:15%"></select>
				
				&nbsp;B:
				<select id="eval_engine_b" style="width:15%"></select>
				<select id="eval_dic_b" style="width:15%"></select>

				<img id='eval_btn_refresh' src='/imgs/refresh.jpg' onmouseover="this.style.cursor='pointer'" onclick="init_evaluation_page(this);" title="Update">
		    </td></tr>
		    <tr><td id="eval_pane_input">		    	    
				<table id="eval_tbl_src">
				    <tr><td>
						<textarea id="eval_src"></textarea>
				    </td><td style="width:50px;" align=right>
						<input type=button title="Make Evaluation Set." onclick="make_evaluation_set('eval_engine_a', 'eval_dic_a', 'eval_engine_b', 'eval_dic_b', 'eval_tag', 'eval_src', 'eval_set')" value="Make" style="width:100%; height:100%;">
				    </td></tr>
				</table>				
		    </td></tr>
		</table>
		
    </div>
    <div id="eval_set" region="center"></div>
</div>
