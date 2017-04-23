<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE,HEAD, OPTIONS');

$file = fopen("mapNBCNewsDataFile.csv", "r") or die("Unable to open file!");
$map = array();

while ($line = fgets($file)) {
	$pair = explode(',', $line);
	$map[$pair[0]] = $pair[1];
}
fclose($file);
?>


<?php
// make sure browsers see this page as utf-8 encoded HTML
	header('Content-Type: text/html; charset=utf-8');
	$limit = 10;
	$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false; 
	$results = false;
	if ($query)
	{

		require_once('Apache/Solr/Service.php');
		$solr = new Apache_Solr_Service('localhost', 8983, '/solr/hw4/');

		if (get_magic_quotes_gpc() == 1) 
		{
			$query = stripslashes($query); 
		}

		try
		{
			if($_REQUEST['rank'] == 'pagerank') {
				$additionalParameters = array(
				'sort' => 'pageRankFile desc'
				);
			}
			if($_REQUEST['rank'] == 'lucene') {
				$additionalParameters = array(
				
				);
			}
			
			$results = $solr->search($query, 0, $limit, $additionalParameters);

		}
		catch (Exception $e)
		{

			die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
		}
	}
?>  

<?php

if ($results) {
	$total = (int) $results->response->numFound; 
	$start = min(1, $total);
	$end = min($limit, $total);

?>

<?php
	if ($total == 0) {
	    include 'SpellCorrector.php';
		echo "Did you mean? "; 
		$arr=explode(" ", $query);

		// print all the value which are in the array
		// $new_query="";
		foreach($arr as $v){
		    
		    $new_query=$new_query.SpellCorrector::correct($v)." ";

		}
		echo "<a href='http://localhost/solr-php-client/hw5.php?q=<?php echo htmlentities($new_query); ?> '><?php echo $new_query; ?></a>"
	}
?>



<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div><ul> 
<?php

	foreach ($results->response->docs as $doc)
	{ 
?>
	<li>
<?php


{ 
	$name = explode('/', $doc->id);
?>

<?php echo "<a href=".$map[end($name)].">".$map[end($name)]."</a>" ?>

<?php
}
?>
 </li>
<?php 
}
?> 
</ul>
<?php 
}
?>





