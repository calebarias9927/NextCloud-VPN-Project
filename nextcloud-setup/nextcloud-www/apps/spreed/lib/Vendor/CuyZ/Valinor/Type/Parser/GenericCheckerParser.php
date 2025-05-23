<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser;

use OCA\Talk\Vendor\CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassTemplatesResolver;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\CompositeTraversableType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\GenericType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\IntegerType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\StringType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Type;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Types\ArrayKeyType;

use function array_keys;

/** @internal */
final class GenericCheckerParser implements TypeParser
{
    public function __construct(
        private TypeParser $delegate,
        private TypeParserFactory $typeParserFactory,
    ) {}

    public function parse(string $raw): Type
    {
        $type = $this->delegate->parse($raw);

        $this->checkGenerics($type);

        return $type;
    }

    private function checkGenerics(Type $type): void
    {
        if ($type instanceof CompositeTraversableType) {
            foreach ($type->traverse() as $subType) {
                $this->checkGenerics($subType);
            }
        }

        if (! $type instanceof GenericType) {
            return;
        }

        $templates = (new ClassTemplatesResolver())->resolveTemplatesFrom($type->className());

        if ($templates === []) {
            return;
        }

        $generics = $type->generics();

        $parser = $this->typeParserFactory->buildAdvancedTypeParserForClass($type);

        foreach ($templates as $templateName => $template) {
            if (! isset($generics[$templateName])) {
                throw new AssignedGenericNotFound($type->className(), ...array_keys($templates));
            }

            array_shift($templates);

            if ($template === null) {
                // If no template is provided, it defaults to mixed type.
                continue;
            }

            $genericType = $generics[$templateName];

            try {
                $templateType = $parser->parse($template);
            } catch (InvalidType $invalidType) {
                throw new InvalidClassTemplate($type->className(), $templateName, $invalidType);
            }

            if ($templateType instanceof ArrayKeyType && $genericType instanceof StringType) {
                $genericType = ArrayKeyType::string();
            }

            if ($templateType instanceof ArrayKeyType && $genericType instanceof IntegerType) {
                $genericType = ArrayKeyType::integer();
            }

            if (! $genericType->matches($templateType)) {
                throw new InvalidAssignedGeneric($genericType, $templateType, $templateName, $type->className());
            }
        }
    }
}
