<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsTool('wikipedia_search', description: 'Searches Wikipedia for a given query', method: 'search')]
#[AsTool('wikipedia_article', description: 'Retrieves a Wikipedia article by its title', method: 'getArticle')]
final readonly class Wikipedia
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $locale = 'en',
    ) {
    }

    /**
     * @param string $query The query to search for on Wikipedia
     */
    public function search(string $query): string
    {
        $result = $this->execute([
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => $query,
        ], $this->locale);

        $titles = array_map(fn (array $item) => $item['title'], $result['query']['search']);

        return 'On Wikipedia, I found the following articles: '.implode(', ', $titles).'.';
    }

    /**
     * @param string $title The title of the article to retrieve from Wikipedia
     */
    public function getArticle(string $title): string
    {
        $result = $this->execute([
            'action' => 'query',
            'format' => 'json',
            'prop' => 'extracts|info|pageimages',
            'titles' => $title,
            'explaintext' => true,
        ], $this->locale);

        return current($result['query']['pages'])['extract'];
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function execute(array $query, ?string $locale = null): array
    {
        $url = sprintf('https://%s.wikipedia.org/w/api.php', $locale ?? $this->locale);
        $response = $this->httpClient->request('GET', $url, ['query' => $query]);

        return $response->toArray();
    }
}
