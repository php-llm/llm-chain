<?php

namespace PhpLlm\LlmChain\Model\Message;

interface MessageVisitor
{
    public function visitSystemMessage(SystemMessage $message): array;

    public function visitUserMessage(UserMessage $message): array;

    public function visitAssistantMessage(AssistantMessage $message): array;

    public function visitToolCallMessage(ToolCallMessage $message): array;
}
