<?php
declare(ENCODING = 'utf-8') ;
namespace Erfurt\Store\Adapter;

/*                                                                        *
 * This script belongs to the Erfurt framework.                           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 2 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/copyleft/gpl.html.                      *
 *                                                                        */


/**
 * Abstract base class for storage adapters.
 *
 * @package Erfurt
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
abstract class AbstractAdapter implements AdapterInterface {

	/**
	 * @array
	 */
	protected $graphs = array();

	/**
	 * @array
	 */
	protected $graphInfoCache;

	/**
	 * The injected knowledge base
	 *
	 * @var \Erfurt\Object\ObjectManager
	 */
	protected $objectManager;


	/**
	 * Injector method for a \Erfurt\Object\ObjectManager
	 *
	 * @var \Erfurt\Object\ObjectManager
	 */
	public function injectObjectManager(\Erfurt\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	abstract protected function fetchGraphInfos();

	protected function getGraphInfos() {
		if (null === $this->graphInfoCache) {
			// try to fetch graph and namespace infos... if all tables are present this should not lead to an error.
			$this->fetchGraphInfos();
		}
		return $this->graphInfoCache;
	}

	/** @see \Erfurt\Store\Adapter\AdapterInterface */
	public function getAvailableGraphs() {
		$graphInfoCache = $this->getGraphInfos();
		$graphs = array();
		foreach ($graphInfoCache as $mInfo) {
			$graphs[$mInfo['graphIri']] = TRUE;
		}
		return $graphs;
	}

	/** @see \Erfurt\Store\Adapter\AdapterInterface */
	public function isGraphAvailable($graphIri) {
		$graphInfoCache = $this->getGraphInfos();
		if (isset($graphInfoCache[$graphIri])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create a graph object with the specified parameters
	 *
	 * @param string $type
	 * @param string $graphIri
	 * @param string $baseIri
	 * @return \Erfurt\Domain\Model\Rdf\Graph The graph object
	 */
	protected function createGraphObject($type, $graphIri, $baseIri) {
		// choose the right type for the graph instance and instanciate it
		if ($type === 'owl') {
			$m = $this->objectManager->create('Erfurt\Domain\Model\Owl\Graph', $graphIri, $baseIri);
		} else if ($type === 'rdfs') {
			$m = $this->objectManager->create('Erfurt\Domain\Model\Rdfs\Graph', $graphIri, $baseIri);
		} else {
			$m = $this->objectManager->create('Erfurt\Domain\Model\Rdf\Graph', $graphIri, $baseIri);
		}
		return $m;
	}

	/** @see \Erfurt\Store\Adapter\AdapterInterface */
	public function getGraph($graphIri) {
		// if graph is already in cache return the cached value
		if (isset($this->graphs[$graphIri])) {
			return clone $this->graphs[$graphIri];
		}
		$graphInfoCache = $this->getGraphInfos();
		$baseIri = $graphInfoCache[$graphIri]['baseIri'];
		if ($baseIri === '') {
			$baseIri = null;
		}
		$m = $this->createGraphObject($graphInfoCache[$graphIri]['type'], $graphIri, $baseIri);
		$this->graphs[$graphIri] = $m;
		return $m;
	}

	/** @see \Erfurt\Store\Adapter\AdapterInterface */
	public function addStatement($graphIri, $subject, $predicate, $object, array $options = array()) {
		$statementArray = array();
		$statementArray["$subject"] = array();
		$statementArray["$subject"]["$predicate"] = array();
		$statementArray["$subject"]["$predicate"][] = $object;
		try {
			$this->addMultipleStatements($graphIri, $statementArray);
		}
		catch (\Exception $e) {
			throw new \Exception('Insertion of statement failed:' . $e->getMessage());
		}
	}

	/**
	 * Returns all statements matching the specified parameters.
	 *
	 * @param string $graphIri
	 * @param string $subject
	 * @param string $predicate
	 * @param string $object
	 */
	public function getMatchingStatements($graphIri, $subject, $predicate, $object) {
		throw new \RuntimeException('Not implemented.', 1309207242);
	}

	/** @see \Erfurt\Store\Adapter\AdapterInterface */
	public function getBlankNodePrefix() {
		return 'bNode';
	}

	/**
	 *
	 * @param string $graphIri
	 * @param string $locator Either a URL or a absolute file name.
	 * @param string $type One of:
	 *		- 'auto' => Tries to detect the type automatically in the following order:
	 *		   1. Detect XML by XML-Header => rdf/xml
	 *		   2. If this fails use the extension of the file
	 *		   3. If this fails throw an exception
	 *		- 'xml'
	 *		- 'n3' or 'nt'
	 * @param boolean $stream Denotes whether $data contains the actual data.
	 *
	 * @throws Erfurt_Exception
	 *
	 * @return boolean On success
	 */
	public function importRdf($graphIri, $data, $type, $locator) {
		// TODO: Implement importRdf() method.
	}

	/**
	 * Exports a given graph to RDF, using the given serialization format. The exported graph is returned as a string or
	 * saved to a file in case a file name is given.
	 *
	 * @param string $graphIri
	 * @param string $serializationType One of:
	 *		- 'xml'
	 *		- 'n3' or 'nt'
	 * @param mixed $filename Either a string containing a absolute filename or null. In case null is given,
	 * this method returns a string containing the serialization.
	 *
	 * @return string/null
	 */
	public function exportRdf($graphIri, $serializationType = 'xml', $filename = null) {
		// TODO: Implement exportRdf() method.
	}
}

?>