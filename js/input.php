<?php

// vars
$expires_offset = 31536000; // 1 year
$out = '';
$prefix = 'input/';
$files = array(
	'actions.js',
	'color-picker.js',
	'date-picker.js',
	'file.js',
	'image.js',
	'relationship.js',
	'tab.js',
	'validation.js',
	'wysiwyg.js'
);


// helpers
function get_file($path)
{
	if ( function_exists('realpath') )
		$path = realpath($path);

	if ( ! $path || ! @is_file($path) )
		return '';

	return @file_get_contents($path);
}

if( $files )
{
	foreach( $files as $file ) {
		$out .= get_file( $prefix . $file ) . "\n";
	}
}


// set headers to return a JS file
header('Content-Type: application/x-javascript; charset=UTF-8');
header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
header("Cache-Control: public, max-age=$expires_offset");

echo $out;
exit;

?>