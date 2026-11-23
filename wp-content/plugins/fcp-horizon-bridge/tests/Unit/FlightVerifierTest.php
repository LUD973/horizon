<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Data\Providers\AeroDataBox\AeroDataBoxClient;
use FCP\Horizon\Domain\Flight\FlightLeg;
use FCP\Horizon\Domain\Flight\FlightNumber;
use FCP\Horizon\Domain\Flight\FlightVerifier;
use PHPUnit\Framework\TestCase;

final class FlightVerifierTest extends TestCase
{
    private function leg(string $from, string $to): FlightLeg
    {
        return new FlightLeg($from, '', $to, '', '2026-12-20 07:00+01:00', '2026-12-20 09:35+01:00', 'Air France', 'Expected');
    }

    public function testParsesAndNormalisesFlightNumbers(): void
    {
        self::assertSame('AF1234', FlightNumber::parse(' af 1234 ')->compact());
        self::assertSame('AF 7', FlightNumber::parse('AF007')->display());
        self::assertSame('U21234', FlightNumber::parse('u2-1234')->compact());
        self::assertNull(FlightNumber::parse('1234'));
        self::assertNull(FlightNumber::parse('12345'));
        self::assertNull(FlightNumber::parse('AF'));
    }

    public function testArrivalMustBeTheExactAirport(): void
    {
        $v = new FlightVerifier();
        $f = FlightNumber::parse('AF1234');
        self::assertSame('ok', $v->verify([$this->leg('JFK', 'CDG')], 'CDG', 'arr', $f)['status']);
        $r = $v->verify([$this->leg('NCE', 'ORY')], 'CDG', 'arr', $f);
        self::assertSame('mismatch', $r['status']);
        self::assertStringContainsString('arrive à Paris-Orly (ORY), pas à Paris-Charles de Gaulle', $r['message']);
        self::assertSame('mismatch', $v->verify([$this->leg('CDG', 'LYS')], 'CDG', 'arr', $f)['status']);
    }

    public function testDepartureChecksTheOriginAirport(): void
    {
        $v = new FlightVerifier();
        $f = FlightNumber::parse('AF006');
        self::assertSame('ok', $v->verify([$this->leg('CDG', 'JFK')], 'CDG', 'dep', $f)['status']);
        $r = $v->verify([$this->leg('ORY', 'JFK')], 'CDG', 'dep', $f);
        self::assertSame('mismatch', $r['status']);
        self::assertStringContainsString('part de Paris-Orly (ORY)', $r['message']);
    }

    public function testAnyLegOfAMultiLegFlightCanMatch(): void
    {
        $v = new FlightVerifier();
        $legs = [$this->leg('NCE', 'LYS'), $this->leg('LYS', 'CDG')];
        self::assertSame('ok', $v->verify($legs, 'CDG', 'arr', FlightNumber::parse('AF7700'))['status']);
    }

    public function testNotFoundAndUnavailable(): void
    {
        $v = new FlightVerifier();
        $f = FlightNumber::parse('AF1234');
        self::assertSame('not_found', $v->verify([], 'CDG', 'arr', $f)['status']);
        self::assertSame('unavailable', $v->verify(null, 'CDG', 'arr', $f)['status']);
    }

    public function testParsesAeroDataBoxPayload(): void
    {
        $json = '[{"number":"AF 1234","status":"Expected","departure":{"airport":{"iata":"JFK","name":"New York, John F Kennedy"},"scheduledTime":{"local":"2026-12-19 22:00-05:00"}},"arrival":{"airport":{"iata":"CDG","name":"Paris, Charles de Gaulle"},"scheduledTime":{"local":"2026-12-20 11:05+01:00"},"revisedTime":{"local":"2026-12-20 10:50+01:00"}},"airline":{"name":"Air France"}}]';
        $legs = AeroDataBoxClient::parse(json_decode($json, true));
        self::assertCount(1, $legs);
        self::assertSame('JFK', $legs[0]->depIata);
        self::assertSame('CDG', $legs[0]->arrIata);
        self::assertSame('10:50', FlightLeg::hour($legs[0]->arrLocal));
        self::assertSame('Air France', $legs[0]->airlineName);
    }
}
