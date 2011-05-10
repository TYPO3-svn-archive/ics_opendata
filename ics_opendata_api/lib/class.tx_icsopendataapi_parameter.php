<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Tsi <tsi@in-cite.net>
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

require_once( t3lib_extMgm::extPath('ics_opendata_api') . 'lib/class.tx_icsopendataapi_value.php' );

/**
 * Objet 'parameter' for the 'ics_opendata_api' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsopendataapi
 */

class tx_icsopendataapi_parameter{

	private $name = '', $type = '', $description = '', $mandatory = false, $default = '';
	private $values = array();
		
	/**
	 * Loads a document xml
	 */
	function loadXML(XMLReader $xmlreader){
		// Check the node name.
		if ($xmlreader->name != 'parameter')
			throw new Exception('parameter element expected. ' . $xmlreader->name . ' found.');
		// Load attributes.
		$this->setName( $xmlreader->getAttribute('name') );
		$this->setType( $xmlreader->getAttribute('type') );
		$this->setMandatory($xmlreader->getAttribute('mandatory')==1);
		$this->setDefault( $xmlreader->getAttribute('default') );
		if ($xmlreader->isEmptyElement)
			return;
		if (!$xmlreader->read())
			throw new Exception('Unable to read the parameter node sub elements.');
		while( $xmlreader->nodeType != XMLReader::END_ELEMENT ){
			if( $xmlreader->nodeType == XMLReader::ELEMENT ){
				switch( $xmlreader->name ){
					case 'description':
						// Load description
						if (!$xmlreader->isEmptyElement){
							$this->setDescription($xmlreader->readString());
							while (($xmlreader->nodeType != XMLReader::END_ELEMENT) || ($xmlreader->name != 'description'))
								if (!$xmlreader->read())
									throw new Exception('Unable to read the parameter description node sub elements.');
						}
						break;
					case 'values':
						// Load values
						if ($xmlreader->isEmptyElement)
							break;
						if (!$xmlreader->read())
							throw new Exception('Unable to read the parameter values node sub elements.');
						while( $xmlreader->nodeType != XMLReader::END_ELEMENT ){
							if( $xmlreader->nodeType == XMLReader::ELEMENT ){
								$value = new tx_icsopendataapi_value();
								$value->loadXML($xmlreader);
								$this->addValue($value);
							}
							if (!$xmlreader->read())
								throw new Exception('Unable to read the parameter values node sub elements.');
						}
						break;
					default:
						throw new Exception('description or values expected. ' . $xmlreader->name . ' found.');
				}
			}
			if (!$xmlreader->read())
				throw new Exception('Unable to read the parameter node sub elements.');
		}		
	}
	
	/**
	 * Put parameter in a document xml
	 * @param $xmlwriter XMLWriter
	 */
	function saveXML(XMLWriter $xmlwriter){
		$xmlwriter->startElement('parameter');
		$xmlwriter->writeAttribute( 'name' , $this->getName() );
		$xmlwriter->writeAttribute( 'type' , $this->getType() );
		$xmlwriter->writeAttribute( 'mandatory' , $this->getMandatory()? 1: 0 );
		if( $this->getDefault() != '' )
			$xmlwriter->writeAttribute( 'default' , $this->getDefault() );
		$xmlwriter->startElement('description');
		$xmlwriter->text( $this->getDescription() );
		$xmlwriter->endElement();
		if( $this->getValuesCount() > 0 ){
			$xmlwriter->startElement('values');
			foreach( $this->values as $value ){
				$value->saveXML($xmlwriter);
			}
			$xmlwriter->endElement();
		}
		$xmlwriter->endElement();
	}
	
	/**
	 * Loads _POST variables
	 */
	function loadPOST($post){
		// Load parameter sub element
		foreach( $post as $postvar=>$value){
			switch( $postvar ){
				case 'name':
					$this->setName($value);
					break;
				case 'type':
					$this->setType($value);
					break;
				case 'mandatory':
					$this->setMandatory(!empty($value));
					break;
				case 'default':
					$this->setDefault($value);
					break;
				case 'description':
					$this->setDescription($value);
					break;
				case 'values':
					// Load values
					foreach( $value as $post_values){
						$value = new tx_icsopendataapi_value();
						$value->loadPOST($post_values);
						$this->addValue($value);
					}
					break;
				default:
					throw new Exception('name, type, mandatory, default, description or values element expected. ' . $postvar . ' found.');
			}
		}
	}
	
	/**
	 * Retrieves parameter's name
	 * @return string
	 */
	function getName(){
		return $this->name;
	}
	
	/**
	 * Defines parameter's name
	 * @param $name string
	 */
	function setName($name){
		$this->name = $name;
	}
	
	/**
	 * Retrieves parameter's type
	 * @return string
	 */
	function getType(){
		return $this->type;
	}
	
	/**
	 * Defines parameter's type
	 * @param $type string
	 */
	function setType($type){
		if (!in_array($type, array('enum', 'string', 'number')))
			throw new Exception('Invalid type. "enum", "string" or "number" expected. "' . $type . '" found.');
		$this->type = $type;
	}
	
	/**
	 * Retrieves parameter's description
	 * @return string
	 */
	function getDescription(){
		return $this->description;
	}
	
	/**
	 * Defines parameter's description
	 * @param $description string
	 */
	function setDescription($description){
		$this->description = $description;
	}
	
	/**
	 * Retrieves mandatory
	 * @return boolean
	 */
	function getMandatory(){
		return $this->mandatory;
	}
	
	/**
	 * Defines mandatory
	 * @param $mandatory boolean
	 */
	function setMandatory($mandatory){
		$this->mandatory = $mandatory;
	}
	
	/**
	 * Retrieves default value
	 * @return string
	 */
	function getDefault(){
		return $this->default;
	}
	
	/**
	 * Defines default value
	 * @param $default string
	 */
	function setDefault($default){
		$this->default = $default;
	}

	/**
	 * Count parameter values
	 * @return integer The number values
	 */
	function getValuesCount(){
		return count($this->values);
	}
	
	/**
	 * Retrieves value
	 * @param $i integer Indice of value
	 * @return value
	 */
	function getValue($i){
		return $this->values[$i];
	}
	
	/**
	 * Delete value
	 * @param $obj value
	 */
	function removeValue($obj){
		$keys = array_keys($this->values, $obj, true);
		rsort($keys);
		foreach ($keys as $key)
			array_splice($this->values, $key, 1);
	}
	
	/**
	 * Insert value in values
	 * @param $obj value
	 */
	function addValue($obj){
		$this->values[] = $obj;
	}	
}

?>