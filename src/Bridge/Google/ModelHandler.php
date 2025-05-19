<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\Choice;
use PhpLlm\LlmChain\Model\Response\ChoiceResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\StreamResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelHandler implements ModelClient, ResponseConverter
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
        private GooglePromptConverter $promptConverter = new GooglePromptConverter(),
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Gemini && $input instanceof MessageBagInterface;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        Assert::isInstanceOf($input, MessageBagInterface::class);

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:%s',
            $model->getName(),
            $options['stream'] ?? false ? 'streamGenerateContent' : 'generateContent',
        );

        $generationConfig = ['generationConfig' => $options];
        unset($generationConfig['generationConfig']['stream']);
        unset($generationConfig['generationConfig']['tools']);

        if (isset($options['tools'])) {
            $toolConfig = array_map(
                function (Metadata $toolConfig) {
                    $parameters = $toolConfig->parameters;
                    unset($parameters['additionalProperties']);

                    return [
                        'functionDeclarations' => [
                            [
                                'description' => $toolConfig->description,
                                'name' => $toolConfig->name,
                                'parameters' => $parameters,
                            ],
                        ],
                    ];
                }, $options['tools']
            );

            unset($options['tools']);
            $generationConfig['tools'] = $toolConfig;
        }

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'x-goog-api-key' => $this->apiKey,
            ],
            'json' => array_merge($generationConfig, $this->promptConverter->convertToPrompt($input)),
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if ($options['stream'] ?? false) {
            return new StreamResponse($this->convertStream($response));
        }

        $data = $response->toArray();

        if (!isset($data['candidates'][0]['content']['parts'][0])) {
            throw new RuntimeException('Response does not contain any content');
        }

        /** @var Choice[] $choices */
        $choices = array_map(
            fn ($content) => $this->convertChoice(
                $content
            ),
            $data['candidates']
        );

        if (1 !== count($choices)) {
            return new ChoiceResponse(...$choices);
        }

        if ($choices[0]->hasToolCall()) {
            return new ToolCallResponse(...$choices[0]->getToolCalls());
        }

        return new TextResponse($choices[0]->getContent());
    }

    private function convertStream(ResponseInterface $response): \Generator
    {
        foreach ((new EventSourceHttpClient())->stream($response) as $chunk) {
            if ($chunk->isFirst() || $chunk->isLast()) {
                continue;
            }

            $jsonDelta = trim($chunk->getContent());

            // Remove leading/trailing brackets
            if (str_starts_with($jsonDelta, '[') || str_starts_with($jsonDelta, ',')) {
                $jsonDelta = substr($jsonDelta, 1);
            }
            if (str_ends_with($jsonDelta, ']')) {
                $jsonDelta = substr($jsonDelta, 0, -1);
            }

            // Split in case of multiple JSON objects
            $deltas = explode(",\r\n", $jsonDelta);

            foreach ($deltas as $delta) {
                if ('' === $delta) {
                    continue;
                }

                try {
                    $data = json_decode($delta, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    dump($delta);
                    throw new RuntimeException('Failed to decode JSON response', 0, $e);
                }

                if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    continue;
                }

                yield $data['candidates'][0]['content']['parts'][0]['text'];
            }
        }
    }

    private function convertChoice(array $choice): Choice
    {
        $stopReason = $choice['finishReason'];

        $contentPart = $choice['content']['parts'][0] ?? [];

        if (isset($contentPart['functionCall'])) {
            return new Choice(
                toolCalls: [
                    $this->convertToolCall($contentPart['functionCall']),
                ]
            );
        }

        if (isset($contentPart['text'])) {
            return new Choice(
                $contentPart['text']
            );
        }

        throw new RuntimeException(sprintf('Unsupported finish reason "%s".', $stopReason));
    }

    private function convertToolCall(array $toolCall): ToolCall
    {
        return new ToolCall($toolCall['id'] ?? '', $toolCall['name'], $toolCall['args']);
    }
}
