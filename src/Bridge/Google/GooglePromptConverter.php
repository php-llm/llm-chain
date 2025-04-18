<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PhpLlm\LlmChain\Model\Message\Role;
use PhpLlm\LlmChain\Model\Message\UserMessage;

final class GooglePromptConverter
{
    /**
     * @return array{
     *     contents: list<array{
     *         role: 'model'|'user',
     *         parts: list<array{inline_data?: array{mime_type: string, data: string}|array{text: string}}>
     *     }>,
     *     system_instruction?: array{parts: array{text: string}}
     * }
     */
    public function convertToPrompt(MessageBagInterface $bag): array
    {
        $body = ['contents' => []];

        $systemMessage = $bag->getSystemMessage();
        if (null !== $systemMessage) {
            $body['system_instruction'] = [
                'parts' => ['text' => $systemMessage->content],
            ];
        }

        foreach ($bag->withoutSystemMessage()->getMessages() as $message) {
            $body['contents'][] = [
                'role' => $message->getRole()->equals(Role::Assistant) ? 'model' : 'user',
                'parts' => $this->convertMessage($message),
            ];
        }

        return $body;
    }

    /**
     * @return list<array{inline_data?: array{mime_type: string, data: string}|array{text: string}}>
     */
    private function convertMessage(MessageInterface $message): array
    {
        if ($message instanceof AssistantMessage) {
            return [['text' => $message->content]];
        }

        if ($message instanceof UserMessage) {
            $parts = [];
            foreach ($message->content as $content) {
                if ($content instanceof Text) {
                    $parts[] = ['text' => $content->text];
                }
                if ($content instanceof Image) {
                    $parts[] = ['inline_data' => [
                        'mime_type' => $content->getFormat(),
                        'data' => $content->asBase64(),
                    ]];
                }
            }

            return $parts;
        }

        return [];
    }
}
