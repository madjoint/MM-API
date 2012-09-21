<?php

function ca_base64_decode($encoded) {
	$decoded = "";
	for ($i=0; $i < ceil(strlen($encoded)/256); $i++)
	$decoded = $decoded . base64_decode(substr($encoded,$i*256,256));
	return($decoded);
}

// <form method="post" enctype="multipart/form-data">
function ca_mmupload($user_id, $file_input_name = 'image', $destination_dir = 'cache/pix/', $temporary_dir = 'cache/') {
	if(!isset($_POST[$file_input_name]) || $_POST[$file_input_name] == '') return '';
	$filename = md5($user_id).".".md5(uniqid(rand(), True)) . ".jpg";
	file_put_contents("{$temporary_dir}{$filename}",
	ca_base64_decode(
	strtr(
	$_POST[$file_input_name],
				' ',
				'+'
				),False
				)
				);

				// Direktorij trik, da ni prevec fajlov v enem direktoriju in da se u koncni fazi lahko to distribuira
				// na vec serverjev
				$destination_dir = $destination_dir.$filename[0].'/'.$filename[1].'/';
				mkdir($destination_dir,0777,True);

				// Optimizacija: neglede na velikost fotke jo resiza na 1280 ali 1024 s 75% kompresijo, kar jo naredi do 10x manjso
				// potem pa iz tamajhne fotke dela naprej vse thumbnaile = less CPU = less RAM
				include('ca/ca_image.php');
				resizeToFile("{$temporary_dir}{$filename}", 300, 300, "{$destination_dir}{$filename}", 75);

				// Original shranimo vseeno, se ne vem zakaj
				// rename("{$temporary_dir}{$filename}", "{$destination_dir}ORIGINAL_{$filename}");
				unlink("{$temporary_dir}{$filename}");

				return($filename);
}

function ca_image_remove_with_cache($filename, $destination_dir = 'cache/pix/') {
	$destination_dir = $destination_dir.$filename[0].'/'.$filename[1].'/';

	// Check if we're rewriting old file. If yes, we need to clean the cache
	if(file_exists($destination_dir.$filename)) {
		// Todo: this is not very optimised way of doing file searching I guess
		$all_files = scandir($destination_dir);
		foreach($all_files as &$a) {
			if(substr($a,0,6) == 'CACHE_') $cache_files[] = $a;
		}
		foreach($cache_files as $c) {
			unlink($destination_dir.$c);
		}
		unlink($destination_dir.$filename);
	}
}

// <form method="post" enctype="multipart/form-data">
function ca_upload($file_input_name, $basename = NULL, $destination_dir = 'cache/pix/', $temporary_dir = 'cache/') {
	if(!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name] == '') return '';

	if (!strstr(basename($_FILES[$file_input_name]["name"]), ".")) {
		ca_debug('ERROR: Picture has no extension !');
		return '';
	}

	$fileChunks = explode(".", basename($_FILES[$file_input_name]["name"]));
	$fileExtension = strtolower($fileChunks[count($fileChunks)-1]);

	if (!in_array($fileExtension, explode(",", "jpg"))) {
		ca_debug('ERROR: Picture has non allowed extension !');
		return '';
	}

	if($basename == NULL) {
		$filename = md5(uniqid(rand(), True)) . "." . $fileExtension;
	} else {
		$filename = $basename.".".$fileExtension;
	}
	move_uploaded_file($_FILES[$file_input_name]["tmp_name"], "{$temporary_dir}{$filename}");

	// remove old file with cache
	ca_image_remove_with_cache($filename, $destination_dir);

	// Direktorij trik, da ni prevec fajlov v enem direktoriju in da se u koncni fazi lahko to distribuira
	// na vec serverjev
	$destination_dir = $destination_dir.$filename[0].'/'.$filename[1].'/';
	mkdir($destination_dir,0777,True);

	// Optimizacija: neglede na velikost fotke jo resiza na 1280 ali 1024 s 75% kompresijo, kar jo naredi do 10x manjso
	// potem pa iz tamajhne fotke dela naprej vse thumbnaile = less CPU = less RAM
	include('ca/ca_image.php');
	resizeToFile("{$temporary_dir}{$filename}", 1024, 768, "{$destination_dir}{$filename}", 75);

	// Original shranimo vseeno, se ne vem zakaj
	// rename("{$temporary_dir}{$filename}", "{$destination_dir}ORIGINAL_{$filename}");
	unlink("{$temporary_dir}{$filename}");

	return($filename);
}

?>
