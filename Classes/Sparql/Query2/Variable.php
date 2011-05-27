<?php
declare(ENCODING = 'utf-8') ;
namespace Erfurt\Sparql\Query2;

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
 * Erfurt Sparql Query2 - Var.
 *
 * @package Semantic
 * @scope prototype
 */
class Variable extends ElementHelper implements Interfaces\VarOrIriRef, Interfaces\VarOrTerm, Interfaces\PrimaryExpression {

	protected $name;
	protected $varLabelType = '?';

	/**
	 * @param string $nname
	 */
	public function __construct($nname) {
		if (is_string($nname) && $nname != '') {
			$this->name = preg_replace('/[^\w]/', '', $nname);
		} else {
			if ($nname instanceof IriRef) {
				$this->name = self::extractName($nname->getIri());
			} else {
				throw new \RuntimeException('Argument 1 passed to ' . get_class($this) . ' : string (not empty) or IriRef expected. ' . typeHelper($nname) . ' found.');
			}
		}
		parent::__construct();
	}

	/**
	 * getSparql
	 * build a valid sparql representation of this obj - should be like '?name'
	 * @return string
	 */
	public function getSparql() {
		return $this->varLabelType . $this->name;
	}

	/**
	 * getName
	 * @return string the name of this var
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * setName
	 * @param string $nname the new name
	 * @return Var $this
	 */
	public function setName($nname) {
		if (is_string($nname)) {
			$this->name = $nname;
		}
		return $this; //for chaining
	}

	/**
	 * setVarLabelType
	 * @param string $ntype the new var label ('?' or '$')
	 * @return Var $this
	 */
	public function setVarLabelType($ntype) {
		if ($ntype == '?' || $ntype == '$') {
			$this->varLabelType = $ntype;
		} else {
			throw new \RuntimeException('Argument 1 passed to Var::setVarLabelType : $ or ? expected. ' . $ntype . ' found.');
		}
		return $this; //for chaining
	}

	/**
	 * getVarLabelType
	 * @return string var label ('?' or '$')
	 */
	public function getVarLabelType() {
		return $this->varLabelType;
	}

	/**
	 * toggleVarLabelType
	 * @return Var $this
	 */
	public function toggleVarLabelType() {
		$this->varLabelType = $this->varLabelType == '?' ? '$' : '?';
		return $this; //for chaining
	}

	/**
	 * extractName
	 *
	 * if you have a iri like
	 * http://example.com/foaf/bob or
	 * http://example.com/foaf#bob
	 * http://example.com/bob/
	 *  -> returns bob
	 *
	 * @param string $name a iri
	 * @return string string after last / or #
	 */
	public static function extractName($name) {
		$parts = preg_split('/[\/#]/', $name);
		$ret = '';
		for ($i = count($parts) - 1; $ret == ''; $i--) {
			$ret = $parts[$i];
		}
		if ($ret == '') {
			$ret = $name;
		}
		return strtolower($ret);
	}

	public function __toString() {
		return $this->getSparql();
	}

}

?>