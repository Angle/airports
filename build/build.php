<?php
set_time_limit(0);

/**
 * Requisitos:
 * - ext-json
 * - ext-curl
 *
 * Uso:
 *   php build.php [opcional: CSV_URL]
 */

// SETTINGS
const AIRPORT_LIST_URL = 'https://github.com/mwgg/Airports/raw/master/airports.json';
const CSV_DOWNLOAD_URL = 'https://raw.githubusercontent.com/jplarar/airport-codes/refs/heads/main/airport-codes.csv';
const DOWNLOAD_TIMEOUT = 60; // seconds
const PLACEHOLDER_STR = 'array("~~PLACEHOLDER FOR AUTO-GENERATED CODE~~")';

// Si true, el CSV puede sobreescribir un 'type' que ya exista en el JSON
const OVERWRITE_TYPE_FROM_CSV = false;

echo "Starting Build procedure.." . PHP_EOL;

// 1) Create folders
$projDir = dirname(__FILE__, 2);
$tmpDir  = $projDir . '/tmp';
$outDir  = $projDir . '/src'; // output

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

// Helpers
function downloadToFile(string $url, string $destPath): void
{
    $fp = fopen($destPath, 'w+');
    if (!$fp) {
        die("Could not open temp file for download: {$destPath}");
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, DOWNLOAD_TIMEOUT);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $ok = curl_exec($ch);
    if ($ok === false) {
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        die("cURL download error: {$err}");
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    if ($httpCode < 200 || $httpCode >= 300) {
        die("Unexpected HTTP code {$httpCode} when downloading {$url}");
    }
}

function loadCsvTypesFromPath(string $csvPath): array
{
    // Devuelve IATA => type
    $fh = fopen($csvPath, 'r');
    if (!$fh) {
        echo "ERROR opening CSV at {$csvPath}. Skipping CSV merge." . PHP_EOL;
        return [];
    }
    $header = fgetcsv($fh);
    if (!$header) {
        fclose($fh);
        echo "ERROR reading CSV header. Skipping CSV merge." . PHP_EOL;
        return [];
    }

    $map = [];
    foreach ($header as $idx => $colName) {
        $map[strtolower(trim((string)$colName))] = $idx;
    }

    // Detectar columnas
    $iataIdx = $map['iata_code'] ?? ($map['iata'] ?? null);
    if ($iataIdx === null) {
        foreach ($map as $col => $idx) {
            if (strpos($col, 'iata') !== false) {
                $iataIdx = $idx;
                break;
            }
        }
    }
    $typeIdx = $map['type'] ?? null;
    if ($typeIdx === null) {
        foreach ($map as $col => $idx) {
            if (strpos($col, 'type') !== false) {
                $typeIdx = $idx;
                break;
            }
        }
    }

    if ($iataIdx === null || $typeIdx === null) {
        fclose($fh);
        echo "ERROR: Could not detect IATA/TYPE columns. Headers: " . implode(' | ', $header) . PHP_EOL;
        return [];
    }

    $result = [];
    $rows = 0;
    $valid = 0;

    while (($row = fgetcsv($fh)) !== false) {
        $rows++;
        $iata = isset($row[$iataIdx]) ? strtoupper(trim((string)$row[$iataIdx])) : '';
        $type = isset($row[$typeIdx]) ? trim((string)$row[$typeIdx]) : '';
        if ($iata === '' || $iata === 'NA') {
            continue;
        }
        if (strlen($iata) > 4) {
            continue;
        }
        if ($type === '') {
            continue;
        }
        $result[$iata] = $type;
        $valid++;
    }
    fclose($fh);

    echo "   · CSV parsed. rows: {$rows}, mapped: {$valid}" . PHP_EOL;
    return $result;
}

function loadCsvTypesFromUrl(string $url, string $tmpDir): array
{
    echo "· Downloading CSV with types.. ";
    $tmpCsv = $tmpDir . '/airport-codes.csv';
    downloadToFile($url, $tmpCsv);
    echo "OK" . PHP_EOL;

    echo "· Reading CSV types.. " . PHP_EOL;
    return loadCsvTypesFromPath($tmpCsv);
}

// PHASE 1: DOWNLOAD & PARSE SOURCE FILE
echo "PROCESSING SOURCE DATABASE" . PHP_EOL;

// 1.1 Download airports.json
echo "· Downloading airports.json.. ";
$tmpAirports = $tmpDir . '/airports.json';
downloadToFile(AIRPORT_LIST_URL, $tmpAirports);
echo "OK" . PHP_EOL;

// 1.2 Parse JSON
echo "· Parsing the JSON file into memory.. ";
$airportsJson = json_decode(file_get_contents($tmpAirports), true);
if (!is_array($airportsJson)) {
    die('Could not parse JSON file.');
}
echo "OK" . PHP_EOL;
echo sprintf("· Parsed %d entries from the source repository." . PHP_EOL, count($airportsJson));

// 1.3 Filter to IATA
echo "· Filtering entries, will only keep those with a valid IATA code.. ";
$airports = [];
foreach ($airportsJson as $icao => $data) {
    if (!empty($data['iata'])) {
        $iata = strtoupper($data['iata']);
        $airports[$iata] = $data;
    }
}
echo "OK" . PHP_EOL;
echo sprintf('· Filtered down to %d entries!' . PHP_EOL, count($airports));

// PHASE 1.4: CSV merge
$csvUrlArg = $argv[1] ?? null;
$csvUrl = $csvUrlArg ? $csvUrlArg : CSV_DOWNLOAD_URL;
$csvTypes = loadCsvTypesFromUrl($csvUrl, $tmpDir);

$mergedFromCsv = 0;
$alreadyHadType = 0;
$missingInCsv = 0;

if (!empty($csvTypes)) {
    echo "· Merging 'type' from CSV into airport entries.. ";
    foreach ($airports as $iata => &$a) {
        $csvType = $csvTypes[$iata] ?? null;

        $hasType = isset($a['type']) && $a['type'] !== '' && $a['type'] !== null;
        if ($hasType && !OVERWRITE_TYPE_FROM_CSV) {
            $alreadyHadType++;
            continue;
        }

        if ($csvType !== null && $csvType !== '') {
            $a['type'] = $csvType;
            $mergedFromCsv++;
        } else {
            $missingInCsv++;
        }
    }
    unset($a);
    echo "OK" . PHP_EOL;

    echo "   · CSV merge summary:" . PHP_EOL;
    echo "     - Updated with type from CSV: {$mergedFromCsv}" . PHP_EOL;
    echo "     - Already had type (kept):     {$alreadyHadType}" . PHP_EOL;
    echo "     - No type in CSV:              {$missingInCsv}" . PHP_EOL;
} else {
    echo "· No CSV types loaded. Skipping merge." . PHP_EOL;
}

// PHASE 2: AUTO-GEN THE LIBRARY CODE
echo "AUTO-GEN LIBRARY CODE" . PHP_EOL;

// 2.1 Load template AirportLibrary.php
echo "· Loading base AirportLibrary.php template.. ";
$templatePath = dirname(__FILE__) . '/AirportLibrary.php';
$airportsPhp = file_get_contents($templatePath);
if ($airportsPhp === false) {
    die('Could not load base airports library code.');
}
echo "OK" . PHP_EOL;

// 2.2 Replace placeholder with array
echo "· Writing parsed data into template.. ";
$airportsPhp = str_replace(PLACEHOLDER_STR, var_export($airports, true), $airportsPhp);
echo "OK" . PHP_EOL;

// 2.3 Write output
echo "· Writing generated PHP code into the output src file.. ";
$outFilename = $outDir . '/AirportLibrary.php';
$fp = fopen($outFilename, 'w+');
if (!$fp) {
    die('Could not create output file.');
}
if (fwrite($fp, $airportsPhp) === false) {
    die('Could not write to output file');
}
fclose($fp);
echo "OK" . PHP_EOL;

echo "[All done]" . PHP_EOL;