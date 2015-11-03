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

	$allTimes = 0;
	$times = array();

	$timeStart = microtime(true);
		$querySearch = new QuerySearch();
		$queryResults = $querySearch->getResults($keywords, $max_results);
		$metadataGetter = new MetadataGetter($queryResults);
		$metastores = $metadataGetter->getAllMetadata();
	$timeEnd = microtime(true);
	$time = $timeEnd - $timeStart;
	$times['Komunikace s Youtube API'] = $time;
	$allTimes += $time;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MI-VMW</title>
	</head>
	<body>
		<h1>YouTube reranking</h1>
		<form action="./" method="POST"> 
			<fieldset>
				<legend>Základní iformace</legend>
				<dl>
					<dt><label for="keywords" >Vyhledat</label></dt>
						<dd><input type="text" name="keywords" value="<?php echo $keywords; ?>" id="keywords" placeholder="Zde zadejte vyhledávaný výraz." required></dd>
					<dt><label for="max_results" >Počet výsledků</label></dt>
						<dd><input type="number" name="max_results" id="max_results" value="<?php echo $max_results ? $max_results : 20; ?>" required min="10" max="50"></dd>
				</dl>
			</fieldset>
			<fieldset>
				<legend>Informace k přerankování</legend>
				<dl>
					<dt>Autor</dt>
						<dd>
							<input type="checkbox" name="author_checkbox">
							<input type="range" name="author_scale" min="0" max="100">
							<input type="text" name="author_value">
						</dd>
					<dt></dt>
						<dd>

						</dd>
				</dl>
			</fieldset>
			<input type="submit" name="submit" value="Odeslat">
		</form>

<?php
if (isset($_POST['submit'])) {
?>
		<table>
			<caption>Časy vykonaných úkonů</caption>
			<thead>
				<tr>
					<th>Úkon</th>
					<th>Čas</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Celkový čas</th>
					<th><?php echo $allTimes; ?>s</th>
				</tr>
			</tfoot>
			<tbody>
<?php
				foreach( $times as $task => $time ) {
					echo "<tr><td>$task</td><td>".$time."s</td></tr>";
				}
?>
			</tbody>
		</table>
		<table>
			<caption>Výsledky</caption>
			<thead>
				<tr>
					<th>Původní výsledek</th>
					<th>Přerankovaný výsledek</th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ( $metastores as $metastore ) {
?>
				<tr>
					<td><?php echo $metastore; ?></td>
					<td>Ve vývoji...</td>
				</tr>
<?php
	}
?>
			</tbody>
		</table>
<?php
}
?>
	</body>
</html>



