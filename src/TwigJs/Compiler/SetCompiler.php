<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TwigJs\Compiler;

use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class SetCompiler implements TypeCompilerInterface
{
    private $count = 0;

    public function getType()
    {
        return 'Twig\Node\SetNode';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\SetNode) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of %s, but got "%s".',
                    $this->getType(),
                    get_class($node)
                )
            );
        }

        $compiler->addDebugInfo($node);

        if (count($node->getNode('names')) > 1) {
            $values = $node->getNode('values');

            foreach ($node->getNode('names') as $idx => $subNode) {
                $compiler
                    ->subcompile($subNode)
                    ->raw(' = ')
                    ->subcompile($values->getNode($idx))
                    ->raw(";\n")
                ;
            }

            return;
        }

        $count = $this->count++;
        $captureStringBuffer = 'cSb'.($count > 0 ? $count : '');
        if ($node->getAttribute('capture')) {
            $compiler
                ->write("var $captureStringBuffer = sb;\n")
                ->write("sb = new twig.StringBuffer;")
                ->subcompile($node->getNode('values'))
            ;
        }

        $compiler->subcompile($node->getNode('names'), false);

        if ($node->getAttribute('capture')) {
            $compiler
                ->raw(" = new twig.Markup(sb.toString());\n")
                ->write("sb = $captureStringBuffer")
            ;
        } else {
            $compiler->raw(' = ');

            if ($node->getAttribute('safe')) {
                $compiler
                    ->raw("new twig.Markup(")
                    ->subcompile($node->getNode('values'))
                    ->raw(")")
                ;
            } else {
                $compiler->subcompile($node->getNode('values'));
            }
        }

        $compiler->raw(";\n");
        $this->count = $count;
    }
}
