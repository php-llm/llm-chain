<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Model\Response\Metadata\ContainsMetadataInterface;

final class TokenOutputProcessor implements OutputProcessor
{
    public function processOutput(Output $output): void
    {
        if (!$output->response instanceof ContainsMetadataInterface || null === $output->httpResponse) {
            return;
        }

        $output->response->addMetadata(
            'remaining-tokens',
            (int) $output->httpResponse->getHeaders(false)['x-ratelimit-remaining-tokens'][0],
        );

        $content = $output->httpResponse->getContent(false);
        $content = \json_decode($content, true);

        if (!\array_key_exists('usage', $content)) {
            return;
        }

        $output->response->addMetadata('prompt_tokens', $content['usage']['prompt_tokens'] ?? null);
        $output->response->addMetadata('completion_tokens', $content['usage']['completion_tokens'] ?? null);
        $output->response->addMetadata('total_tokens', $content['usage']['total_tokens'] ?? null);
    }
}
