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
 * @category Erfurt
 * @package Store_Adapter
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
abstract class AbstractAdapter implements AdapterInterface {

	/**
	 * @array
	 */
	protected $graphCache = array();

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

	/** @see \Erfurt\Store\Adapter\AdapterInterface */
	public function getGraph($graphIri) {
		// if graph is already in cache return the cached value
		if (isset($this->graphCache[$graphIri])) {
			return clone $this->graphCache[$graphIri];
		}
		$graphInfoCache = $this->getGraphInfos();
		$baseIri = $graphInfoCache[$graphIri]['baseIri'];
		if ($baseIri === '') {
			$baseIri = null;
		}
		// choose the right type for the graph instance and instanciate it
		if ($graphInfoCache[$graphIri]['type'] === 'owl') {
			$m = $this->objectManager->create('Erfurt\Domain\Model\Owl\Graph', $graphIri, $baseIri);
		} else {
			if ($this->graphInfoCache[$graphIri]['type'] === 'rdfs') {
				$m = $this->objectManager->create('Erfurt\Domain\Model\Rdfs\Graph', $graphIri, $baseIri);
			} else {
				$m = $this->objectManager->create('Erfurt\Domain\Model\Rdf\Graph', $graphIri, $baseIri);
			}
		}
		$this->graphCache[$graphIri] = $m;
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

}

?>