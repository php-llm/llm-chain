<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox\Tool;

use PhpLlm\LlmChain\ToolBox\AsTool;
use PhpLlm\LlmChain\ToolBox\Tool\Wikipedia\Client;

#[AsTool('wikipedia_search', description: 'Searches Wikipedia for a given query', method: 'search')]
#[AsTool('wikipedia_article', description: 'Retrieves a Wikipedia article by its title', method: 'getArticle')]
final class Wikipedia
{
    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * @param string $query The query to search for on Wikipedia
     */
    public function search(string $query): string
    {
        $result = $this->client->search($query);
        $titles = array_map(fn (array $item) => $item['title'], $result['query']['search']);

        return 'On Wikipedia, I found the following articles: '.implode(', ', $titles).'.';
    }

    /**
     * @param string $title The title of the article to retrieve from Wikipedia
     */
    public function getArticle(string $title): string
    {
        $result = $this->client->getArticle($title);

        return current($result['query']['pages'])['extract'];
    }
}
