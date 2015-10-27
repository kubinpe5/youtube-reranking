<?php

error_reporting( E_ERROR | E_PARSE );
ini_set( 'display_errors', 1 );

include_once "UrlForvarder.php";
include_once "QuerySearch.php";
include_once "MetadataGetter.php";

define ('API_KEY', 'AIzaSyDzWsixl9gkY7ux_cmTY9C7Zqh39SxCUvE');

if (isset($_POST['submit'])) {
	$keywords = htmlspecialchars($_POST['keywords']);
	if (is_numeric($_POST['max_results'])) 
		$max_results = $_POST['max_results'];
	else
		throw new Exception("Error Processing Request", 1);

	echo "keywords: ".$keywords." and max_results: ".$max_results."<br>";
}

$querySearch = new QuerySearch();

$queryResults = $querySearch->getResults($keywords, $max_results);

$metadataGetter = new MetadataGetter($queryResults);


?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Our awesome youtube reranking</title>
    </style>
	</head>

	<body>

		<h1>YouTube Reranking bitch</h1>

		<form action="./" method="POST"> 
			<dl>
				<dt><label for="keywords" >Vyhledat</label></dt>
				<dd><input type="text" name="keywords" value="<?php echo $keywords; ?>" id="keywords" placeholder="Zde zadejte vyhledávaný výraz." required></dd>

				<dt><label for="max_results" >Počet výsledků</label></dt>
				<dd><input type="number" name="max_results" id="max_results" value="<?php echo $max_results ? $max_results : 20; ?>" required min="10" max="50"></dd>
	
				<dt>Odeslat</dt>
				<dd><input type="submit" name="submit" value="Odeslat"></dd>
			</dl>

			
		</form>

<?php

$metadataGetter->startIteration();

while ( !is_null($metastore = $metadataGetter->nextElem()) ) {
	echo $metastore;
}

?>

	</body>

</html>



