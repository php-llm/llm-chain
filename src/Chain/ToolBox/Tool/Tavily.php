<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Tool integration of tavily.com.
 */
#[AsTool('tavily_search', description: 'search for information on the internet', method: 'search')]
#[AsTool('tavily_extract', description: 'fetch content from a website', method: 'extract')]
final readonly class Tavily
{
    /**
     * @param array<string, string|string[]|int|bool> $options
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private array $options = ['include_images' => false],
    ) {
    }

    /**
     * @param string $query The search query to use
     */
    public function search(string $query): string
    {
        $response = $this->httpClient->request('POST', 'https://api.tavily.com/search', [
            'json' => array_merge($this->options, [
                'query' => $query,
                'api_key' => $this->apiKey,
            ]),
        ]);

        return $response->getContent();
    }

    /**
     * TODO: Support list of URLs.
     *
     * @param string $url URL to fetch information from
     */
    public function extract(string $url): string
    {
        $response = $this->httpClient->request('POST', 'https://api.tavily.com/extract', [
            'json' => [
                'urls' => [$url],
                'api_key' => $this->apiKey,
            ],
        ]);

        return $response->getContent();
    }
}
