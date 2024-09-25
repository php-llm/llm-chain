<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\ToolBox\Tool;

use PhpLlm\LlmChain\ToolBox\AsTool;
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
