<?php

class UrlForvarder {

    /**
     * Constructor
     */
    public function __construct( ) {
    
    }

    /**
     * Validates the URL.
     *
     * @param url given url to validate
	   *
     * @return false if the URL is valid youtube video.
     * @return true if the URL is not valid youtube video.
     */
    private function validateUrl( $url ) {
		    $pattern = '/^(?:http:\/\/|https:\/\/)?(www\.)?(youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){6,11})(\S*)?$/';
		    return !preg_match($pattern, $url);
      	    return false;
    }

    /**
     * Gets ID of video from given URL.
     *
     * @param url
     *
     * @throws Exception( 'The final URL is not valid youtube video!' )
     *
     * @return urlId
     */
    private function getId( $url ) {
    	  if( $this->validateUrl( $url ) )
			  throw new Exception( 'The final URL is not valid youtube video!' );
    	  $pom = explode( 'v=', $url );
    	  $result = explode( '&', $pom[1] );
		    return $result[0];
    }

    /**
	   * Some URLs leads to redirect page, this function finds the final URL.
	   *
	   * @param url given url
	   * 
	   * @throws Exception( 'URL not found!' )
	   * @throws Exception( 'Problem detected! Most probably resource was not found or there is a loop inside redirects.' )
	   *
	   * @return finalUrl
	   */
	  private function redirect( $url ) {
  		  $headers = get_headers( $url, 1 );
  		  if( $headers == false ) throw new Exception( 'URL not found!' );
		    if( !array_key_exists( 'Location', $headers ) ) {
			      if( explode( ' ', $headers[0] )[1] == '200' ) return $url;
  			    throw new Exception( 'Problem detected! Most probably resource was not found.' );
  		  } else if( !is_array( $headers[ 'Location' ] ) )
  			    return $headers[ 'Location' ];
  		  else
  			    return end( $headers[ 'Location' ] );
	  }
}