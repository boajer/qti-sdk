<?php

use qtism\runtime\tests\AssessmentItemSessionFactory;
use qtism\runtime\tests\Route;
use qtism\runtime\tests\AbstractAssessmentTestSessionFactory;
use qtism\runtime\tests\TestResultsSubmission;

class SimpleResultsSubmittableTestSessionFactory extends AbstractAssessmentTestSessionFactory {
    
    protected function instantiateAssessmentTestSession(Route $route) {
        return new SimpleResultsSubmittableTestSession($this->getAssessmentTest(), new AssessmentItemSessionFactory(), $route, $this->mustConsiderMinTime());
    }
}