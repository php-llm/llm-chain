<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google;

use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessorInterface;
use PhpLlm\LlmChain\Platform\Response\StreamResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Adds grounding metadata from Google Search / URL context.
 *
 * @author Valtteri R <valtzu@gmail.com>
 */
final class GroundingOutputProcessor implements OutputProcessorInterface
{
    public function processOutput(Output $output): void
    {
        if ($output->response instanceof StreamResponse) {
            // Streams have to be handled manually as the tokens are part of the streamed chunks
            return;
        }

        $rawResponse = $output->response->getRawResponse()?->getRawObject();
        if (!$rawResponse instanceof ResponseInterface) {
            return;
        }

        $metadata = $output->response->getMetadata();

        $content = $rawResponse->toArray(false);

        if (!$grounding = $content['candidates'][0]['groundingMetadata'] ?? null) {
            return;
        }

        $metadata->add('grounding', $grounding);
    }
}
