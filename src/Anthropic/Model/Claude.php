<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic\Model;

use PhpLlm\LlmChain\Anthropic\ClaudeRuntime;
use PhpLlm\LlmChain\Anthropic\Model\Claude\Version;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\Response;

final class Claude implements LanguageModel
{
    public function __construct(
        private ClaudeRuntime $runtime,
        private Version $version = Version::SONNET_35,
        private float $temperature = 1.0,
        private int $maxTokens = 1000,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): Response
    {
        $system = $messages->getSystemMessage();

        $response = $this->runtime->request([
            'model' => $this->version->value,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'system' => $system->content,
            'messages' => $messages->withoutSystemMessage(),
        ]);

        return new Response(new Choice($response['content'][0]['text']));
    }

    public function hasToolSupport(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function hasStructuredOutputSupport(): bool
    {
        return false;
    }
}
