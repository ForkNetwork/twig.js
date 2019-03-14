<?php

namespace TwigJs\Compiler;

use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class DoCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\DoNode';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\DoNode) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of %s, but got "%s".',
                    $this->getType(),
                    get_class($node)
                )
            );
        }

        $compiler
            ->addDebugInfo($node)
            ->write('')
            ->subcompile($node->getNode('expr'))
            ->raw(";\n")
        ;
    }
}
