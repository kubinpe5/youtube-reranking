<?php

require_once 'google-api-php-client/autoload.php';
require_once 'google-api-php-client/src/Google/Client.php';
require_once 'google-api-php-client/src/Google/Service/YouTube.php';

/**
* 
*/
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
/*
  try {
    // Call the search.list method to retrieve results matching the specified
    // query term.
    
    

    # Call the videos.list method to retrieve location details for each video.
    $videosResponse = $youtube->videos->listVideos('snippet, recordingDetails', array(
    'id' => $videoIds,
    ));

    $videos = '';

    // Display the list of matching videos.
    foreach ($videosResponse['items'] as $videoResult) {
      $videos .= sprintf('<li>%s (%s,%s)</li>',
          $videoResult['snippet']['title'],
          $videoResult['recordingDetails']['location']['latitude'],
          $videoResult['recordingDetails']['location']['longitude']);
    }

    $htmlBody .= <<<END
    <h3>Videos</h3>
    <ul>$videos</ul>
END;
  } catch (Google_Service_Exception $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }
}
?>

*/