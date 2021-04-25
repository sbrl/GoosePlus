<?php

/**
 * Get the actual absolute origin of the request sent by the user.
 * Lifted from Pepperminty Wiki, which took this from StackOverflow.
 * @package core
 * @param  array	$s						The $_SERVER variable contents. Defaults to $_SERVER.
 * @param  bool		$use_forwarded_host		Whether to utilise the X-Forwarded-Host header when calculating the actual origin.
 * @return string							The actual origin of the user's request.
 */
function url_origin( $s = false, $use_forwarded_host = false )
{
	global $env;
	if($s === false) $s = $_SERVER;
	$ssl      = $env->is_secure;
	$sp       = strtolower( $s['SERVER_PROTOCOL'] );
	$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
	$port     = $s['SERVER_PORT'];
	$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
	$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
	$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
	return $protocol . '://' . $host;
}

/**
 * Get the full url, as requested by the client.
 * @package core
 * @see		http://stackoverflow.com/a/8891890/1460422	This Stackoverflow answer.
 * @param	array	$s                  The $_SERVER variable. Defaults to $_SERVER.
 * @param	bool		$use_forwarded_host Whether to take the X-Forwarded-Host header into account.
 * @return	string						The full url, as requested by the client.
 */
function full_url($s = false, $use_forwarded_host = false) {
	if($s == false) $s = $_SERVER;
	return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}


function full_url_noquery($s = false, $use_forwarded_host = false) {
	$result = full_url($s, $use_forwarded_host);
	
	// Remove everything after and including the question mark
	$qloc = strpos($result, "?");
	if($qloc !== false) $result = substr($result, 0, $qloc);
	
	return $result;
}
