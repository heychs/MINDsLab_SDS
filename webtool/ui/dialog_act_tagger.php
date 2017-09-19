<!-- start: dialog_action tagger main layout -->
<div class="easyui-layout dialog_act_tagger_layout_main" fit="true">

    <!-- main layout: north -->
    <div region="north" class="easyui-panel dialog_act_tagger_panel_main_north" id="dialog_act_tagger_panel_main_north" title="" noheader="true" split="true">

        <!-- north layout -->
        <div class="easyui-layout" id="layout_scenario" fit="true">

            <!-- Scenario list -->
            <div region="center" class="easyui-panel dialog_act_tagger_panel_utter_list" title="Scenario" id="dialog_act_tagger_panel_utter_list" split="true"></div>
            
            <!-- Task Manager -->
            <div region="east" class="easyui-panel dialog_act_tagger_task_manager" title="Task Manager" id="dialog_act_tagger_task_manager" split="true"></div>

        </div>
        
    </div>

    <!-- main layout: center -->
    <div region="center" class="easyui-panel dialog_act_tagger_layout_tagged_result" title="" noheader="true" split="true">

        <!-- slot tagging layout -->
        <div class="easyui-layout" fit="true">

            <!-- dialog_action Tagging -->
            <div region="center" class="easyui-panel dialog_act_tagger_tagged_result" title="Dialog Act" id="dialog_act_tagger_tagged_result" split="true"></div>

            <!-- Slot Structure -->
            <div region="east" class="easyui-panel dialog_act_tagger_slot_structure" title="Slot Structure" id="dialog_act_tagger_slot_structure" split="true"></div>

        </div>

    </div>

    <!-- main layout: south -->
    <div region="south" id="dialog_act_tagger_candidate" class="easyui-panel dialog_act_tagger_candidate" title="Tagging Guide" split="true">
    </div>
</div>
<!-- end: dialog_action tagger main layout -->