<?php

function ca_tts($say_text, $destination_dir = 'cache/sfx/') {
	$filename = md5(uniqid(rand(), True)) . ".mp3";

	// Direktorij trik, da ni prevec fajlov v enem direktoriju in da se u koncni fazi lahko to distribuira
	// na vec serverjev
	$destination_dir = $destination_dir.$filename[0].'/'.$filename[1].'/';
	mkdir($destination_dir,0777,True);

	if(
	file_put_contents(
		"{$destination_dir}{$filename}",
	file_get_contents(
'http://translate.google.com/translate_tts?q='.rawurlencode(substr($say_text,0,75))
)
) !== False
)
return($filename);
else
return(False);
}

function ca_tts_simple($say_text, $destination_dir = './') {
	$filename = md5(uniqid(rand(), True)) . ".mp3";

	if(
		file_put_contents(
			"{$filename}",
			file_get_contents(
				'http://translate.google.com/translate_tts?q='.rawurlencode(substr($say_text,0,75))
			)
		) !== False
	)
		return($filename);
	else
		return(False);
}


?>