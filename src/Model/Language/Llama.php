<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Language;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\SystemMessage;
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
            'system' => self::convertMessage($systemMessage ?? new SystemMessage('')),
            'prompt' => self::convertToPrompt($messages->withoutSystemMessage()),
        ]);

        return new TextResponse(implode('', $response['output']));
    }

    /**
     * @todo make method private, just for testing
     */
    public static function convertToPrompt(MessageBag $messageBag): string
    {
        $messages = [];

        /** @var UserMessage|SystemMessage|AssistantMessage $message */
        foreach ($messageBag->getIterator() as $message) {
            $messages[] = self::convertMessage($message);
        }

        return trim(implode(PHP_EOL.PHP_EOL, $messages));
    }

    /**
     * @todo make method private, just for testing
     */
    public static function convertMessage(UserMessage|SystemMessage|AssistantMessage $message): string
    {
        if ($message instanceof SystemMessage) {
            return trim(<<<SYSTEM
<|begin_of_text|><|start_header_id|>system<|end_header_id|>

{$message->content}<|eot_id|>
SYSTEM);
        }

        if ($message instanceof AssistantMessage) {
            if ('' === $message->content || null === $message->content) {
                return '';
            }

            return trim(<<<ASSISTANT
<|start_header_id|>{$message->getRole()->value}<|end_header_id|>

{$message->content}<|eot_id|>
ASSISTANT);
        }

        if ($message instanceof UserMessage) {
            $count = count($message->content);

            $contentParts = [];
            if ($count > 1) {
                foreach ($message->content as $value) {
                    if ($value instanceof Text) {
                        $contentParts[] = $value->text;
                    }

                    if ($value instanceof Image) {
                        $contentParts[] = $value->url;
                    }
                }
            } elseif (1 === $count) {
                $value = $message->content[0];
                if ($value instanceof Text) {
                    $contentParts[] = $value->text;
                }

                if ($value instanceof Image) {
                    $contentParts[] = $value->url;
                }
            } else {
                throw new RuntimeException('Unsupported message type.');
            }

            $content = implode(PHP_EOL, $contentParts);

            return trim(<<<USER
<|start_header_id|>{$message->getRole()->value}<|end_header_id|>

{$content}<|eot_id|>
USER);
        }

        throw new RuntimeException('Unsupported message type.'); // @phpstan-ignore-line
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
