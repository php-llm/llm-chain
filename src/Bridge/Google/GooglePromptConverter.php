<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Message\Content\File;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PhpLlm\LlmChain\Model\Message\Role;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
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
            return [
                array_filter(
                    [
                        'text' => $message->content,
                        'functionCall' => ($message->toolCalls[0] ?? null) ? [
                            'id' => $message->toolCalls[0]->id,
                            'name' => $message->toolCalls[0]->name,
                            'args' => $message->toolCalls[0]->arguments,
                        ] : null,
                    ],
                    fn ($content) => !is_null($content)
                ),
            ];
        }

        if ($message instanceof ToolCallMessage) {
            $responseContent = json_validate($message->content) ?
                json_decode($message->content, true) : $message->content;

            return [
                [
                    'functionResponse' => array_filter(
                        [
                            'id' => $message->toolCall->id,
                            'name' => $message->toolCall->name,
                            'response' => is_array($responseContent) ? $responseContent : [
                                'rawResponse' => $responseContent, // Gemini expects the response to be an object, but not everyone uses objects as their responses.
                            ],
                        ], fn ($value) => $value
                    ),
                ],
            ];
        }

        if ($message instanceof UserMessage) {
            $parts = [];
            foreach ($message->content as $content) {
                if ($content instanceof Text) {
                    $parts[] = ['text' => $content->text];
                }
                if ($content instanceof Image || $content instanceof Audio || $content instanceof File) {
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
