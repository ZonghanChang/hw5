
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE,HEAD, OPTIONS');

include 'SpellCorrector.php';
$q=$_GET["q"];
$q = strtolower($q);
$q1=split (" ", $q);
$count=count($q1);
$arr=explode(" ", $q);
foreach($arr as $v){
	$new_query=$new_query.SpellCorrector::correct($v)."";
}

$url="http://localhost:8983/solr/hw4/suggest?wt=json&indent=true&q=".$q1[$count-1];
$json=file_get_contents($url);
$obj = json_decode($json,true);
$obj["correction"]["term"] = $new_query;
$res = json_encode($obj);
echo $res;
?>