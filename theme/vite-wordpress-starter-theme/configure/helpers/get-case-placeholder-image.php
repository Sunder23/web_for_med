<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Placeholder picture for a case without an image ("Case file is rendering").
 * Used by the single case intro, the cases archive slider and blog cards without a featured image.
 *
 * @return array{url:string,width:int,height:int} Static image URL and its size.
 */
function starter_get_case_placeholder_image() {
	return array(
		'url'    => starter_get_static_dir() . '/images/case-placeholder.webp',
		'width'  => 783,
		'height' => 573,
	);
}
