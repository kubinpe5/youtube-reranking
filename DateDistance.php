<?php

class DateDistance {

	private $metastores;

	public function __construct( array &$metastores ) {
		$this->metastores = $metastores;
	}

	public function compute( $checkbox, $weight, $userDate ) {
		if( !$checkbox || $userDate == null )
			return;
		$userDate = new \Datetime( $userDate );
		$max = 0;
		// compute distance
		foreach( $this->metastores as &$metastore ) {
			$videoDate = new \Datetime( $metastore->publishedAt );
			$distance = abs( $userDate->getTimestamp() - $videoDate->getTimestamp() );
			$metastore->dateDistance = $distance;
			if( $distance > $max ) $max = $distance;
		}

		// normalize
		foreach( $this->metastores as &$metastore )
			$metastore->dateDistanceNormalized = ( 1 - ( $metastore->dateDistance / $max ) ) * $weight;
	}

}