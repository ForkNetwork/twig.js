<?php

namespace TwigJs\Compiler\Expression;

use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class MethodCallCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\Expression\MethodCallExpression';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\Expression\MethodCallExpression) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of %s, but got "%s".',
                    $this->getType(),
                    get_class($node)
                )
            );
        }

        $compiler
            ->subcompile($node->getNode('node'))
            ->raw('.')
            ->raw($node->getAttribute('method'))
            ->raw('(')
        ;
        $first = true;
        foreach ($node->getNode('arguments')->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;

            $compiler->subcompile($pair['value']);
        }
        $compiler->raw(')');
    }
}
