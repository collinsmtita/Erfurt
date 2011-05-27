<?php
declare(ENCODING = 'utf-8');
namespace Erfurt\Configuration;

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
 * Enter descriptions here
 *
 * @package Semantic
 * @scope singleton
 * @api
 */
class NamespacesConfiguration extends AbstractConfiguration implements \Erfurt\Singleton {

	/**
	 * Abstract_Configuration provides a property based interface to
	 * an array. The data are read-only unless $allowModifications
	 * is set to true on construction.
	 *
	 * Abstract_Configuration also implements Countable and Iterator to
	 * facilitate easy access to the data.
	 *
	 * @param array $configuration
	 * @param boolean $allowModifications
	 * @return void
	 */
	public function __construct(array $namespacesConfiguration = array(), $allowModifications = false) {
		if (empty($namespacesConfiguration)) {
			$namespaces = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('prefix,iri', 'tx_semantic_domain_model_rdf_namespace');
			if (is_array($namespaces)) {
				$namespacesConfiguration = array();
				foreach ($namespaces as $namespaceDefinition) {
					$namespacesConfiguration[$namespaceDefinition['prefix']] = $namespaceDefinition['iri'];
				}
			} else {
				throw new Exception\NoNamespacesException('No namespaces defined, although they are required. Neither in datebase nor in configuaration. Please import at least the static namespaces.', 1303199037);
			}
		}
		return parent::__construct($namespacesConfiguration, $allowModifications);
	}

}
