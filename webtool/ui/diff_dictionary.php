<?
// * TO DO
?>
<style type="text/css">
	#diff_dict_body { margin:0; padding:0; width:100%; height:100%; }
	#diff_dict_top 	{ margin:0; padding:0; overflow:auto; width:100%; height:80px; font-size:100%; }
	
	#diff_dict_result { margin:0; padding:0; width:100%; height:100%; font-size:110%; }
</style>

<div id="diff_dict_body" class="easyui-layout" fit="true">
    <div id="diff_dict_top" class="easyui-accordion" region="north" split="true">

		<table border="1">
			<tr>
				<td style="width:20px; text-align:center;">
					A:
				</td>
				<td>
					<select id='diff_dict_a' onchange="diff_dict_update_file_name(this.value, 'diff_fn_a');"></select>
					<select id='diff_fn_a'></select>
				</td>
				<td style="width:20px; text-align:center;">
					B:
				</td>
				<td>
					<select id='diff_dict_b' onchange="diff_dict_update_file_name(this.value, 'diff_fn_b');"></select>
					<select id='diff_fn_b'></select>
				</td>
				<td style="">
					<select id='diff_dict_user_email'></select>
					<select id='diff_dict_m_date'></select>
				</td>
				<td>
				   	<img src='/imgs/refresh.jpg' onmouseover="this.style.cursor='pointer'" onclick="init_diff_dictionary_page()" />
				   	<img src='/imgs/statistics.png' onmouseover="this.style.cursor='pointer'" onclick="diff_dict_run(this)" />
				</td>
			</tr>
		</table>

	</div>
	<div id="diff_dict_result" region="center">
	</div>
</div>