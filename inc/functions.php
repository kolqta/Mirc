<?php

function sendHeader() {
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: *");
	header("Access-Control-Allow-Headers: Accept");
	
	header("Content-Security-Policy: default-src 'self'");
	// header("style-src: 'self' 'unsafe-inline'");
	// header("; font-src 'self' data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' localhost");
}
function sendJson( $data = '' ) {
	sendHeader();
	header('Content-Type: application/json; charset=utf-8');
	die($data);
}

function clearStr($str = '') {
	return str_replace(array(':', '/', '*', '"', ""), '', $str);
}

function containsWord($str, $word) {
    return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $str);
}

// get_defined_vars()
// get_defined_functions()
// get_defined_constants()
// get_included_files()
function p ( $a, $s = 0 ) {		  
$bt = debug_backtrace();
  $caller = array_shift($bt);

  echo '<br />'.$caller['file'].'<vr />';
  echo 'line:'.$caller['line'];
	echo '<pre>';
	print_r($a);
	echo '</pre>';
	if($s) exit;
}