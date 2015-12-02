<?php

class GPSDistance {

	private $metastores;

	public function __construct( array &$metastores ) {
		$this->metastores = $metastores;
	}

	private function greatCircleDistance( $firstLongitude, $firstLatitude, $secondLongitude, $secondLatitude ) {
		$o = acos(
				sin( $firstLatitude ) * sin( $secondLatitude ) +
				cos( $firstLatitude ) * cos( $secondLatitude ) * cos( $firstLongitude - $secondLongitude )
		);
		$r = 6371;
		$d = $o * $r;
		return $d;
	}

	public function compute( $checkbox, $weight, $GPS ) {
		if( !$checkbox || $GPS == null )
			return;
		$splitGPS = explode( ",", $GPS );
		if( count( $splitGPS ) != 2 )
			return;
		$userLongitude = floatval( trim( $splitGPS[ 0 ] ) );
		$userLatitude = floatval( trim( $splitGPS[ 1 ] ) );

		$max = 0;
		// compute distance
		foreach( $this->metastores as &$metastore ) {
			$videoLongitude = $metastore->longitude;
			$videoLatitude = $metastore->latitude;
			if( $videoLongitude == "" || $videoLatitude == "" )
				continue;
			$distance = $this->greatCircleDistance( $userLongitude, $userLatitude, $videoLongitude, $videoLatitude );
			$metastore->gpsDistance = $distance;
			if( $distance > $max )
				$max = $distance;
		}

		// normalize
		foreach( $this->metastores as &$metastore ) {
			if( $metastore->longitude == "" || $metastore->latitude == "" )
				continue;
			$metastore->gpsDistanceNormalized = ( 1 - ( $metastore->gpsDistance / $max ) ) * $weight;
		}
	}

}