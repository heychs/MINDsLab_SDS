<style type="text/css">
	#sent_eval_main_container { margin:0; padding:0; width:100%; height:100%; }
	#sent_eval_result	{ margin:0; padding:0; overflow:auto; width:100%; height:100%; font-size:110%; }
	#sent_eval_top 	{ margin:0; padding:0; overflow:auto; width:100%; height:60px; font-size:110%; }
</style>

<div id="sent_eval_top">
	a: <select id="sent_eval_fn_a"></select>
	b: <select id="sent_eval_fn_b"></select>
	n_list: <input type=text id="sent_eval_n_list" value="2,3,4,5" />

	<br />

	data-set: <select id="sent_eval_fn_data_set"></select>

   	<img src='/imgs/refresh.jpg' onmouseover="this.style.cursor='pointer'" onclick="get_tuning_set_list('sent_eval_fn_data_set'); update_file_list('sent_eval.', 'sent_eval_fn_a', null); update_file_list('sent_eval.', 'sent_eval_fn_b', null);">

   	<img src='/imgs/trans.png' title="sentence evaluation" onmouseover="this.style.cursor='pointer'" onclick="sent_eval(this, 'sent_eval_result');">
   	<img src='/imgs/search.png' title="get result" onmouseover="this.style.cursor='pointer'" onclick="get_sent_eval_result(this, 'sent_eval_result');">
</div>
<hr />
<div id="sent_eval_result"></div>