<div class="easyui-panel" id="res_manager_main_panel" title="Resource Manager" fit="true" tools="#res_main_panel_tools">  
    <div id="res_manager_layout" class="easyui-layout" fit="true">
    	<div region="west" split="true" class="res_west" noheader="true">
            <div class="easyui-panel" id="res_category" title="meta category" fit="true" tools="#res_category_tools"></div>  
        </div>
    	<div region="center" noheader="true" class="res_center" id="res_manager_result"></div>
    </div>
</div>  

<div id="res_category_tools">
	<a href="#" class="icon-reload" onclick="res_category_refresh()" title="refresh"></a>
	<a href="#" class="layout-button-left" onclick="res_hide_category()" title="hide category"></a>
</div>

<div id="res_category_context_menu" class="easyui-menu" style="width:120px;">
    <div onclick="res_add_category($('#res_category'))" iconCls="icon-add">Add New</div>
    <div onclick="res_delete_category($('#res_category'))" iconCls="icon-remove">Delete</div>
    <div onclick="res_rename_node($('#res_category'), res_category_refresh)" iconCls="icon-rename">Rename</div>
    <div class="menu-sep"></div>
    <div onclick="res_get_new_wiki_meta_data($('#res_category'), $('#res_manager_result'))" iconCls="icon-rename">New Wiki Info.</div>
</div>

<div id="res_data_tree_context_menu" class="easyui-menu" style="width:120px;">
    <div onclick="res_add_category($('#res_data_tree'))" iconCls="icon-add">Add New</div>
    <div onclick="res_delete_category($('#res_data_tree'))" iconCls="icon-remove">Delete</div>
    <div class="menu-sep"></div>
    <div onclick="res_rename_node($('#res_data_tree'), res_data_tree_refresh)" iconCls="icon-rename">Rename</div>
</div>

<div id="res_main_panel_tools">
	<a href="#" class="icon-search" onclick="" title="search"></a>
</div>