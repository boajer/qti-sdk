<?php
use qtism\runtime\storage\common\AbstractStorage;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\data\AssessmentTest;
use qtism\runtime\tests\SessionManager;
use qtism\runtime\storage\binary\BinaryAssessmentTestSeeker;
use qtism\runtime\storage\binary\TemporaryQtiBinaryStorage;
use qtism\data\storage\php\PhpDocument;

date_default_timezone_set('UTC');

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

function loadTestDefinition(array &$average = null) {
    $start = microtime();
    
    $phpDoc = new PhpDocument();
    $phpDoc->load(dirname(__FILE__) . '/../../test/samples/custom/php/linear_5_items.php');
    
    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
    
    return $phpDoc->getDocumentComponent();
}

function createFactory() {
    return new SessionManager();
}

function createStorage(SessionManager $factory, AssessmentTest $test) {
    return new TemporaryQtiBinaryStorage($factory, new BinaryAssessmentTestSeeker($test));
}

function spentTime($start, $end, array &$registration = null) {
    $startTime = explode(' ', $start);
    $endTime = explode(' ', $end);
    $time = ($endTime[0] + $endTime[1]) - ($startTime[0] + $startTime[1]);
    
    if (!is_null($registration)) {
        $registration[] = $time;
    }
    
    return $time;
}

function attempt(AssessmentTestSession $session, $identifier, array &$average = null) {
    $start = microtime();

    $session->beginAttempt();
    $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier($identifier)))));

    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

function retrieve(AbstractStorage $storage, AssessmentTest $test, $sessionId, array &$average = null) {
    $start = microtime();

    $session = $storage->retrieve($test, $sessionId);

    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }

    return $session;
}

function persist(AbstractStorage $storage, AssessmentTestSession $session, &$average = null) {
    $start = microtime();

    $storage->persist($session);

    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

function moveNext(AssessmentTestSession $session, array &$average) {
    $start = microtime();

    $session->moveNext();

    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

function neighbourhood(AssessmentTestSession $session, array &$average = null) {
    $start = microtime();
    $neighbourhood = $session->getPossibleJumps();

    if (is_null($average) === false) {
        spentTime($start, microtime(), $average);
    }
}

$averageAttempt = array();
$effectiveAverageAttempt = array();
$averageRetrieve = array();
$averagePersist = array();
$averageNext = array();
$averageLoad = array();
$averageNeighbourhood = array();

// Beginning of the session + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = $storage->instantiate($test);
$sessionId = $session->getSessionId();
$session->beginTestSession();
$storage->persist($session);
unset($session);
unset($storage);
unset($test);

$end = microtime();
echo "Beginning of the session + persistance (" . spentTime($start, $end) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $test, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceA', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo "Retrieving session + attempt 1 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $test, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceB', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo "Retrieving session + attempt 2 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $test, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceC', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo "Retrieving session + attempt 3 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $test, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceD', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();
unset($session);
unset($storage);
unset($test);

echo "Retrieving session + attempt 4 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n";

// Retrieving session + make an attemp + persistance.
$start = microtime();

$test = loadTestDefinition($averageLoad);
$storage = createStorage(createFactory(), $test);
$session = retrieve($storage, $test, $sessionId, $averageRetrieve);
neighbourhood($session, $averageNeighbourhood);
attempt($session, 'ChoiceE', $effectiveAverageAttempt);
moveNext($session, $averageNext);
persist($storage, $session, $averagePersist);
$end = microtime();

$numberCorrect = $session->numberCorrect();

unset($session);
unset($storage);
unset($test);

echo "Retrieving session + attempt 5 + persistance (" . spentTime($start, $end, $averageAttempt) . ")\n\n";

echo "Average attempt time = " . (array_sum($averageAttempt) / count($averageAttempt)) . "\n";
echo "Effective average attempt time = " . (array_sum($effectiveAverageAttempt) / count($effectiveAverageAttempt)) . "\n";
echo "Retrieve average time = " . (array_sum($averageRetrieve) / count($averageRetrieve)) . "\n";
echo "Persist average time = " . (array_sum($averagePersist) / count($averagePersist)) . "\n";
echo "MoveNext average time = " . (array_sum($averageNext) / count($averageNext)) . "\n";
echo "Load average time = " . (array_sum($averageLoad) / count($averageLoad)) . "\n";
echo "Neighbourhood time = " . (array_sum($averageNeighbourhood) / count($averageNeighbourhood)) . "\n";
echo "Number correct = ${numberCorrect}\n";
