<?php
require_once (dirname(__FILE__) . '/../../QtiSmTestCase.php');

use qtism\data\QtiIdentifiableCollection;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;

class QtiIdentifiableCollectionTest extends QtiSmTestCase {
	
	public function testWithWeights() {
		
		$weight1 = new Weight('weight1', 1.0);
		$weight2 = new Weight('weight2', 1.1);
		$weight3 = new Weight('weight3', 1.2);
		$weights = new WeightCollection(array($weight1, $weight2, $weight3));
		
		$this->assertTrue($weights->getByIdentifier('weight1') === $weight1);
		$this->assertTrue($weights->getByIdentifier('weight2') === $weight2);
		$this->assertTrue($weights->getByIdentifier('weight3') === $weight3);
		
		$this->assertTrue($weights->getByIdentifier('weightX') === null);
	}
}