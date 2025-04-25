<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsTool('crawler', 'A tool that crawls one page of a website and returns the visible text of it.')]
final readonly class Crawler
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
        if (!class_exists(DomCrawler::class)) {
            throw new \RuntimeException('The DomCrawler component is not installed. Please install it using "composer require symfony/dom-crawler".');
        }
    }

    /**
     * @param string $url the URL of the page to crawl
     */
    public function __invoke(string $url): string
    {
        $response = $this->httpClient->request('GET', $url);

        return (new DomCrawler($response->getContent()))->filter('body')->text();
    }
}
