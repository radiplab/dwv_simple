<?php
	// dwv-simplistic: multisequence viewer
	// dwv-jqmobile-first-refactor: initial tweaks to viewer

    // Request argument $casefolder expected
    // Within that folder, multiple zip files
    // Each .zip file is considered to be a separate sequence, with the name of the sequence the zip file name
    
	// Other optional request arguments:
	// width: default window width
	// level: default window level
	// image: default image number
	// sequence: filename (including .zip) of only sequence to load
	
	// Test URL:
	// http://localhost/dwvDev/viewer.php?casefolder=0000063&sequence=AX%20T2%20FS.zip	
	// http://localhost/dwv-jqmobile/viewer.php?casefolder=01%20-%20EAC&image=20&width=350&level=155

	// Test URL: http://localhost/dwv-simplistic/viewer.php?input=dicom%2Fvascular01%2Fcta.zip&casefolder=mri01
    // Test URL with new loading: http://localhost/dwv-simplistic/viewer.php?casefolder=mri01
    // Test HTML only: http://localhost/dwv-simplistic/index.html?input=dicom%2Fvascular01%2Fcta.zip
    // Test HTML with MRI: http://localhost/dwv-simplistic/index.html?input=dicom%2Fmri01%2FDiffusion.zip
    $casefolder = urldecode($_REQUEST['casefolder']);
	$begin_image_number = urldecode((isset($_REQUEST["image"])?$_REQUEST["image"]:""));
	$begin_window_width = urldecode((isset($_REQUEST["width"])?$_REQUEST["width"]:""));
	$begin_window_level = urldecode((isset($_REQUEST["level"])?$_REQUEST["level"]:""));
	$begin_zoom = urldecode((isset($_REQUEST["zoom"])?$_REQUEST["zoom"]:""));
	$sequence = urldecode((isset($_REQUEST["sequence"])?$_REQUEST["sequence"]:""));
    chdir("dicom/$casefolder");
?>

<!-- Zoom: appContext.stepZoom(1.2, event.targetTouches[0].clientX, event.targetTouches[0].clientY); -->

<!DOCTYPE html>
<!-- <html manifest="cache.manifest"> -->
<html>

<head>
<title>Case: <?php echo $casefolder ?></title>
<meta charset="UTF-8">
<meta name="description" content="Medical viewer using DWV (DICOM Web Viewer) and jQery Mobile.">
<meta name="keywords" content="DICOM,HTML5,JavaScript,medical,imaging,DWV">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<meta name="theme-color" content="#2F3BA2"/>

<script>
	var sequences = new Array();
	var appContext = null;
	<?php
		echo("var url_base = 'dicom/$casefolder/';\n");
		echo("var begin_image_number = '$begin_image_number';\n");
		echo("var begin_window_width = '$begin_window_width';\n");
		echo("var begin_window_level = '$begin_window_level';\n");
		echo("var begin_zoom = '$begin_zoom';\n");
	
		// Iterate through provided folder, argument $casefolder
		// Each .zip file is considered to be a separate sequence, with the name of the sequence the zip file name
		// Sequences are considered to be folder names in the case
		$sequences = array();
		if ($sequence != "") {
			$filename = $sequence;
			$path_parts = pathinfo($filename);
			$sequence = $path_parts['filename'];
			$extension = $path_parts['extension'];
			$sequences[$sequence] = $filename;
			echo("sequences['$sequence'] = '$filename';\n");			
		} else {
			$filenames = scandir(getcwd());
			natsort($filenames);
			foreach (($filenames) as $filename) {
				if ($filename != "." and $filename != ".." and $filename != ".DS_Store") {
					$path_parts = pathinfo($filename);
					$sequence = $path_parts['filename'];
					$extension = $path_parts['extension'];
					$sequences[$sequence] = $filename;
					echo("sequences['$sequence'] = '$filename';\n");
				}
			}
		}
	
	?>
</script>

<link rel="manifest" href="resources/manifest.json">
<link type="text/css" rel="stylesheet" href="css/style.css" />
<link type="text/css" rel="stylesheet" href="ext/jquery-mobile/jquery.mobile-1.4.5.min.css" />
<link type="text/css" rel="stylesheet" href="node_modules/nprogress/nprogress.css" />
<style type="text/css" >
.ui-popup .ui-controlgroup { background-color: #252525; }
.colourLi > .ui-input-text { text-align: center; }
.colourLi > .ui-input-text input { min-height: 2em; width: 7em; display:inline-block }
.lwColourLi > .ui-input-text { text-align: center; }
.lwColourLi > .ui-input-text input { min-height: 2em; width: 7em; display:inline-block }
.ffColourLi > .ui-input-text { text-align: center; }
.ffColourLi > .ui-input-text input { min-height: 2em; width: 7em; display:inline-block }
/* jquery-mobile strip not visible enough */
.table-stripe tbody tr:nth-child(odd) td,
.table-stripe tbody tr:nth-child(odd) th {
  background-color: #eeeeee; /* non-RGBA fallback  */
  background-color: rgba(0,0,0,0.1);
  
}
</style>
<!-- mobile web app -->
<meta name="mobile-web-app-capable" content="yes" />
<link rel="shortcut icon" sizes="16x16" href="resources/icons/dwv-16.png" />
<link rel="shortcut icon" sizes="32x32" href="resources/icons/dwv-32.png" />
<link rel="shortcut icon" sizes="64x64" href="resources/icons/dwv-64.png" />
<link rel="shortcut icon" sizes="128x128" href="resources/icons/dwv-128.png" />
<link rel="shortcut icon" sizes="256x256" href="resources/icons/dwv-256.png" />
<!-- apple specific -->
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<link rel="apple-touch-icon" sizes="16x16" href="resources/icons/dwv-16.png" />
<link rel="apple-touch-icon" sizes="32x32" href="resources/icons/dwv-32.png" />
<link rel="apple-touch-icon" sizes="64x64" href="resources/icons/dwv-64.png" />
<link rel="apple-touch-icon" sizes="128x128" href="resources/icons/dwv-128.png" />
<link rel="apple-touch-icon" sizes="256x256" href="resources/icons/dwv-256.png" />
<!-- Third party (dwv) -->
<script type="text/javascript" src="node_modules/i18next/i18next.min.js"></script>
<script type="text/javascript" src="node_modules/i18next-xhr-backend/i18nextXHRBackend.min.js"></script>
<script type="text/javascript" src="node_modules/i18next-browser-languagedetector/i18nextBrowserLanguageDetector.min.js"></script>
<script type="text/javascript" src="node_modules/jszip/dist/jszip.min.js"></script>
<script type="text/javascript" src="node_modules/konva/konva.min.js"></script>
<script type="text/javascript" src="node_modules/magic-wand-js/js/magic-wand-min.js"></script>
<!-- Third party (viewer) -->
<script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="ext/jquery-mobile/jquery.mobile-1.4.5.min.js"></script>
<script type="text/javascript" src="node_modules/nprogress/nprogress.js"></script>
<script type="text/javascript" src="ext/flot/jquery.flot.min.js"></script>
<!-- decoders -->
<script type="text/javascript" src="node_modules/dwv/decoders/pdfjs/jpx.js"></script>
<script type="text/javascript" src="node_modules/dwv/decoders/pdfjs/util.js"></script>
<script type="text/javascript" src="node_modules/dwv/decoders/pdfjs/arithmetic_decoder.js"></script>
<script type="text/javascript" src="node_modules/dwv/decoders/pdfjs/jpg.js"></script>
<script type="text/javascript" src="node_modules/dwv/decoders/rii-mango/lossless-min.js"></script>
<!-- dwv -->
<script type="text/javascript" src="node_modules/dwv/dist/dwv.js"></script>

<!-- Google -->
<script type="text/javascript" src="ext/google-api-javascript-client/client.js"></script>
<script type="text/javascript" src="ext/google-api-javascript-client/api.js"></script>
<script type="text/javascript" src="src/google.js"></script>

<!-- Dropbox -->
<script type="text/javascript" src="ext/dropbox-dropins/dropins.js"
    id="dropboxjs" data-app-key="96u3396jtx3bwr8"></script>
<script type="text/javascript" src="src/dropbox.js"></script>

<!-- Launch the app -->
<script type="text/javascript" src="src/register-sw.js"></script>
<script type="text/javascript" src="src/appgui.js"></script>
<script type="text/javascript" src="src/applauncher.js"></script>

<!--**jc - additional styles -->
<style type="text/css">
.ui-page {
	background-color: black;
}

.ui-overlay-b {
	background-color: black;
}
</style>

</head>

<!--**jc - I had to add oncontextmenu to stop right click from displaying menu -->
<body oncontextmenu="return false;">

<!-- Main page -->
<div data-role="page" data-theme="b" id="main">

<!-- pageHeader #dwvversion -->
<div id="pageHeader" data-role="header">
<span class="sequencesContainer" id="sequencesContainer"></span>
<!--**jc - remove help button
<a href="#help_page" data-icon="carat-r" class="ui-btn-right"
  data-transition="slide" data-i18n="basics.help">Help</a>
-->
</div><!-- /pageHeader -->

<!-- DWV -->
<div id="dwv">

<div id="pageMain" data-role="content" style="padding:2px;">

<!-- Toolbar -->
<div class="toolbar" id="toolbar"></div>

<!-- Auth popup **jc - remove auth popup
<div data-role="popup" id="popupAuth">
<a href="#" data-rel="back" data-role="button" data-icon="delete"
  data-iconpos="notext" class="ui-btn-right" data-i18n="basics.close">Close</a>
<div style="padding:10px 20px;">
<h3 data-i18n="io.GoogleDrive.auth.title">Google Drive Authorization</h3>
<p data-i18n="io.GoogleDrive.auth.body">Please authorize DWV to access your Google Drive.
<br>This is only needed the first time you connect.</p>
<button id="gauth-button" class="ui-btn" data-i18n="io.GoogleDrive.auth.button">Authorize</button>
</div>
</div> 
/popup -->


<!-- Open popup -->
<div data-role="popup" id="popupOpen">
<a href="#" data-rel="back" data-role="button" data-icon="delete"
  data-iconpos="notext" class="ui-btn-right" data-i18n="basics.close">Close</a>
<div style="padding:10px 20px;">
<h3 data-i18n="basics.open">Open</h3>
<div id="dwv-loaderlist"></div>
</div>
</div><!-- /popup -->

<!-- Layer Container -->
<div class="layerContainer">
<!--**jc - hide dropbox div
<div class="dropBox"></div>
-->
<canvas class="imageLayer">Only for HTML5 compatible browsers...</canvas>
<div class="drawDiv"></div>
<div class="infoLayer">
<div class="infotl info"></div>
<div class="infotc infoc"></div>
<div class="infotr info"></div>
<div class="infocl infoc"></div>
<div class="infocr infoc"></div>
<div class="infobl info"></div>
<div class="infobc infoc"></div>
<div class="infobr info"></div>
</div><!-- /infoLayer -->
</div><!-- /layerContainer -->

<!-- History -->
<div class="history" title="History" style="display:none;"></div>

</div><!-- /content -->
<!--
<div data-role="footer">
<div data-role="navbar" class="toolList">
</div>
<input type="range" id="sliceRange" value="0">
</div>
-->
</div><!-- /page main -->

</div><!-- /dwv -->

<!-- Tags page -->
<div data-role="page" data-theme="b" id="tags_page">

<div data-role="header">
<a href="#main" data-icon="back" data-transition="slide"
  data-direction="reverse" data-i18n="basics.back">Back</a>
<h1 data-i18n="basics.dicomTags">DICOM Tags</h1>
</div><!-- /header -->

<div data-role="content">
<!-- Tags -->
<div id="dwv-tags" title="Tags"></div>
</div><!-- /content -->

</div><!-- /page tags_page-->

<!-- Draw list page -->
<div data-role="page" data-theme="b" id="drawList_page">

<div data-role="header">
<a href="#main" data-icon="back" data-transition="slide"
  data-direction="reverse" data-i18n="basics.back">Back</a>
<h1 data-i18n="basics.drawList">Draw list</h1>
</div><!-- /header -->

<div data-role="content">
<!-- DrawList -->
<div id="dwv-drawList" title="Draw list"></div>
</div><!-- /content -->

</div><!-- /page draw-list_page-->


<!-- Help page -->
<div data-role="page" data-theme="b" id="help_page">

<div data-role="header">
<a href="#main" data-icon="back" data-transition="slide"
  data-direction="reverse" data-i18n="basics.back">Back</a>
<h1 data-i18n="basics.help">Help</h1>
</div><!-- /header -->

<div data-role="content">
<!-- Help -->
<div id="dwv-help" title="Help"></div>
</div><!-- /content -->

</div><!-- /page help_page-->

<!--**jc - overlay for loading -->
<div id="loadingOverlay">
	<img src="resources/images/loading.gif" id="loadingImage"></img>
</div>

</body>
</html>
