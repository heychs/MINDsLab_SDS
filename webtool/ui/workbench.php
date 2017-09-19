<div class="easyui-layout" fit="true">
    <div class="easyui-panel workbench_layout_main_top" id="workbench_layout_main_top" region="north" split="true" noheader="true">
    
        <!-- workbench top -->
        <div class="easyui-layout" fit="true">
            <div class="easyui-panel workbench_panel_src" region="west" split="true" noheader="true">
            
                <!-- src -->
                <div id="workbench_panel_src" class="easyui-panel workbench_panel_src" title="src" iconCls="icon-ok" fit="true">  
                    <textarea id="workbench_src"></textarea>
                </div>  
        
            </div>
            <div class="easyui-panel workbench_panel_ref" region="center" split="true" noheader="true">
            
                <!-- ref -->
                <div id="workbench_panel_ref" class="easyui-panel workbench_panel_ref" title="ref" iconCls="icon-ok" fit="true">  
                    <textarea id="workbench_ref"></textarea>
                </div>  
        
            </div>
        </div>
        
    </div>
    
    <!-- workbench result -->
    <div class="easyui-panel" id="workbench_result" region="center" split="true" noheader="true" title="Result"></div>
</div>