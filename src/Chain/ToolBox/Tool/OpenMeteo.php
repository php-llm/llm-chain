<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsTool(name: 'weather', description: 'get the current weather for a location')]
final readonly class OpenMeteo
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @param float $latitude  the latitude of the location
     * @param float $longitude the longitude of the location
     */
    public function __invoke(float $latitude, float $longitude): string
    {
        $response = $this->httpClient->request('GET', 'https://api.open-meteo.com/v1/forecast', [
            'query' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,wind_speed_10m',
            ],
        ]);

        return $response->getContent();
    }
}
