<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 9/9/18
 * Time: 19:52
 */

function unzipFile($fromZipArchive, $toDirectory) {
	$archive = new ZipArchive();

    if ($archive->open($fromZipArchive) === true) {
        $files = [];
        for($x = 0; $x < $archive->numFiles; $x++) {
            $files[] = $archive->statIndex($x)['name'];
        }

        $archive->extractTo($toDirectory);

        $archive->close();

        foreach($files as &$file) {
            $file = $toDirectory . '/' . $file;
        }

        printMsg("Unzipped " . count($files) . " files to: " . $toDirectory );

        return $files;
    } else {
        throw new RuntimeException("Decompress operation from ZIP file failed.");
    }
}

function isZipFile($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === "zip";
}