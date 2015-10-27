<?php

include_once "MetadataStore.php";

class MetadataGetter {
	private $videoResults;

	function __construct( array $videoResults ) {
		$this->videoResults = $videoResults;
	}

	private function get_remote_data( $url ) {
		return file_get_contents( $url );
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


	public function startIteration() {
		reset($videoResults);
	}

	public function nextElem() {
		$returnObject = new MetadataStore();
		$id = next($this->videoResults);
		if ( $id == null )
			return null;
		
		$json = $this->get_remote_data("https://www.googleapis.com/youtube/v3/videos?id=".$id.
			"&key=".API_KEY."&part=snippet,statistics,contentDetails,recordingDetails");
		$my_hash = json_decode($json); 
		
		$returnObject->id = $id;
		$returnObject->name = $my_hash->items[0]->snippet->title;
		$returnObject->author = $my_hash->items[0]->snippet->channelTitle;
		$returnObject->publishedAt = $my_hash->items[0]->snippet->publishedAt;
		$returnObject->definition = $my_hash->items[0]->contentDetails->definition;
		$returnObject->viewCount = $my_hash->items[0]->statistics->viewCount;
		$returnObject->likeCount = $my_hash->items[0]->statistics->likeCount;
		$returnObject->dislikeCount = $my_hash->items[0]->statistics->dislikeCount;
		$returnObject->commentCount = $my_hash->items[0]->statistics->commentCount;
		$returnObject->latitude = $my_hash->items[0]->recordingDetails->location->latitude;
		$returnObject->longitude = $my_hash->items[0]->recordingDetails->location->longitude;
//		$returnObject->address = $this->getaddress($latitude, $longitude);

		return $returnObject;
	}	

	public function prevElem() {
		$id = prev($videoResults);
	}

}