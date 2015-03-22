<?php

/***
 * Usage: run_this.php </full/source/path> </full/target/path> [debug]
 */

date_default_timezone_set('Australia/Melbourne');

$debug  = $argc > 2 ? $argv[4] : 0;

// confirm valid source
$source = $argv[1];
if (!is_dir($source))      { die("Invalid path to photo directory\n"); }

// confirm valid target
$target = $argv[2];
if (!file_exists($target)) { ee("mkdir $target"); }


// scan list of files
$files = scandir($source);

foreach ($files as $f) {

	// get basic file info
	$f_ext = substr  ($f, -3, 3);
	$f_bsn = basename($f, "." . $f_ext);

	if (preg_match('/jpg|JPG|PNG/', $f_ext)) {
		// target filename based on file mod time
		$f_tgt = get_fmodtm("$source/$f");

		// target filename based on exif info
		$f_tgt = get_fexifinfo("$source/$f", $f_tgt);

		// copy source to target
		$f_tgt = cc("$source/$f_bsn", "$target/$f_tgt", $f_ext);

		// copy AAE file as well, if not duplicate image
		if ($f_tgt !== 0) { cc("$source/$f_bsn", "$target/$f_tgt", "AAE"); }
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

function ee($cmd) {
	global $debug;
	if ($debug) { echo "$cmd\n"; return 0; } else { return shell_exec($cmd); }
}

function cc($source, $target, $ext) {
	$i = 0;

	// check source file exists
	if (!file_exists("$source.$ext")) { return 0; }

	// check target has series
	if (file_exists("$target-$i.$ext")) {
		while (file_exists("$target-$i.$ext")) { $i++; }
		$target = "$target-$i";
	}

	// check if target exists
	elseif (file_exists("$target.$ext")) {
		// check if target file is the same
		if (strcmp(shell_exec("diff \"$source.$ext\" \"$target.$ext\""), "") === 0) {
			echo "Duplicated target file: $source.$ext $target.$ext\n";
			return 0;
		}

		// move target and associated AAE file to 0 series
		ee("mv -i \"$target.$ext\" \"$target-0.$ext\"");
		if (file_exists("$target.AAE")) { ee("mv -i \"$target.AAE\" \"$target-0.AAE\""); }

		$target = "$target-1";
	}

	// execute
	ee("cp -i \"$source.$ext\" \"$target.$ext\"");
	return basename($target, "." . $ext); //$target;
}


?>