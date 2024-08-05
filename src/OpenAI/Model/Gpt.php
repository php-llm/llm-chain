<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime;

final class Gpt implements LanguageModel
{
    public function __construct(
        private Runtime $client,
        private Version $version = Version::GPT_4o,
        private float   $temperature = 1.0,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): array
    {
        $body = [
            'model' => $this->version->value,
            'temperature' => $this->temperature,
            'messages' => $messages,
        ];

        return $this->client->request('chat/completions', array_merge($body, $options));
    }
}
