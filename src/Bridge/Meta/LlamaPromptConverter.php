<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Meta;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;

final class LlamaPromptConverter
{
    public function convertToPrompt(MessageBag $messageBag): string
    {
        $messages = [];

        /** @var UserMessage|SystemMessage|AssistantMessage $message */
        foreach ($messageBag->getIterator() as $message) {
            $messages[] = self::convertMessage($message);
        }

        $messages = array_filter($messages, fn ($message) => '' !== $message);

        return trim(implode(PHP_EOL.PHP_EOL, $messages)).PHP_EOL.PHP_EOL.'<|start_header_id|>assistant<|end_header_id|>';
    }

    public function convertMessage(UserMessage|SystemMessage|AssistantMessage $message): string
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
}
