<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic\Model;

use PhpLlm\LlmChain\Anthropic\ClaudeRuntime;
use PhpLlm\LlmChain\Anthropic\Model\Claude\Version;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\Response;

final readonly class Claude implements LanguageModel
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
        $body = [
            'model' => $this->version->value,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'system' => $system->content,
            'messages' => $messages->withoutSystemMessage(),
        ];

        $response = $this->runtime->request(array_merge($body, $options));

        return new Response(new Choice($response['content'][0]['text']));
    }

    public function supportsToolCalling(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }
}
