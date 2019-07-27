<?php

namespace Angle\Airports;

/**
 * Airport property wrapper
 *
 * @author Edmundo Fuentes <me@edmundofuentes.com>
 * @author Angle Consulting <email@angle.mx>
 * @copyright 2019 Angle Consulting
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/Angle/airports
 * @package Angle\Airports
 */
class Airport
{
    /**
     * IATA code
     * @var string
     */
    public $iata;

    /**
     * ICAO code
     * @var string
     */
    public $icao;

    /**
     * Airport official name (en)
     * @var string
     */
    public $name;

    /**
     * City name
     * @var string
     */
    public $city;

    /**
     * State/Region name
     * @var string
     */
    public $state;

    /**
     * Country 2-letter code (ISO 3166 ALPHA-2)
     * @var string
     */
    public $country;

    /**
     * Elevation in feet over sea level
     * @var int
     */
    public $elevation;

    /**
     * Latitude
     * @var float
     */
    public $lat;

    /**
     * Longitude
     * @var float
     */
    public $lon;

    /**
     * Timezone string
     * @var string
     */
    public $tz;

    /**
     * Returns false if the airport could not be created from the
     * @param array $data
     * @return Airport
     */
    public static function createFromArray(array $data)
    {
        $a = new self;

        if (array_key_exists('iata', $data))        $a->iata = $data['iata'];
        if (array_key_exists('icao', $data))        $a->icao = $data['icao'];
        if (array_key_exists('name', $data))        $a->name = $data['name'];
        if (array_key_exists('city', $data))        $a->city = $data['city'];
        if (array_key_exists('state', $data))       $a->state = $data['state'];
        if (array_key_exists('country', $data))     $a->country = $data['country'];
        if (array_key_exists('elevation', $data))   $a->elevation = $data['elevation'];
        if (array_key_exists('lat', $data))         $a->lat = $data['lat'];
        if (array_key_exists('lon', $data))         $a->lon = $data['lon'];
        if (array_key_exists('tz', $data))          $a->tz = $data['tz'];

        return $a;
    }

    public function toArray()
    {
        return json_decode(json_encode($this), true);
    }
}