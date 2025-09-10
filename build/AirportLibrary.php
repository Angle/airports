<?php

namespace Angle\Airports;

use InvalidArgumentException;

/**
 * Auto-generated PHP abstract class that contains the global IATA Airport database in pure PHP code.
 *
 * The database is pulled from the ICAO Airport database published by [mwgg](https://github.com/mwgg) at [github.com/mwgg/Airports](https://github.com/mwgg/Airports)
 *
 * @author Edmundo Fuentes <me@edmundofuentes.com>
 * @author Angle Consulting <email@angle.mx>
 * @copyright 2019 Angle Consulting
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/Angle/airports
 * @package Angle\Airports
 */
abstract class AirportLibrary
{

    /**
     * Return the Airport properties as an array, false if given IATA code is not found.
     *
     * @param string $iata Airport IATA code (3-letter code)
     * @throws InvalidArgumentException if the IATA code is not in the proper 3-letter format
     * @return Airport|false
     */
    public static function find($iata)
    {
        $iata = strtoupper($iata);

        if (!preg_match('/^[A-Z]{3}$/', $iata)) {
            throw new InvalidArgumentException('Invalid/malformed IATA code');
        }

        if (!self::exists($iata)) return false;

        return Airport::createFromArray(self::$library[$iata]);
    }

    /**
     * Lookup all the available airports for the given country
     *
     * @param string $country Country 2-letter code (ISO 3166 ALPHA-2)
     * @throws InvalidArgumentException if the country code is not in the proper 2-letter ISO format
     * @return Airport[] with the IATA code as the key of each entry
     */
    public static function findByCountry($country)
    {
        $country = strtoupper($country);

        if (!preg_match('/^[A-Z]{2}$/', $country)) {
            throw new InvalidArgumentException('Invalid/malformed 2-letter country code (expecting ISO 3166 ALPHA-2)');
        }

        $r = array();

        foreach (self::$library as $iata => $a) {
            if ($a['country'] == $country) {
                $r[$iata] = Airport::createFromArray($a);
            }
        }

        return $r;
    }

    /**
     * Check if a given IATA code exists in our library
     *
     * @param string $iata
     * @return bool
     */
    public static function exists($iata)
    {
        return array_key_exists($iata, self::$library);
    }


    /**
     * Returns the complete Airport Library list as an array
     *
     * @return array
     */
    public static function getFullList()
    {
        return self::$library;
    }

    /**
     * Finds the nearest airport to a given set of coordinates, optionally filtered by type(s).
     *
     * @param float $lat Latitude of the reference point
     * @param float $lon Longitude of the reference point
     * @param string[]|null $types Array of airport types to restrict the search (e.g. ["large_airport", "heliport"]).
     *                             If null or empty, searches all airports.
     * @return Airport|null
     */
    public static function findNearest($lat, $lon, array $types = null)
    {
        $minDistance = 99999999; // Fallback for PHP 5.3 (no PHP_FLOAT_MAX)
        $nearest = null;

        // Decide the dataset: filter by type or use all
        if ($types && count($types) > 0) {
            $dataset = self::findByTypes($types);
        } else {
            $dataset = self::$library;
        }

        foreach ($dataset as $data) {
            if (!isset($data['lat'], $data['lon'])) {
                continue;
            }

            $distance = self::haversine($lat, $lon, $data['lat'], $data['lon']);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = Airport::createFromArray($data);
            }
        }

        return $nearest;
    }

    /**
     * Haversine formula to calculate distance between two lat/lon points in kilometers.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in kilometers
     */
    private static function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth radius in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Lookup all the available airports that match any of the given types.
     *
     * @param string[] $types Array of airport types (e.g. ["large_airport", "heliport"])
     * @throws InvalidArgumentException if $types is empty or not an array
     * @return Airport[] with the IATA code as the key of each entry
     */
    public static function findByTypes(array $types)
    {
        if (empty($types)) {
            throw new InvalidArgumentException('You must provide at least one type to filter.');
        }

        // Normalize all types to lowercase for matching
        $normalizedTypes = array_map('strtolower', $types);

        $result = array();

        foreach (self::$library as $iata => $data) {
            if (!isset($data['type'])) {
                continue;
            }

            if (in_array(strtolower($data['type']), $normalizedTypes, true)) {
                $result[$iata] = $data;
            }
        }

        return $result;
    }

    /**
     * Contains the most up-to-date list of IATA airports in the world.
     * Sourced from: https://github.com/mwgg/Airports
     *
     * THIS ARRAY IS AUTO-GENERATED FROM THE SOURCE airports.json FILE. DO NOT EDIT BELOW THIS POINT.
     *
     * @var array
     */
    private static $library = array("~~PLACEHOLDER FOR AUTO-GENERATED CODE~~");
}