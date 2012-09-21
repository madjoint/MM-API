<?php

// Resize image to max_x or max_y to target file with specified quality
function resizeToFile($sourcefile, $dest_x, $dest_y, $targetfile, $jpegqual) {


	/* Get the dimensions of the source picture */
	$picsize=getimagesize("$sourcefile");

	$source_x = $picsize[0];
	$source_y  = $picsize[1];

	$source_ratio = $source_x/$source_y;

	if($source_ratio > 1) {
		$dest_y = round($dest_x / $source_ratio);
	} else {
		$dest_x = round($dest_y * $source_ratio);
	}

	// echo($source_x.'x'.$source_y.'-'.$source_ratio.'-'.$dest_x.'x'.$dest_y.'-');

	$source_id = imageCreateFromJPEG($sourcefile);

	/* Create a new image object (not neccessarily true colour) */

	//$target_id=imagecreate($dest_x, $dest_y);
	$target_id=imagecreatetruecolor($dest_x, $dest_y);

	// use createtruecolor with gd 2.x

	// use copyresampled with gd 2.x and imagecopyresized
	$target_pic=imagecopyresampled($target_id,$source_id,
	0,0,0,0,
	$dest_x,$dest_y,
	$source_x,$source_y);

	/* Create a jpeg with the quality of "$jpegqual" out of the
	 image object "$target_pic".
	 This will be saved as $targetfile */

	imagejpeg($target_id,$targetfile,$jpegqual);

	return true;

}


?>