<?php

declare(strict_types=1);

namespace OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Tree\Builder;

use OCA\Talk\Vendor\CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Object\Exception\CannotFindObjectBuilder;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Object\Exception\InvalidSource;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Tree\Message\Message;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use OCA\Talk\Vendor\CuyZ\Valinor\Mapper\Tree\Shell;
use OCA\Talk\Vendor\CuyZ\Valinor\Type\ObjectType;
use Throwable;

use function assert;
use function count;

/** @internal */
final class ObjectNodeBuilder implements NodeBuilder
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        // @infection-ignore-all
        assert($type instanceof ObjectType);

        if ($type->accepts($shell->value())) {
            return TreeNode::leaf($shell, $shell->value());
        }

        if ($shell->enableFlexibleCasting() && $shell->value() === null) {
            $shell = $shell->withValue([]);
        } else {
            $shell = $shell->transformIteratorToArray();
        }

        $class = $this->classDefinitionRepository->for($type);
        $builders = $this->objectBuilderFactory->for($class);

        foreach ($builders as $builder) {
            $argumentsValues = ArgumentsValues::forClass($builder->describeArguments(), $shell);

            if ($argumentsValues->hasInvalidValue()) {
                if (count($builders) === 1) {
                    return TreeNode::error($shell, new InvalidSource($shell->value(), $builder->describeArguments()));
                }

                continue;
            }

            $children = $this->children($shell, $argumentsValues, $rootBuilder);

            try {
                $object = $this->buildObject($builder, $children);
            } catch (Message $exception) {
                if ($exception instanceof UserlandError) {
                    $exception = ($this->exceptionFilter)($exception->previous());
                }

                return TreeNode::error($shell, $exception);
            }

            if ($argumentsValues->hadSingleArgument()) {
                $node = TreeNode::flattenedBranch($shell, $object, $children[0]);
            } else {
                $node = TreeNode::branch($shell, $object, $children);
                $node = $node->checkUnexpectedKeys();
            }

            if ($node->isValid() || count($builders) === 1) {
                return $node;
            }
        }

        return TreeNode::error($shell, new CannotFindObjectBuilder($builders));
    }

    /**
     * @return list<TreeNode>
     */
    private function children(Shell $shell, ArgumentsValues $arguments, RootNodeBuilder $rootBuilder): array
    {
        $children = [];

        foreach ($arguments as $argument) {
            $name = $argument->name();
            $type = $argument->type();
            $attributes = $argument->attributes();

            $child = $shell->child($name, $type, $attributes);

            if ($arguments->hasValue($name)) {
                $child = $child->withValue($arguments->getValue($name));
            }

            $children[] = $rootBuilder->build($child);
        }

        return $children;
    }

    /**
     * @param list<TreeNode> $children
     */
    private function buildObject(ObjectBuilder $builder, array $children): ?object
    {
        $arguments = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return null;
            }

            $arguments[$child->name()] = $child->value();
        }

        return $builder->build($arguments);
    }
}
