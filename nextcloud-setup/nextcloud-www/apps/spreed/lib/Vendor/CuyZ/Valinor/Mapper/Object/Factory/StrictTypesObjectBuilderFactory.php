<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Object\Factory;

use OCA\Talk\Vendor\CuyZ\Valinor\Definition\ClassDefinition;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Object\Exception\PermissiveTypeNotAllowed;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\CompositeType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Type;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Types\MixedType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Types\UndefinedObjectType;

/** @internal */
final class StrictTypesObjectBuilderFactory implements ObjectBuilderFactory
{
    public function __construct(private ObjectBuilderFactory $delegate) {}

    public function for(ClassDefinition $class): array
    {
        $builders = $this->delegate->for($class);

        foreach ($builders as $builder) {
            $arguments = $builder->describeArguments();

            foreach ($arguments as $argument) {
                $argumentSignature = $builder->signatureForArgument($argument->name());

                $this->checkPresenceOfPermissiveType($argumentSignature, $argument->type());
            }
        }

        return $builders;
    }

    private function checkPresenceOfPermissiveType(string $signature, Type $type): void
    {
        if ($type instanceof CompositeType) {
            foreach ($type->traverse() as $subType) {
                self::checkPresenceOfPermissiveType($signature, $subType);
            }
        }

        if ($type instanceof MixedType || $type instanceof UndefinedObjectType) {
            throw new PermissiveTypeNotAllowed($signature, $type);
        }
    }
}
