# Airports

This library is an auto-generated PHP abstract class that contains the global IATA Aiport database in pure PHP code.
  
The database is pulled from the ICAO Airport database published by [mwgg](https://github.com/mwgg) at [github.com/mwgg/Airports](https://github.com/mwgg/Airports).

**Note:** we haven't tested if this is faster than loading from a raw .json file or a SQLite database. However, it definitely simplifies our deployment processes. Please feel free to run benchmarks and submit pull requests!

## Installation
```bash
composer require anglemx/airports
```

## Usage
After installing it with Composer, simply import it into your code.

```php
use Angle\Airports\AirportLibrary;
```

### Find a single airport from a known IATA code

```php
/** @var \Angle\Airports\Airport $airport */
$airport = AirportLibrary::find('LMM');
```

An airport entry contains:
```
var_dump($airport);

/**
object(Angle\Airports\Airport)(10) {
  ["icao"]=>
  string(4) "MMLM"
  ["iata"]=>
  string(3) "LMM"
  ["name"]=>
  string(38) "Valle del Fuerte International Airport"
  ["city"]=>
  string(10) "Los Mochis"
  ["state"]=>
  string(7) "Sinaloa"
  ["country"]=>
  string(2) "MX"
  ["elevation"]=>
  int(16)
  ["lat"]=>
  float(25.6851997375)
  ["lon"]=>
  float(-109.081001282)
  ["tz"]=>
  string(16) "America/Mazatlan"
}
*/

// If needed, the `Airport` object can also be casted as an array:

var_dump($airport->toArray());

/**
array(10) {
  ["iata"]=>
  string(3) "LMM"
  ["icao"]=>
  string(4) "MMLM"
  ["name"]=>
  string(38) "Valle del Fuerte International Airport"
  ["city"]=>
  string(10) "Los Mochis"
  ["state"]=>
  string(7) "Sinaloa"
  ["country"]=>
  string(2) "MX"
  ["elevation"]=>
  int(16)
  ["lat"]=>
  float(25.6851997375)
  ["lon"]=>
  float(-109.081001282)
  ["tz"]=>
  string(16) "America/Mazatlan"
}
*/
```


### Find all the airports in a country

```php
/** @var \Angle\Airports\Airport[] $airports */
$airports = AirportLibrary::findByCountry('MX');
```

Returns an array of airports for the specified country, with the IATA code as the key of each entry.


### Get the full airport list
```php
$airports = AirportLibrary::getFullList();
```

Returns an array of all the airports (as arrays, not objects) in the list, with the IATA code as the key of each entry.


## Build / Update
Requirements:
- ext-curl
- ext-json

Run the script at:

```bash
$> php build/build.php
```

This will download the newest copy of the source `airports.json` [from the GitHub repository](https://github.com/mwgg/Airports/raw/master/airports.json) into a temporary file, it will parse it and it will replace the placeholder found in `build/AirportLibrary.php` to write the final auto-generated file to `src/AirportLibrary.php`.

**Do not modify the file in `src/AirportLibrary.php` directly.** Instead modify the _template_ file in the `build/` directory, and then run the `build.php` script.


We're targeting PHP 5.3 even thought it's quite old at this point, in order to maintain compatibility with older codebases. This means:
- Not using PHP7's constant arrays or any other modern features that could speed up the performance of the library.
- Using PHPUnit 4 even thought it's deprecated by now.

## Testing
The library is _very_ simple, but we wrote some "tests" just for the heck of it.  These require PHPUnit 4.
```bash
$> composer install
```

```bash
$> ./vendor/bin/phpunit tests/AirportLibraryTest
```

## Credits
The source information is pulled from the ICAO Airport database published by [mwgg](https://github.com/mwgg) at [github.com/mwgg/Airports](https://github.com/mwgg/Airports).


## License
MIT License. © 2019 [Angle Consulting](https://angle.mx).