<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\Metadata\MetadataAwareTrait;

/**
 * Base response of converted response classes.
 *
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
abstract class BaseResponse implements ResponseInterface
{
    use MetadataAwareTrait;
    use RawResponseAwareTrait;
}
