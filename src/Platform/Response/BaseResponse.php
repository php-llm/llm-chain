<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\Metadata\MetadataAwareTrait;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
abstract class BaseResponse implements ResponseInterface
{
    use MetadataAwareTrait;
    use RawResponseAwareTrait;
}
