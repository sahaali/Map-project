<meta charset="UTF-8">
<?
set_time_limit(0);
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);


$csvFile = file('april_2018.csv');
$main = [];
foreach ($csvFile as $line) {
	
  $data = str_getcsv($line);
	//$addr = $data[5].', '.$data[6].', '.$data[7].', '.$data[8].', '.$data[9];
	$addr = str_replace('City of Kamloops','Kamloops',$data[3]).', '.str_replace("UNIT ",'',$data[7]);
	echo $addr.'<br>';
	/* ------------ GEOCODE --------------*/
	
	$i=0;
	$api_index = 0;
	$api = [
			'AIzaSyCvJM1wSf3LjwRnNog7gxnPmQyWcYBsA3U',
			'AIzaSyBMZj7338GtbomQV9xQyzwcZhES6uOhw80',
			'AIzaSyCANNotXGYMO9-woy5ckgAnQDzRYjRPBMI',
			'AIzaSyD4qmCXobQDBm4V3WYedbafE5zbLa7RFlo',
			'AIzaSyBKeO6ZCver3tZ1edCUEIBhelZu3klg9ic',
			'AIzaSyDYB0MjcxW-W-1gBNtlVq-__IX4iSwZQbk',
			'AIzaSyBY1fTeF2CUBSq4ko0vKPJa_RezcDMdFuM',
			'AIzaSyCQQPMjmIpiR8u9vdOqVyG5_R_vj5drseI',
			'AIzaSyAmzJ5Bp_tS8tk0U5gJc5CEZfKG2tk27Eg',
		];
		
	echo $url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.$api[$api_index].'&address=' . rawurlencode($addr).'&region=ca';
	echo '<br>';
	$json = json_decode(file_get_contents($url));
	if (@$json->error_message == "You have exceeded your daily request quota for this API."){
		$api_index++;
	}
	$lat_ = $json->results[0]->geometry->location->lat;
	$lng_ = $json->results[0]->geometry->location->lng;
	$data[] = $lat_;
	$data[] = $lng_;
  echo '<br>'.$lat_."|".$lng_;
  
	/* ------------ GEOCODE --------------*/
	
	echo '<hr>';
	$main[] = $data;
	flush();
}

$fp = fopen('april_2018_geocoded.csv', 'w');
foreach ($main as $fields) {
    fputcsv($fp, $fields);
}
fclose($fp);
?>