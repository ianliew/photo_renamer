<?php

/***
 * Usage: exif_info.php /full/source/path
 *
 * Outputs modt (and exif if available) timestamp of a file.
 */

date_default_timezone_set('Australia/Melbourne');

// confirm valid source
$source = $argv[1];
if (!is_dir($source))      { die("Invalid path to photo directory\n"); }

// scan list of files
$files = scandir($source);

foreach ($files as $f) {

	// get basic file info
	$f_ext = substr  ($f, -3, 3);
	$f_bsn = basename($f, "." . $f_ext);

	if (preg_match('/jpg|JPG|PNG/', $f_ext)) {
		// target filename based on file mod time
		$f_tgt = get_fmodtm("$source/$f");

		echo "file $f\t";
		echo "modt: $f_tgt\t";

		// target filename based on exif info
		$f_exf = get_fexifinfo("$source/$f", $f_tgt);
		if (strcmp($f_tgt, $f_exf) !== 0) { echo "exif: $f_exf"; } echo "\n";

	}

}


/********** Functions ****************/

function get_fmodtm($file) {
	if (file_exists($file)) {
		$fmtd = filemtime($file);
		return date("Y-m-d_H.i.s", $fmtd);
	}
	return "";
}

function get_fexifinfo($file, $orig) {
	if (preg_match('/jpg|JPG/', substr($file, -3, 3))) {
		$exif = exif_read_data($file);
		if ($exif === false) {
			echo $debug ? "No header data\n" : "" ;
		} else {
			$result = preg_replace('/(\d{4}):(\d\d):(\d\d) (\d\d):(\d\d):(\d\d)/',
				'$1-$2-$3_$4.$5.$6',
				$exif["DateTimeOriginal"]);
		}

		// use exif over file mod time
		if (strlen($result) == 19) {
			return $result;
		}
	}
	return $orig;
}


?>