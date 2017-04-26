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
<html>
	<head>
		<title>PHP Solr Client</title>
	</head>
	    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

	


	<body>
	   
		<form action="http://localhost/solr-php-client/hw5.php" accept-charset="utf-8">
			<label for="q">Search:</label>
      
      
			 <input id="q" name="q" type="text" /> 

			<input type="submit" value="Search""/> 

			<br>
			<input type="radio" name="rank" value="lucene" />lucene
			<input type="radio" name="rank" value="pagerank" /> pagerank
		</form>
	      
	  

	  <script type="text/javascript">
		$(function() {
            var URL_PREFIX = "http://localhost/solr-php-client/auto.php?q=";
            $("#q").autocomplete({
                source : function(request, response) {
                    var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                    var URL = URL_PREFIX + lastword;
                    $.ajax({
                        url : URL,
                        success : function(data) {
                        	json = JSON.parse(data);
                            var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);

                            var suggestions = json.suggest.suggest[lastword].suggestions;
                            if (json["correction"]["term"] !== $("#q").val()) {
                            	duplicate = false;
                            	for(i in suggestions) {
                            		
                            		if(suggestions[i].term === json["correction"]["term"]){
                            			
                            			duplicate = true;
                            			break;
                            		}
                            	}
                            	if(!duplicate) {
                            		suggestions.unshift(json["correction"]);
                            	}
                            	
                            }

                            suggestions = $.map(suggestions, function (value, index) {
                                var prefix = "";
                                var query = $("#q").val();
                                var queries = query.split(" ");
                                if (queries.length > 1) {
                                    var lastIndex = query.lastIndexOf(" ");
                                    prefix = query.substring(0, lastIndex + 1).toLowerCase();
                                }
                                if (!/^[0-9a-zA-Z]+$/.test(value.term)) {
                                    return null;
                                }
                                return prefix + value.term;
                            });
                            response(suggestions.slice(0, 5));
                        },
                    });
                },
                minLength : 1
            });
        });
	</script>


<?php

if ($results) {
	$total = (int) $results->response->numFound; 
	$start = min(1, $total);
	$end = min($limit, $total);
?>
 
<?php
	if ($total == 0) {
	    include 'SpellCorrector.php';
		echo "Did you mean "; 
		$arr=explode(" ", $query);
		foreach($arr as $v){
		    $new_query=$new_query.SpellCorrector::correct($v)." ";
		}
		
	}
?> 
<a href='http://localhost/solr-php-client/hw5.php?q=<?php echo htmlentities($new_query); ?> '><?php echo $new_query; ?></a>

<div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div><ul> 
<?php

	foreach ($results->response->docs as $doc)
	{ 
?>
	<li>
	
<?php


{ 
	$name = end(explode('/', $doc->id));
	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTMLFile("./NBCNewsDownloadData/".$name);
	libxml_clear_errors();
	$tags=$dom->getElementsByTagName('p');
	$content = $dom->saveHTML();
	$snipet = false;
	$arr = explode(" ", $query);
	foreach ($tags as $tag) {
		$p = $tag->nodeValue."\n";
		$found = False;
		foreach ($arr as $word) {
			$position = stripos($p, $word);
			if($position !== False) {
				$end = strpos($content, ".", $position);
				$start = strrpos(substr($p , 0, $position), ".") + 0;
				$len =  $end - $start + 1;
				if($len > 160) {
					$len = 160;
				}
				$snipet = substr($p, $start, $len);
				$found = true;
				break;
			}
		}
		if($found) {
			break;
		}
	}

	
?>

<?php echo "<a href=".$map[$name].">".$map[$name]."</a><br>" ?>
<?php echo "<p>".$snipet."</p><hr>" ?>

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

</body>
</html>
