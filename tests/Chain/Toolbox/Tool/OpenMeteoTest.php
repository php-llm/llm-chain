<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Toolbox\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Tool\OpenMeteo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(OpenMeteo::class)]
final class OpenMeteoTest extends TestCase
{
    #[Test]
    public function current(): void
    {
        $response = $this->jsonMockResponseFromFile(__DIR__.'/fixtures/openmeteo-current.json');
        $httpClient = new MockHttpClient($response);

        $openMeteo = new OpenMeteo($httpClient);

        $actual = $openMeteo->current(52.52, 13.42);
        $expected = [
            'weather' => 'Overcast',
            'time' => '2024-12-21T01:15',
            'temperature' => '2.6°C',
            'wind_speed' => '10.7km/h',
        ];

        static::assertSame($expected, $actual);
    }

    #[Test]
    public function forecast(): void
    {
        $response = $this->jsonMockResponseFromFile(__DIR__.'/fixtures/openmeteo-forecast.json');
        $httpClient = new MockHttpClient($response);

        $openMeteo = new OpenMeteo($httpClient);

        $actual = $openMeteo->forecast(52.52, 13.42, 3);
        $expected = [
            [
                'weather' => 'Light Rain',
                'time' => '2024-12-21',
                'temperature_min' => '2°C',
                'temperature_max' => '6°C',
            ],
            [
                'weather' => 'Light Showers',
                'time' => '2024-12-22',
                'temperature_min' => '1.3°C',
                'temperature_max' => '6.4°C',
            ],
            [
                'weather' => 'Light Snow Showers',
                'time' => '2024-12-23',
                'temperature_min' => '1.5°C',
                'temperature_max' => '4.1°C',
            ],
        ];

        static::assertSame($expected, $actual);
    }

    /**
     * This can be replaced by `JsonMockResponse::fromFile` when dropping Symfony 6.4.
     */
    private function jsonMockResponseFromFile(string $file): JsonMockResponse
    {
        return new JsonMockResponse(json_decode(file_get_contents($file), true));
    }
}
