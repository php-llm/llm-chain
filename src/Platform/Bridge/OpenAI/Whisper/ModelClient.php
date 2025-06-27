<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper\Task;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface as BaseModelClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class ModelClient implements BaseModelClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Whisper;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        // Extract task from options if provided, default to transcription for backward compatibility
        $task = $options['task'] ?? Task::TRANSCRIPTION;
        unset($options['task']);

        $endpoint = match ($task) {
            Task::TRANSCRIPTION => 'transcriptions',
            Task::TRANSLATION => 'translations',
            default => 'transcriptions',
        };

        return $this->httpClient->request('POST', "https://api.openai.com/v1/audio/{$endpoint}", [
            'auth_bearer' => $this->apiKey,
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'body' => array_merge($options, $payload, ['model' => $model->getName()]),
        ]);
    }
}
