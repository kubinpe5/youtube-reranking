<?php

class AuthorDistance {
	
	private $metastores;

	public function __construct( array &$metastores ) {
		$this->metastores = $metastores;
	}

	public function compute( $checkbox, $weight, $author) {
		if ( !checkbox || $author == null ) 
			return;
		$author_array = str_split($author);
		$max = 0;
		// take each metadata author and compute distance with the string from input
		foreach( $this->metastores as $metastore ) {
			$meta_author_array = str_split($metastore->author);
			// actual computing the distance
//----------------------------------------------------------------------------------------------------------------
			function lev($s,$t) {
				$m = strlen($s);
				$n = strlen($t);										// todo
	
				for($i=0;$i<=$m;$i++) $d[$i][0] = $i;
				for($j=0;$j<=$n;$j++) $d[0][$j] = $j;
	
				for($i=1;$i<=$m;$i++) {
					for($j=1;$j<=$n;$j++) {
						$c = ($s[$i-1] == $t[$j-1])?0:1;
						$d[$i][$j] = min($d[$i-1][$j]+1,$d[$i][$j-1]+1,$d[$i-1][$j-1]+$c);
					}
				}
				if( $d[$m][$n] > $max ) $max = $d[$m][$n];
				return $d[$m][$n];
			}
//----------------------------------------------------------------------------------------------------------------

		}

	}
		// normalize 		// todo
		foreach( $this->metastores as &$metastore )
			$metastore->dateDistanceNormalized = ( 1 - ( $metastore->dateDistance / $max ) ) * $weight;
}