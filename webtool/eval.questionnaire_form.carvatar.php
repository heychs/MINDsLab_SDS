<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv='Content-Type' content='text/html;' charset="UTF-8" />

<style>
* {
	font-size: 12px;
}	
</style>		

		<script src="/jquery/jquery-1.7.1.js"></script>
		<script src="/jquery/jquery.jsoncookie.js"></script>
		<script type="text/javascript" src="js/g_variable.js"></script>

<script>
	function on_form_load() {
		var user_name = $.cookie("user_name");
		
		$("#user_name").val(user_name);		

		if( user_name != "" ) {
			$("#user_name_span").html("<b>"+user_name+"</b> 님 ");		
		}  
		
//		$('#questionnaire_form').attr('action', g_variable['_POST_URL_']);
	}	

	function save_survay() {
		var post_data = {
			"request": "submit_survey",
			"project_name": $.cookie("project_name"),
			"user_name": $.cookie("user_name")
		};
		
		var form_data = $("form[name=questionnaire_form]").serializeArray();
		
		if( form_data.length < 5 ) {
			alert("설문지 항목을 모두 선택해 주세요.");
			return;
		}
		
		for( var i in form_data ) {
			var name = form_data[i].name;
			var value = form_data[i].value;
			
			post_data[name] = value;
		}
		
		if( post_data["survey_q4"] == "" ) {
			alert("4번 항목을 적어 주세요.");
			return;
		}
		
		$.post(g_variable["_POST_URL_"], post_data, function(data) {
		    var ret = jQuery.parseJSON(data);
		    
		    alert("설문에 응해주셔서 감사합니다.");
		});
	}	
</script>		

	</head>
<body style="margin:10px;" onload="on_form_load();">
<form name="questionnaire_form" action="" method="POST">
	<div>&nbsp;&nbsp;<span id="user_name_span"></span>ETRI 차량용 대화 시스템을 평가해주셔서 감사합니다.</div>
	<div>&nbsp;&nbsp;아래에 평가를 하신 후 ETRI 시스템에 대해 느끼신 점을 점수로 적어주십시요. </div>
	<div>&nbsp;&nbsp;&nbsp;&nbsp;(0점:매우 아니다, 1점: 아니다, 2점: 보통, 3점: 그렇다, 4점: 매우 그렇다)</div>
	<div>&nbsp;&nbsp;향후 ETRI 시스템을 개선하기 위함입니다.</div>
	<div>&nbsp;&nbsp;감사합니다.</div>
	
	<br />
	
	<!-- ---------------------------------- -->
	<div>
	1. 시스템이 적절하게 응답을 하였습니까?
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=radio name="survey_q1" value=1>매우 아니다 
		<input type=radio name="survey_q1" value=2>아니다
		<input type=radio name="survey_q1" value=3>보통
		<input type=radio name="survey_q1" value=4>그렇다
		<input type=radio name="survey_q1" value=5>매우 그렇다
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	<div>
	2. 실제 상황과 어느 정도 유사하다고 생각하십니까? 
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=radio name="survey_q2" value=1>매우 다르다 
		<input type=radio name="survey_q2" value=2>다르다
		<input type=radio name="survey_q2" value=3>보통
		<input type=radio name="survey_q2" value=4>유사하다
		<input type=radio name="survey_q2" value=5>매우 유사하다
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	<div>
	3. 새로운 차량 관련 정보를 배우셨습니까?
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=radio name="survey_q3" value=1>매우 아니다 
		<input type=radio name="survey_q3" value=2>아니다
		<input type=radio name="survey_q3" value=3>보통
		<input type=radio name="survey_q3" value=4>그렇다
		<input type=radio name="survey_q3" value=5>매우 그렇다
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	<div>
	4. 차량용 음성 대화시스템이 시급히 도입되어야 할 차량 관련 분야는 무엇이라고 생각하십니까? (분야를 기술해 주세요.)
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=text name="survey_q4" style="width:90%;">
	</div>

	<br>
	
	<!-- ---------------------------------- -->
	<div>
	5. 4번의 차량용 음성대화시스템이 도입되면 사용하시겠습니까?
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=radio name="survey_q5" value=1>매우 아니다 
		<input type=radio name="survey_q5" value=2>아니다
		<input type=radio name="survey_q5" value=3>보통
		<input type=radio name="survey_q5" value=4>그렇다
		<input type=radio name="survey_q5" value=5>매우 그렇다
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	
	<div align=center id=div_submit>
		<input type=button style="width:90%;" value="제        출" onClick="save_survay(); ">
	</div>

</form>

</body>

</html>
