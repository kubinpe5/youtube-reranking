<?php

class ViewsDistance {

	private $metastores;

	public function __construct( array &$metastores ) {
		$this->metastores = $metastores;
	}

	public function compute( $checkbox, $weight, $userViews ) {
		if( !$checkbox || $userViews == null )
			return;
		$max = 0;
		// compute distance
		foreach( $this->metastores as &$metastore ) {
			$videoViews = $metastore->viewCount;
			$distance = abs( $userViews - $videoViews );
			$metastore->viewsDistance = $distance;
			if( $distance > $max ) $max = $distance;
		}

		// normalize
		foreach( $this->metastores as &$metastore )
			$metastore->viewsDistanceNormalized = ( 1 - ( $metastore->viewsDistance / $max ) ) * $weight;
	}

}