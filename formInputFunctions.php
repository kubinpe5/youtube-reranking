<?php

function handleTextInput( $input ) {
	if( $input === null || $input === '' )
		return null;
	return htmlspecialchars( $input );
}

function handleIntegerInput( $input ) {
	if( $input === null || $input === '' )
		return null;
	if( is_numeric( $input ) ) 
		return intval( $input );
	else
		throw new Exception("Not numeric input. You hacker!", 1);
}

function handleCheckboxInput( $input ) {
	return $input === 'on';
}

function handleGpsInput( $input ) {
	// TODO
	return handleTextInput( $input );
}

function handleDateInput( $input ) {
	if( $input === null || $input === '' )
		return null;
	return new \DateTime( $input );
}