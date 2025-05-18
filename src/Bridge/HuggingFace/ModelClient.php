<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\HuggingFace;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformModelClient;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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

    public function supports(Model $model, object|array|string $input): bool
    {
        return true;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        $task = $options['task'] ?? null;
        unset($options['task']);

        return $this->httpClient->request('POST', $this->getUrl($model, $input, $task), [
            'auth_bearer' => $this->apiKey,
            ...$this->getPayload($input, $options),
        ]);
    }

    /**
     * @param array<mixed>|string|object $input
     */
    private function getUrl(Model $model, object|array|string $input, ?string $task): string
    {
        $endpoint = Task::FEATURE_EXTRACTION === $task ? 'pipeline/feature-extraction' : 'models';
        $url = sprintf('https://router.huggingface.co/%s/%s/%s', $this->provider, $endpoint, $model->getName());

        if ($input instanceof MessageBagInterface) {
            $url .= '/v1/chat/completions';
        }

        return $url;
    }

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     *
     * @return array<string, mixed>
     */
    private function getPayload(object|array|string $input, array $options): array
    {
        if ($input instanceof Audio || $input instanceof Image) {
            return [
                'headers' => ['Content-Type' => $input->getFormat()],
                'body' => $input->asBinary(),
            ];
        }

        if ($input instanceof MessageBagInterface) {
            return [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'messages' => $input,
                    ...$options,
                ],
            ];
        }

        if (is_string($input) || is_array($input)) {
            $payload = [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'inputs' => $input,
                ],
            ];

            if (0 !== count($options)) {
                $payload['json']['parameters'] = $options;
            }

            return $payload;
        }

        throw new InvalidArgumentException('Unsupported input type: '.get_debug_type($input));
    }
}
