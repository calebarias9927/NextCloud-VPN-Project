<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Lexer\Token;

use BackedEnum;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Exception\Magic\ValueOfIncorrectSubType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Exception\Magic\ValueOfClosingBracketMissing;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Exception\Magic\ValueOfOpeningBracketMissing;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Type;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Types\EnumType;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\Types\UnionType;
use OCA\Talk\Vendor\CuyZ\Valinor\Utility\IsSingleton;

use function array_map;
use function array_values;
use function count;
use function is_a;

/** @internal */
final class ValueOfToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || !$stream->forward() instanceof OpeningBracketToken) {
            throw new ValueOfOpeningBracketMissing();
        }

        $subType = $stream->read();

        if ($stream->done() || !$stream->forward() instanceof ClosingBracketToken) {
            throw new ValueOfClosingBracketMissing($subType);
        }

        if (! $subType instanceof EnumType) {
            throw new ValueOfIncorrectSubType($subType);
        }

        if (! is_a($subType->className(), BackedEnum::class, true)) {
            throw new ValueOfIncorrectSubType($subType);
        }

        $cases = array_map(
            // @phpstan-ignore-next-line / We know it's a BackedEnum
            fn (BackedEnum $case) => ValueTypeFactory::from($case->value),
            array_values($subType->cases()),
        );

        if (count($cases) > 1) {
            return new UnionType(...$cases);
        }

        return $cases[0];
    }

    public function symbol(): string
    {
        return 'value-of';
    }
}
