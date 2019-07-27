<?php

namespace Angle\Airports;

use InvalidArgumentException;

/**
 * Auto-generated PHP abstract class that contains the global IATA Aiport database in pure PHP code.
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
     * @return array|false
     */
    public static function find($iata)
    {
        $iata = strtoupper($iata);

        if (!preg_match('/^[A-Z]{3}$/', $iata)) {
            throw new InvalidArgumentException('Invalid/malformed IATA code');
        }

        if (!self::exists($iata)) return false;

        return self::$library[$iata];
    }

    /**
     * Lookup all the available aiports for the given country
     * @param string $country Country 2-letter code (ISO 3166 ALPHA-2)
     * @throws InvalidArgumentException if the country code is not in the proper 2-letter ISO format
     * @return array
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
                $r[$iata] = $a;
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
     * Contains the most up-to-date list of IATA airports in the world.
     * Sourced from: https://github.com/mwgg/Airports
     *
     * THIS ARRAY IS AUTO-GENERATED FROM THE SOURCE airports.json FILE. DO NOT EDIT BELOW THIS POINT.
     *
     * @var array
     */
    private static $library = array("~~PLACEHOLDER FOR AUTO-GENERATED CODE~~");
}