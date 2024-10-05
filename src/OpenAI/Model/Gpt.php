<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\ChoiceResponse;
use PhpLlm\LlmChain\Response\ResponseInterface;
use PhpLlm\LlmChain\Response\StreamResponse;
use PhpLlm\LlmChain\Response\TextResponse;
use PhpLlm\LlmChain\Response\ToolCall;
use PhpLlm\LlmChain\Response\ToolCallResponse;

final class Gpt implements LanguageModel
{
    /**
     * @param array<mixed> $options The default options for the model usage
     */
    public function __construct(
        private readonly Platform $platform,
        private ?Version $version = null,
        private readonly array $options = ['temperature' => 1.0],
    ) {
        $this->version ??= Version::gpt4o();
    }

    /**
     * @param array<mixed> $options The options to be used for this specific call.
     *                              Can overwrite default options.
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface
    {
        $body = array_merge($this->options, $options, [
            'model' => $this->version->name,
            'messages' => $messages,
        ]);

        $response = $this->platform->request('chat/completions', $body);

        if ($response instanceof \Generator) {
            return new StreamResponse($this->convertStream($response));
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
        return $this->version->supportImageInput;
    }

    public function supportsStructuredOutput(): bool
    {
        return $this->version->supportStructuredOutput;
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
