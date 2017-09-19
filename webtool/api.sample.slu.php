<?

header('Content-Type: text/html; charset=utf-8');

$url = "http://mt.etri.re.kr/webtool/api/main.php";
//$url = "http://129.254.186.88/webtool/api/main.php";

echo "<textarea style='width: 100%; height: 100%;'>";


echo "\n\n=============================================================\n";
echo "stop\n";
echo "=============================================================\n";
// stop
$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query(
			array(
				"request" 	=> "stop_dialog_system",
				"user_name" => "carvatar",
				"project_name"=> "carvatar",
				"dialog_system_engine" => "dial",
				"dialog_system_channel" => "20"
			)
		)
	)
);

$context  = stream_context_create($opts);
$result = file_get_contents($url, false, $context);

$result = json_decode($result, 1);
print_r( $result );


echo "\n\n=============================================================\n";
echo "init\n";
echo "=============================================================\n";
// init
$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query(
			array(
				"request" 	=> "init_dialog_system",
				"user_name" => "carvatar",
				"project_name"=> "carvatar",
				"shell_cmd" => "GET_SLOT_TAGGING",
				"dialog_system_engine" => "dial",
				"dialog_system_channel" => "20"
			)
		)
	)
);

$context  = stream_context_create($opts);
$result = file_get_contents($url, false, $context);

$result = json_decode($result, 1);
print_r($result);

echo "\n\n=============================================================\n";
echo "speak\n";
echo "=============================================================\n";
// stop
$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query(
			array(
				"request" 	=> "dialog_system_user_utter",
				"user_name" => "carvatar",
				"project_name"=> "carvatar",
				"user_utter" => "네 이름이 뭐야?",
				"shell_cmd" => "GET_SLOT_TAGGING",
				"dialog_system_channel" => "20"
			)
		)
	)
);

$context  = stream_context_create($opts);
$result = file_get_contents($url, false, $context);

$result = json_decode($result, 1);
print_r( $result );


echo "\n\n=============================================================\n";
echo "speak\n";
echo "=============================================================\n";
// stop
$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query(
			array(
				"request" 	=> "dialog_system_user_utter",
				"user_name" => "carvatar",
				"project_name"=> "carvatar",
				"user_utter" => "에어컨은 어떻게 키는거야",
				"shell_cmd" => "GET_SLOT_TAGGING",
				"dialog_system_channel" => "20"
			)
		)
	)
);

$context  = stream_context_create($opts);
$result = file_get_contents($url, false, $context);

$result = json_decode($result, 1);
print_r( $result );



echo "\n\n=============================================================\n";
echo "stop\n";
echo "=============================================================\n";
// stop
$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query(
			array(
				"request" 	=> "stop_dialog_system",
				"user_name" => "carvatar",
				"project_name"=> "carvatar",
				"dialog_system_engine" => "dial",
				"dialog_system_channel" => "20"
			)
		)
	)
);

$context  = stream_context_create($opts);
$result = file_get_contents($url, false, $context);

$result = json_decode($result, 1);
print_r( $result );



echo "</textarea>";


?>
