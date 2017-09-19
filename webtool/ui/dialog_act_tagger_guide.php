<!-- start: dialog_action tagger main layout -->
<div class="easyui-layout dialog_act_tagger_guide_layout_main" fit="true">

    <!-- main layout: north -->
    <div region="north" class="easyui-panel dialog_act_tagger_guide_panel_main_north" title="" noheader="true" split="true">

        <!-- north layout -->
        <div class="easyui-layout" id="layout_scenario" fit="true">

            <!-- Task Manager -->
            <div region="west" class="easyui-panel" title="Task Graph" id="dialog_act_tagger_guide_task_manager" split="true">
            </div>

            <!-- Scenario list -->
            <div region="center" class="easyui-panel" title="Utterance List" id="dialog_act_tagger_guide_utter_structure" split="true">
            </div>
            
        </div>
        
    </div>

    <!-- main layout: center -->
    <div region="center" class="easyui-panel" title="" id="dialog_act_tagger_guide_layout_tagged_result" noheader="true" split="true">

        <!-- slot tagging layout -->
        <div class="easyui-layout" fit="true">

            <!-- dialog_action Tagging -->
            <div region="center" class="easyui-panel" title="Utterance & Paraphrase" id="dialog_act_tagger_guide_tagged_result" split="true">
            </div>

            <!-- Slot Structure -->
            <div region="east" class="easyui-panel" noheader="true" split="true" style="width: 300px;">
            	
				<div class="easyui-panel" title="Slot Info." id="dialog_act_tagger_guide_slot_structure" style=""></div>             	
				<div class="easyui-panel" title="Mission" id="dialog_act_tagger_guide_mission" style=""></div>             	
				<div class="easyui-panel" title="Slot Values" id="dialog_act_tagger_guide_slot_values" style=""></div>             	

            </div>

        </div>

    </div>

    <!-- main layout: south -->
    <div region="south" id="dialog_act_tagger_guide_candidate" class="easyui-panel dialog_act_tagger_guide_panel_tagging_guide" title="Tagging Guide" split="true">
    </div>
</div>
<!-- end: dialog_action tagger main layout -->