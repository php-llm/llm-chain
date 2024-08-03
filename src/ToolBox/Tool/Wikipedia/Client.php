<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox\Tool\Wikipedia;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Client
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $locale = 'en',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function search(string $query, ?string $locale = null): array
    {
        return $this->execute([
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => $query,
        ], $locale);
    }

    /**
     * @return array<string, mixed>
     */
    public function getArticle(string $title, ?string $locale = null): array
    {
        return $this->execute([
            'action' => 'query',
            'format' => 'json',
            'prop' => 'extracts|info|pageimages',
            'titles' => $title,
            'explaintext' => true,
        ], $locale);
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
