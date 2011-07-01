<?php
declare(ENCODING = 'utf-8') ;
namespace Erfurt\Storage\Memory;

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

use \Erfurt\Store\Adapter;

/**
 * In-memory storage adapter
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class MemoryStorageAdapter extends Adapter\AbstractAdapter {

	/**
	 * The graphs available in this store
	 *
	 * @var \Erfurt\Domain\Model\Rdf\Graph[]
	 */
	protected $graphs = array();

	/**
	 * All statements in this store. The statements are stored by their hash key, which is computed from graph, subject, predicate and object
	 *
	 * @var array
	 * @see hashStatement()
	 */
	protected $statements = array();

	/**
	 * The statements in this store, ordered by graph and encodede with their hash key
	 *
	 * @var array
	 */
	protected $statementsByGraph = array();

	/**
	 * All subjects indexed by their subject
	 *
	 * @var array
	 */
	protected $statementsBySubject = array();

	/**
	 * All subjects indexed by their predicate
	 *
	 * @var array
	 */
	protected $statementsByPredicate = array();

	/**
	 * All subjects indexed by their object
	 *
	 * @var array
	 */
	protected $statementsByObject = array();

	/**
	 * This method allows the backend to (re)initialize itself, e.g. when an import was done.
	 */
	public function init() {
		// TODO: Implement init() method.
	}

	/**
	 * Fetches information about available graphs. This method does nothing in this adapter, as it keeps all graphs
	 * in memory.
	 *
	 * @return
	 */
	protected function fetchGraphInfos() {
		return $this->graphInfoCache;
	}

	/** @see \Erfurt\Store\Adapter\AdapterInterface */
	public function getAvailableGraphs() {
		return $this->graphs;
	}

	/**
	 * @param string $graphIri The Iri, which identifies the graph to look for.
	 * @param boolean $useAc Whether to use access control or not.
	 *
	 * @return boolean Returns true if graph exists and is available for the user ($useAc === true).
	 */
	public function isGraphAvailable($graphIri) {
		return isset($this->graphs[$graphIri]) && is_object($this->graphs[$graphIri]);
	}

	/**
	 * Returns a graph by its IRI. Unlike in other adapters, the graph object will not be created if it is not cached.
	 *
	 * @return \Erfurt\Domain\Model\Rdf\Graph
	 */
	public function getGraph($graphIri) {
		if (!isset($this->graphs[$graphIri])) {
			// TODO throw exception
		}
		return $this->graphs[$graphIri];
	}

	/**
	 * Creates a new empty graph (named graph) with the URI specified.
	 *
	 * @param string $graphIri
	 * @param int $type
	 * @return boolean true on success, false otherwise
	 */
	public function createGraph($graphIri, $type = \Erfurt\Store\Store::GRAPH_TYPE_OWL) {
		// TODO: Check if this implementation really makes sense; maybe it could also be (largely) moved to AbstractAdapter
		$baseIri = $graphIri;
		$graphObject = $this->createGraphObject($type, $graphIri, $baseIri);

		$this->graphs[$graphIri] = $graphObject;
		$this->graphInfoCache[$graphIri] = true;
		return TRUE;
	}

	/**
	 * @param string $graphIri The Iri, which identifies the graph.
	 *
	 * @throws Erfurt\Exception Throws an exception if no permission, graph not existing or deletion fails.
	 */
	public function deleteGraph($graphIri) {
		if (!isset($this->graphs[$graphIri])) {
			// TODO: throw exception
		}
		unset($this->graphs[$graphIri]);
		unset($this->graphInfoCache[$graphIri]);
		foreach ($this->statementsByGraph[$graphIri] as $statementHash) {
			$statement = $this->statements[$statementHash];

			$this->deleteStatement($statement['g'], $statement['s'], $statement['p'], $statement['o']);
		}
	}

	/**
	 * Returns the formats this store can import.
	 *
	 * @return  array
	 */
	public function getSupportedImportFormats() {
		// TODO: Implement getSupportedImportFormats() method.
	}

	/**
	 * Returns the formats this store can export.
	 *
	 * @return  array
	 */
	public function getSupportedExportFormats() {
		// TODO: Implement getSupportedExportFormats() method.
	}

	/**
	 * Executes a SPARQL ASK query and returns a boolean result value.
	 *
	 * @param string $graphIri
	 * @param string $askSparql
	 * @param boolean $useAc Whether to check for access control.
	 */
	public function sparqlAsk($query) {
		// TODO: Implement sparqlAsk() method.
	}

	/**
	 * @param string $query A string containing a sparql query
	 * @param array $graphIris An additional array of graphIris to query against. If a non empty array is given, the
	 * values in this array will overwrite all FROM and FROM NAMED clauses in the query. If the array contains no
	 * element, the FROM and FROM NAMED is evaluated. If non of them is present, all available graphs are queried.
	 * @param array Option array to push down parameters to adapters
	 * feel free to add anything you want. put the store name in front for special options, but use macros
	 *	  'result_format' => ['plain' | 'xml']
	 *	  'timeout' => 1000 (in msec)
	 * I included some define macros at the top of Store.php
	 *
	 * deprecated: @param string $resultform Currently supported are: 'plain' and 'xml'
	 * @param boolean $useAc Whether to check for access control or not.
	 *
	 * @throws Erfurt_Exception Throws an exception if query is no string.
	 *
	 * @return mixed Returns a result depending on the query, e.g. an array or a boolean value.
	 */
	public function sparqlQuery($query, $options = array()) {
		// TODO: Implement sparqlQuery() method.
	}

	/**
	 * Returns an MD5 hash for a statement
	 *
	 * @param string $graphIri The graph IRI
	 * @param string $subject
	 * @param string $predicate
	 * @param string $object
	 * @return string The statement hash
	 */
	protected function hashStatement($graphIri, $subject, $predicate, $object) {
		$hash = md5(
			$graphIri
			. '|' . $subject
			. '|' . $predicate
			. '|' . $object
		);
		return $hash;
	}

	/**
	 * Adds statements in an array to the graph specified by $graphIri.
	 *
	 * @param string $graphIri
	 * @param array  $statementsArray
	 * @param array  $options ("escapeLiteral" => true/false) to disable automatical escaping characters
	 */
	public function addMultipleStatements($graphIri, array $statementsArray, array $options = array()) {
		if (!$this->isGraphAvailable($graphIri)) {
			throw new Adapter\Exception("Graph $graphIri does not exist. Can't add statements to it.", 1308322086);
		}

		foreach ($statementsArray as $subject => $predicatesArray) {
			foreach ($predicatesArray as $predicate => $objectsArray) {
				foreach ($objectsArray as $object) {
					$hash = $this->hashStatement($graphIri, $subject, $predicate, $object);

					$this->statements[$hash] = array(
						'g' => $graphIri,
						's' => $subject,
						'p' => $predicate,
						'o' => $object
					);

					$this->addToIndexTable($this->statementsByGraph, $graphIri, $hash);
					$this->addToIndexTable($this->statementsBySubject, $subject, $hash);
					$this->addToIndexTable($this->statementsByPredicate, $predicate, $hash);
					$this->addToIndexTable($this->statementsByObject, $object, $hash);
				}
			}
		}
	}

	/**
	 * Adds a hash entry to a two-dimensional index table. This is used for storing the graph, subject, predicate and object
	 * hashes.
	 *
	 * @param array $indexTable The index table. Passed by reference
	 * @param string $key The key to store the value under
	 * @param string $hash The hash to store
	 */
	protected function addToIndexTable(&$indexTable, $key, $hash) {
		if (!isset($indexTable[$key])) {
			$indexTable[$key] = array();
		}

		$indexTable[$key][] = $hash;
	}

	/**
	 * Removes a hash from a two-dimensional index table
	 *
	 * @param array $indexTable The index table. Passed by reference
	 * @param string $key The key the hash is stored under
	 * @param string $hash The hash to remove
	 * @return void
	 */
	protected function removeFromIndexTable(&$indexTable, $key, $hash) {
		if (!in_array($hash, $indexTable[$key])) {
			return;
		}

		$index = array_search($hash, $indexTable[$key]);
		array_splice($indexTable[$key], $index, 1, array());
	}

	/**
	 * Takes an arbitrary number of arguments and intersects them, leaving out NULL values.
	 *
	 * @return array The intersection
	 */
	protected function intersectWithoutNullValues() {
		$intersectionArguments = array();
		foreach (func_get_args() as $argument) {
			if (is_array($argument)) {
				$intersectionArguments[] = $argument;
			}
		}

		if (count($intersectionArguments) == 0) {
			return array();
		} elseif (count($intersectionArguments) == 1) {
			return $intersectionArguments[0];
		} else {
			return call_user_func_array('array_intersect', $intersectionArguments);
		}
	}

	/**
	 * Returns all statements that match the given parameters. NULL values count as wildcards
	 *
	 * @param string $graphIri The IRI of the graph
	 * @param mixed $subject (string or null)
	 * @param mixed $predicate (string or null)
	 * @param mixed $object (string or null)
	 *
	 * @throws Erfurt_Exception
	 *
	 * @return array The matching statements
	 */
	public function getMatchingStatements($graphIri, $subject, $predicate, $object) {
		if ((!isset($this->graphs[$graphIri]))
				|| (!empty($subject) && empty($this->statementsBySubject[$subject]))
				|| (!empty($predicate) && empty($this->statementsByPredicate[$predicate]))
				|| (!empty($object) && empty($this->statementsByObject[$object]))
			) {

			return array();
		}

		$matchingStatements = $this->intersectWithoutNullValues(
			$this->statementsByGraph[$graphIri],
			$this->statementsBySubject[$subject],
			$this->statementsByPredicate[$predicate],
			$this->statementsByObject[$object]
		);

		$statements = array();
		foreach ($matchingStatements as $statementHash) {
			$statements[] = $this->statements[$statementHash];
		}
		return $statements;
	}

	/**
	 *
	 * @param string $graphIri
	 * @param mixed $subject (string or null)
	 * @param mixed $predicate (string or null)
	 * @param mixed $object (string or null)
	 * @param array $options
	 *
	 * @throws Erfurt_Exception
	 *
	 * @return int The number of statements deleted
	 */
	public function deleteMatchingStatements($graphIri, $subject, $predicate, $object, array $options = array()) {
			// TODO check and use $options
		$matchingStatements = $this->getMatchingStatements($graphIri, $subject, $predicate, $object);

		foreach ($matchingStatements as $statement) {
			$this->deleteStatement($statement['g'], $statement['s'], $statement['p'], $statement['o']);
		}
	}

	public function deleteStatement($graphIri, $subject, $predicate, $object) {
		$hash = $this->hashStatement($graphIri, $subject, $predicate, $object);
		$this->removeFromIndexTable($this->statementsByGraph, $graphIri, $hash);
		$this->removeFromIndexTable($this->statementsBySubject, $subject, $hash);
		$this->removeFromIndexTable($this->statementsByPredicate, $predicate, $hash);
		$this->removeFromIndexTable($this->statementsByObject, $object, $hash);
		unset($this->statements[$hash]);
	}

	/**
	 * Deletes statements in an array from the graph specified by $graphIri.
	 *
	 * @param string $graphIri
	 * @param array  $statementsArray
	 */
	public function deleteMultipleStatements($graphIri, array $statementsArray) {
		// TODO: Implement deleteMultipleStatements() method.
	}
}

?>