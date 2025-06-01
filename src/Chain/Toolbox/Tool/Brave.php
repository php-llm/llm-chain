<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Attribute\With;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
#[AsTool('brave_search', 'Tool that searches the web using Brave Search')]
final readonly class Brave
{
    /**
     * @param array<string, mixed> $options See https://api-dashboard.search.brave.com/app/documentation/web-search/query#WebSearchAPIQueryParameters
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
        private array $options = [],
    ) {
    }

    /**
     * @param string $query  the search query term
     * @param int    $count  The number of search results returned in response.
     *                       Combine this parameter with offset to paginate search results.
     * @param int    $offset The number of search results to skip before returning results.
     *                       In order to paginate results use this parameter together with count.
     *
     * @return array<int, array{
     *     title: string,
     *     description: string,
     *     url: string,
     * }>
     */
    public function __invoke(
        #[With(maximum: 500)]
        string $query,
        int $count = 20,
        #[With(minimum: 0, maximum: 9)]
        int $offset = 0,
    ): array {
        $response = $this->httpClient->request('GET', 'https://api.search.brave.com/res/v1/web/search', [
            'headers' => ['X-Subscription-Token' => $this->apiKey],
            'query' => array_merge($this->options, [
                'q' => $query,
                'count' => $count,
                'offset' => $offset,
            ]),
        ]);

        $data = $response->toArray();

        return array_map(static function (array $result) {
            return ['title' => $result['title'], 'description' => $result['description'], 'url' => $result['url']];
        }, $data['web']['results'] ?? []);
    }
}
