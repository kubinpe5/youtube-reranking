<?php

error_reporting( E_ERROR | E_PARSE );
ini_set( 'display_errors', 1 );

include_once "UrlForvarder.php";
include_once "QuerySearch.php";

define ('API_KEY', 'AIzaSyBVYtP85g7VCilGKbzkQqPCf8CxokAfvhU');

$querySearch = new QuerySearch();

$querySearch->getResults("fishing", 50);


