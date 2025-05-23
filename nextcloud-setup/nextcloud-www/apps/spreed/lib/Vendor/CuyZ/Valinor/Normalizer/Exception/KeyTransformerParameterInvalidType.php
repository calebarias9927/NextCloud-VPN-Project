<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Normalizer\Exception;

use OCA\Talk\Vendor\CuyZ\Valinor\Definition\MethodDefinition;
use LogicException;

/** @internal */
final class KeyTransformerParameterInvalidType extends LogicException
{
    public function __construct(MethodDefinition $method)
    {
        parent::__construct(
            "Key transformer parameter must be a string, {$method->parameters->at(0)->type->toString()} given for `$method->signature`.",
            1701706316,
        );
    }
}
