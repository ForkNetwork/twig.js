<?php

namespace TwigJs\Compiler\Test;

use Twig\Node\Expression\TestExpression;
use TwigJs\JsCompiler;
use TwigJs\TestCompilerInterface;

class DefinedCompiler implements TestCompilerInterface
{
    public function getName()
    {
        return 'defined';
    }

    public function compile(JsCompiler $compiler, TestExpression $node)
    {
        $compiler->subcompile($node->getNode('node'));
    }
}
