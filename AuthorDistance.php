<?php

class AuthorDistance {
	
	private $metastores;

	public function __construct( array &$metastores ) {
		$this->metastores = $metastores;
	}

	public function levenshteinDistance( $user_string, $meta_string ) {
		$m = strlen($user_string);
		$n = strlen($meta_string);

		for( $i = 0; $i <= $m; $i++ ) 
			$distance[$i][0] = $i;
		for( $j = 0; $j <= $n; $j++ ) 
			$distance[0][$j] = $j;

		for( $i = 1; $i <= $m; $i++ ) {
			for( $j = 1 ; $j <= $n; $j++ ) {
				$increment = ( $user_string[$i-1] == $meta_string[$j-1] ) ? 0 : 1;
				$distance[$i][$j] = min( $distance[$i-1][$j] + 1, $distance[$i][$j-1] + 1, $distance[$i-1][$j-1] + $increment);
			}
		}
		if( $distance[$m][$n] > $max ) $max = $distance[$m][$n];
		return $distance[$m][$n];
	}

	public function compute( $checkbox, $weight, $author) {
		if ( !checkbox || $author == null ) 
			return;
		$max = PHP_INT_MIN;
		// take each metadata author and compute distance with the string from input
		foreach( $this->metastores as $metastore ) {
			// actual computing the distance
			$distance = $this->levenshteinDistance( $author, $metastore->author );
			$metastore->authorDistance = $distance;
			if( $distance > $max ) $max = $distance;
		}
		// normalize
		foreach( $this->metastores as &$metastore )
			$metastore->authorDistanceNormalized = ( 1 - ( $metastore->authorDistance / $max ) ) * $weight;
	}
}