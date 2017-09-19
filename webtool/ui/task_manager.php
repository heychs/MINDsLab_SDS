<!doctype html>
<html>
	<head>
		<title>Task Manager</title>
		<link rel="stylesheet" href="../style/dialog_act_tagger.task_manager.css" />

	    <!-- DEP -->
        <script src="/jquery/jquery-1.7.1.js"></script>
        <script src="/jquery/jquery.jsoncookie.js"></script>
		<script src="/jquery/ui/js/jquery-ui-1.8.13-min.js"></script>
		
		<!-- task manager -->
		<script src="/jquery/ui/js/jsBezier-0.3-min.js"></script>

		<script src="/jquery/jsPlumb/1.3.13/jquery.jsPlumb-1.3.13-all.js"></script>
        
		<script src="../js/g_variable.js"></script>
		<script src="../js/dialog_act_tagger.task_manager.js"></script>
        
	</head>
	<body>
		<div style="position: relative">
			<div id="task_manager"></div>
		</div>
        <div style="display: none;"><textarea id="task_info"></textarea></div>

		<script type="text/javascript">
            init_task_manager();
        </script>
	</body>
</html>
