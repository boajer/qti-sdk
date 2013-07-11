<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Cardinality;
use qtism\data\expressions\ExpressionCollection;

/**
 * Please note that this class represents the QTI 'and' class.
 * We cannot use the 'And' class name because it is a reserved word
 * in PHP.
 * 
 * From IMS QTI:
 * 
 * The and operator takes one or more sub-expressions each with a base-type of 
 * boolean and single cardinality. The result is a single boolean which is 
 * true if all sub-expressions are true and false if any of them are false.
 * If one or more sub-expressions are NULL and all others are true then the 
 * operator also results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AndOperator extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		
		parent::__construct($expressions, 1, -1, array(Cardinality::single), array(OperatorBaseType::BOOLEAN));
	}
	
	public function getQtiClassName() {
		return 'and';
	}
}