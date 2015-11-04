<?php
error_reporting( E_ERROR | E_PARSE );
ini_set( 'display_errors', 1 );

include_once "settings.php";
include_once "formInputFunctions.php";
include_once "UrlForvarder.php";
include_once "QuerySearch.php";
include_once "MetadataGetter.php";
include_once "RerankInput.php";
include_once "DateDistance.php";
include_once "AuthorDistance";
include_once "ViewsDistance.php";

if (isset($_POST['submit'])) {
	$keywords 		= handleTextInput( $_POST['keywords'] );
	$max_results 	= handleIntegerInput( $_POST['max_results'] );
	$rerankInputs 	= array();
	$rerankInputs['author']		= new RerankInput( handleCheckboxInput($_POST[AUTHOR_CHECKBOX]), handleIntegerInput($_POST[AUTHOR_SCALE]), handleTextInput($_POST[AUTHOR_VALUE]) );
	$rerankInputs['published']	= new RerankInput( handleCheckboxInput($_POST[PUBLISHED_CHECKBOX]), handleIntegerInput($_POST[PUBLISHED_SCALE]), handleDateInput($_POST[PUBLISHED_VALUE]) );
	$rerankInputs['gps']		= new RerankInput( handleCheckboxInput($_POST[GPS_CHECKBOX]), handleIntegerInput($_POST[GPS_SCALE]), handleGpsInput($_POST[GPS_VALUE]) );
	$rerankInputs['views']		= new RerankInput( handleCheckboxInput($_POST[VIEWS_CHECKBOX]), handleIntegerInput($_POST[VIEWS_SCALE]), handleIntegerInput($_POST[VIEWS_VALUE]) );

	$times = array();
	$timeStart = microtime(true);
		$querySearch = new QuerySearch();
		$queryResults = $querySearch->getResults($keywords, $max_results);
		$metadataGetter = new MetadataGetter($queryResults);
		$rawMetastores = $metadataGetter->getAllMetadata();
		$rank = 0;
		$metastores = array();
    	foreach( $rawMetastores as &$metastore ) {
    		$metastore->setOldRank( ++$rank );
    		$metastores [$rank] = $metastore;
    	}
	$timeEnd = microtime(true);
	$times['Komunikace s Youtube API'] = $timeEnd - $timeStart;

	$dateDistance 	= new DateDistance( $metastores );
	$viewsDistance 	= new ViewsDistance( $metastores );

	$timeStart = microtime(true);
		$dateDistance->compute( $rerankInputs['published']->getCheckbox(), $rerankInputs['published']->getRange(), $rerankInputs['published']->getValue() );
	$timeEnd = microtime(true);
	$times['Výpočet vzdáleností datumů'] = $timeEnd - $timeStart;

	$timeStart = microtime(true);
		$viewsDistance->compute( $rerankInputs['views']->getCheckbox(), $rerankInputs['views']->getRange(), $rerankInputs['views']->getValue() );
	$timeEnd = microtime(true);
	$times['Výpočet vzdáleností počtu shlénutí'] = $timeEnd - $timeStart;

	$allTimes = 0;
	foreach( $times as $time )
		$allTimes += $time;
} else {
	$rerankInputs['author']		= new RerankInput;
	$rerankInputs['published']	= new RerankInput;
	$rerankInputs['gps']		= new RerankInput;
	$rerankInputs['views']		= new RerankInput;
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
						<dd><input type="search" name="keywords" value="<?php echo $keywords; ?>" id="keywords" placeholder="Zde zadejte vyhledávaný výraz." required></dd>
					<dt><label for="max_results" >Počet výsledků</label></dt>
						<dd><input type="number" name="max_results" id="max_results" value="<?php echo $max_results ? $max_results : 20; ?>" required min="10" max="50"></dd>
				</dl>
			</fieldset>
			<fieldset>
				<legend>Informace k přerankování</legend>
				<dl>
					<dt><label for="author_value">Autor</label></dt>
						<dd>
							<input type="checkbox" <?php echo($rerankInputs['author']->getCheckbox())?'checked':''; ?> name="<?php echo AUTHOR_CHECKBOX; ?>">
							<input type="range" value="<?php echo $rerankInputs['author']->getRange(); ?>" name="<?php echo AUTHOR_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
							<input type="text" value="<?php echo $rerankInputs['author']->getValue(); ?>" name="<?php echo AUTHOR_VALUE; ?>" id="author_value">
						</dd>
					<dt><label for="published_value">Datum publikování</label></dt>
						<dd>
							<input type="checkbox" <?php echo($rerankInputs['published']->getCheckbox())?'checked':''; ?>  name="<?php echo PUBLISHED_CHECKBOX; ?>">
							<input type="range" value="<?php echo $rerankInputs['published']->getRange(); ?>" name="<?php echo PUBLISHED_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
							<input type="date" value="<?php echo $rerankInputs['published']->getValue(); ?>" name="<?php echo PUBLISHED_VALUE; ?>" id="published_value">
						</dd>
					<dt><label for="gps_value">Souřadnice GPS umístění</label></dt>
						<dd>
							<input type="checkbox" <?php echo($rerankInputs['gps']->getCheckbox())?'checked':''; ?> name="<?php echo GPS_CHECKBOX; ?>">
							<input type="range" value="<?php echo $rerankInputs['gps']->getRange(); ?>" name="<?php echo GPS_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
							<input type="text" value="<?php echo $rerankInputs['gps']->getValue(); ?>" name="<?php echo GPS_VALUE; ?>" id="gps_value">
						</dd>
					<dt><label for="views_value">Počet shlédnutí</label></dt>
						<dd>
							<input type="checkbox" <?php echo($rerankInputs['views']->getCheckbox())?'checked':''; ?> name="<?php echo VIEWS_CHECKBOX; ?>">
							<input type="range" value="<?php echo $rerankInputs['views']->getRange(); ?>" name="<?php echo VIEWS_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
							<input type="number" value="<?php echo $rerankInputs['views']->getValue(); ?>" name="<?php echo VIEWS_VALUE; ?>" id="views_value" min="0">
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
					<th>Zaokrouhlený čas</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Celkový čas</th>
					<th><?php echo round( $allTimes, 3 ); ?>s</th>
				</tr>
			</tfoot>
			<tbody>
<?php
	foreach( $times as $task => $time ) {
?>
				<tr>
					<td><?php echo $task; ?></td>
					<td><?php echo round( $time, 3 ); ?>s</td>
				</tr>
<?php
	}
?>
			</tbody>
		</table>
		<table>
			<caption>Výsledky</caption>
			<thead>
				<tr>
					<th>Rank</th>
					<th>Původní výsledek</th>
					<th>Přerankovaný výsledek</th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach( $metastores as &$metastore ) {
?>
				<tr>
					<td><?php echo $metastore->getOldRank(); ?></td>
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



