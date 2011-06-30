<?php
declare(ENCODING = 'utf-8') ;
namespace Erfurt\Tests\Unit\Storage\Memory;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Andreas Wolf <andreas.wolf@ikt-werk.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


class MemoryStorageAdapterTest extends \Erfurt\Tests\BaseTestCase {
	/**
	 * @var \Erfurt\Storage\Memory\MemoryStorageAdapter
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new \Erfurt\Storage\Memory\MemoryStorageAdapter();

		$mockedObjectManager = $this->getMock('\Erfurt\Object\ObjectManager');
		$mockedObjectManager->expects($this->any())->method('create')->will($this->returnValue($this->getMock('\Erfurt\Domain\Model\Rdf\Graph')));
		$this->fixture->injectObjectManager($mockedObjectManager);
	}

	/**
	 * Returns a randomized statement for use with the addMultipleStatements method of the Adapter
	 */
	protected function getRandomStatement() {
		return array(
			uniqid() => array( // subject
				uniqid() => array( // predicate
					uniqid() // object
				)
			)
		);
	}

	protected function prepareMockedGraph($iri) {
		$mockedGraph = $this->getMock('\Erfurt\Domain\Model\Rdf\Graph');
		$mockedObjectManager = $this->getMock('\Erfurt\Object\ObjectManager');
		$mockedObjectManager->expects($this->atLeastOnce())->method('create')->with($this->anything(), $this->equalTo($iri))->will($this->returnValue($mockedGraph));
		$this->fixture->injectObjectManager($mockedObjectManager);

		return $mockedGraph;
	}

	/**
	 * @test
	 */
	public function createGraphCreatesAndStoresNewGraph() {
		// TODO: is this a correct IRI for a graph?
		$iri = 'http://example.org/some/random/uri/' . uniqid() . '#';

		$mockedGraph = $this->prepareMockedGraph($iri);

		$this->fixture->createGraph($iri);
		$this->assertTrue($this->fixture->isGraphAvailable($iri));
		$this->assertSame($mockedGraph, $this->fixture->getGraph($iri));
	}

	/**
	 * @test
	 * @depends createGraphCreatesAndStoresNewGraph
	 */
	public function graphIsAvailableAfterItHasBeenCreated() {
		$iri = 'http://example.org/some/random/uri/' . uniqid() . '#';

		$mockedGraph = $this->prepareMockedGraph($iri);

		$this->fixture->createGraph($iri);
		$graphs = $this->fixture->getAvailableGraphs();
		$this->assertTrue($this->fixture->isGraphAvailable($iri));
		$this->assertEquals($mockedGraph, $graphs[$iri]);
	}

	/**
	 * @test
	 * @depends createGraphCreatesAndStoresNewGraph
	 */
	public function statementsCanBeAddedAndRetrieved() {
		$mockedObjectManager = $this->getMock('\Erfurt\Object\ObjectManager');
		$mockedObjectManager->expects($this->atLeastOnce())->method('create')->will($this->returnValue($this->getMock('\Erfurt\Domain\Model\Rdf\Graph')));
		$this->fixture->injectObjectManager($mockedObjectManager);

		$graphIri = uniqid();
		$s = uniqid();
		$p = uniqid();
		$o1 = uniqid(); $o2 = uniqid();
		$statements = array(
			$s => array( // subject 1
				$p => array( // predicate 1
					$o1, // object 1
					$o2  // object 2
				)
			)
		);

		$this->fixture->createGraph($graphIri);
		$randomIri = uniqid();
		$this->fixture->createGraph($randomIri);

		$this->fixture->addMultipleStatements($graphIri, $statements);
			// add another statement to some random graph to make sure that we only get results for the desired graph
		$this->fixture->addMultipleStatements($randomIri, array(uniqid() => array(uniqid() => array(uniqid()))));
		$statements = $this->fixture->getMatchingStatements($graphIri, NULL, NULL, NULL);

		$this->assertEquals(2, count($statements), 'We expected to get two statements, but we got ' . count($statements));
		$this->assertEquals(array($graphIri, $s, $p, $o1), array_values($statements[0]));
		$this->assertEquals(array($graphIri, $s, $p, $o2), array_values($statements[1]));
	}

	/**
	 * @test
	 */
	public function addMultipleStatementsThrowsExceptionIfGraphDoesNotExist() {
		$this->setExpectedException('\Erfurt\Store\Adapter\Exception', '', 1308322086);

		$this->fixture->addMultipleStatements(uniqid(), array());
	}

	/**
	 * @test
	 */
	public function getMatchingStatementsReturnsOnlyStatementsForGivenGraph() {
		$firstIri = uniqid(); $secondIri = uniqid();
		$s1 = uniqid();$p1 = uniqid();$o1 = uniqid();
		$s2 = uniqid();$p2 = uniqid();$o2 = uniqid();

		$this->fixture->createGraph($firstIri);
		$this->fixture->createGraph($secondIri);
		$this->fixture->addStatement($firstIri, $s1, $p1, $o1);
		$this->fixture->addStatement($secondIri, $s2, $p2, $o2);

		$firstGraphStatements = $this->fixture->getMatchingStatements($firstIri, NULL, NULL, NULL);
		$this->assertEquals(1, count($firstGraphStatements));
		$this->assertEquals(array('g' => $firstIri, 's' => $s1, 'p' => $p1, 'o' => $o1), $firstGraphStatements[0]);
	}

	/**
	 * Adds various statements to a graph and returns them
	 *
	 * @test
	 * @depends statementsCanBeAddedAndRetrieved
	 */
	public function getMatchingStatementsReturnsAllMatchingStatements() {
			// we create a simple graph here with one subject, two predicates and two objects
		$graphIri = uniqid();
		$subject = uniqid(); $unusedSubject = uniqid();
		$predicate1 = uniqid(); $predicate2 = uniqid();
		$object1 = uniqid(); $object2 = uniqid();
		$statements = array(
			$subject => array(
				$predicate1 => array($object1, $object2),
				$predicate2 => array($object1)
			)
		);
		$this->fixture->createGraph($graphIri);
		$this->fixture->addMultipleStatements($graphIri, $statements);

		$statementsForUnusedSubject = $this->fixture->getMatchingStatements($graphIri, $unusedSubject, NULL, NULL);
		$statementsForPredicate1 = $this->fixture->getMatchingStatements($graphIri, NULL, $predicate1, NULL);
		$statementsForPredicate2 = $this->fixture->getMatchingStatements($graphIri, NULL, $predicate2, NULL);
		$statementsForObject1 = $this->fixture->getMatchingStatements($graphIri, NULL, NULL, $object1);
		$statementsForObject2 = $this->fixture->getMatchingStatements($graphIri, NULL, NULL, $object2);

		$this->assertEquals(0, count($statementsForUnusedSubject));

		$this->assertEquals(2, count($statementsForPredicate1));
		$this->assertContains(array('g' => $graphIri, 's' => $subject, 'p' => $predicate1, 'o' => $object1), $statementsForPredicate1);
		$this->assertContains(array('g' => $graphIri, 's' => $subject, 'p' => $predicate1, 'o' => $object2), $statementsForPredicate1);

		$this->assertEquals(1, count($statementsForPredicate2));
		$this->assertContains(array('g' => $graphIri, 's' => $subject, 'p' => $predicate2, 'o' => $object1), $statementsForPredicate2);

		$this->assertEquals(2, count($statementsForObject1));
		$this->assertContains(array('g' => $graphIri, 's' => $subject, 'p' => $predicate1, 'o' => $object1), $statementsForObject1);
		$this->assertContains(array('g' => $graphIri, 's' => $subject, 'p' => $predicate2, 'o' => $object1), $statementsForObject1);

		$this->assertEquals(1, count($statementsForObject2));
		$this->assertContains(array('g' => $graphIri, 's' => $subject, 'p' => $predicate1, 'o' => $object2), $statementsForObject2);
	}

	/**
	 * @test
	 * @depends statementsCanBeAddedAndRetrieved
	 */
	public function deleteMatchingStatementsRemovesAllMatchingStatementsFromGraph() {
			// we create a simple graph here with one subject, two predicates and two objects
		$graphIri = uniqid();
		$subject = uniqid();
		$predicate1 = uniqid(); $predicate2 = uniqid();
		$object1 = uniqid(); $object2 = uniqid();
		$statements = array(
			$subject => array(
				$predicate1 => array($object1, $object2),
				$predicate2 => array($object1)
			)
		);
		$this->fixture->createGraph($graphIri);
		$this->fixture->addMultipleStatements($graphIri, $statements);

		$this->fixture->deleteMatchingStatements($graphIri, $subject, NULL, NULL);

		$this->assertEquals(0, count($this->fixture->getMatchingStatements($graphIri, $subject, NULL, NULL)));
	}
}

