<?php
set_time_limit(0);




# BUILD REQUIREMENTS
# ext-json
# ext-curl

# SETTINGS
const AIRPORT_LIST_URL = 'https://github.com/mwgg/Airports/raw/master/airports.json';
const DOWNLOAD_TIMEOUT = 60; // seconds
const PLACEHOLDER_STR = 'array("~~PLACEHOLDER FOR AUTO-GENERATED CODE~~")';



echo "Starting Build procedure.." . PHP_EOL;

# 1. Create the required file structure to hold the temp files
$projDir = dirname(__FILE__, 2);
$tmpDir = $projDir . '/tmp';
$outDir = $projDir . '/src'; // output

echo "· Create /tmp directory.. ";

if (!is_dir($tmpDir)) {
    if (!mkdir($tmpDir, 0777, true)) {
        die('Failed to create temp folder.');
    }
    echo "OK" . PHP_EOL;
} else {
    echo "SKIP" . PHP_EOL;
}

echo "· Create /src directory (output).. ";

if (!is_dir($outDir)) {
    if (!mkdir($outDir, 0777, true)) {
        die('Failed to create src folder.');
    }
    echo "OK" . PHP_EOL;
} else {
    echo "SKIP" . PHP_EOL;
}

# PHASE 1: DOWNLOAD & PARSE SOURCE FILE

echo "PROCESSING SOURCE DATABASE" . PHP_EOL;

# 1.1 Download the raw airports.json into a temp file
echo "· Creating temp file to write airports.json into.. ";
$tmpFilename = $tmpDir . '/airports.json';

# 1.2 Download the raw airport list into a temp .json file
# src: https://stackoverflow.com/a/6409531
//This is the file where we save the    information
$fp = fopen ($tmpFilename, 'w+');

if (!$fp) {
    die('Could not create a temp file to hold the raw airport list in json.');
}

echo "OK" . PHP_EOL;


echo "· Downloading the raw airports.json into a temp file.. ";

// Here is the file we are downloading
$ch = curl_init(AIRPORT_LIST_URL);
curl_setopt($ch, CURLOPT_TIMEOUT, DOWNLOAD_TIMEOUT); // allow up to a minute

// write curl response to file
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// get curl response
curl_exec($ch);
curl_close($ch);

fclose($fp);

echo "OK" . PHP_EOL;


# 1.3 Parse the JSON into memory
echo "· Parsing the JSON file into memory.. ";
$airportsJson = json_decode(file_get_contents($tmpFilename), true);

if (!$airportsJson) {
    die('Could not parse JSON file.');
}

echo "OK" . PHP_EOL;

echo sprintf("Parsed %d entries from the source repository." . PHP_EOL, count($airportsJson));

# 1.4 Strip out the entries that do not have a IATA code
echo "· Filtering entries, will only keep those with a valid IATA code.. ";
$airports = array();

foreach ($airportsJson as $icao => $data) {
    if ($data['iata']) {
        // If the airport has a valid IATA code, append it to our new "filtered" list using its IATA code as the key
        $airports[$data['iata']] = $data;
    }
}

echo "OK" . PHP_EOL;

echo sprintf('· Filtered down to %d entries!' . PHP_EOL, count($airports));


## PHASE 2: AUTO-GEN THE LIBRARY CODE
echo "AUTO-GEN LIBRARY CODE" . PHP_EOL;

# 2.1 Load the base airports.php file
echo "· Loading base AirportLibrary.php template.. ";
$airportsPhp = file_get_contents(dirname(__FILE__) . '/AirportLibrary.php');

if (!$airportsPhp) {
    die('Could not load base airports library code.');
}

echo "OK" . PHP_EOL;

# 2.2 Replace the placeholder string in the airports.php file with the actual airports array that we've parsed.
echo "· Writing parsed data into template.. ";
$airportsPhp = str_replace(PLACEHOLDER_STR, var_export($airports, true), $airportsPhp);
echo "OK" . PHP_EOL;


# 2.3. Write the auto-generated file into the output /src
echo "· Writing generated PHP code into the output src file.. ";

$outFilename = $outDir . '/AirportLibrary.php';

$fp = fopen($outFilename, 'w+');

if (!$fp) {
    die('Could not create a temp file to hold the raw airport list in json.');
}

if (fwrite($fp, $airportsPhp) === false) {
    die('Could not write to output file');
}

fclose($fp);

echo "OK" . PHP_EOL;

echo "[All done]" . PHP_EOL;

