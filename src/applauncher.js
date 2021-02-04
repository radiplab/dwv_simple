/**
 * Application launcher.
 */

// start app function
function startApp() {
    // translate page
    dwv.i18nPage();

    // main application
    var myapp = new dwv.App();

    //**jc - Set the sequences variable
    if (sequences && sequences != null) {
    	myapp.setSequences(sequences);
    	myapp.URLBase = url_base;
    }

    // display loading time
    var listener = function (event) {
        if (event.type === "load-start") {
            console.time("load-data");
        }
        else {
            console.timeEnd("load-data");
        }
    };

    // before myapp.init since it does the url load
    myapp.addEventListener("load-start", listener);
    myapp.addEventListener("load-end", listener);

    // also available:
    //myapp.addEventListener("load-progress", listener);
    //myapp.addEventListener("draw-create", listener);
    //myapp.addEventListener("draw-move", listener);
    //myapp.addEventListener("draw-change", listener);
    //myapp.addEventListener("draw-delete", listener);
    //myapp.addEventListener("wl-width-change", listener);
    //myapp.addEventListener("wl-center-change", listener);
    //myapp.addEventListener("colour-change", listener);
    //myapp.addEventListener("position-change", listener);
    //myapp.addEventListener("slice-change", listener);
    //myapp.addEventListener("frame-change", listener);
    //myapp.addEventListener("zoom-change", listener);
    //myapp.addEventListener("offset-change", listener);
    //myapp.addEventListener("filter-run", listener);
    //myapp.addEventListener("filter-undo", listener);

    // initialise the application
    var options = {
        "containerDivId": "dwv",
        "fitToWindow": true,
        //"gui": ["tool", "load", "help", "undo", "version", "tags", "drawList"],
        "loaders": ["File", "Url", "GoogleDrive", "Dropbox"],
        //"tools": ["Scroll", "WindowLevel", "ZoomAndPan", "Draw", "Livewire", "Filter", "Floodfill"],
        "tools": ["Scroll", "WindowLevel", "ZoomAndPan"],
		//"filters": ["Threshold", "Sharpen", "Sobel"],
        //"shapes": ["Arrow", "Ruler", "Protractor", "Rectangle", "Roi", "Ellipse", "FreeHand"],
        "isMobile": true,
        "helpResourcesPath": "resources/help"
        //"defaultCharacterSet": "chinese"
    };

    if ( dwv.browser.hasInputDirectory() ) {
        options.loaders.splice(1, 0, "Folder");
    }

	/**jc - Add slider for images */
	/*
	// base function to get elements
	dwv.gui.getElement = dwv.gui.base.getElement;
	dwv.gui.displayProgress = function (percent) {};	

	var range = document.getElementById("sliceRange");
	range.min = 0;
	myapp.addEventListener("load-end", function () {
		range.max = myapp.getImage().getGeometry().getSize().getNumberOfSlices() - 1;
	});
	myapp.addEventListener("slice-change", function () {
		range.value = myapp.getViewController().getCurrentPosition().k;
	});

	var changeImage = function () {
		var pos = myapp.getViewController().getCurrentPosition();
		pos.k = this.value;
		myapp.getViewController().setCurrentPosition(pos);		
	}
	
	range.oninput = changeImage;
	range.onchange = changeImage;
	*/
	
    myapp.init(options);

    var size = dwv.gui.getWindowSize();
    $(".layerContainer").height(size.height);

	// load dicom data
	//myapp.loadURLs(["dicom/vascular01/cta.zip"]);	
}

// Image decoders (for web workers)
dwv.image.decoderScripts = {
    "jpeg2000": "node_modules/dwv/decoders/pdfjs/decode-jpeg2000.js",
    "jpeg-lossless": "node_modules/dwv/decoders/rii-mango/decode-jpegloss.js",
    "jpeg-baseline": "node_modules/dwv/decoders/pdfjs/decode-jpegbaseline.js"
};

// status flags
var domContentLoaded = false;
var i18nInitialised = false;
// launch when both DOM and i18n are ready
function launchApp() {
    if ( domContentLoaded && i18nInitialised ) {
        startApp();
    }
}
// i18n ready?
dwv.i18nOnInitialised( function () {
    // call next once the overlays are loaded
    var onLoaded = function (data) {
        dwv.gui.info.overlayMaps = data;
        i18nInitialised = true;
        launchApp();
    };
    // load overlay map info
    $.getJSON( dwv.i18nGetLocalePath("overlays.json"), onLoaded )
    .fail( function () {
        console.log("Using fallback overlays.");
        $.getJSON( dwv.i18nGetFallbackLocalePath("overlays.json"), onLoaded );
    });
});

// check browser support
dwv.browser.check();
// initialise i18n
dwv.i18nInitialise("auto", "node_modules/dwv");

// DOM ready?
$(document).ready( function() {
    domContentLoaded = true;
    launchApp();
});
