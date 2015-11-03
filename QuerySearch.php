<?php

require_once 'google-api-php-client/autoload.php';
require_once 'google-api-php-client/src/Google/Client.php';
require_once 'google-api-php-client/src/Google/Service/YouTube.php';

class QuerySearch {
	private $youtube;

	function __construct() {
		$client = new Google_Client();
		$client->setDeveloperKey(API_KEY);
		$this->youtube = new Google_Service_YouTube($client);
	}

	public function getResults ( $query, $max_results ) {
		$searchResponse = $this->youtube->search->listSearch('id,snippet', array(
        	'type' => 'video',
        	'q' => $query,
        	'maxResults' => $max_results,
    	));
		// Herby je nejlepší a nějak mi to nešlo napsat tak to tak asi nebude :-D
		$videoResults = array();
    	# Merge video ids
    	foreach ($searchResponse['items'] as $searchResult) {
    		array_push($videoResults, $searchResult['id']['videoId']);
    	}
    	
    	return $videoResults;
	}
}