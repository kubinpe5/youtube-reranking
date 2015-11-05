<?php

include_once "MetadataStore.php";
include_once "ParallelCurl.php";

$GLOBALS['metastores'];
function on_request_done($content, $url, $ch, $search) {
    $my_hash = json_decode($content);
    $metastore = new MetadataStore();
	$metastore->id = $search['id'];
	$metastore->name = $my_hash->items[0]->snippet->title;
	$metastore->author = $my_hash->items[0]->snippet->channelTitle;
	$metastore->publishedAt = $my_hash->items[0]->snippet->publishedAt;
	$metastore->definition = $my_hash->items[0]->contentDetails->definition;
	$metastore->duration = $my_hash->items[0]->contentDetails->duration;
	$metastore->viewCount = $my_hash->items[0]->statistics->viewCount;
	$metastore->likeCount = $my_hash->items[0]->statistics->likeCount;
	$metastore->dislikeCount = $my_hash->items[0]->statistics->dislikeCount;
	$metastore->commentCount = $my_hash->items[0]->statistics->commentCount;
	$metastore->latitude = $my_hash->items[0]->recordingDetails->location->latitude;
	$metastore->longitude = $my_hash->items[0]->recordingDetails->location->longitude;
	$GLOBALS['metastores'][] = $metastore;
}

class MetadataGetter {
	private $videoResults;

	function __construct( array $videoResults ) {
		$this->videoResults = $videoResults;
	}

	public function getaddress( $lat, $lon ) {
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lon).'&sensor=false';
		$jso = @file_get_contents($url);
		$data = json_decode($jso);
		$status = $data->status;
		if ( $status == "OK" )
			return $data->results[0]->formatted_address;
		else
			return false;
	}

	public function getAllMetadata() {
		$GLOBALS['metastores'] = array();
		$curl_options = array(
		    CURLOPT_SSL_VERIFYPEER => FALSE,
		    CURLOPT_SSL_VERIFYHOST => FALSE,
		    CURLOPT_USERAGENT, 'Parallel Curl google API request'
		);
		$parallel_curl = new ParallelCurl( count($this->videoResults), $curl_options );
		foreach ( $this->videoResults as $id ) {
			$metastore = new MetadataStore();
			$search_url = "https://www.googleapis.com/youtube/v3/videos?id=".$id.
				"&key=".API_KEY."&part=snippet,statistics,contentDetails,recordingDetails";
			$parallel_curl->startRequest( $search_url, 'on_request_done', array( 'id' => $id) );
		}
		$parallel_curl->finishAllRequests();
		return $GLOBALS['metastores'];
	}

}