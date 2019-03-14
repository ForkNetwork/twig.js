<?php

namespace TwigJs\Compiler\Expression\Filter;

use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class DefaultCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\Expression\Filter\DefaultFilter';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\Expression\Filter\DefaultFilter) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of \Twig_Node_Expression_Filter_Default, but got "%s".',
                    get_class($node)
                )
            );
        }

        $compiler->subcompile($node->getNode('node'));
    }
}
