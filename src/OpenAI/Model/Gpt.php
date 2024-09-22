<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\Response;
use PhpLlm\LlmChain\Response\ToolCall;

final class Gpt implements LanguageModel
{
    public function __construct(
        private Runtime $runtime,
        private Version $version = Version::GPT_4o,
        private float $temperature = 1.0,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): Response
    {
        $body = [
            'model' => $this->version->value,
            'temperature' => $this->temperature,
            'messages' => $messages,
        ];

        $response = $this->runtime->request('chat/completions', array_merge($body, $options));

        return new Response(...array_map([$this, 'convertChoice'], $response['choices']));
    }

    public function supportsToolCalling(): bool
    {
        return true;
    }

    public function supportsStructuredOutput(): bool
    {
        return $this->version->supportsStructuredOutput();
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

        throw new \RuntimeException('Unsupported finish reason');
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
