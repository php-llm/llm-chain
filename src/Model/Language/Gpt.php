<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Language;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Platform\OpenAI\Platform;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\ChoiceResponse;
use PhpLlm\LlmChain\Response\ResponseInterface;
use PhpLlm\LlmChain\Response\StreamResponse;
use PhpLlm\LlmChain\Response\TextResponse;
use PhpLlm\LlmChain\Response\ToolCall;
use PhpLlm\LlmChain\Response\ToolCallResponse;

final class Gpt implements LanguageModel
{
    public const GPT_35_TURBO = 'gpt-3.5-turbo';
    public const GPT_35_TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';
    public const GPT_4 = 'gpt-4';
    public const GPT_4_TURBO = 'gpt-4-turbo';
    public const GPT_4O = 'gpt-4o';
    public const GPT_4O_MINI = 'gpt-4o-mini';
    public const O1_MINI = 'o1-mini';
    public const O1_PREVIEW = 'o1-preview';

    /**
     * @param array<mixed> $options The default options for the model usage
     */
    public function __construct(
        private readonly Platform $platform,
        private readonly string $version = self::GPT_4O,
        private readonly array $options = ['temperature' => 1.0],
        private bool $supportsImageInput = false,
        private bool $supportsStructuredOutput = false,
    ) {
        if (false === $this->supportsImageInput) {
            $this->supportsImageInput = in_array($this->version, [self::GPT_4_TURBO, self::GPT_4O, self::GPT_4O_MINI, self::O1_MINI, self::O1_PREVIEW], true);
        }

        if (false === $this->supportsStructuredOutput) {
            $this->supportsStructuredOutput = in_array($this->version, [self::GPT_4O, self::GPT_4O_MINI], true);
        }
    }

    /**
     * @param array<string, mixed> $options The options to be used for this specific call.
     *                                      Can overwrite default options.
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface
    {
        $body = array_merge($this->options, $options, [
            'model' => $this->version,
            'messages' => $messages,
        ]);

        $response = $this->platform->request('chat/completions', $body);

        if ($response instanceof \Generator) {
            if ($this->streamIsToolCall($response)) {
                return new ToolCallResponse(...$this->convertStreamToToolCalls($response));
            } else {
                return new StreamResponse($this->convertStream($response));
            }
        }

        if (!isset($response['choices'])) {
            throw new RuntimeException('Response does not contain choices');
        }

        /** @var Choice[] $choices */
        $choices = array_map([$this, 'convertChoice'], $response['choices']);

        if (1 !== count($choices)) {
            return new ChoiceResponse(...$choices);
        }

        if ($choices[0]->hasToolCall()) {
            return new ToolCallResponse(...$choices[0]->getToolCalls());
        }

        return new TextResponse($choices[0]->getContent());
    }

    public function supportsToolCalling(): bool
    {
        return true;
    }

    public function supportsImageInput(): bool
    {
        return $this->supportsImageInput;
    }

    public function supportsStructuredOutput(): bool
    {
        return $this->supportsStructuredOutput;
    }

    private function streamIsToolCall(\Generator $response): bool
    {
        $data = $response->current();

        return isset($data['choices'][0]['delta']['tool_calls']);
    }

    private function convertStream(\Generator $generator): \Generator
    {
        foreach ($generator as $data) {
            if (!isset($data['choices'][0]['delta']['content'])) {
                continue;
            }

            yield $data['choices'][0]['delta']['content'];
        }
    }

    /**
     * @return ToolCall[]
     */
    private function convertStreamToToolCalls(\Generator $response): array
    {
        $toolCalls = [];
        foreach ($response as $data) {
            if (!isset($data['choices'][0]['delta']['tool_calls'])) {
                continue;
            }

            foreach ($data['choices'][0]['delta']['tool_calls'] as $i => $toolCall) {
                if (isset($toolCall['id'])) {
                    // initialize tool call
                    $toolCalls[$i] = [
                        'id' => $toolCall['id'],
                        'function' => $toolCall['function'],
                    ];
                    continue;
                }

                // add arguments delta to tool call
                $toolCalls[$i]['function']['arguments'] .= $toolCall['function']['arguments'];
            }
        }

        return array_map([$this, 'convertToolCall'], $toolCalls);
    }

    /**
     * @param array{
     *     index: integer,
     *     message: array{
     *         role: 'assistant',
     *         content: ?string,
     *         tool_calls: array{
     *             id: string,
     *             type: 'function',
     *             function: array{
     *                 name: string,
     *                 arguments: string
     *             },
     *         },
     *         refusal: ?mixed
     *     },
     *     logprobs: string,
     *     finish_reason: 'stop'|'length'|'tool_calls'|'content_filter',
     * } $choice
     */
    private function convertChoice(array $choice): Choice
    {
        if ('tool_calls' === $choice['finish_reason']) {
            return new Choice(toolCalls: array_map([$this, 'convertToolCall'], $choice['message']['tool_calls']));
        }

        if ('stop' === $choice['finish_reason']) {
            return new Choice($choice['message']['content']);
        }

        throw new RuntimeException(sprintf('Unsupported finish reason "%s".', $choice['finish_reason']));
    }

    /**
     * @param array{
     *     id: string,
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         arguments: string
     *     }
     * } $toolCall
     */
    private function convertToolCall(array $toolCall): ToolCall
    {
        $arguments = json_decode($toolCall['function']['arguments'], true, JSON_THROW_ON_ERROR);

        return new ToolCall($toolCall['id'], $toolCall['function']['name'], $arguments);
    }
}
