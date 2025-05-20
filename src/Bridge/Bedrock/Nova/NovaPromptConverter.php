<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Bedrock\Nova;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;

use function Symfony\Component\String\u;

final class NovaPromptConverter
{
    /**
     * @return array<array<string, mixed>>
     */
    public function convertToPrompt(MessageBagInterface $messageBag): array
    {
        $messages = [];

        /** @var UserMessage|SystemMessage|AssistantMessage $message */
        foreach ($messageBag->getMessages() as $message) {
            $messages[] = $this->convertMessage($message);
        }

        return $messages;
    }

    /**
     * @return array<string, mixed>
     */
    public function convertMessage(UserMessage|SystemMessage|AssistantMessage|ToolCallMessage $message): array
    {
        $convertedMessage = [];
        $convertedMessage['role'] = $message->getRole()->value;
        $content = [];

        if ($message instanceof ToolCallMessage) {
            return [
                'role' => 'user',
                'content' => [
                    [
                        'toolResult' => [
                            'toolUseId' => $message->toolCall->id,
                            'content' => [
                                'text' => $message->content,
                            ],
                        ],
                    ],
                ],
            ];
        }

        if ($message instanceof AssistantMessage && $message->hasToolCalls()) {
            return [
                'role' => 'assistant',
                'content' => array_map(static function (ToolCall $toolCall) {
                    return [
                        'toolUse' => [
                            'toolUseId' => $toolCall->id,
                            'name' => $toolCall->name,
                            'input' => empty($toolCall->arguments) ? new \stdClass() : $toolCall->arguments,
                        ],
                    ];
                }, $message->toolCalls),
            ];
        }

        if (is_string($message->content)) {
            $convertedMessage['content'][]['text'] = $message->content;
        } else {
            foreach ($message->content as $value) {
                $contentPart = [];
                if ($value instanceof Text) {
                    $contentPart['text'] = $value->text;
                } elseif ($value instanceof Image) {
                    $contentPart['image']['format'] = u($value->getFormat())->replace('jpg', 'jpeg')->toString();
                    $contentPart['image']['source']['bytes'] = $value->asBase64();
                } else {
                    throw new RuntimeException('Unsupported message type.');
                }
                $convertedMessage['content'][] = $contentPart;
            }
        }

        return $convertedMessage;
    }
}
