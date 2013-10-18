<?php

use qtism\data\storage\php\PhpAssessmentTestDocument;

use qtism\data\storage\xml\XmlAssessmentTestDocument;

use qtism\data\storage\php\PhpDocument;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class PhpAssessmentTestDocumentTest extends QtiSmTestCase {
	
    /**
     * 
     * @dataProvider loadTestSamplesDataProvider
     * @param string $testUri
     */
    public function testLoadTestSamples($testUri) {
        // Basic XML -> PHP transormation + save + load
        $xmlDoc = new XmlAssessmentTestDocument('2.1');
        $xmlDoc->load($testUri);
        
        $phpDoc = new PhpAssessmentTestDocument('2.1');
        $phpDoc->getPhpDocument()->setDocumentComponent($xmlDoc);
        
        $file = tempnam('/tmp', 'qsm');
        $phpDoc->save($file);
        
        $phpDoc = new PhpAssessmentTestDocument('2.1');
        $phpDoc->load($file);
        
        $this->assertInstanceOf('qtism\\data\\AssessmentTest', $phpDoc);
        $this->assertEquals($file, $phpDoc->getUri());
        
        unset($file);
    }
    
    public function loadTestSamplesDataProvider() {
        return array(
            array(self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml'),
            array(self::samplesDir() . 'ims/tests/arbitrary_weighting_of_item_outcomes/arbitrary_weighting_of_item_outcomes.xml'),
            array(self::samplesDir() . 'ims/tests/basic_statistics_as_outcomes/basic_statistics_as_outcomes.xml'),
            array(self::samplesDir() . 'ims/tests/branching_based_on_the_response_to_an_assessmentitem/branching_based_on_the_response_to_an_assessmentitem.xml'),
            array(self::samplesDir() . 'ims/tests/controlling_the_duration_of_an_item_attempt/controlling_the_duration_of_an_item_attempt.xml'),
            array(self::samplesDir() . 'ims/tests/controlling_item_feedback_in_relation_to_the_test/controlling_item_feedback_in_relation_to_the_test.xml'),
            array(self::samplesDir() . 'ims/tests/early_termination_of_test_based_on_accumulated_item_outcomes/early_termination_of_test_based_on_accumulated_item_outcomes.xml'),
            array(self::samplesDir() . 'ims/tests/feedback_examples_test/feedback_examples_test.xml'),
            array(self::samplesDir() . 'ims/tests/golden_required_items_and_sections/golden_required_items_and_sections.xml'),
            array(self::samplesDir() . 'ims/tests/interaction_mix_sachsen/interaction_mix_sachsen.xml'),
            array(self::samplesDir() . 'ims/tests/items_arranged_into_sections_within_tests/items_arranged_into_sections_within_tests.xml'),
            array(self::samplesDir() . 'ims/tests/mapping_item_outcomes_prior_to_aggregation/mapping_item_outcomes_prior_to_aggregation.xml'),
            array(self::samplesDir() . 'ims/tests/randomizing_the_order_of_items_and_sections/randomizing_the_order_of_items_and_sections.xml'),
            array(self::samplesDir() . 'ims/tests/sets_of_items_with_leading_material/sets_of_items_with_leading_material.xml'),
            array(self::samplesDir() . 'ims/tests/simple_feedback_test/simple_feedback_test.xml'),
            array(self::samplesDir() . 'ims/tests/specifiying_the_number_of_allowed_attempts/specifiying_the_number_of_allowed_attempts.xml')
        );
    }
}