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
 * represents a build-in isBlank function call
 *
 * @package Semantic
 * @scope prototype
 */
class IsBlank extends ElementHelper implements Interfaces\BuiltInCall {
	protected $element;

	/**
	 *
	 * @param Interfaces\Expression $element
	 */
	public function __construct(Interfaces\Expression $element) {
		$this->element = $element;
		parent::__construct();
	}

	/**
	 * get the string representation
	 * @return string
	 */
	public function getSparql() {
		return 'isBLANK(' . $this->element->getSparql() . ')';
	}

}

?>
