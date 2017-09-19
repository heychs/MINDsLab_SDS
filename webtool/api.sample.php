<?
exit();

header('Content-Type: text/html; charset=utf-8');

$url = "http://mt.etri.re.kr/webtool/api/main.php";

echo "<textarea style='width: 100%; height: 100%;'>";


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
				"user_name" => "gnb",
				"project_name"=> "gnb",
				"dialog_system_engine" => "dial",
				"dialog_system_channel" => "0"
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
				"user_name" => "gnb",
				"project_name"=> "gnb",
				"user_utter" => "I want to buy a city tour bus.",
				"dialog_system_channel" => "0"
			)
		)
	)
);

$context  = stream_context_create($opts);
$result = file_get_contents($url, false, $context);

$result = json_decode($result, 1);
print_r( $result );


echo "\n\n=============================================================\n";
echo "dialog_system_get_recommendation_info\n";
echo "=============================================================\n";
// stop
$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query(
			array(
				"request" 	=> "dialog_system_get_recommendation_info",
				"user_name" => "gnb",
				"project_name"=> "gnb",
				"dialog_system_channel" => "0"
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
				"user_name" => "gnb",
				"project_name"=> "gnb",
				"dialog_system_engine" => "dial",
				"dialog_system_channel" => "0"
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
