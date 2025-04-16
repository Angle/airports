<?php

use PHPUnit\Framework\TestCase;

use Angle\Airports\AirportLibrary;
use Angle\Airports\Airport;

class AirportLibraryTest extends TestCase
{
    /** @test */
    public function canFindValidAirport()
    {
        $a = AirportLibrary::find('LMM');

        $this->assertNotFalse($a);
        $this->assertInstanceOf('\Angle\Airports\Airport', $a);
        $this->assertArrayHasKey('iata', $a->toArray());
    }

    /** @test */
    public function canDetectNonExistingAirport()
    {
        $a = AirportLibrary::find('ZZZ');

        $this->assertFalse($a);
    }

    /** @test */
    public function canDetectMalformedAirportCode()
    {
        $this->setExpectedException('InvalidArgumentException');

        $a = AirportLibrary::find('WXZY1');

    }

    /** @test */
    public function canFindByCountry()
    {
        $a = AirportLibrary::findByCountry('MX');

        // There should always be at least 1 airport in Mexico
        $this->assertNotEquals(0, count($a));

        $total = count(AirportLibrary::getFullList());

        // Verify that we are not returning the full Library
        $this->assertNotEquals($total, count($a));
    }

    /** @test */
    public function canDetectMalformedCountryCode()
    {
        $this->setExpectedException('InvalidArgumentException');

        $a = AirportLibrary::findByCountry('MEXICO');
    }

    /** @test */
    public function canFindNearestAirport()
    {
        // Coordinates near Mexico City
        $lat = 19.4326;
        $lon = -99.1332;

        $airport = AirportLibrary::findNearest($lat, $lon);
        $this->assertInstanceOf('\Angle\Airports\Airport', $airport);
    }
}