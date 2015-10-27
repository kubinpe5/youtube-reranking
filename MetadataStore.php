<?php

class MetadataStore {
	public $name;
	public $viewCount;
	public $likeCount;
	public $dislikeCount;
	public $definition;
	public $commentCount;
	public $latitude;
	public $longitude;
	public $address;
	public $publishedAt;

	public function __toString() {
		$ret = "";
		$ret .= "Name = ".$this->name.".<br>";
		$ret .= "Publikováno = ".$this->publishedAt.".<br>";
		$ret .= "Rozlišení = ".$this->definition.".<br>";
		$ret .= "latitude = ".$this->latitude.".<br>";
		$ret .= "longitude = ".$this->longitude.".<br>";
		if($address)
			$ret .= "Adresa je: ".$this->address.".<br>";
		else
			$ret .= "Adresa není k dispozici.";
		$ret .= "Počet shlédnutí je ".$this->viewCount.".<br>";
		$ret .= "Počet like je ".$this->likeCount.".<br>";
		$ret .= "Počet dislike je ".$this->dislikeCount.".<br>";
		$ret .= "Počet komentů je ".$this->commentCount.".<br><br>";

		return $ret;
	}
}