<style type="text/css">
	#tab_admin_result { margin:0; padding:0; width:100%; height:100%; font-size:110%; }
</style>
<div class="easyui-layout" fit="true">
    <div class="easyui-accordion" region="north" split="true" style="height: 42px; padding: 5px;">
		<a href="#" class="easyui-linkbutton" iconCls="icon-ok" onclick="get_user_list(this, 'tab_admin_result');" title="Get User List">User List</a>
<!--
		<a href="#" class="easyui-linkbutton" iconCls="icon-ok" onclick="get_auth_code(this, 'tab_admin_result');" title="Get Auth Code">Auth Code</a>
		<a href="#" class="easyui-linkbutton" iconCls="icon-ok" onclick="get_cluster_status(this, 'tab_admin_result');" title="Get Process Status">Process Status</a>
		<a href="#" class="easyui-linkbutton" iconCls="icon-ok" onclick="get_svn_info(this, 'tab_admin_result', '', 10);" title="Get SVN List">SVN List</a>
-->
    </div>
    <div id="tab_admin_result" region="center"></div>
</div>