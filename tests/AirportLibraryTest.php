<?php

use PHPUnit\Framework\TestCase;

use Angle\Airports\AirportLibrary;

class AirportLibraryTest extends TestCase
{
    /** @test */
    public function canFindValidAirport()
    {
        $a = AirportLibrary::find('LMM');

        $this->assertNotFalse($a);
        $this->assertArrayHasKey('iata', $a);
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
}