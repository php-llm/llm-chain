<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\Whisper;

use PhpLlm\LlmChain\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformResponseFactory;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelClient implements PlatformResponseFactory, PlatformResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Whisper && $input instanceof File;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        assert($input instanceof File);

        return $this->httpClient->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
            'auth_bearer' => $this->apiKey,
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'body' => array_merge($options, $model->getOptions(), [
                'model' => $model->getVersion(),
                'file' => fopen($input->path, 'r'),
            ]),
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        return new TextResponse($data['text']);
    }
}
