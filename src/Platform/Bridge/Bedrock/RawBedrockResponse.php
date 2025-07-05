<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock;

use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Platform\Response\RawResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class RawBedrockResponse implements RawResponseInterface
{
    public function __construct(
        private InvokeModelResponse $invokeModelResponse,
    ) {
    }

    public function getRawData(): array
    {
        return json_decode($this->invokeModelResponse->getBody(), true, 512, \JSON_THROW_ON_ERROR);
    }

    public function getRawObject(): InvokeModelResponse
    {
        return $this->invokeModelResponse;
    }
}
