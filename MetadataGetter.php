<?php

include_once "MetadataStore.php";

/**
* 
*/
class MetadataGetter {
	private $videoResults;

	function __construct( array $videoResults ) {
		$this->videoResults = $videoResults;
	}

	private
function get_remote_data($url, $post_paramtrs = false)
{
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	if ($post_paramtrs) {
		curl_setopt($c, CURLOPT_POST, TRUE);
		curl_setopt($c, CURLOPT_POSTFIELDS, "var1=bla&" . $post_paramtrs);
	}

	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
	curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
	curl_setopt($c, CURLOPT_MAXREDIRS, 10);
	$follow_allowed = (ini_get('open_basedir') || ini_get('safe_mode')) ? false : true;
	if ($follow_allowed) {
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
	}

	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 9);
	curl_setopt($c, CURLOPT_REFERER, $url);
	curl_setopt($c, CURLOPT_TIMEOUT, 60);
	curl_setopt($c, CURLOPT_AUTOREFERER, true);
	curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
	$data = curl_exec($c);
	$status = curl_getinfo($c);
	curl_close($c);
	preg_match('/(http(|s)):\/\/(.*?)\/(.*\/|)/si', $status['url'], $link);
	$data = preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/|\/)).*?)(\'|\")/si', '$1=$2' . $link[0] . '$3$4$5', $data);
	$data = preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/)).*?)(\'|\")/si', '$1=$2' . $link[1] . '://' . $link[3] . '$3$4$5', $data);
	if ($status['http_code'] == 200) {
		return $data;
	}
	elseif ($status['http_code'] == 301 || $status['http_code'] == 302) {
		if (!$follow_allowed) {
			if (empty($redirURL)) {
				if (!empty($status['redirect_url'])) {
					$redirURL = $status['redirect_url'];
				}
			}

			if (empty($redirURL)) {
				preg_match('/(Location:|URI:)(.*?)(\r|\n)/si', $data, $m);
				if (!empty($m[2])) {
					$redirURL = $m[2];
				}
			}

			if (empty($redirURL)) {
				preg_match('/href\=\"(.*?)\"(.*?)here\<\/a\>/si', $data, $m);
				if (!empty($m[1])) {
					$redirURL = $m[1];
				}
			}

			if (!empty($redirURL)) {
				$t = debug_backtrace();
				return call_user_func($t[0]["function"], trim($redirURL) , $post_paramtrs);
			}
		}
	}

	return "ERRORCODE22 with $url!!<br/>Last status codes<b/>:" . json_encode($status) . "<br/><br/>Last data got<br/>:$data";
}
	public function getaddress($lat,$lon) {
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
		if ( $id == null ) {
			return null;
		}
		
		$json = $this->get_remote_data("https://www.googleapis.com/youtube/v3/videos?id=".$id.
			"&key=".API_KEY."&part=snippet,statistics,contentDetails,recordingDetails");
		$my_hash = json_decode($json); 
		
		$returnObject->name = $my_hash->items[0]->snippet->title;
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