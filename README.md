# dwv-simple
Want to embed DICOM studies in online quizzes? This may be the answer for you. ivmartel developed the wonderful DICOM Web Viewer (DWV) and made it freely available on Github. I took a version of that viewer, and hacked it down and modified it to have the simple interface I wanted. The goal was to have something that basically just looked like an image on a quiz, but surprise, you could interact with it too. Here's an example:

<figure>
  <iframe src="https://www.radmodules.com/dwv/viewer-new.php?casefolder=0000242&sequence=EAC.zip&image=2&width=334&level=172" width="500" height="500" frameborder="0"></iframe>
</figure>

Important files include:
index.php: This will list all folders in the dicom folder with a link to see each in the DWV (dwv.php)
dwv.php: Wrapper around the modified DWV
viewer.php: Modified DWV

This modification includes:
- Reduced number of overlays
- Modified controls - right-click to window-level, double-click to zoom/reset, left click and drag to zoom, and support for touch
- Support for multiple sequences
- Support for default window/level and starting image # fed from URL request parameters

Medical viewer using [DWV](https://github.com/ivmartel/dwv) (DICOM Web Viewer) and [jQuery mobile](https://jquerymobile.com/).

All coding/implementation contributions and comments are welcome. Releases should be ready for deployment otherwise download the code and install dependencies with a `yarn` or `npm` `install`.

dwv-jqmobile is not certified for diagnostic use. Released under GNU GPL-3.0 license (see [license.txt](license.txt)).

[![Build Status](https://travis-ci.org/ivmartel/dwv-jqmobile.svg?branch=master)](https://travis-ci.org/ivmartel/dwv-jqmobile)

## Available Scripts

``` bash
# install dependencies
yarn install

# serve at localhost:8080 with live reload
yarn run start

# serve a developement version at localhost:8080 with live reload
yarn run dev

# run unit tests
yarn run test
```
