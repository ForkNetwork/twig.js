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

namespace TwigJs\Compiler\Expression;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class FunctionCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\Expression\FunctionExpression';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\Expression\FunctionExpression) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of %s, but got "%s".',
                    $this->getType(),
                    get_class($node)
                )
            );
        }

        $function = $compiler->getEnvironment()->getFunction($node->getAttribute('name'));
        if (false === $function) {
            throw new SyntaxError(
                sprintf(
                    'The function "%s" does not exist',
                    $node->getAttribute('name')
                ),
                $node->getTemplateLine()
            );
        }

        if ($jsFunction = $compiler->getJsFunction($node->getAttribute('name'))) {
            $compiler->raw($jsFunction.'(');
        } else {
            $compiler
                ->raw('this.env_.invoke(')
                ->string($node->getAttribute('name'))
                ->raw(', ')
            ;
        }

        $compiler
            ->raw($function->needsEnvironment() ? 'this.env_' : '')
        ;

        if ($function->needsContext()) {
            $compiler->raw($function->needsEnvironment() ? ', context' : 'context');
        }

        $first = true;
        foreach ($node->getNode('arguments') as $argNode) {
            if (!$first) {
                $compiler->raw(', ');
            } else {
                if ($function->needsEnvironment() || $function->needsContext()) {
                    $compiler->raw(', ');
                }
                $first = false;
            }
            $compiler->subcompile($argNode);
        }

        $compiler->raw(')');
    }
}
