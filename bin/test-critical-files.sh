#!/bin/bash -ex
#
# Exist codes:
# - 0: OK
# - 1: file missing
# - 2: folder missing

function testRequiredFiles {
	parentFolder='./'
	requiredFiles=(
		one-click-demo-import.php
		readme.txt
		assets/css/main.css
		assets/js/main.js
		inc/class-ocdi-helpers.php
		inc/class-ocdi-importer.php
		inc/class-ocdi-widget-importer.php
		inc/class-ocdi-logger.php
		vendor/humanmade/WordPress-Importer/class-logger-cli.php
		vendor/humanmade/WordPress-Importer/class-logger.php
		vendor/humanmade/WordPress-Importer/class-wxr-importer.php
	)
	requiredFolders=(
		assets/js
		assets/css
		inc
		languages
		vendor
	)

	# loop for files
	for file in "${requiredFiles[@]}"
	do
		filePath="$parentFolder/$file"
		if [[ ! -f $filePath ]]; then
			echo "File $filePath does not exist!"
			exit 1
		fi
	done

	# loop for directories
	for folder in "${requiredFolders[@]}"
	do
		folderPath="$parentFolder/$folder"
		if [[ ! -d $folderPath ]]; then
			echo "Directory $folderPath does not exist!"
			exit 2
		fi
	done
}

# call and unset
testRequiredFiles
unset testRequiredFiles