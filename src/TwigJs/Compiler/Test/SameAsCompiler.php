<?php

namespace TwigJs\Compiler\Test;

use Twig\Node\Expression\TestExpression;
use TwigJs\JsCompiler;
use TwigJs\TestCompilerInterface;

class SameAsCompiler implements TestCompilerInterface
{
    public function getName()
    {
        return 'sameas';
    }

    public function compile(JsCompiler $compiler, TestExpression $node)
    {
        $compiler
            ->raw('(')
            ->subcompile($node->getNode('node'))
            ->raw(' === ')
            ->subcompile($node->getNode('arguments')->getNode(0))
            ->raw(')');
    }
}
