<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Definition\Exception;

use OCA\Talk\Vendor\CuyZ\Valinor\Type\ObjectType;
use LogicException;

/** @internal */
final class InvalidTypeAliasImportClass extends LogicException
{
    public function __construct(ObjectType $type, string $className)
    {
        parent::__construct(
            "Cannot import a type alias from unknown class `$className` in class `{$type->className()}`.",
            1638535486
        );
    }
}
