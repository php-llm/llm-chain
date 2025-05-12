<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\Metadata\MetadataAwareTrait;

abstract class BaseResponse implements ResponseInterface
{
    use MetadataAwareTrait;
    use RawResponseAwareTrait;
}
