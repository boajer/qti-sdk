<?php

namespace qtism\common\datatypes;

use qtism\common\Comparable;
use \DateInterval;
use \DateTime;
use \Exception;
use \InvalidArgumentException;

/**
 * Implementation of the QTI duration datatype.
 * 
 * The duration datatype enables you to express time duration as specified
 * by ISO8601.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Duration implements Comparable {
	
	private $interval;
	
	/**
	 * Create a new instance of Duration.
	 * 
	 * The $intervalSpec argument is a duration specification:
	 * 
	 * The format begins with <b>P</b> for "period". Each year of the period is represented
	 * by an integer value followed by the period symbol. If the duration contains timing elements,
	 * this portion of the specification is prefixed by letter <b>T</b>.
	 * 
	 * * Y -> Years
	 * * M -> Months
	 * * D -> Days
	 * * W -> Week. It will be converted to days. Then, you cannot combine it with D.
	 * * H -> Hours
	 * * M -> Minutes
	 * * S -> Seconds
	 * 
	 * Here are examples: 2 days will be <b>P2D</b>, 2 seconds will be <b>P2TS</b>,
	 * 6 years and 5 minutes will be <b>P6YT5M</b>.
	 * 
	 * Please note that this datatype does not support negative durations.
	 * 
	 * @param string $intervalSpec A duration as in ISO8601.
	 * @throws InvalidArgumentException If $intervalSpec is not a valid ISO8601 duration.
	 */
	public function __construct($intervalSpec) {
		if (is_string($intervalSpec) && !empty($intervalSpec)) {
			try {
				$d1 = new DateTime();
				$d2 = new DateTime();
				$d2->add(new DateInterval($intervalSpec));
				$interval = $d2->diff($d1);
				$interval->invert = ($interval->invert === 1) ? 0 : 1;
				$this->setInterval($interval);
			}
			catch (Exception $e) {
				$msg = "The specified interval specification cannot be processed as an ISO8601 duration.";
				throw new InvalidArgumentException($msg, 0, $e);
			}
		}
		else {
			$msg = "The intervalSpec argument must be a non-empty string.";
			throw new InvalidArgumentException($msg);
		}
		
	}
	
	/**
	 * Get the PHP DateInterval object corresponding to the duration.
	 * 
	 * @return DateInterval A DateInterval PHP object.
	 */
	protected function getInterval() {
		return $this->interval;
	}
	
	/**
	 * Set the PHP DateInterval object corresponding to the duration.
	 * 
	 * @param DateInterval $interval A DateInterval PHP object.
	 */
	protected function setInterval(DateInterval $interval) {
		$this->interval = $interval;
	}
	
	/**
	 * Get the number of years.
	 * 
	 * @return int
	 */
	public function getYears() {
		return $this->getInterval()->y;
	}
	
	/**
	 * Get the number of months.
	 * 
	 * @return int
	 */
	public function getMonths() {
		return $this->getInterval()->m;
	}
	
	/**
	 * Get the number of days.
	 * 
	 * @param boolean $total Wether the number of days must be the total of days or simply an offset (default).
	 * @return int
	 */
	public function getDays($total = false) {
		return ($total == true) ? $this->getInterval()->days : $this->getInterval()->d;
	}
	
	/**
	 * Get the number of hours.
	 * 
	 * @return int
	 */
	public function getHours() {
		return $this->getInterval()->h;
	}
	
	/**
	 * Get the number of minutes.
	 * 
	 * @return int
	 */
	public function getMinutes() {
		return $this->getInterval()->i;
	}
	
	/**
	 * Get the number of seconds.
	 * 
	 * @return int
	 */
	public function getSeconds() {
		return $this->getInterval()->s;
	}
	
	public function __toString() {
		$string = '';
		
		if ($this->getYears() > 0 || $this->getMonths() > 0 || $this->getDays() > 0) {
			$string .= 'P';
			
			if ($this->getYears() > 0) {
				$string .= $this->getYears() . 'Y';
			}
			
			if ($this->getMonths() > 0) {
				$string .= $this->getMonths() . 'M';
			}
			
			if ($this->getDays() > 0) {
				$string .= $this->getDays() . 'D';
			}
		}
		
		if ($this->getHours() > 0 || $this->getMinutes() > 0 || $this->getSeconds() > 0) {
			$string .= 'T';
			
			if ($this->getHours() > 0) {
				$string .= $this->getHours() . 'H';
			}
			
			if ($this->getMinutes() > 0) {
				$string .= $this->getMinutes() . 'M';
			}
			
			if ($this->getSeconds() > 0) {
				$string .= $this->getSeconds() . 'S';
			}
		}
		
		return $string;
	}
	
	/**
	 * Whether a given $obj is equal to this Duration.
	 * 
	 * @param mixed $obj A given value.
	 * @return boolean Whether the equality is established.
	 */
	public function equals($obj) {
		return (gettype($obj) === 'object' &&
				$obj instanceof self &&
				'' . $obj === '' . $this);
	}
	
	public function __clone() {
		// ... :'( ... https://bugs.php.net/bug.php?id=50559
		$d1 = new DateTime();
		$d2 = new DateTime();
		$d2->add(new DateInterval('' . $this));
		$interval = $d2->diff($d1);
		$interval->invert = ($interval->invert === 1) ? 0 : 1;
		$this->setInterval($interval);
	}
}