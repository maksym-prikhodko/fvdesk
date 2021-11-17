<?php
namespace PhpParser;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
abstract class PrettyPrinterAbstract
{
    protected $precedenceMap = array(
        'Expr_BinaryOp_Pow'            => array(  0,  1),
        'Expr_BitwiseNot'              => array( 10,  1),
        'Expr_PreInc'                  => array( 10,  1),
        'Expr_PreDec'                  => array( 10,  1),
        'Expr_PostInc'                 => array( 10, -1),
        'Expr_PostDec'                 => array( 10, -1),
        'Expr_UnaryPlus'               => array( 10,  1),
        'Expr_UnaryMinus'              => array( 10,  1),
        'Expr_Cast_Int'                => array( 10,  1),
        'Expr_Cast_Double'             => array( 10,  1),
        'Expr_Cast_String'             => array( 10,  1),
        'Expr_Cast_Array'              => array( 10,  1),
        'Expr_Cast_Object'             => array( 10,  1),
        'Expr_Cast_Bool'               => array( 10,  1),
        'Expr_Cast_Unset'              => array( 10,  1),
        'Expr_ErrorSuppress'           => array( 10,  1),
        'Expr_Instanceof'              => array( 20,  0),
        'Expr_BooleanNot'              => array( 30,  1),
        'Expr_BinaryOp_Mul'            => array( 40, -1),
        'Expr_BinaryOp_Div'            => array( 40, -1),
        'Expr_BinaryOp_Mod'            => array( 40, -1),
        'Expr_BinaryOp_Plus'           => array( 50, -1),
        'Expr_BinaryOp_Minus'          => array( 50, -1),
        'Expr_BinaryOp_Concat'         => array( 50, -1),
        'Expr_BinaryOp_ShiftLeft'      => array( 60, -1),
        'Expr_BinaryOp_ShiftRight'     => array( 60, -1),
        'Expr_BinaryOp_Smaller'        => array( 70,  0),
        'Expr_BinaryOp_SmallerOrEqual' => array( 70,  0),
        'Expr_BinaryOp_Greater'        => array( 70,  0),
        'Expr_BinaryOp_GreaterOrEqual' => array( 70,  0),
        'Expr_BinaryOp_Equal'          => array( 80,  0),
        'Expr_BinaryOp_NotEqual'       => array( 80,  0),
        'Expr_BinaryOp_Identical'      => array( 80,  0),
        'Expr_BinaryOp_NotIdentical'   => array( 80,  0),
        'Expr_BinaryOp_Spaceship'      => array( 80,  0),
        'Expr_BinaryOp_BitwiseAnd'     => array( 90, -1),
        'Expr_BinaryOp_BitwiseXor'     => array(100, -1),
        'Expr_BinaryOp_BitwiseOr'      => array(110, -1),
        'Expr_BinaryOp_BooleanAnd'     => array(120, -1),
        'Expr_BinaryOp_BooleanOr'      => array(130, -1),
        'Expr_BinaryOp_Coalesce'       => array(140,  1),
        'Expr_Ternary'                 => array(150, -1),
        'Expr_Assign'                  => array(160,  1),
        'Expr_AssignRef'               => array(160,  1),
        'Expr_AssignOp_Plus'           => array(160,  1),
        'Expr_AssignOp_Minus'          => array(160,  1),
        'Expr_AssignOp_Mul'            => array(160,  1),
        'Expr_AssignOp_Div'            => array(160,  1),
        'Expr_AssignOp_Concat'         => array(160,  1),
        'Expr_AssignOp_Mod'            => array(160,  1),
        'Expr_AssignOp_BitwiseAnd'     => array(160,  1),
        'Expr_AssignOp_BitwiseOr'      => array(160,  1),
        'Expr_AssignOp_BitwiseXor'     => array(160,  1),
        'Expr_AssignOp_ShiftLeft'      => array(160,  1),
        'Expr_AssignOp_ShiftRight'     => array(160,  1),
        'Expr_AssignOp_Pow'            => array(160,  1),
        'Expr_BinaryOp_LogicalAnd'     => array(170, -1),
        'Expr_BinaryOp_LogicalXor'     => array(180, -1),
        'Expr_BinaryOp_LogicalOr'      => array(190, -1),
        'Expr_Include'                 => array(200, -1),
    );
    protected $noIndentToken;
    protected $canUseSemicolonNamespaces;
    public function __construct() {
        $this->noIndentToken = '_NO_INDENT_' . mt_rand();
    }
    public function prettyPrint(array $stmts) {
        $this->preprocessNodes($stmts);
        return ltrim(str_replace("\n" . $this->noIndentToken, "\n", $this->pStmts($stmts, false)));
    }
    public function prettyPrintExpr(Expr $node) {
        return str_replace("\n" . $this->noIndentToken, "\n", $this->p($node));
    }
    public function prettyPrintFile(array $stmts) {
        $p = rtrim($this->prettyPrint($stmts));
        $p = preg_replace('/^\?>\n?/', '', $p, -1, $count);
        $p = preg_replace('/<\?php$/', '', $p);
        if (!$count) {
            $p = "<?php\n\n" . $p;
        }
        return $p;
    }
    protected function preprocessNodes(array $nodes) {
        $this->canUseSemicolonNamespaces = true;
        foreach ($nodes as $node) {
            if ($node instanceof Stmt\Namespace_ && null === $node->name) {
                $this->canUseSemicolonNamespaces = false;
            }
        }
    }
    protected function pStmts(array $nodes, $indent = true) {
        $result = '';
        foreach ($nodes as $node) {
            $result .= "\n"
                    . $this->pComments($node->getAttribute('comments', array()))
                    . $this->p($node)
                    . ($node instanceof Expr ? ';' : '');
        }
        if ($indent) {
            return preg_replace('~\n(?!$|' . $this->noIndentToken . ')~', "\n    ", $result);
        } else {
            return $result;
        }
    }
    protected function p(Node $node) {
        return $this->{'p' . $node->getType()}($node);
    }
    protected function pInfixOp($type, Node $leftNode, $operatorString, Node $rightNode) {
        list($precedence, $associativity) = $this->precedenceMap[$type];
        return $this->pPrec($leftNode, $precedence, $associativity, -1)
             . $operatorString
             . $this->pPrec($rightNode, $precedence, $associativity, 1);
    }
    protected function pPrefixOp($type, $operatorString, Node $node) {
        list($precedence, $associativity) = $this->precedenceMap[$type];
        return $operatorString . $this->pPrec($node, $precedence, $associativity, 1);
    }
    protected function pPostfixOp($type, Node $node, $operatorString) {
        list($precedence, $associativity) = $this->precedenceMap[$type];
        return $this->pPrec($node, $precedence, $associativity, -1) . $operatorString;
    }
    protected function pPrec(Node $node, $parentPrecedence, $parentAssociativity, $childPosition) {
        $type = $node->getType();
        if (isset($this->precedenceMap[$type])) {
            $childPrecedence = $this->precedenceMap[$type][0];
            if ($childPrecedence > $parentPrecedence
                || ($parentPrecedence == $childPrecedence && $parentAssociativity != $childPosition)
            ) {
                return '(' . $this->{'p' . $type}($node) . ')';
            }
        }
        return $this->{'p' . $type}($node);
    }
    protected function pImplode(array $nodes, $glue = '') {
        $pNodes = array();
        foreach ($nodes as $node) {
            $pNodes[] = $this->p($node);
        }
        return implode($glue, $pNodes);
    }
    protected function pCommaSeparated(array $nodes) {
        return $this->pImplode($nodes, ', ');
    }
    protected function pNoIndent($string) {
        return str_replace("\n", "\n" . $this->noIndentToken, $string);
    }
    protected function pComments(array $comments) {
        $result = '';
        foreach ($comments as $comment) {
            $result .= $comment->getReformattedText() . "\n";
        }
        return $result;
    }
}
