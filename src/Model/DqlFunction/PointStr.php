<?php
namespace App\Model\DqlFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class PointStr extends FunctionNode {

    private $arg;
    
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker) {
        return 'GeomFromText('.$this->arg->dispatch($sqlWalker).')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->arg = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}