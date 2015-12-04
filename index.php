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
include_once "AuthorsDistance.php";
include_once "ViewsDistance.php";
include_once "GPSDistance.php";

if( isset( $_POST[ 'submit' ] ) ) {
	$keywords = handleTextInput( $_POST[ 'keywords' ] );
	$max_results = handleIntegerInput( $_POST[ 'max_results' ] );
	$rerankInputs = array();
	$rerankInputs[ 'author' ] = new RerankInput( handleCheckboxInput( $_POST[ AUTHOR_CHECKBOX ] ), handleIntegerInput( $_POST[ AUTHOR_SCALE ] ), handleTextInput( $_POST[ AUTHOR_VALUE ] ) );
	$rerankInputs[ 'published' ] = new RerankInput( handleCheckboxInput( $_POST[ PUBLISHED_CHECKBOX ] ), handleIntegerInput( $_POST[ PUBLISHED_SCALE ] ), handleDateInput( $_POST[ PUBLISHED_VALUE ] ) );
	$rerankInputs[ 'gps' ] = new RerankInput( handleCheckboxInput( $_POST[ GPS_CHECKBOX ] ), handleIntegerInput( $_POST[ GPS_SCALE ] ), handleGpsInput( $_POST[ GPS_VALUE ] ) );
	$rerankInputs[ 'views' ] = new RerankInput( handleCheckboxInput( $_POST[ VIEWS_CHECKBOX ] ), handleIntegerInput( $_POST[ VIEWS_SCALE ] ), handleIntegerInput( $_POST[ VIEWS_VALUE ] ) );
	$times = array();
	$timeStart = microtime( true );
	$querySearch = new QuerySearch();
	$queryResults = $querySearch->getResults( $keywords, $max_results );
	$metadataGetter = new MetadataGetter( $queryResults );
	$rawMetastores = $metadataGetter->getAllMetadata();
	$rank = 0;
	$metastores = array();
	foreach( $rawMetastores as &$metastore ) {
		$metastore->setOldRank( ++$rank );
		$metastores [ $rank ] = $metastore;
	}
	$timeEnd = microtime( true );
	$times[ 'Komunikace s Youtube API' ] = $timeEnd - $timeStart;

	$dateDistance = new DateDistance( $metastores );
	$viewsDistance = new ViewsDistance( $metastores );
	$authorDistance = new AuthorsDistance( $metastores );
	$gpsDistance = new GPSDistance( $metastores );

	$timeStart = microtime( true );
	$dateDistance->compute( $rerankInputs[ 'published' ]->getCheckbox(), $rerankInputs[ 'published' ]->getRange(), $rerankInputs[ 'published' ]->getValue() );
	$timeEnd = microtime( true );
	$times[ 'Výpočet vzdáleností datumů' ] = $timeEnd - $timeStart;

	$timeStart = microtime( true );
	$viewsDistance->compute( $rerankInputs[ 'views' ]->getCheckbox(), $rerankInputs[ 'views' ]->getRange(), $rerankInputs[ 'views' ]->getValue() );
	$timeEnd = microtime( true );
	$times[ 'Výpočet vzdáleností počtu shlénutí' ] = $timeEnd - $timeStart;

	$timeStart = microtime( true );
	$authorDistance->compute( $rerankInputs[ 'author' ]->getCheckbox(), $rerankInputs[ 'author' ]->getRange(), $rerankInputs[ 'author' ]->getValue() );
	$timeEnd = microtime( true );
	$times[ 'Výpočet vzdáleností jmen autorů' ] = $timeEnd - $timeStart;

	$timeStart = microtime( true );
	$gpsDistance->compute( $rerankInputs[ 'gps' ]->getCheckbox(), $rerankInputs[ 'gps' ]->getRange(), $rerankInputs[ 'gps' ]->getValue() );
	$timeEnd = microtime( true );
	$times[ 'Výpočet vzdáleností GPS souřadnic' ] = $timeEnd - $timeStart;

	$allTimes = 0;
	foreach( $times as $time )
		$allTimes += $time;
	foreach( $metastores as &$metastore ) {
		$metastore->computeRerankResult();
	}
	$rankedMetastores = $metastores;

	$toRerank = false;
	foreach( $rerankInputs as $rerankInput ) {
		if( $rerankInput->getCheckbox() )
			$toRerank = true;
	}
	if( $toRerank )
		usort( $rankedMetastores, function ( $a, $b ) { return $a->rerankResult < $b->rerankResult; } );
} else {
	$rerankInputs[ 'author' ] = new RerankInput;
	$rerankInputs[ 'published' ] = new RerankInput;
	$rerankInputs[ 'gps' ] = new RerankInput;
	$rerankInputs[ 'views' ] = new RerankInput;
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>MI-VMW</title>
	<link rel="stylesheet" type="text/css" href="bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="icon.png" sizes="16x16" type="image/png">
</head>
<body>
<div class="container">
	<div class="row">
		<img src="./logo.png" class="logo" alt="logo">

		<h1 class="center heading1"> reranking</h1>

		<form action="./" method="POST" class="inputs">
			<fieldset class="query">
				<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 col-xs-offset-0 col-sm-offset-1 col-md-offset-2 col-lg-offset-3">
					<h2 class="center heading">Základní iformace</h2>
					<dl class="subcontent">
						<dt><label for="keywords">Vyhledat</label></dt>
						<dd>
							<input class="form-control" type="search" name="keywords" value="<?php echo $keywords; ?>" id="keywords" placeholder="Zde zadejte vyhledávaný výraz." required>
						</dd>
						<dt><label for="max_results">Počet výsledků</label></dt>
						<dd>
							<input class="form-control" type="number" name="max_results" id="max_results" value="<?php echo $max_results ? $max_results : 20; ?>" required min="10" max="50">
						</dd>
					</dl>
				</div>
			</fieldset>
			<fieldset class="rerankInformation col-xs-6 col-sm-6 col-md-6 col-lg-6">
				<h2 class="center heading">Informace k přerankování</h2>

				<div class="subcontent">
					<dl class="rerankInformation">
						<dt><label for="author_value">Autor</label></dt>
						<dd>
							<div class="row">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="authorComputing" id="c1" type="checkbox" <?php echo ( $rerankInputs[ 'author' ]->getCheckbox() ) ? 'checked' : ''; ?> name="<?php echo AUTHOR_CHECKBOX; ?>">
                                    <label for="c1"></label>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="authorRange" type="range" value="<?php echo $rerankInputs[ 'author' ]->getRange(); ?>" name="<?php echo AUTHOR_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="authorText form-control" type="text" value="<?php echo $rerankInputs[ 'author' ]->getValue(); ?>" name="<?php echo AUTHOR_VALUE; ?>" id="author_value">
								</div>
							</div>
						</dd>
						<dt><label for="published_value">Datum publikování</label></dt>
						<dd>
							<div class="row">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="publishedComputing" id="c2" type="checkbox" <?php echo ( $rerankInputs[ 'published' ]->getCheckbox() ) ? 'checked' : ''; ?> name="<?php echo PUBLISHED_CHECKBOX; ?>">
                                    <label for="c2"></label>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="publishedRange" type="range" value="<?php echo $rerankInputs[ 'published' ]->getRange(); ?>" name="<?php echo PUBLISHED_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="publishedText form-control" type="date" value="<?php echo $rerankInputs[ 'published' ]->getValue(); ?>" name="<?php echo PUBLISHED_VALUE; ?>" id="published_value">
								</div>
							</div>
						</dd>
						<dt><label for="gps_value">Souřadnice GPS umístění</label></dt>
						<dd>
							<div class="row">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="gpsComputing" id="c3" type="checkbox" <?php echo ( $rerankInputs[ 'gps' ]->getCheckbox() ) ? 'checked' : ''; ?> name="<?php echo GPS_CHECKBOX; ?>">
                                    <label for="c3"></label>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="gpsRange" type="range" value="<?php echo $rerankInputs[ 'gps' ]->getRange(); ?>" name="<?php echo GPS_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="gpsText form-control" type="text" value="<?php echo $rerankInputs[ 'gps' ]->getValue(); ?>" name="<?php echo GPS_VALUE; ?>" id="gps_value">
								</div>
							</div>
						</dd>
						<dt><label for="views_value">Počet shlédnutí</label></dt>
						<dd>
							<div class="row">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="viewsComputing" id="c4" type="checkbox" <?php echo ( $rerankInputs[ 'views' ]->getCheckbox() ) ? 'checked' : ''; ?> name="<?php echo VIEWS_CHECKBOX; ?>">
                                    <label for="c4"></label>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="viewsRange" type="range" value="<?php echo $rerankInputs[ 'views' ]->getRange(); ?>" name="<?php echo VIEWS_SCALE; ?>" min="<?php echo MIN_WEIGHT; ?>" max="<?php echo MAX_WEIGHT; ?>">
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input class="viewsText form-control" type="number" value="<?php echo $rerankInputs[ 'views' ]->getValue(); ?>" name="<?php echo VIEWS_VALUE; ?>" id="views_value" min="0">
								</div>
							</div>
						</dd>
					</dl>
					<input class="btn btn-default btn-success center-block" type="submit" name="submit" value="Odeslat">
				</div>
			</fieldset>
		</form>
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
			<?php if( isset( $_POST[ 'submit' ] ) ) { ?>
			<h2 class="center heading">Časy vykonaných úkonů</h2>
			<table class="times table table-bordered table-striped subcontentTable">
				<thead>
				<tr>
					<th>Úkon</th>
					<th>Zaokrouhlený čas</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td>Celkový čas</td>
					<td>
						<?php echo number_format( $allTimes, 5 ); ?> sekund
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach( $times as $task => $time ) { ?>
					<tr>
						<td>
							<?php echo $task; ?>
						</td>
						<td>
							<?php echo number_format( $time, 5 ); ?> sekund
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<table class="results table table-bordered subcontentTable table-striped">
				<caption class="center heading top-buffer">Výsledky</caption>
				<thead>
				<tr>
					<!--th>Rank</th-->
					<th>Původní výsledek</th>
					<th>Přerankovaný výsledek</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach( $metastores as &$metastore ) { ?>
					<tr>
						<!--td>
							<?php echo $metastore->getOldRank(); ?></td-->
						<td class="metadata">
							<?php echo $metastore; ?>
						</td>
						<td class="metadata">
							<?php if( $toRerank ) {
								echo $rankedMetastores[ $metastore->getOldRank() - 1 ];
							} else echo $rankedMetastores[ $metastore->getOldRank() ]; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php } ?>
		</div>
	</div>
</div>

<div id="footer">
  <div class="container center">
    <dl class="text-muted">
      <dt>Copyright</dt>
        <dd>© 2015 - 2016</dd>
      <dt>Webmasters</dt>
        <dd>Kubín Petr &amp; Herbert Waage</dd>
      <dt>Aktualizováno</dt>
        <dd>5.12.2015</dd>
    </dl>
  </div>
</div>

</body>
</html>