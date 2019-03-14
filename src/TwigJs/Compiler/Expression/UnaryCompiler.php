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

use Twig\Node\Node;
use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

abstract class UnaryCompiler implements TypeCompilerInterface
{
    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\Expression\Unary\AbstractUnary) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of \Twig\Node\Expression\Unary\AbstractUnary, but got "%s".',
                    get_class($node)
                )
            );
        }

        $this->operator($compiler, $node);
        $compiler->raw('(');
        $compiler
            ->subcompile($node->getNode('node'))
            ->raw(')')
        ;
    }

    abstract protected function operator(JsCompiler $compiler, Node $node);
}
