<style type="text/css">
	.corpus_top 	{ 
	   width:100%; 
       height:40px; 
       font-size:100%; 
    }

	#tab_corpus_body 	{ margin:0; padding:0; width:100%; height:100%; }
	#tab_corpus_result 	{ margin:0; padding:0; width:100%; height:100%; font-size:110%; }
</style>

<div class="easyui-layout" fit="true">
    <div title="" class="easyui-accordion corpus_top" region="north" split="true">
		<table>
			<tr><td style='text-align:right;'>
				File Name:
			</td><td style='width:200px;'>
				<select id="corpus_file_name"></select>
			</td><td style='width:70px; text-align:right;'>
				Keyword:
			</td><td style='width:200px;'>
				<input type=text id='corpus_keyword' style='width:100%;' value='' onkeydown="keyword_keydown('corpus_btn_search')" />
			</td><td>
				<input type=button id='corpus_btn_search' style='width:60px;' value='Search' onclick="search_corpus(document.getElementById('corpus_keyword'), 'tab_corpus_result', -1, get_text_by_id('corpus_file_name'))" />
			</td><td>
				<img id='corpus_btn_refresh' src='/imgs/refresh.jpg' onmouseover="this.style.cursor='pointer'" onclick="init_corpus_page(this);" title="Update" />
			</td></tr>
		</table>    	
    </div>
    <div title="title" id="tab_corpus_result" region="center">
    </div>
</div>
