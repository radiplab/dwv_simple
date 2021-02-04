<!DOCTYPE html>
<html>
<head>
<script>

<?php
chdir("dicom");
echo "var cases = [];\n";
foreach (scandir(getcwd()) as $casedir) {
	if (is_dir($casedir) and $casedir != "." and $casedir != "..") {
		echo "var sequences_array = [];\n";
		chdir("$casedir");
		foreach (scandir(getcwd()) as $sequencedir) {
			if (is_dir($sequencedir) and $sequencedir != "." and $sequencedir != ".." and $sequencedir != ".DS_Store") {
				
				// Create a javascript object of the sequence and add it to sequences array
				$num_images = count(scandir($sequencedir)) - 2;

				echo "var filenames_array = [";
				$i = 0;
				$filenames = scandir($sequencedir);
				natsort($filenames);
				foreach (($filenames) as $filename) {
					if ($filename != "." and $filename != ".." and $filename != ".DS_Store") {
						if ($i == 0) {
							echo "'" . $filename . "'";
						} else {
							echo ", '" . $filename . "'";
						}
						$i++;
					}
				}
				echo "]\n";
				
				echo "sequences_array.push({name:'$sequencedir', num_images:'$num_images', filenames:filenames_array});\n";
			}
		}
		echo "cases.push({name:'$casedir', sequences:sequences_array});\n";
		chdir("../");
	}
}
?>

for (var i = 0; i < cases.length; i++) {
  cases[i].name;
}

</script>
</head>
<body>

<?php

echo "<h1>Displaying cases in " . getcwd() . "</h1><br/>";

foreach (scandir(getcwd(), 1) as $dir) {
	if (is_dir($dir) and $dir != "." and $dir != "..") {
		echo "<h2><a href='dwv.php?casefolder=" . $dir . "'>" . $dir . "</a></h2>";
	}
}

?>

</body>
</html>