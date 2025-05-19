<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\AwsBedrock;

use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Model\Response\StreamResponse;

final class TokenOutputProcessor implements OutputProcessor
{
    public function processOutput(Output $output): void
    {
        if ($output->response instanceof StreamResponse) {
            // Streams have to be handled manually as the tokens are part of the streamed chunks
            return;
        }

        $rawResponse = $output->response->getRawResponse();
        if (null === $rawResponse) {
            return;
        }

        $metadata = $output->response->getMetadata();

        $content = $rawResponse->toArray(false);

        if (!\array_key_exists('usage', $content)) {
            return;
        }

        $metadata->add('prompt_tokens', $content['usage']['inputTokens'] ?? null);
        $metadata->add('completion_tokens', $content['usage']['outputTokens'] ?? null);
        $metadata->add('total_tokens', $content['usage']['totalTokens'] ?? null);
    }
}
