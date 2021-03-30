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
		assets/demo-content/about-page.xml
		inc/CreateDemoContent/DemoContentCreator.php
		inc/CustomizerImporter.php
		inc/CustomizerOption.php
		inc/Downloader.php
		inc/Helpers.php
		inc/ImportActions.php
		inc/Importer.php
		inc/Logger.php
		inc/OneClickDemoImport.php
		inc/ReduxImporter.php
		inc/WidgetImporter.php
		inc/WXRImporter.php
		inc/WPCLICommands.php
		vendor/awesomemotive/wp-content-importer-v2/src/WPImporterLoggerCLI.php
		vendor/awesomemotive/wp-content-importer-v2/src/WPImporterLogger.php
		vendor/awesomemotive/wp-content-importer-v2/src/WXRImporter.php
		vendor/awesomemotive/wp-content-importer-v2/src/WXRImportInfo.php
	)
	requiredFolders=(
		assets/js
		assets/css
		assets/demo-content
		assets/images
		inc
		inc/CreateDemoContent
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
