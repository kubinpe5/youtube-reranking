<?php

class MetadataStore {

	private $oldRank;

	public $id;
	public $name;

	public $author;
	public $viewCount;
	public $likeCount;
	public $dislikeCount;
	public $definition;
	public $duration;
	public $commentCount;
	public $latitude;
	public $longitude;
	public $publishedAt;

	public $authorDistance = 0;
	public $authorDistanceNormalized = 0;
	public $dateDistance = 0;
	public $dateDistanceNormalized = 0;
	public $viewsDistance = 0;
	public $viewsDistanceNormalized = 0;
	public $gpsDistance = 0;
	public $gpsDistanceNormalized = 0;

	public $rerankResult = 0;

	public function __toString() {
		$ret = "<dl class=\"videoInformation\">";
		$ret .= "<dt>Název videa<dt>";
		$ret .= "<dd>" . $this->name . "</dd>";
		$ret .= "<dt>Jméno autora<dt>";
		$ret .= "<dd>" . $this->author . "</dd>";
		$ret .= "<dt>Publikováno<dt>";
		$ret .= "<dd>" . ( ( new \Datetime( $this->publishedAt ) )->format( 'd. m. Y' ) ) . "</dd>";
		$ret .= "<dt>Počet shlédnutí<dt>";
		$ret .= "<dd>" . $this->viewCount . "</dd>";
		$ret .= "<dt>GPS souřadnice<dt>";
		if( $this->longitude == "" || $this->latitude == "" )
			$ret .= "<dd>CHYBÍ</dd>";
		else
			$ret .= "<dd>" . $this->longitude . ", " . $this->latitude . "</dd>";
		$ret .= "</dl>";
		$ret .= "<table class=\"rerankingResults\">";
		$ret .= "<caption>Výsledky rerankingu</caption>";
		$ret .= "<thead>";
		$ret .= "<tr>";
		$ret .= "<th>Položka</th>";
		$ret .= "<th>Hodnota</th>";
		$ret .= "</tr>";
		$ret .= "</thead>";
		$ret .= "<tfoot>";
		$ret .= "<tr>";
		$ret .= "<th>Celkový výsledek k přerankování</th>";
		$ret .= "<th>$this->rerankResult</th>";
		$ret .= "</tr>";
		$ret .= "</tfoot>";
		$ret .= "<tbody>";
		$ret .= "<tr><td>Normalizovaná vzdálenost jmen autorů</td><td>" . $this->authorDistanceNormalized . "</td></tr>";
		$ret .= "<tr><td>Normalizovaná vzdálenost datumu</td><td>" . $this->dateDistanceNormalized . "</td></tr>";
		$ret .= "<tr><td>Normalizovaná vzdálenost počtu shlédnutí</td><td>" . $this->viewsDistanceNormalized . "</td></tr>";
		$ret .= "<tr><td>Normalizovaná vzdálenost GPS souřadnic</td><td>" . $this->gpsDistanceNormalized . "</td></tr>";
		$ret .= "</tbody>";
		$ret .= "</table>";
		return $ret;
	}

	public function setOldRank( $rank ) {
		$this->oldRank = $rank;
		return $this;
	}

	public function getOldRank() {
		return $this->oldRank;
	}

	public function computeRerankResult() {
		$this->rerankResult =
			$this->authorDistanceNormalized +
			$this->dateDistanceNormalized +
			$this->viewsDistanceNormalized +
			$this->gpsDistanceNormalized;
	}

}