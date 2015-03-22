# Photo Renamer

## Usage

`php renamer.php </full/source/path> </full/target/path> [debug]`

## Description

This tool renames photos downloaded from iPhone with the `IMG_XXXX.JPG/PNG` format into the `YYYY-MM-DD_HH:mm:ss.JPG/PNG` format based on either the file modification time or the EXIF `DateTimeOriginal` property if available. It then move the files into the target directory.

It will also move the associated AAE (non-destructive adjustment in photo editing) file into the target directory.

Duplicated files (binary comparison) will be deleted. Non duplicated files will same timestamp will be series-ed with YYYY-MM-DD_HH:mm:ss**-X**.JPG/PNG with X starting from **0**;

### non-destructive version

`php renamer_non_destructive.php </full/source/path> </full/target/path> [debug]`

Will copy instead of move, and keep duplicate files in source directory instead of deleting.