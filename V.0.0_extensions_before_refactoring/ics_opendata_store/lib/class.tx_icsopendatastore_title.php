<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cité Solution <technique@in-cite.net>
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
/*
 * $Id$
 */

/**
 * Class 'tx_icsopendatastore_title' to display title column label
 *
 * @author Tsi Yang <tsi@in-cite.net>
 *
 * @package	TYPO3
 * @subpackage	tx_icsopendatastore
 *
 */
class tx_icsopendatastore_title	{
	function getRecordTitle($params, $pObj){
		if ($params['table'] == 'tx_icsopendatastore_files')	{
			if ($params['row']['record_type'] == '0')	{
				$params['title'] = $params['row']['file'];
			}	else	{
				$params['title'] = $params['row']['url'];
			}
		}
	}
}


