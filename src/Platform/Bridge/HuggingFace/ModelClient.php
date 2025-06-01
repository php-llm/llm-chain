<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace;

use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface as PlatformModelClient;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class ModelClient implements PlatformModelClient
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private string $provider,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    public function supports(Model $model): bool
    {
        return true;
    }

    /**
     * The difference in HuggingFace here is that we treat the payload as the options for the request not only the body.
     */
    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        // Extract task from options if provided
        $task = $options['task'] ?? null;
        unset($options['task']);

        return $this->httpClient->request('POST', $this->getUrl($model, $task), [
            'auth_bearer' => $this->apiKey,
            ...$this->getPayload($payload, $options),
        ]);
    }

    private function getUrl(Model $model, ?string $task): string
    {
        $endpoint = Task::FEATURE_EXTRACTION === $task ? 'pipeline/feature-extraction' : 'models';
        $url = \sprintf('https://router.huggingface.co/%s/%s/%s', $this->provider, $endpoint, $model->getName());

        if (Task::CHAT_COMPLETION === $task) {
            $url .= '/v1/chat/completions';
        }

        return $url;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function getPayload(array|string $payload, array $options): array
    {
        // Expect JSON input if string or not
        if (\is_string($payload) || !(isset($payload['body']) || isset($payload['json']))) {
            $payload = ['json' => ['inputs' => $payload]];

            if (0 !== \count($options)) {
                $payload['json']['parameters'] = $options;
            }
        }

        // Merge options into JSON payload
        if (isset($payload['json'])) {
            $payload['json'] = array_merge($payload['json'], $options);
        }

        $payload['headers'] ??= ['Content-Type' => 'application/json'];

        return $payload;
    }
}
