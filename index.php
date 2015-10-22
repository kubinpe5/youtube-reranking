<?php
error_reporting( E_ERROR | E_PARSE );
ini_set( 'display_errors', 1 );

include_once "Connector.php";
include_once "QuerySearch.php";

$myUrls = array(
	'<strong>Direct URL:</strong><br>'								=> 'https://www.youtube.com/watch?v=HYCsJsTHM3Y',
	'<strong>Redirect URL:</strong><br>'							=> 'https://youtu.be/HYCsJsTHM3Y',
	'<strong>Many redirects URL:</strong><br>'						=> 'http://bit.ly/1Zy2WT6',
	'<strong>Existing URL, but non-existing resource:</strong><br>'	=> 'http://webdev.fit.cvut.cz/~waageher/MI-VMW/nejsemtu',
	'<strong>Non-existing URL:</strong><br>'						=> 'http://faktNEexistujukamo.cz',
	'<strong>Non-valid URL:</strong><br>'							=> 'http://seznam.cz'
//	'<strong>Infinite URL redirects'								=> 'http://webdev.fit.cvut.cz/~waageher/MI-VMW/redirect'	
);

$connectors = array();
foreach( $myUrls as $key => $myUrl ) {
	echo $key;
	try {
		$connectors [] = new Connector( $myUrl );
	} catch( Exception $e ) {
		echo $e->getMessage().'<br>';
	}
}
