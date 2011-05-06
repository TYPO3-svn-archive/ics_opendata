<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In-Cite Solution <technique@in-cite.net>
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

/**
 * Left menu of the extension ics_opendata
 *
 * @author	Mathias Cocheri <mcocheri@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsopendata
 */
class tx_icsopendata_FormMenu
{
    // === ATTRIBUTS ============================================================================== //

	private $_extkey = 'ics_opendata';
	
    // === OPERATIONS ============================================================================= //
	
    /**
     * renderMenu : return menu and form content
     *
     * @return String
     */
    public function renderForm($FormData, $pObj)
    {
		// Get Data
		$post = t3lib_div::_POST();
		$sourceselected = $post['sourceselected'];
		$tableselected = $post['tableselected'];
		$commandselected = $post['commandselected'];
		
		// Menu
		$content .= '
					<table>
						<tbody>';
						
		$content .= $this->generalMenu();
		$content .= $this->sourceMenu();
		$content .= $this->commandMenu();
		$content .= $this->packageVersion();
		$content .= $this->extensionKey();
		$content .= $this->generateMenu();
		
		
		$content .= '
						</tbody>
					</table>';
			
		// Menu command
		$content .= '
					<input type="hidden" id="' . $this->_extkey . 'menuCmd" name="ics_opendata[menuCmd]">
					<input type="hidden" id="' . $this->_extkey . 'menusourceselected" name="sourceselected" value="' . $sourceselected . '">
					<input type="hidden" id="' . $this->_extkey . 'menusourcedeleted" name="sourcedeleted">
					<input type="hidden" id="' . $this->_extkey . 'menutableselected" name="tableselected" value="' . $tableselected . '">
					<input type="hidden" id="' . $this->_extkey . 'menutabledeleted" name="tabledeleted">
					<input type="hidden" id="' . $this->_extkey . 'menucommandselected" name="commandselected" value="' . $commandselected . '">
					<input type="hidden" id="' . $this->_extkey . 'menucommanddeleted" name="commanddeleted">';
		
		return $content;
		
    }//End renderMenu
	
	private function generalMenu()
	{
		$content = '
							<tr>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "general"; 
										document.getElementById("opendatamenu").submit();') . '">
										<strong>' . $GLOBALS['LANG']->getLL('menu.general') . '</strong> 
									</a>
								</td>';
								
		$exttitle = $GLOBALS['repository']->get('extensiontitle');
		$teststring = $GLOBALS['repository']->get('extensiondescription') . $GLOBALS['repository']->get('authorname') . $GLOBALS['repository']->get('authoremail');
		
		if( empty($exttitle) && empty($teststring)) {
			$content .= '
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "general"; 
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-add')  . '
									</a>
								</td>';
		}
		
		$content .= '
							</tr>';

		if( !empty($exttitle) ) {
			$content .= '
							<tr>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "general"; 
										document.getElementById("opendatamenu").submit();') . '">
										' . $exttitle . ' [edit]
									</a>
								</td>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "general"; 
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-delete')  . '
									</a>
								</td>
							</tr>';
		}
		elseif( !empty($teststring) ) {
			$content .= '
							<tr>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "general"; 
										document.getElementById("opendatamenu").submit();') . '">
										' . 'general infos [edit]' . '
									</a>
								</td>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "general"; 
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-delete')  . '
									</a>
								</td>
							</tr>';
		}
		return $content;
	}
	
	private function sourceMenu() 
	{
		$post = t3lib_div::_POST();
		// Delete source 
		if( !empty($post['sourcedeleted']) ) {
			$basexml = $GLOBALS['repository']->get('basexml');
			if( !empty($basexml) ) {
				$basexml->setXmlChild($post['sourcedeleted'], null);
				$GLOBALS['repository']->set('basexml', $basexml);
			}
			$GLOBALS['repository']->setSourceData($post['sourcedeleted'], null);
			
		}
		
		// Delete table 
		if( !empty($post['tabledeleted']) ) {
			$sourceinfos = $GLOBALS['repository']->getSourceInfos($post['sourceselected']);
			$basexml = $GLOBALS['repository']->get('basexml');
			if( !empty($basexml) ) {
				$sourcexml = &$basexml->getXmlChild($post['sourceselected']);
				$sourcexml->setXmlChild($post['tabledeleted'], null);
			}
			
			unset($sourceinfos['selecteditems'][$post['tabledeleted']]);
			$GLOBALS['repository']->setSourceInfos($post['sourceselected'], $sourceinfos);
			$GLOBALS['repository']->set('basexml', $basexml);
		}
		
		// --- sources
		$content .= '
							<tr>
								<td><strong>' . $GLOBALS['LANG']->getLL('menu.sources') . '</strong></td>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "source"; 
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-add')  . '
									</a>
								</td>
							</tr>';
					
		$sources = $GLOBALS['repository']->getSources();
		foreach ($sources as $sourceid=>$value) {

			$tablesselected = $value['infos']['selecteditems'];
			
			$data = $value['data'];
			$sourcename = $data->getName();
			$content .= '
							<tr>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "sourceprofile"; 
										document.getElementById("' . $this->_extkey . 'menusourceselected").value = "' . $sourceid . '";
										document.getElementById("opendatamenu").submit();') . '">
										 [' . $sourcename . ']
									</a>
								</td>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "menu"; 
										document.getElementById("' . $this->_extkey . 'menusourcedeleted").value = "' . $sourceid . '";
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-delete')  . '
									</a>
								</td>
							</tr>';
			foreach($tablesselected as $tablename=>$value) {
				$nbfields = sizeof($value['fields']);
				$content .= '
							<tr>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "tableprofile"; 
										document.getElementById("' . $this->_extkey . 'menusourceselected").value = "' . $sourceid . '";
										document.getElementById("' . $this->_extkey . 'menutableselected").value = "' . $tablename . '";
										document.getElementById("opendatamenu").submit();') . '">
										- ' . $tablename . ' (' . $nbfields . ')
									</a>
								</td>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "menu"; 
										document.getElementById("' . $this->_extkey . 'menusourceselected").value = "' . $sourceid . '";
										document.getElementById("' . $this->_extkey . 'menutabledeleted").value = "' . $tablename . '";
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-delete')  . '
									</a>
								</td>
							</tr>';
			}
		}
			
		return $content;
	}
	
	private function commandMenu()
	{
		$post = t3lib_div::_POST();
		// Delete command
		if( !empty($post['commanddeleted']) || $post['commanddeleted'] == 0) {
			$commands = $GLOBALS['repository']->get('commands');
			$commandfields = $GLOBALS['repository']->get('commandsfields');
			unset($commandfields[$post['commanddeleted']]);
			unset($commands[$post['commanddeleted']]);
			$GLOBALS['repository']->set('commands', $commands);
		}
		
		$basexml = $GLOBALS['repository']->get('basexml');
		// --- commands
		$content .= '
							<tr>
								<td><strong>' . $GLOBALS['LANG']->getLL('menu.commands') . '</strong></td>';
		if( !empty($basexml) || true) {
			$content .= '
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "commandprofile"; 
										document.getElementById("' . $this->_extkey . 'menucommandselected").value = "newcommand";
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-add')  . '
									</a>
								</td>';
		}
		$content .= '
							</tr>';
		
		$commands = $GLOBALS['repository']->get('commands');
		if( !empty($commands) ) {
			foreach($commands as $i=>$command) {
				$commandname = $command->getName();
				$warnings = $command->getWarning();
				$warning = '';
				if( !empty($warnings) ) {
					if( $command->isActive() ) {
						$warning = ' ' . t3lib_iconWorks::getSpriteIcon('status-dialog-warning');
					}
					else {
						$warning = ' ' . t3lib_iconWorks::getSpriteIcon('status-dialog-error');
					}
				}
				$content .= '
							<tr>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "commandprofile"; 
										document.getElementById("' . $this->_extkey . 'menucommandselected").value = "' . $i . '";
										document.getElementById("opendatamenu").submit();') . '">
										[' . $commandname . ']' . $warning . '
									</a>
								</td>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "menu"; 
										document.getElementById("' . $this->_extkey . 'menucommanddeleted").value = "' . $i . '";
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-delete')  . '
									</a>	
								</td>
							</tr>';
			}
		}
		return $content;
	}
	
	private function sumUpMenu() 
	{
		$content .= '
							<tr>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "sumup"; 
										document.getElementById("opendatamenu").submit();') . '">
										<strong>' . $GLOBALS['LANG']->getLL('menu.sumup') . '</strong> 
									</a>
								</td>
								<td>
									<a href="#" onclick="' . htmlspecialchars('
										document.getElementById("' . $this->_extkey . 'menuCmd").value = "sumup"; 
										document.getElementById("opendatamenu").submit();') . '">
										' . t3lib_iconWorks::getSpriteIcon('actions-edit-add')  . '
									</a>
								</td>
							</tr>';
		return $content;
	}
	
	private function packageVersion()
	{
		$package = t3lib_div::_GP('packageversion');
		if (!empty($package))
			$GLOBALS['repository']->set('packageversion', htmlspecialchars($package));
		$packageversion = $GLOBALS['repository']->get('packageversion');
		if (empty($packageversion))	
			$packageversion = '1.0';
		$content .= '
					<tr><td></td></tr>
					<tr>
						<td>
							' . $GLOBALS['LANG']->getLL('menu.packageversion') . ' : 
						</td>
					</tr>
					<tr>
						<td>
							<input type="text" name="packageversion" value="' . $packageversion . '">
						</td>
					</tr>';
		return $content;
	}
	
	private function extensionKey()
	{
		$extkey = t3lib_div::_GP('extensionkey');
		if( !empty($extkey) ) {
			$GLOBALS['repository']->set('extensionkey', htmlspecialchars($this->cleanStr($extkey)));
		}
		$extensionkey = $GLOBALS['repository']->get('extensionkey');
		$content .= '
					<tr><td></td></tr>
					<tr>
						<td>
							' . $GLOBALS['LANG']->getLL('menu.extkey') . ' : 
						</td>
					</tr>
					<tr>
						<td>
							<input type="text" name="extensionkey" value="' . $extensionkey . '">
						</td>
					</tr>';
		if( empty($extensionkey) ) {
			$content .= '
					<tr>
						<td>
							<strong>' . $GLOBALS['LANG']->getLL('menu.extkeynotdefine') . '<strong>
						</td>
					</tr>';
		}
		$content .= '
					<tr>
						<td>
							<input type="submit" name="icsopendata_updatekey" value="Update Extkey">
						</td>
					</tr>';
		return $content;
	}
	
	private function generateMenu()
	{
		$content = '';
		$extensionkey = $GLOBALS['repository']->get('extensionkey');
		if( !empty($extensionkey) ) {
			$content .= '
							<tr>
								<td>
									<input type="submit" name="icsopendatagenerate" value="Display code">
								</td>
							</tr>';
		}
		return $content;
	}
	
	private function cleanStr($in) {
		$search = array ('@[������]@i','@[�����]@i','@[����]@i','@[�����]@i','@[����]@i','@[�]@i','@[ ]@i','@[^a-zA-Z0-9_]@');
		$replace = array ('e','a','i','u','o','c','_','');
		return preg_replace($search, $replace, $in);
	}
	
	public function getNextFormId()
	{
		$post = t3lib_div::_POST();
		$ics_opendata = $post['ics_opendata'];
		
		if( isset($post['icsopendatagenerate']) ) {
			$extkey = $GLOBALS['repository']->get('extensionkey');
			if( empty($extkey) )
				return 'menu';
			return 'result';
		}
		
		if( isset($post['icsopendata_updatekey']) )
			return 'menu';

		
		return $ics_opendata['menuCmd'];
	}
	
} /* end of class tx_icsopendata_FormMenu */

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_opendata/lib/forms/class.form_menu.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_opendata/lib/forms/class.form_menu.php']);
}

?>