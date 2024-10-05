<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Language;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\MessageInterface;
use PhpLlm\LlmChain\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Ollama;
use PhpLlm\LlmChain\Platform\Replicate;
use PhpLlm\LlmChain\Response\TextResponse;

final readonly class Llama implements LanguageModel
{
    public function __construct(
        private Replicate|Ollama $platform,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): TextResponse
    {
        $systemMessage = $messages->getSystemMessage();
        $endpoint = $this->platform instanceof Replicate ? 'predictions' : 'chat';

        $response = $this->platform->request('meta/meta-llama-3.1-405b-instruct', $endpoint, [
            'system' => $systemMessage?->content,
            'prompt' => self::convertToPrompt($messages->withoutSystemMessage()),
        ]);

        return new TextResponse(implode('', $response['output']));
    }

    private static function convertToPrompt(MessageBag $messageBag): string
    {
        $messages = [];

        /** @var MessageInterface $message */
        foreach ($messageBag->getIterator() as $message) {
            if ($message instanceof UserMessage) {
                $content = $message->content[0]->text;
            } elseif ($message instanceof AssistantMessage && null !== $message->content) {
                $content = $message->content;
            } else {
                continue;
            }

            $messages[] = sprintf('%s: %s', ucfirst($message->getRole()->value), $content);
        }

        return implode(PHP_EOL, $messages);
    }

    public function supportsToolCalling(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsImageInput(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }
}
