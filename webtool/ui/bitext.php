<?
// * TO DO
?>
<style type="text/css">
	#bitext_main_container { margin:0; padding:0; width:100%; height:100%; }
	#bitext_result	{ margin:0; padding:0; overflow:auto; width:100%; height:100%; font-size:110%; }
	#bitext_top 	{ margin:0; padding:0; overflow:auto; width:100%; height:35px; font-size:110%; }
</style>

<div id="bitext_top">

<table style='' border='0'>
	<tr>
		<td>
			<select id="tuning_file_name"></select>
		   	<img src='/imgs/refresh.jpg' onmouseover="this.style.cursor='pointer'" onclick="init_bitext_page()" />
		
			&nbsp;&nbsp;&nbsp;
			
			검색어:
		   	<input type='text' id="bitext_search_keyword" value="" style="width:100px;" onkeydown="keyword_keydown('btn_bitext_search')" />
		
		   	<img src='/imgs/search.png' id="btn_bitext_search" title="get by bleu" onmouseover="this.style.cursor='pointer'" onclick="set_text_by_id('tab_bitext.page_no', 0); get_bitext_sent(null, 'bitext_result', -1, get_text_by_id('tab_bitext.page_no_max_display'));">
		
			<select id='bitext_sort_type' style="">
				<option value="SENT_ID" selected="selected">SENT ID</option>
				<option value="SENT_ID_REVERSE">SENT ID (R)</option>
				<option value="BLEU">BLEU</option>
			</select>
		</td>
		<td>
			<table style='width:150px;'>
				<tr><td style='width:10px; text-align:left;'>
					<img src='/imgs/prev.png' onmouseover="this.style.cursor='pointer'" title='Previous' onclick="get_bitext_sent_prev(this, 'bitext_result', 'tab_bitext.page_no');">
				</td><td>
					<input type='text' id='tab_bitext.page_no' style='width:100%; text-align:center;' value='1' onkeydown="if( event.keyCode == 13 ) { set_text_by_id('tab_bitext.page_no', this.value-1); get_bitext_sent_next(this, 'bitext_result', 'tab_bitext.page_no');}">
				</td><td style=''>
					<input type='text' id='tab_bitext.page_no_max_display' style='width:100%; text-align:center;' value='1'  onkeydown="if( event.keyCode == 13 ) { set_text_by_id('tab_bitext.page_no', get_text_by_id('tab_bitext.page_no')-1); get_bitext_sent_next(this, 'bitext_result', 'tab_bitext.page_no');}">
				</td><td style='width:10px; text-align:right;'>
					<img src='/imgs/next.png' onmouseover="this.style.cursor='pointer'" title='Next' onclick="get_bitext_sent_next(this, 'bitext_result', 'tab_bitext.page_no');">
				</td></tr>
			</table>			
		</td>
		<td>
			&nbsp;&nbsp;&nbsp;
			<img src='/imgs/add.png' title="add new sentence" onmouseover="this.style.cursor='pointer'" onclick="bitext_add_new_sentence_html(this, 'bitext_result');">
		</td>
		<td id='tab_bitext_statistics_btn' style='display:none;'>
			&nbsp;&nbsp;&nbsp;
			<img src='/imgs/statistics2.png' onmouseover="this.style.cursor='pointer'" onclick="get_bitext_statistics(this, 'bitext_result');" title='statistic'>
		</td>
	</tr>
</table>

</div>
<hr />
<div id='bitext_result' style='overflow:auto; height:90%;'></div>

