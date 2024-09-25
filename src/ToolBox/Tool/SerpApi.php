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

#[AsTool(name: 'serpapi', description: 'search for information on the internet')]
final readonly class SerpApi
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
    ) {
    }

    /**
     * @param string $query The search query to use
     */
    public function __invoke(string $query): string
    {
        $response = $this->httpClient->request('GET', 'https://serpapi.com/search', [
            'query' => [
                'q' => $query,
                'api_key' => $this->apiKey,
            ],
        ]);

        return sprintf('Results for "%s" are "%s".', $query, $this->extractBestResponse($response->toArray()));
    }

    /**
     * @param array<string, mixed> $results
     */
    private function extractBestResponse(array $results): string
    {
        return implode('. ', array_map(fn ($story) => $story['title'], $results['organic_results']));
    }
}
