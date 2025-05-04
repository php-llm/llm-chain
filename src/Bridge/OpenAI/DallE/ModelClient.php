<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\DallE;

use PhpLlm\LlmChain\Bridge\OpenAI\DallE;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformResponseFactory;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;
use Webmozart\Assert\Assert;

/**
 * @see https://platform.openai.com/docs/api-reference/images/create
 */
final readonly class ModelClient implements PlatformResponseFactory, PlatformResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::startsWith($apiKey, 'sk-', 'The API key must start with "sk-".');
    }

    public function supports(Model $model): bool
    {
        return $model instanceof DallE;
    }

    public function request(Model $model, array|string $payload, array $options = []): HttpResponse
    {
        return $this->httpClient->request('POST', 'https://api.openai.com/v1/images/generations', [
            'auth_bearer' => $this->apiKey,
            'json' => \array_merge($options, [
                'model' => $model->getName(),
                'prompt' => $payload,
            ]),
        ]);
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $response = $response->toArray();
        if (!isset($response['data'][0])) {
            throw new RuntimeException('No image generated.');
        }

        $images = [];
        foreach ($response['data'] as $image) {
            if ('url' === $options['response_format']) {
                $images[] = new UrlImage($image['url']);

                continue;
            }

            $images[] = new Base64Image($image['b64_json']);
        }

        return new ImageResponse($image['revised_prompt'] ?? null, ...$images);
    }
}
