<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Model\Response\Metadata\MetadataAwareTrait;

abstract class BaseResponse implements ResponseInterface
{
    use MetadataAwareTrait;
    use RawResponseAwareTrait;
}
