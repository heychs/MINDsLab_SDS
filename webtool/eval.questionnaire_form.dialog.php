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
		
		if( form_data.length < 11 ) {
			alert("설문지 항목을 모두 선택해 주세요.");
			return;
		}
		
		for( var i in form_data ) {
			var name = form_data[i].name;
			var value = form_data[i].value;
			
			post_data[name] = value;
		}
		
		if( post_data["survey_q8"] == "" ) {
			alert("8번 항목을 적어 주세요.");
			return;
		}
		if( post_data["survey_q9"] == "" ) {
			alert("9번 항목을 적어 주세요.");
			return;
		}
		if( post_data["survey_q10"] == "" ) {
			alert("10번 항목을 적어 주세요.");
			return;
		}
		if( post_data["survey_q11"] == "" ) {
			alert("11번 항목을 적어 주세요.");
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
	<div>&nbsp;&nbsp;<span id="user_name_span"></span>저희 한국어 내비게이션 대화 시스템을 평가해주셔서 감사합니다. </div>
	<div>&nbsp;&nbsp;아래에 평가를 하신 후 저희 시스템에 대해 느끼신 점을 점수로 적어 주십시요. </div>
	<div>&nbsp;&nbsp;&nbsp;&nbsp;(0점:매우 아니다, 1점: 아니다, 2점: 보통, 3점: 그렇다, 4점: 매우 그렇다)</div>
	<div>&nbsp;&nbsp;향후 저희 시스템을 개선하기 위함입니다.</div>
	<div>&nbsp;&nbsp;감사합니다.</div>
	
	<br />
	
	<!-- ---------------------------------- -->
	<div>
	1. 시스템 평가를 위해 완수하신 태스크들이 전체적으로 자신에게 필요한 것이었습니까? 
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
	2. 시스템 평가의 태스크들이 차량 운행 중에 필요한 상황들이었습니까? 
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
	3. 차량 운행 상황에서 대화형으로 작동하는 것이 운전에 도움이 된다고 생각하십니까?
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
	4. 시스템이 적절하게 응답하고 작동 하였습니까? 
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=radio name="survey_q4" value=1>매우 아니다 
		<input type=radio name="survey_q4" value=2>아니다
		<input type=radio name="survey_q4" value=3>보통
		<input type=radio name="survey_q4" value=4>그렇다
		<input type=radio name="survey_q4" value=5>매우 그렇다
	</div>

	<br>
	
	<!-- ---------------------------------- -->
	<div>
	5. 주어진 태스크를 수행하기 위해, 대화시스템과 주고 받은 대화 길이가 적당했다고 생각하십니까? 
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
	
	<div>
	6. 본 대화시스템은 사용자가 먼저 명령을 내리는 방식입니다. 시스템이 주도적으로 여러 번 질문하는 기존 방식과 비교하여 이런 방식이 사용하기에 편리한 것 같습니까? 
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=radio name="survey_q6" value=1>매우 아니다 
		<input type=radio name="survey_q6" value=2>아니다
		<input type=radio name="survey_q6" value=3>보통
		<input type=radio name="survey_q6" value=4>그렇다
		<input type=radio name="survey_q6" value=5>매우 그렇다
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	
	<div>
	7. 만약 내비게이션에 장착되어 상용화된다면 구매하여 사용할 생각이 있습니까? 
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=radio name="survey_q7" value=1>매우 아니다 
		<input type=radio name="survey_q7" value=2>아니다
		<input type=radio name="survey_q7" value=3>보통
		<input type=radio name="survey_q7" value=4>그렇다
		<input type=radio name="survey_q7" value=5>매우 그렇다
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	<div>
	8. 평가에 활용한 도메인은 DMB 작동, 날씨 정보 검색, 교통 정보 검색, 내비게이션 명령어, 목적지/경유지 검색, 주변검색이었습니다
   대화가 되어서 가장 효율적이라고 생각하시는 도메인들을 적어 주세요?  
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=text name="survey_q8" style="width: 90%;">
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	
	<div>
	9. 위의 도메인에서 가장 자주 사용할 것 같은 도메인들은 어떤 것입니까? 
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=text name="survey_q9" style="width: 90%;">
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	<div>
	10. 현재 적용 분야 말고 다른 분야에 적용하려고 합니다. 어떤 분야가 유용하겠습니까? (분야를 기술해 주세요) 
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=text name="survey_q10" style="width: 90%;">
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->
	<div>
	11. 활용하는데 있어서 아쉬운 점이나 개선해야 할 사항을 기술해 주세요.
	</div>
	
	<div>
		&nbsp;&nbsp;&nbsp;
		<input type=text name="survey_q11" style="width: 90%;">
	</div>
	
	<br>
	
	<!-- ---------------------------------- -->

	<div align=center id=div_submit>
		<input type=button style="width:90%;" value="제        출" onClick="save_survay(); ">
	</div>

</form>

</body>

</html>
