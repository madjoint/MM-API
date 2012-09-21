<?php

// include('ca/ca_config.php');

error_reporting(NULL);

$CONF["IMAGE_ENABLE_PROCESSOR:GD"] = 1;
$CONF["IMAGE_PROCESSOR:GD2"] = 1;
$CONF["IMAGE_DEFAULT_DIRECTORY"] = "cache/pix";
$CONF["IMAGE_PROCESSOR_DEBUG_MODE"] = 0;
$CONF["IMAGE_FORCE_CONSTRAIN_PROPORTIONS"] = 1;
$CONF["IMAGE_PROCESS_MODE"] = "crop";
$CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"] = 1.0;
$CONF["IMAGE_MAX_FILE_SIZE"] = 4777777;
$CONF["IMAGE_HEADER_STRING"] = "Content-type: image/jpeg";
$CONF["IMAGE_NOFILE_DEFAULT_FILE"] = 'no'.rand(1,62).'.jpg';
$CONF["IMAGE_CACHE_PROCESSED"] = 1;
$CONF["IMAGE_CACHE_DISPLAY:USE_FORWARD"] = 1;
$CONF["IMAGE_USE_STAMP_TEXT"] = 0;
$CONF["IMAGE_STAMP_TEXT"] = "mmatcher.com";
$CONF["IMAGE_STAMP_TEXT_COLOR"] = "0,0,0";
$CONF["IMAGE_STAMP_TEXT_SIZE"] = 10;
$CONF["IMAGE_STAMP_TEXT_LOCATION_Y"] = "top";
$CONF["IMAGE_STAMP_TEXT_LOCATION_X"] = "left";
$CONF["IMAGE_STAMP_TEXT_PADDING_Y"] = 5;
$CONF["IMAGE_STAMP_TEXT_PADDING_X"] = 5;
$CONF["IMAGE_STAMP_TEXT_DROPHILIGHT"] = 0;
$CONF["IMAGE_STAMP_TEXT_DROPHILIGHT_DEPHASE"] = 1;
$CONF["IMAGE_STAMP_TEXT_DROPHILIGHT_COLOR"] = "255,255,255";
$CONF["IMAGE_QUALITY"] = 95;
$CONF["IMAGE_MAX_WIDTH"] = 600;
$CONF["IMAGE_THUMBNAILS_SIZE"] = 128;
$CONF["IMAGE_STAMP_MINWIDTH"] = 100;
$CONF["IMAGE_USE_WATERMARK"] = 0;
$CONF["IMAGE_WATERMARK_FILE"] = "";
$CONF["IMAGE_WATERMARK_PADDING"] = 10;
$CONF["IMAGE_WATERMARK_RESIZE_FACTOR"] = 40;
$CONF["IMAGE_WATERMARK_MINWIDTH"] = 100;
$CONF["IMAGE_WATERMARK_BLEND_VISIBILITY"] = 80;
$CONF["PICTURES_ALLOWED_EXTENTIONS"] = "jpg,jpeg,png";

$GLOBALS["USE_NOPICTURE"] = true;

if (isset($_GET["file"])) {

	/*
	 We will also prevent "slashes" in the filename
	 as a protection (so they can't use the ../ call
	 to force this to climb up in the directory tree
	 */
	if (!strpos($_GET["file"], "/")) {
		/*
		 Check the file name extention gainst the
		 allowed list. We won't work with unallowed
		 stuff
		 */
		$fileNameChunks = explode(".", $_GET["file"]);
		if (in_array($fileNameChunks[count($fileNameChunks)-1], explode(",", $CONF["PICTURES_ALLOWED_EXTENTIONS"]))) {
			/*
			 We will take a look out and see if the
			 file exists
			 */
			$fn = $_GET['file'];
			if (is_file("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fn[0]}/{$fn[1]}/{$_GET["file"]}")) {
				/*
				 Don't process pictures that could cause potential
				 errors because of their size (max allocation.e)
				 */
				if (filesize("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fn[0]}/{$fn[1]}/{$_GET["file"]}") < $CONF["IMAGE_MAX_FILE_SIZE"]) {
					/*
					 We're over all the checks, let's set the following
					 variable so the processor don't take the "nopicture"
					 file.
					 */
					$GLOBALS["USE_NOPICTURE"] = false;

				}
			}
		}
	}
}


/*
 Set the $fileName variable for future use
 */
if ($GLOBALS["USE_NOPICTURE"]) $fileName = $CONF["IMAGE_NOFILE_DEFAULT_FILE"];
else $fileName = $_GET["file"];

// PICTURE WIDTH VARIABLE //////////////////////////////////////////////////////
/*
 Now we will check if a picture size has
 been passed. If not, we will consider
 that we must process a thumbnail
 */
if (isset($_GET["width"]) and is_numeric($_GET["width"]) and $_GET["width"] > 0) {

	/*
	 We won't process a wallpaper-sized picture,
	 let's make sure we don't go over the max width
	 setting
	 */
	if ($_GET["width"] < $CONF["IMAGE_MAX_WIDTH"])
	$newWidth = $_GET["width"];

	else $newWidth = $CONF["IMAGE_MAX_WIDTH"];

}

/*
 Ok so no width? Let's make a thumbnail!
 */
else {
	// rok.krulec@ if no width, maybe there's height, if not make thumbnail
	if(!isset($_GET['height']))
	$newWidth = $CONF["IMAGE_THUMBNAILS_SIZE"];
}


if (isset($_GET["height"]) and is_numeric($_GET["height"]) and $_GET["height"] > 0) {

	/*
	 We won't process a wallpaper-sized picture,
	 let's make sure we don't go over the max height
	 setting
	 */
	if ($_GET["height"] < $CONF["IMAGE_MAX_WIDTH"])
	$newHeight = $_GET["height"];

	else $newHeight = $CONF["IMAGE_MAX_WIDTH"];

}

// LOAD PICTURE FILE CONTENT & STREAM CACHE ////////////////////////////////////
/*
 Let's first see if we've got a cached version
 and if we're configured to use the cache.
 */

// rok.krulec@ debug mode - turn off in production
//        $CONF["IMAGE_CACHE_PROCESSED"] = False;

// rok.krulec@ - added mode_suffix
$mode_suffix = '';
if(isset($_GET['mode'])) {
	if($_GET['mode'] == 'resize') {
		$mode_suffix = '_resize';
	}
}

if(isset($_GET['watermark'])) {
	$CONF["IMAGE_USE_WATERMARK"] = True;

	$CONF["IMAGE_WATERMARK_FILE"] = 'watermark.png';
	switch($_GET['watermark']) {
		case 'flower': $CONF["IMAGE_WATERMARK_FILE"] = 'watermark_flower.png'; break;
		case 'lower_right': $CONF["IMAGE_WATERMARK_FILE"] = 'watermark_lower_right.png'; break;
		case 'upper_left': $CONF["IMAGE_WATERMARK_FILE"] = 'watermark_upper_left.png'; break;
		default: $CONF["IMAGE_WATERMARK_FILE"] = 'watermark.png'; $_GET['watermark'] = 'watermark';
	}
}

if ($CONF["IMAGE_CACHE_PROCESSED"] && !isset($_GET['process'])) {
	if(isset($newWidth) && isset($newHeight)) {
		$suffix = $mode_suffix.'_WH';
	};
	if(isset($newWidth) && !isset($newHeight)) {
		$suffix = $mode_suffix.'_W';
	};
	if(!isset($newWidth) && isset($newHeight)) {
		$suffix = $mode_suffix.'_H';
	};
	if(!isset($newWidth) && !isset($newHeight)) {
		$suffix = $mode_suffix.'';
	};
	if(isset($_GET['watermark']))
	$suffix .= '_'.$_GET['watermark'];

	/*
	 We will now check if a cache of the called image exists.
	 */
	if(is_file("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/CACHE_{$fileName}{$suffix}".$newWidth.".jpg")) {
		/*
		 Configuration says: forward the user to the picture
		 if we've got a cache! Let's do it...
		 */
		if ($CONF["IMAGE_CACHE_DISPLAY:USE_FORWARD"]) {
			header("Location: ".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/CACHE_{$fileName}{$suffix}".$newWidth.".jpg");
			/*
			 Don't go any further, we're done.
			 */
			die('');
		}

		/*
		 We've been instructed to STREAM the file instead
		 of forwarding the user to it... how about doing it?
		 */
		else {
			header($CONF["IMAGE_HEADER_STRING"]);
			echo file_get_contents("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/CACHE_{$fileName}{$suffix}".$newWidth.".jpg");
			/*
			 Don't go any further, we're done.
			 */
			die('');
		}
	}

	/*
	 There was no cache file, we will have to process
	 the original
	 */
	else $fileBuffer = file_get_contents("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");

}

/*
 We've been instructed not to use cache ... We will buffer
 the original image
 */
$fileBuffer = file_get_contents("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");

/*
 If the processor is disabled, we will just stream what
 we buffered...
 */
if (!$CONF["IMAGE_ENABLE_PROCESSOR:GD"]) {
	header($CONF["IMAGE_HEADER_STRING"]);
	echo $fileBuffer;
	/*
	 Don't go any further, we're done.
	 */
	die();
}

// PROCESSOR ///////////////////////////////////////////////////////////////////
/*
 Load the original file into an image handle
 */
switch(substr($fileName, strrpos($fileName, ".")+1, strlen($fileName))) {
	/*
	 Jpeg Image
	 */
	case("jpg"): case("jpeg"):
		$handle = imagecreatefromjpeg("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");
		break;

		/*
		 GIF image, note that we got two methods for loading
		 it here, one using the GD1 engine, one with GD2.
		 */
	case("gif"):
		$tempHandle = imagecreatefromgif("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");

		if ($CONF["IMAGE_PROCESSOR:GD2"]) $handle = imagecreatetruecolor(imagesx($tempHandle), imagesy($tempHandle));
		else $handle = imagecreate($newWidth, $processHeight);

		imagecopy($handle, $tempHandle, 0, 0, 0, 0, imagesx($tempHandle), imagesy($tempHandle));
		break;

		/*
		 Png image
		 */
	case("png"):
		$handle = imagecreatefrompng("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");
		break;
}

// BEGIN rok.krulec@
if(isset($_GET['mode']))
if($_GET['mode'] == 'resize')
$CONF["IMAGE_PROCESS_MODE"] = 'resize';
// END rok.krulec@

/* Process to crop, fill or resize */
switch($CONF["IMAGE_PROCESS_MODE"]) {

	case("resize"):
		/*
		 In resize mode, we will only find the source picture biggest size axis (W/H) and
		 resize that max value to the required processed size, the other axix is resized
		 acordingly
		 */
		list($sourceWidth, $sourceHeight) = getimagesize("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");

		if ($sourceWidth >= $sourceHeight)
		$ratio = $newWidth / $sourceWidth;
		else
		$ratio = $newWidth / $sourceHeight;

		$thumb = imagecreatetruecolor(round($sourceWidth * $ratio), round($sourceHeight * $ratio));

		imagecopyresampled(
		$thumb,
		$handle,
		0,
		0,
		0,
		0,
		round($sourceWidth * $ratio),
		round($sourceHeight * $ratio),
		$sourceWidth,
		$sourceHeight
		);

		break;

	case("crop"): default:
		/*
		 The crop mode picks the lowest source axis as its reference,
		 the second axis is centered and cropped at constrained proportions
		 values
		 */

		// BEGIN rok.krulec@
		// we gat height
		if($newHeight) {

			$thumb = imagecreatetruecolor(
			$newWidth = round($newHeight * $CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"]),
			$newHeight
			);

			list($sourceWidth, $sourceHeight) = getimagesize("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");

			if ($sourceWidth >= $sourceHeight) {

				$intraSourceWidth = round($sourceHeight * $CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"]);

				imagecopyresampled(
				$thumb, // destination image
				$handle, // source image
				0, // destination x
				0, // destination y
				($sourceWidth / 2) - ($intraSourceWidth / 2), // source x
				0, // source y
				$newWidth,  // destionation width
				$newHeight, // destionation height
				$intraSourceWidth, // source width
				$sourceHeight // source height
				);

			}

			else {

				$intraSourceHeight = round($sourceWidth / $CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"]);

				imagecopyresampled(
				$thumb,
				$handle,
				0,
				0,
				0,
				($sourceHeight / 2) - ($intraSourceHeight / 2),
				$newWidth,
				$newHeight,
				$sourceWidth,
				$intraSourceHeight
				);
			}
			// we gat width
		} else {
			$thumb = imagecreatetruecolor(
			$newWidth,
			$newHeight = round($newWidth * $CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"])
			);

			list($sourceWidth, $sourceHeight) = getimagesize("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");

			if ($sourceWidth >= $sourceHeight) {

				$intraSourceWidth = round($sourceHeight / $CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"]);

				imagecopyresampled(
				$thumb,
				$handle,
				0,
				0,
				($sourceWidth / 2) - ($intraSourceWidth / 2),
				0,
				$newWidth,
				$newHeight,
				$intraSourceWidth,
				$sourceHeight
				);

			}

			else {

				$intraSourceHeight = round($sourceWidth * $CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"]);

				imagecopyresampled(
				$thumb,
				$handle,
				0,
				0,
				0,
				($sourceHeight / 2) - ($intraSourceHeight / 2),
				$newWidth,
				$newHeight,
				$sourceWidth,
				$intraSourceHeight
				);
			}
		}
		// END rok.krulec@
		break;

	case("fill"):
		/*
		 Fill mode resizes the original picture according to its
		 highest axis to fit the thumbnail size. The lower axis
		 is then used to center the result into the thumbnail
		 */
		$thumb = imagecreatetruecolor(
		$newWidth,
		$newHeight = round($newWidth * $CONF["IMAGE_CONSTRAIN_PROPORTIONS_ASPECT_RATIO"])
		);

		list($sourceWidth, $sourceHeight) = getimagesize("".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/{$fileName}");

		if ($sourceWidth >= $sourceHeight) {
			$ratio = $newWidth / $sourceWidth;

			$intraSourceWidth = $newWidth;
			$intraSourceHeight = $sourceHeight * $ratio;

			imagecopyresampled(
			$thumb,
			$handle,
			0,
			($newHeight / 2) - ($intraSourceHeight / 2),
			0,
			0,
			$newWidth,
			ceil($intraSourceHeight),
			$sourceWidth,
			$sourceHeight
			);
		}

		else {
			$ratio = $newHeight / $sourceHeight;

			$intraSourceHeight = $newHeight;
			$intraSourceWidth = $sourceWidth * $ratio;

			imagecopyresampled(
			$thumb,
			$handle,
			($newWidth / 2) - ($intraSourceWidth / 2),
			0,
			0,
			0,
			ceil($intraSourceWidth),
			$newHeight,
			$sourceWidth,
			$sourceHeight
			);
		}

		break;
}

// IMAGE STAMP /////////////////////////////////////////////////////////////////
if (($CONF["IMAGE_USE_STAMP_TEXT"]) && ($CONF["IMAGE_STAMP_TEXT"] != "")) {

	/*
	 Lets just make sure this thumbnail is big enough to welcome
	 the stamp text.
	 */
	if ($newWidth >= $CONF["IMAGE_STAMP_MINWIDTH"]) {

		/*
		 Now let's see where to place that stamp, The following switches
		 will give a startup position for the stamp X/Y coords
		 */
		switch($CONF["IMAGE_STAMP_TEXT_LOCATION_Y"]) {
			case("top"):
				$stampLocationY = 0;
				break;

			case("middle"):
				$stampLocationY = ($processHeight / 2) - ($CONF["IMAGE_STAMP_TEXT_SIZE"]/2);
				break;

			case("bottom"):
				$stampLocationY = $processHeight - $CONF["IMAGE_STAMP_TEXT_SIZE"];
				break;
		}

		switch($CONF["IMAGE_STAMP_TEXT_LOCATION_X"]) {
			case("left"):
				$stampLocationX = 0;
				break;

			case("middle"):
				$stampLocationX = ($newWidth / 2) - ((strlen($CONF["IMAGE_STAMP_TEXT"]) * $CONF["IMAGE_STAMP_TEXT_SIZE"])/2);
				break;

			case("right"):
				$stampLocationX = $newWidth - (strlen($CONF["IMAGE_STAMP_TEXT"]) * $CONF["IMAGE_STAMP_TEXT_SIZE"]);
				break;
		}

		/*
		 Now we add the padding values!
		 */
		$stampLocationX = $stampLocationX + $CONF["IMAGE_STAMP_TEXT_PADDING_X"];
		$stampLocationY = $stampLocationY + $CONF["IMAGE_STAMP_TEXT_PADDING_Y"];

		/*
		 Are we supposed to add a drop shadow hilight on that?
		 */
		if ($CONF["IMAGE_STAMP_TEXT_DROPHILIGHT"]) {
			/*
			 Yep! First thing first; find the color code
			 and attribute it to the handler.
			 */
			$color = explode(",", $CONF["IMAGE_STAMP_TEXT_DROPHILIGHT_COLOR"]);
			$dropColor = imagecolorallocate($thumb, $color[0], $color[1], $color[2]);

			/*
			 .. and add the string to the image (as this
			 is a hilight and that we want it to be under the
			 other text, we will set it first. How Logic!
			 */
			imagestring(
			$thumb,
			$CONF["IMAGE_STAMP_TEXT_SIZE"],
			$stampLocationX + $CONF["IMAGE_STAMP_TEXT_DROPHILIGHT_DEPHASE"],
			$stampLocationY + $CONF["IMAGE_STAMP_TEXT_DROPHILIGHT_DEPHASE"],
			$CONF["IMAGE_STAMP_TEXT"],
			$dropColor);
		}

		/*
		 Now we will add the top layer stamp. Let's find
		 that color code and attribute it to the handler.
		 */
		$color = explode(",", $CONF["IMAGE_STAMP_TEXT_COLOR"]);
		$stampColor = imagecolorallocate($thumb, $color[0], $color[1], $color[2]);

		/*
		 ... and text that!
		 */
		imagestring(
		$thumb,
		$CONF["IMAGE_STAMP_TEXT_SIZE"],
		$stampLocationX,
		$stampLocationY,
		$CONF["IMAGE_STAMP_TEXT"],
		$stampColor
		);
	}
}

function alpha_blending ($dest, $source, $dest_x, $dest_y) {
	## lets blend source pixels with source alpha into destination =)
	for ($y = 0; $y < imagesy($source); $y++) {
		for ($x = 0; $x < imagesx($source); $x++) {
			 
			$argb_s = imagecolorat($source    ,$x            ,$y);
			$argb_d = imagecolorat($dest    ,$x+$dest_x    ,$y+$dest_y);
			 
			$a_s    = ($argb_s >> 24) << 1; ## 7 to 8 bits.
			$r_s    =  $argb_s >> 16     & 0xFF;
			$g_s    =  $argb_s >>  8    & 0xFF;
			$b_s    =  $argb_s            & 0xFF;
			 
			$r_d    =  $argb_d >> 16    & 0xFF;
			$g_d    =  $argb_d >>  8    & 0xFF;
			$b_d    =  $argb_d            & 0xFF;
			 
			$a_s = abs($r_s-255);

			## source pixel 100% opaque (alpha == 0)
			if ($a_s == 0) {
				$r_d = $r_s; $g_d = $g_s; $b_d = $b_s;
			}
			## source pixel 100% transparent (alpha == 255)
			else if ($a_s > 253) {
				## using source alpha only, we have to mix (100-"some") percent
				## of source with "some" percent of destination.
			} else {
				$r_d = (($r_s * (0xFF-$a_s)) >> 8) + (($r_d * $a_s) >> 8);
				$g_d = (($g_s * (0xFF-$a_s)) >> 8) + (($g_d * $a_s) >> 8);
				$b_d = (($b_s * (0xFF-$a_s)) >> 8) + (($b_d * $a_s) >> 8);
			}
			 
			$rgb_d = imagecolorallocatealpha ($dest, $r_d, $g_d, $b_d, 0);
			imagesetpixel ($dest, $x, $y, $rgb_d);
		}
	}
}

// IMAGE WATERMARK /////////////////////////////////////////////////////////////
if ($CONF["IMAGE_USE_WATERMARK"]) {

	/* Load watermark */
	list($srcmarkwidth, $srcmarkheight) = getimagesize("cache/pix/".$CONF["IMAGE_WATERMARK_FILE"]);
	$tempwmhandler = imagecreatefrompng("cache/pix/".$CONF["IMAGE_WATERMARK_FILE"]);

	if ($srcmarkwidth >= $srcmarkheight) {
		$wmwidth = (imagesx($thumb) * $CONF["IMAGE_WATERMARK_RESIZE_FACTOR"]) / 100;
		$wmheight = round($srcmarkheight * ($wmwidth / $srcmarkwidth));
	}

	else {
		$wmheight = (imagesy($thumb) * $CONF["IMAGE_WATERMARK_RESIZE_FACTOR"]) / 100;
		$wmwidth = round($markheight * ($wmheight / $srcmarkheight));
	}

	$wmheight = $newHeight;
	$wmwidth = $newWidth;

	$watermark = imagecreatetruecolor($wmwidth, $wmheight);
	imagecopyresampled($watermark, $tempwmhandler, 0, 0, 0, 0, $wmwidth, $wmheight, $srcmarkwidth, $srcmarkheight);
	//				imagecopyresampled($watermark, $tempwmhandler, 0, 0, 0, 0, 400, 300, 400, 300);
	//                imagecopyresampled($watermark, $tempwmhandler, 0, 0, 0, 0, 400, 300, 400, 300);

	//                imagecolortransparent($watermark, imagecolorallocate($watermark, 0, 0, 0));
	//                imagealphablending($watermark, True);
	/*
	 imagecopymerge(
	 $thumb,
	 $watermark,
	 imagesx($thumb) - $wmwidth,// - $CONF["IMAGE_WATERMARK_PADDING"],
	 imagesy($thumb) - $wmheight,// - $CONF["IMAGE_WATERMARK_PADDING"],
	 0,
	 0,
	 $srcmarkwidth,
	 $srcmarkheight,
	 100
	 );
	 */
	alpha_blending($thumb,$watermark,0,0);
	imagesavealpha($thumb,true);
	imagedestroy($watermark);
}

// SAVE CACHE //////////////////////////////////////////////////////////////////
/*
 Ok now we want to save a cached version of what
 we processed... well - Do we?
 */
if ($CONF["IMAGE_CACHE_PROCESSED"]) {
	/*
	 Simple as one, two, sixteen. We save the cached
	 result in a jpeg file!
	 */
	imagejpeg(
	$thumb,
                        "".$CONF["IMAGE_DEFAULT_DIRECTORY"]."/{$fileName[0]}/{$fileName[1]}/CACHE_{$fileName}{$suffix}".$newWidth.".jpg",
	$CONF["IMAGE_QUALITY"]
	);
}

// STREAM BUFFER ///////////////////////////////////////////////////////////////
/*
 Now we will stream the image to the browser.
 */
(!$CONF["IMAGE_PROCESSOR_DEBUG_MODE"]?header($CONF["IMAGE_HEADER_STRING"]):NULL);
imagejpeg($thumb, NULL, $CONF["IMAGE_QUALITY"]);

// nardim da dela na rgb podlago

/*
 And clean the mess ;)
 */
imagedestroy($thumb);
imagedestroy($handle);

?>
