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
	public $commentCount;
	public $latitude;
	public $longitude;
	public $publishedAt;

	public $dateDistance = 0;
	public $dateDistanceNormalized = 0;
	public $viewsDistance = 0;
	public $viewsDistanceNormalized = 0;

	public function __toString() {
		$ret = "";
		/*
		$ret .= "Id = ".$this->id."<br>";
		$ret .= "Name = ".$this->name.".<br>";
		$ret .= "Author = ".$this->author.".<br>";
		$ret .= "Publikováno = ".$this->publishedAt.".<br>";
		$ret .= "Rozlišení = ".$this->definition.".<br>";
		$ret .= "latitude = ".$this->latitude.".<br>";
		$ret .= "longitude = ".$this->longitude.".<br>";
		$ret .= "Počet shlédnutí je ".$this->viewCount.".<br>";
		$ret .= "Počet like je ".$this->likeCount.".<br>";
		$ret .= "Počet dislike je ".$this->dislikeCount.".<br>";
		$ret .= "Počet komentů je ".$this->commentCount.".<br>";
		*/
		$ret .= "Publikováno ".( (new \Datetime($this->publishedAt))->format('d. m. Y') )." (výpis jen dne)<br>";
		$ret .= "Počet shlédnutí ".( $this->viewCount )." <br>";
		$ret .= "<hr>";
		$ret .= "Normalizovaná vzdálenost jmen autorů = ".$this->authorDistanceNormalized."<br></br>";
		$ret .= "Normalizovaná vzdálenost datumu = ".$this->dateDistanceNormalized."<br>";
		$ret .= "Normalizovaná vzdálenost počtu shlédnutí = ".$this->viewsDistanceNormalized."<br></br>";
		return $ret;
	}

	public function setOldRank( $rank ) {
		$this->oldRank = $rank;
		return $this;
	}

	public function getOldRank(  ) {
		return $this->oldRank;
	}

}