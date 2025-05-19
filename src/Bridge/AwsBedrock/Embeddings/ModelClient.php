<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\AwsBedrock\Embeddings;

use PhpLlm\LlmChain\Bridge\AwsBedrock\BedrockRequestSigner;
use PhpLlm\LlmChain\Bridge\AwsBedrock\Embeddings;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformResponseFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelClient implements PlatformResponseFactory
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter] private BedrockRequestSigner $requestSigner,
        private string $region,
    ) {
        Assert::stringNotEmpty($region, 'The region must not be empty.');
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Embeddings;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        $signedParameters = $this->requestSigner->signRequest(
            method: 'POST',
            endpoint: $bedrockEndpoint = sprintf(
                'https://bedrock-runtime.%s.amazonaws.com/model/%s/invoke',
                $this->region,
                $model->getName(),
            ),
            jsonBody: is_string($input) ? [
                'inputText' => $input,
            ] : $input
        );

        return $this->httpClient->request('POST', $bedrockEndpoint, $signedParameters);
    }
}
