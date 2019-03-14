<?php

namespace TwigJs\Twig;

use Twig\Error\SyntaxError;
use Twig\Parser;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * @property Parser $parser
 */
class TwigJsTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $node = new TwigJsNode(
            array(),
            array(),
            $token->getLine(),
            $this->getTag()
        );

        $stream = $this->parser->getStream();
        while (!$stream->test(Token::BLOCK_END_TYPE)) {
            if ($stream->test(Token::NAME_TYPE, 'name')) {
                $stream->next();
                $stream->expect(Token::OPERATOR_TYPE, '=');
                $node->setAttribute(
                    'name',
                    $stream->expect(Token::STRING_TYPE)->getValue()
                );

                continue;
            }

            $token = $stream->getCurrent();

            throw new SyntaxError(
                sprintf(
                    'Unexpected token "%s" of value "%s"',
                    Token::typeToEnglish($token->getType()),
                    $token->getValue()
                ),
                $token->getLine()
            );
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return $node;
    }

    public function getTag()
    {
        return 'twig_js';
    }
}
