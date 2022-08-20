<?php
/**
 * Concatenate block.json block files into one blocks.json file.
 *
 * @since 1.0.0
 */

$dir      = dirname( __FILE__, 2 ) . '/src/blocks';
$iterator = new FilesystemIterator( $dir, FilesystemIterator::SKIP_DOTS );
$blocks   = array();

foreach ( $iterator as $item ) {
	if ( ! file_exists( $item->getPathname() . '/block.json' ) ) {
		continue;
	}

	$metada     = json_decode( file_get_contents( $item->getPathname() . '/block.json' ) );
	$schema_key = '$schema';
	unset( $metada->{$schema_key} );

	$blocks[] = $metada;
}

/*
 * Get Minifier.
 *
 * Credits: @getify on GitHub
 * @see https://github.com/getify/JSON.minify.
 */
require_once dirname( __FILE__ ) . '/JSON.minify/minify.json.php';

$blocks_json = json_encode( $blocks );
$json        = json_minify( $blocks_json );

if ( false !== file_put_contents( dirname( __FILE__, 2 ) . '/bp-attachments/assets/blocks/blocks.json', $json ) ) {
	echo "\n" . count( $blocks) . " blocks were added to blocks.json successfully.\n";
} else {
	echo "\nOuch there was a problem concatenating block.json files.\n";
}
