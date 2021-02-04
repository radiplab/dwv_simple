<?php
	// Test URL:
	// http://localhost/dwvDev/dwv.php?casefolder=0000063
	// Wrapper around viewer.php to support loading of multiple sequences
?>
<!DOCTYPE html>
<html>
<head>
<title>Image Viewer</title>
<meta charset="UTF-8">
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
<script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
<script>
var sequences = new Array();
var loadedSequenceNum = 0;
<?php
    $casefolder = urldecode($_REQUEST['casefolder']);
	$begin_image_number = urldecode((isset($_REQUEST["image"])?$_REQUEST["image"]:""));
	$begin_window_width = urldecode((isset($_REQUEST["width"])?$_REQUEST["width"]:""));
	$begin_window_level = urldecode((isset($_REQUEST["level"])?$_REQUEST["level"]:""));
	$begin_zoom = urldecode((isset($_REQUEST["zoom"])?$_REQUEST["zoom"]:""));
	$sequence = urldecode((isset($_REQUEST["sequence"])?$_REQUEST["sequence"]:""));
	$message = urldecode((isset($_REQUEST["message"])?$_REQUEST["message"]:""));
	
	echo("var casefolder = '$casefolder';\n");
    chdir("dicom/$casefolder");
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
			if ($filename != "." and $filename != ".." and $filename != ".DS_Store" and substr($filename, -4) === ".zip") {
				$path_parts = pathinfo($filename);
				$sequence = $path_parts['filename'];
				$extension = $path_parts['extension'];
				$sequences[$sequence] = $filename;
				echo("sequences['$sequence'] = '$filename';\n");
			}
		}
	}
	echo("var begin_image_number = '$begin_image_number';\n");
	echo("var begin_window_width = '$begin_window_width';\n");
	echo("var begin_window_level = '$begin_window_level';\n");
	echo("var begin_zoom = '$begin_zoom';\n");
	echo("var message = '$message';\n");
?>

function onLoad() {	
	//**jc - if "casefolder" was in the URL then we're loading zip files in a folder       			
	var sequencesContainer = document.getElementById("sequencesContainer");
	if (Object.keys(sequences).length > 1) {
		// Draw the sequence links
		var sequencesList = document.createElement('ul');
		sequencesList.id = 'sequencesList';
		sequencesList.style.padding = '0px';
		sequencesList.style.width = '100%';
		sequencesContainer.appendChild(sequencesList);
		var i = 0;
		for (let key in sequences) {
			let value = sequences[key];

			sequenceName = key;
			filename = value;
			var sequenceLink = document.createElement('a');
			var sequenceLinkId = "sequenceLink" + i;
			sequenceLink.href = "javascript:loadSequence('" + filename + "', " + i + ")";
			sequenceLink.id = sequenceLinkId;
			sequenceLink.classList.add("sequenceLink");
			sequenceLink.innerHTML = sequenceName;
			
			var li = document.createElement('li');
			li.style.display = 'inline';
			sequencesList.appendChild(li);
			li.appendChild(sequenceLink);
			
			var dwv = document.createElement("IFRAME");
			dwv.style.display = "none";
			dwv.id = "dwv" + i;
			dwv.classList.add("dwv");
			var dwvContainer = document.getElementById("dwv-container");
			dwvContainer.appendChild(dwv);
			
			i++;
		}
		
		// Center the sequences
		var center = parseInt($(window).width() / 2);
		var sequencesWidth = $(sequencesContainer).width();
		var sequencesLeft = center - (sequencesWidth / 2);
		//sequencesContainer.style.left = sequencesLeft + "px";	
	} else {
		var dwv = document.createElement("IFRAME");
		dwv.style.display = "none";
		dwv.id = "dwv0";
		dwv.classList.add("dwv");
		var dwvContainer = document.getElementById("dwv-container");
		dwvContainer.appendChild(dwv);		
	}

	// Display the message
	displayMessage(message);

	preloadSequences();
	
	// Load the first sequence and draw the sequence links
	// key = sequence name; value = filename
	var sequenceName = Object.keys(sequences)[0];
	var filename = sequences[sequenceName];
	loadSequence(filename, 0);
}

function displayMessage(message_text) {
	// Display the message if provided
	if (message_text != '') {
		// Create the div
		var messageDiv = document.createElement('div');
		messageDiv.classList.add("message");
		messageDiv.innerHTML = message_text;
		messageDiv.style.visibility = 'hidden';
		document.body.appendChild(messageDiv);
		
		// Position it
		var top = '5px';
		if ($("#pageHeader").height() != 0) {
			top = parseInt(($("#pageHeader").height()/2) - ($(messageDiv).height()/2)) + 'px';
		}
		var left = parseInt($(window).width() - $(messageDiv).width() - 10) + 'px';
		messageDiv.style.top = top;
		messageDiv.style.left = left;
		
		// Show it
		messageDiv.style.visibility = 'visible';
	}	
}

function preloadSequences() {
	var numSequences = Object.keys(sequences).length;
	for (var i = 0; i < numSequences; i++) {
		var iframeId = "dwv" + i;
		var iframe = document.getElementById(iframeId);
		
		var key = Object.keys(sequences)[i];
		var filename = sequences[key];
		if (numSequences == 1) {
			var iframe_src = "viewer.php?casefolder=" + encodeURI(casefolder) + "&sequence=" + encodeURI(filename);
			if (begin_image_number != '') {
				iframe_src = iframe_src + "&image=" + encodeURI(begin_image_number);
			}
			if (begin_window_width != '') {
				iframe_src = iframe_src + "&width=" + encodeURI(begin_window_width);
			}
			if (begin_window_level != '') {
				iframe_src = iframe_src + "&level=" + encodeURI(begin_window_level);
			}
			if (begin_zoom != '') {
				iframe_src = iframe_src + "&zoom=" + encodeURI(begin_zoom);
			}
			iframe.src = iframe_src;
		} else {
			iframe.src = "viewer.php?casefolder=" + encodeURI(casefolder) + "&sequence=" + encodeURI(filename);
		}
	}
	resize();
}

function loadSequence(filename, sequenceNum) {
	var loadedIframe = document.getElementById("dwv" + loadedSequenceNum);
	loadedIframe.style.display = "none";
	
	var loadedSequenceLink = document.getElementById("sequenceLink" + loadedSequenceNum);
	if (loadedSequenceLink !== null) {
		loadedSequenceLink.className = "sequenceLink";
	}

	var iframeId = "dwv" + sequenceNum;
	var iframeToLoad = document.getElementById(iframeId);
	iframeToLoad.style.display = "block";
	sequenceLinkId = "sequenceLink" + sequenceNum;
	
	var sequenceLink = document.getElementById(sequenceLinkId);
	if (sequenceLink !== null) {
		sequenceLink.className = "sequenceLinkActive";
	}
	
	loadedSequenceNum = sequenceNum;
	
}

function resize() {
	var numSequences = Object.keys(sequences).length;
	for (var i = 0; i < numSequences; i++) {
		var iframe = document.getElementById("dwv" + i);
		var iframeHeight = $(window).height() - $(pageHeader).height();
		iframeHeight = iframeHeight + "px";
		document.getElementById("dwv-container").style.height = iframeHeight;
		iframe.style.height = iframeHeight;
	}
}

</script>
<style>

body {
	background-color: black;
	margin: 0;
}

#dwv-container {
	width: 100%;
	float: left;
}

.dwv {
	width: 100%;
	border: none;
}
</style>

</head>

<body oncontextmenu="return false;" onload="onLoad()" onresize="resize()">

<div id="pageHeader" data-role="header">
<span class="sequencesContainer" id="sequencesContainer"></span>
</div><!-- /pageHeader -->

<div id="dwv-container">
</div>

</body>
</html>
