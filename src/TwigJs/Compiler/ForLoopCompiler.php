<?php

namespace TwigJs\Compiler;

use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class ForLoopCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\ForLoopNode';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\ForLoopNode) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of %s, but got "%s".',
                    $this->getType(),
                    get_class($node)
                )
            );
        }

        if ($node->getAttribute('else')) {
            $compiler
                ->write("")
                ->subcompile(new NameExpression('_iterated', $node->getTemplateLine()))
                ->raw(" = true;\n")
            ;
        }

        if ($node->getAttribute('with_loop')) {
            $compiler
                ->write("++")
                ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                ->raw("['index0'];\n")
                ->write("++")
                ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                ->raw("['index'];\n")
                ->write("")
                ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                ->raw("['first'] = false;\n")
            ;

            if (!$node->getAttribute('ifexpr')) {
                $compiler
                    ->write("if (")
                    ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                    ->raw("['length']) {\n")
                    ->indent()
                    ->write("--")
                    ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                    ->raw("['revindex0'];\n")
                    ->write("--")
                    ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                    ->raw("['revindex'];\n")
                    ->write("")
                    ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                    ->raw("['last'] = 0 === ")
                    ->subcompile(new NameExpression('loop', $node->getTemplateLine()))
                    ->raw("['revindex0'];\n")
                    ->outdent()
                    ->write("}\n")
                ;
            }
        }
    }
}
