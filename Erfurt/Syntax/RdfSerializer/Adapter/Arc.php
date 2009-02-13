<?php
require_once 'Erfurt/Syntax/RdfSerializer/Adapter/Interface.php';
require_once 'arc/ARC2.php';

/**
 * This class acts as an intermediate implementation for some important formats.
 * It uses the ARC library unitl we have own implementations.
 * 
 * @package   syntax
 * @author    Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright Copyright (c) 2008 {@link http://aksw.org aksw}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id$
 */
class Erfurt_Syntax_RdfSerializer_Adapter_Arc implements Erfurt_Syntax_RdfSerializer_Adapter_Interface
{    
    private $_format = null; 
    private $_serializer = null;
    
    public function __construct($format = 'rdfxml')
    {
        error_reporting(E_ALL | ~E_STRICT);
        
        $this->_format = $format;
        
        switch ($format) {
            case 'rdfxml':
                $this->_serializer = ARC2::getRDFXMLSerializer();
                break;
            case 'turtle':
                $this->_serializer = ARC2::getTurtleSerializer();
                break;
            default:
        }
    }
    
    public function __destruct()
    {
        if (defined(_EFDEBUG)) {
            error_reporting(E_ALL | E_STRICT);
        }
    }
    
    public function serializeResourceToString($resourceUri, $graphUri)
    {
        $triples = array();
        $store = Erfurt_App::getInstance()->getStore();
        
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $sparql = new Erfurt_Sparql_SimpleQuery();
        $sparql->setProloguePart('SELECT ?s ?p ?o');
        $sparql->addFrom($graphUri);
        $sparql->setWherePart('WHERE { ?s ?p ?o . FILTER (!isLiteral(?o)) . FILTER (sameTerm(?s, <' . $resourceUri . '>)) }');
        
        $result1 = $store->sparqlQuery($sparql, 'plain', false);
        
        $sparql = new Erfurt_Sparql_SimpleQuery();
        $sparql->setProloguePart('SELECT ?s ?p ?o');
        $sparql->addFrom($graphUri);
        $sparql->setWherePart('WHERE { ?s ?p ?o . FILTER (isLiteral(?o)) . FILTER (sameTerm(?s, <' . $resourceUri . '>)) }');
        $result2 =$store->sparqlQuery($sparql, 'plain', false);
        
        foreach ($result1 as $stm) {
            $s = $stm['s'];
            $p = $stm['p'];
            $o = $stm['o'];
            
            if (!isset($triples["$s"])) {
                $triples["$s"] = array();
            }
            
            if (!isset($triples["$s"]["$p"])) {
                $triples["$s"]["$p"] = array();
            }
            
            if (substr($o, 0, 2) == '_:') {
                $triples["$s"]["$p"][] = array(
                    'type'  => 'bnode',
                    'value' => $o
                );
            } else {
                $triples["$s"]["$p"][] = array(
                    'type'  => 'uri',
                    'value' => $o
                );
            }
        }
        
        foreach ($result2 as $stm) {
            $s = $stm['s'];
            $p = $stm['p'];
            $o = $stm['o'];
            
            if (!isset($triples["$s"])) {
                $triples["$s"] = array();
            }
            
            if (!isset($triples["$s"]["$p"])) {
                $triples["$s"]["$p"] = array();
            }
            
            $objArray = array(
                'type'  => 'literal'
            );
            
            if (strrpos($o, '^^') !== false) {
                $objArray['value'] = substr($o, 0, strrpos($o, '^^'));
                $objArray['datatype'] = substr($o, strrpos($o, '^^')+2);
            } else if (strrpos($o, '@') !== false) {
                $objArray['value'] = substr($o, 0, strrpos($o, '@'));
                $objArray['lang'] = substr($o, strrpos($o, '@')+1);
            } else {
                $objArray['value'] = $o;
            }
            
            $triples["$s"]["$p"][] = $objArray;             
        }
        
        $result = $this->_serializer->getSerializedIndex($triples);
        
        return $result;
    }
    
    public function serializeGraphToString($graphUri)
    {   
        $triples = array();
        $store = Erfurt_App::getInstance()->getStore();
        
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $sparql = new Erfurt_Sparql_SimpleQuery();
        $sparql->setProloguePart('SELECT ?s ?p ?o');
        $sparql->addFrom($graphUri);
        $sparql->setWherePart('WHERE { ?s ?p ?o . FILTER (!isLiteral(?o)) }');
        
        $result1 = $store->sparqlQuery($sparql, 'plain', false);
        
        $sparql = new Erfurt_Sparql_SimpleQuery();
        $sparql->setProloguePart('SELECT ?s ?p ?o');
        $sparql->addFrom($graphUri);
        $sparql->setWherePart('WHERE { ?s ?p ?o . FILTER (isLiteral(?o)) }');
        $result2 =$store->sparqlQuery($sparql, 'plain', false);
        
        foreach ($result1 as $stm) {
            $s = $stm['s'];
            $p = $stm['p'];
            $o = $stm['o'];
            
            if (!isset($triples["$s"])) {
                $triples["$s"] = array();
            }
            
            if (!isset($triples["$s"]["$p"])) {
                $triples["$s"]["$p"] = array();
            }
            
            if (substr($o, 0, 2) == '_:') {
                $triples["$s"]["$p"][] = array(
                    'type'  => 'bnode',
                    'value' => $o
                );
            } else {
                $triples["$s"]["$p"][] = array(
                    'type'  => 'uri',
                    'value' => $o
                );
            }
        }
        
        foreach ($result2 as $stm) {
            $s = $stm['s'];
            $p = $stm['p'];
            $o = $stm['o'];
            
            if (!isset($triples["$s"])) {
                $triples["$s"] = array();
            }
            
            if (!isset($triples["$s"]["$p"])) {
                $triples["$s"]["$p"] = array();
            }
            
            $objArray = array(
                'type'  => 'literal'
            );
            
            if (strrpos($o, '^^') !== false) {
                $objArray['value'] = substr($o, 0, strrpos($o, '^^'));
                $objArray['datatype'] = substr($o, strrpos($o, '^^')+2);
            } else if (strrpos($o, '@') !== false) {
                $objArray['value'] = substr($o, 0, strrpos($o, '@'));
                $objArray['lang'] = substr($o, strrpos($o, '@')+1);
            } else {
                $objArray['value'] = $o;
            }
            
            $triples["$s"]["$p"][] = $objArray;             
        }
        
        $result = $this->_serializer->getSerializedIndex($triples);
        
        
        return $result;
    }
     
    public function reset()
    {
        switch ($this->_format) {
            case 'rdfxml':
                $this->_serializer = ARC2::getRDFXMLSerializer();
                break;
            case 'turtle':
                $this->_serializer = ARC2::getTurtleSerializer();
                break;
            default:
        }
    }
}