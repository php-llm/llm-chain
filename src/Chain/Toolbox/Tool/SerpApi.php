<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
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
