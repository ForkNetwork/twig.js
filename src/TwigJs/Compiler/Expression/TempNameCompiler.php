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

class TempNameCompiler implements TypeCompilerInterface
{
    public function getType()
    {
        return 'Twig\Node\Expression\TempNameExpression';
    }

    public function compile(JsCompiler $compiler, Node $node)
    {
        if (!$node instanceof \Twig\Node\Expression\TempNameExpression) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of %s, but got "%s".',
                    $this->getType(),
                    get_class($node)
                )
            );
        }


        $name = $node->getAttribute('name');
        if (isset($compiler->localVarMap[$name])) {
            $compiler->raw($compiler->localVarMap[$name]);

            return;
        }

        /*
        Contrary to the name of this class, this code no longer compiles the
        name of the node to a temporary variable name. Because of the troubles
        resulting from this optimization reported in schmittjoh/twig.js#12, this
        has been replaced with the slightly less performant but more reliable
        NameCompiler output which reads the variable directly from the context.
        */
        $compiler
            ->raw('(')
            ->string($name)
            ->raw(' in context ? context[')
            ->string($name)
            ->raw('] : null)')
        ;
    }
}
