<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Meta;

use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;

final class LlamaPromptConverter
{
    public function convertToPrompt(MessageBagInterface $messageBag): string
    {
        $messages = [];

        /** @var UserMessage|SystemMessage|AssistantMessage $message */
        foreach ($messageBag->getMessages() as $message) {
            $messages[] = self::convertMessage($message);
        }

        $messages = array_filter($messages, fn ($message) => '' !== $message);

        return trim(implode(\PHP_EOL.\PHP_EOL, $messages)).\PHP_EOL.\PHP_EOL.'<|start_header_id|>assistant<|end_header_id|>';
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

        // Handling of UserMessage
        $count = \count($message->content);

        $contentParts = [];
        if ($count > 1) {
            foreach ($message->content as $value) {
                if ($value instanceof Text) {
                    $contentParts[] = $value->text;
                }

                if ($value instanceof ImageUrl) {
                    $contentParts[] = $value->url;
                }
            }
        } elseif (1 === $count) {
            $value = $message->content[0];
            if ($value instanceof Text) {
                $contentParts[] = $value->text;
            }

            if ($value instanceof ImageUrl) {
                $contentParts[] = $value->url;
            }
        } else {
            throw new RuntimeException('Unsupported message type.');
        }

        $content = implode(\PHP_EOL, $contentParts);

        return trim(<<<USER
            <|start_header_id|>{$message->getRole()->value}<|end_header_id|>

            {$content}<|eot_id|>
            USER);
    }
}
