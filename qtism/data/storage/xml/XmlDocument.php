<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *   
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * 
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package 
 */


namespace qtism\data\storage\xml;

use qtism\data\QtiDocument;
use qtism\data\storage\xml\marshalling\MarshallerFactory;
use qtism\data\AssessmentTest;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\storage\xml\marshalling\UnmarshallingException;
use qtism\data\QtiComponent;
use qtism\data\storage\Utils as StorageUtils;
use qtism\data\storage\xml\Utils as XmlUtils;
use \DOMDocument;
use \DOMElement;
use \DOMException;
use \RuntimeException;
use \InvalidArgumentException;

/**
 * This class represents a QTI-XML Document.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class XmlDocument extends QtiDocument {
	
	/**
	 * The produced domDocument after a successful call to
	 * XmlDocument::load or XmlDocument::save.
	 * 
	 * @var DOMDocument
	 */
	private $domDocument = null;
	
	/**
	 * Create a new XmlDocument.
	 * 
	 * @param string $version The version of the QTI specfication to use in order to load or save an AssessmentTest.
	 * @param QtiComponent $documentComponent (optional) A QtiComponent object to be bound to the QTI XML document to save.
	 */
	public function __construct($version = '2.1', QtiComponent $documentComponent = null) {
		parent::__construct($version, $documentComponent);
	}
	
	/**
	 * Set the DOMDocument object in use.
	 * 
	 * @param DOMDocument $domDocument A DOMDocument object.
	 */
	protected function setDomDocument(DOMDocument $domDocument) {
		$this->domDocument = $domDocument;
	}
	
	/**
	 * Get the DOMDocument object in use.
	 * 
	 * @return DOMDocument
	 */
	public function getDomDocument() {
		return $this->domDocument;
	}
	
	/**
	 * Load a QTI-XML assessment file. The file will be loaded and represented in
	 * an AssessmentTest object.
	 *
	 * @param string $uri The Uniform Resource Identifier that identifies/locate the file.
	 * @param boolean $validate XML Schema validation? Default is false.
	 * @throws XmlStorageException If an error occurs while loading the QTI-XML file.
	 */
	public function load($uri, $validate = false) {
	    $this->loadImplementation($uri, $validate, false);
	    
	    // We now are sure that the URI is valid.
	    $this->setUrl($uri);
	}
	
	/**
	 * Load QTI-XML from string.
	 *
	 * @param string $string The QTI-XML string.
	 * @param boolean $validate XML Schema validation? Default is false.
	 * @throws XmlStorageException If an error occurs while parsing $string.
	 */
	public function loadFromString($string, $validate = false) {
	    $this->loadImplementation($string, $validate, true);
	}
	
	protected function loadImplementation($data, $validate = false, $fromString = false) {
		try {
			$this->setDomDocument(new DOMDocument('1.0', 'UTF-8'));
			$this->getDomDocument()->preserveWhiteSpace = false;
			
			// disable xml warnings and errors and fetch error information as needed.
			$oldErrorConfig = libxml_use_internal_errors(true);
			$loadMethod = ($fromString === true) ? 'loadXML' : 'load';
			$doc = $this->getDomDocument();
			
			if (call_user_func_array(array($doc, $loadMethod), array($data, LIBXML_COMPACT|LIBXML_NONET|LIBXML_XINCLUDE))) {
				
				// Infer the QTI version.
				if (($version = XmlUtils::inferQTIVersion($this->getDomDocument())) !== false) {
					$this->setVersion($version);
				}
				else {
					$msg = "Cannot infer QTI version. Is it well formed?";
					throw new XmlStorageException($msg);
				}
				
				if ($validate === true) {
					$this->schemaValidate();
				}
				
				try {
					// Get the root element and unmarshall.
					$element = $this->getDomDocument()->documentElement;
					$factory = $this->createMarshallerFactory();
					$marshaller = $factory->createMarshaller($element);
					$this->setDocumentComponent($marshaller->unmarshall($element, $this->getDocumentComponent()));
				}
				catch (UnmarshallingException $e) {
					$line = $e->getDOMElement()->getLineNo();
					$msg = "An error occured while processing QTI-XML at line ${line}.";
					throw new XmlStorageException($msg, $e);
				}
			}
			else {
				$libXmlErrors = libxml_get_errors();
				$formattedErrors = self::formatLibXmlErrors($libXmlErrors);
				
				libxml_clear_errors();
				libxml_use_internal_errors($oldErrorConfig);
				
				$msg = "An internal occured while parsing QTI-XML:\n${formattedErrors}";
				throw new XmlStorageException($msg, null, new LibXmlErrorCollection($libXmlErrors));
			}
		}
		catch (DOMException $e) {
			$line = $e->getLine();
			$msg = "An error occured while parsing QTI-XML at line ${line}.";
			throw new XmlStorageException($msg, $e);
		}
	}
	
	/**
	 * Save the Assessment Document at the location described by $uri. Please be carefull
	 * to provide an AssessmentTest object to save before calling this method.
	 *
	 * @param string $uri The URI describing the location to save the QTI-XML representation of the Assessment Test.
	 * @param boolean $formatOutput Wether the XML content of the file must be formatted (new lines, indentation) or not.
	 * @throws XmlStorageException If an error occurs while transforming the AssessmentTest object to its QTI-XML representation.
	 */
	public function save($uri, $formatOutput = true) {
	    $this->saveImplementation($uri, $formatOutput);
	}
	
	/**
	 * Save the Assessment Document as an XML string.
	 *
	 * @param boolean $formatOutput Wether the XML content of the file must be formatted (new lines, indentation) or not.
	 * @throws XmlStorageException If an error occurs while transforming the AssessmentTest object to its QTI-XML representation.
	 */
	public function saveToString($formatOutput = true) {
	    return $this->saveImplementation('', $formatOutput);
	}
	
	
	protected function saveImplementation($uri = '', $formatOutput = true) {
		$assessmentTest = $this->getDocumentComponent();
		
		if (!empty($assessmentTest)) {
			$this->setDomDocument(new DOMDocument('1.0', 'UTF-8'));
			
			if ($formatOutput == true) {
				$this->getDomDocument()->formatOutput = true;
			}
			
			try {
				$factory = $this->createMarshallerFactory();
				$marshaller = $factory->createMarshaller($this->getDocumentComponent());
				$element = $marshaller->marshall($this->getDocumentComponent());
				
				$rootElement = $this->getDomDocument()->importNode($element, true);
				$this->getDomDocument()->appendChild($rootElement);
				$this->decorateRootElement($rootElement);
				
				if (empty($uri) === false) {
				    
				    if ($this->getDomDocument()->save($uri) === false) {
				        // An error occured while saving.
				        $msg = "An internal error occured while saving QTI-XML file at '${uri}'.";
				        throw new XmlStorageException($msg);
				        
				        $this->setUrl($uri);
				    }
				}
				else {
				    if (($strXml = $this->getDomDocument()->saveXML()) !== false) {
				        return $strXml;
				    }	
				    else {
				        // An error occured while saving.
				        $msg = "An internal error occured while exporting QTI-XML as string.";
				        throw new XmlStorageException($msg);
				    }
				}
			}
			catch (DOMException $e) {
				$msg = "An internal error occured while saving QTI-XML.";
				throw new XmlStorageException($msg, $e);
			}
		}
		else {
			$msg = "The Assessment Document cannot be saved. No AssessmentTest object provided.";
			throw new XmlStorageException($msg);
		}
	}
	
	public function schemaValidate($filename = '') {
		if (empty($filename)) {
			$filename = XmlUtils::getSchemaLocation($this->getVersion());
		}
		
		if (is_readable($filename)) {
			
			$oldErrorConfig = libxml_use_internal_errors(true); 
			
			$doc = $this->getDomDocument();
			if ($doc->schemaValidate($filename) === false) {
				
				$libXmlErrors = libxml_get_errors();
				$formattedErrors = self::formatLibXmlErrors($libXmlErrors);
				
				libxml_clear_errors();
				libxml_use_internal_errors($oldErrorConfig);

				$msg = "The document could not be validated with schema '${filename}':\n${formattedErrors}";
				throw new XmlStorageException($msg, null, new LibXmlErrorCollection($libXmlErrors));
			}
		}
		else {
			$msg = "Schema '${filename}' cannot be read. Does this file exist? Is it readable?";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Decorate the root element of the XmlAssessmentDocument with the appropriate
	 * namespaces and schema definition.
	 *
	 * @param DOMElement $rootElement The root DOMElement object of the document to decorate.
	 */
	protected function decorateRootElement(DOMElement $rootElement) {
		$qtiSuffix = 'v2p1';
		$xsdLocation = 'http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1.xsd';
		switch (trim($this->getVersion())) {
			case '2.0':
				$qtiSuffix = 'v2p0';
				$xsdLocation = 'http://www.imsglobal.org/xsd/imsqti_v2p0.xsd';
			break;
		}
		
		$rootElement->setAttribute('xmlns', "http://www.imsglobal.org/xsd/imsqti_${qtiSuffix}");
		$rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$rootElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', "http://www.imsglobal.org/xsd/imsqti_${qtiSuffix} ${xsdLocation}");
	}
	
	protected static function formatLibXmlErrors(array $libXmlErrors) {
		$formattedErrors = array();
			
		foreach ($libXmlErrors as $error) {
			switch ($error->level) {
				case LIBXML_ERR_WARNING:
					$formattedErrors[] = "Warning: " . trim($error->message) . " at " . $error->line . ":" . $error->column . ".";
					break;
						
				case LIBXML_ERR_ERROR:
					$formattedErrors[] = "Error: " . trim($error->message) . " at " . $error->line . ":" . $error->column . ".";
					break;
						
				case LIBXML_ERR_FATAL:
					$formattedErrors[] = "Fatal Error: " . trim($error->message) . " at " . $error->line . ":" . $error->column . ".";
					break;
			}
		}
			
		$formattedErrors = implode("\n", $formattedErrors);
		return $formattedErrors;
	}
	
	/**
	 * MarshallerFactory factory method (see gang of four).
	 * 
	 * @return MarshallerFactory An appropriate MarshallerFactory object.
	 */
	protected function createMarshallerFactory() {
		return new MarshallerFactory();
	}
}
