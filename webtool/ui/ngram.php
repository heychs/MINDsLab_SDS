<style type="text/css">
	#tab_ngram_body 	{ margin:0; padding:0; width:100%; height:100%; }
	#tab_ngram_top 		{ margin:0; padding:0; width:100%; height:100px; font-size:110%; }
	#tab_ngram_result 	{ margin:0; padding:0; width:100%; height:100%; font-size:110%; }
</style>

<div id="tab_ngram_body" class="easyui-layout" fit="true">
    <div id="tab_ngram_top" class="easyui-accordion" region="north" split="true">

		<table style="width:100%;">
			<tr><td>

				<table>
					<tr><td style='text-align:right;'>
						Lang:
					</td><td style=''>
						<select id="ngram_in_lang">
							<option value="ko" selected="selected">ko</option>
							<option value="zh">zh</option>
							<option value="en">en</option>
						</select>
					</td><td style='text-align:right;'>
						File Name:
					</td><td style=''>
						<select id="ngram_in_file_name"></select>
					</td><td style=''>
						LM Count:
					</td><td style=''>
						<select id="ngram_lm_file_name"></select>
					</td><td style=''>
						<input type="text" id="ngram_n_list" value="3,4,5,6" style="width:100px" />
					</td><td style=''>
						<input type="text" id="ngram_w_type" value="bleu,ncount,wfreq,lnfreq" style="width:100px" />
					</td><td>
						<input type="button" id='ngram_btn_run' style='width:60px;' value='Run' onclick="run_ngram_match(this)" />
						<input type="button" id='ngram_btn_get_result' style='width:60px;' value='View' onclick="get_result_ngram_match('ngram_in_file_name', 'tab_ngram_result')" />
					</td><td>
						<img id='ngram_btn_refresh' src='/imgs/refresh.jpg' onmouseover="this.style.cursor='pointer'" onclick="init_ngram(this);" title="Update" />
					</td></tr>
				</table>    	

			</td></tr>
			<tr><td>
				
				<textarea id="ngram_in_text" style="width:100%; height:55px;"></textarea>
				
			</td></tr>
		</table>
		
    </div>
    <div id="tab_ngram_result" region="center"></div>
</div>