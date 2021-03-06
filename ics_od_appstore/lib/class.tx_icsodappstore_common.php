<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cit� Solution <technique@in-cite.net>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   61: class tx_icsodappstore_common extends tslib_pibase
 *
 *   85:     function tx_icsodappstore_common()
 *   95:     function init()
 *  110:     function renderContentError($msg)
 *  124:     function getApplications($mode, $selectFields = null, $parameter = null)
 *  283:     function existInApplicationTCA($field)
 *  301:     function getStatistics($selectFields = '', $whereFields = array(), $addWhereText = '', $groupby = '', $order = '', $limit = '', $debugger = false)
 *  353:     function renderLogo($fieldname, $fieldconf, $value)
 *  375:     function getAppPlatforms($application)
 *  392:     function getAllPlatforms()
 *  406:     function getPlatforms($search)
 *
 * TOTAL FUNCTIONS: 10
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Common for plugins' for the 'ics_od_appstore' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodappstore
 */
class tx_icsodappstore_common extends tslib_pibase {

	const APPMODE_ALL = 0; /**< Mode all applications */
	const APPMODE_USER = 1; /**< Mode users applications */
	const APPMODE_SINGLE = 2; /**< Mode details application */
	const APPMODE_SINGLEUSER = 3; /**< Mode details user application */
	const APPMODE_SEARCH = 4; /**< Mode search */
	const APPMODE_MAX = 4; /**< Max number mode */

	var $templateFile = "typo3conf/ext/ics_od_appstore/res/template.html"; /**< Path of template file */
	var $tables = array(
		'applications' => 'tx_icsodappstore_applications',
		'statistics' => 'tx_icsodappstore_statistics',
		'users' => 'fe_users',
		'platforms' => 'tx_icsodappstore_platforms',
		'apps_platforms_mm' => 'tx_icsodappstore_apps_platforms_mm'
	); /**< database table */


	/**
	 * __Constructor
	 *
	 * @return	void
	 */
	function tx_icsodappstore_common() {
		tslib_pibase::tslib_pibase();

	}

	/**
	 * Init the plugin
	 *
	 * @return	boolean
	 */
	function init() {
		if (is_array($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_icsodappstore.'])) {
			$this->conf = array_merge($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_icsodappstore.'], $this->conf);
		}

		$templateFile = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template', 'main');
		$templateFile = $templateFile? $templateFile: $this->conf['template'];
		$this->templateFile = $templateFile ? $templateFile : $this->templateFile;

		return true;
	}

	/**
	 * Render the error content
	 *
	 * @param	string		$msg: The error message
	 * @return	string		The error content
	 */
	function renderContentError($msg) {
		$html = $this->cObj->fileResource($this->templateFile);
		$template = $this->cObj->getSubpart($html, '###TEMPLATE_ERROR###');
		return $this->cObj->substituteMarker($template, '###ERROR_MSG###', htmlspecialchars($msg));
	}

	/**
	 * Return applications depending on mode and paramaters
	 *
	 * @param	const		$mode: constants modes
	 * @param	string		$selectFields: list of selected fields
	 * @param	mixed		$parameter: userid for mode APPMODE_SINGLEUSER OR APPMODE_USER, search criteria for mode APPMODE_SEARCH
	 * @return	array
	 */
	function getApplications($mode, $selectFields = null, $parameter = null) {
		// Error
		if ($mode > self::APPMODE_MAX)
			return false;
		if (($mode == self::APPMODE_SINGLE || $mode == self::APPMODE_SINGLEUSER) && !$parameter )
			return false;

		$select_table = '`'.$this->tables['applications'].'`
			INNER JOIN `'.$this->tables['users'].'`
			ON `'.$this->tables['users'].'`.`uid` = `'.$this->tables['applications'].'`.`fe_cruser_id`';
		$addWhere = array();
		$order = ' `'.$this->tables['applications'].'`.`tstamp` DESC ';
		$limit = '';
		$groupby = '';

		switch($mode) {
			case self::APPMODE_ALL:
				$fav_applis = t3lib_div::intExplode(',', $this->conf['fav_applis'], true);
				
				// limit
				if ($selectFields!='count') {
					$rows_by_page = $this->conf['list.']['colNum'] * $this->conf['list.']['rowsByCol'];
					$limit = ($parameter['page'] * $rows_by_page) . ',' . $rows_by_page;
				}
				
				// Sort
				if ($parameter['sort']) {
					$orderAvailable = explode(',', $this->conf['list.']['orderAvailable']);
					if (in_array($parameter['sort'], $orderAvailable) ) {
						if ($parameter['sort'] == 'favorite') {
							$order = 'FIND_IN_SET(' . '`'.$this->tables['applications'].'`.`uid`' . ', ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(implode(',', $fav_applis), $this->tables['applications']) . ')';
						}
						else {
							$o = $parameter['sort'] == 'title' ? ' ASC':' DESC';
							$order = $parameter['sort']. $o;
						}
					}
				}
				
				// Filter platforms
				if (is_array($parameter['platforms']) && !empty($parameter['platforms'])) {
					$addWhere[] = '`'.$this->tables['apps_platforms_mm'].'`.`uid_foreign` IN(' . implode(',', $parameter['platforms']) . ') ';
					$select_table .= ' LEFT JOIN `'.$this->tables['apps_platforms_mm'].'` ON `'.$this->tables['apps_platforms_mm'].'`.`uid_local` = `'.$this->tables['applications'].'`.`uid`';
				}
				
				// Filter Title or Description
				if(isset($parameter['searchW']) && !empty($parameter['searchW'])) {
					$addWhere[] = '(`'.$this->tables['applications'].'`.`title` LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$parameter['searchW'].'%', $this->tables['applications']) . ' 
								OR `'.$this->tables['applications'].'`.`description` LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$parameter['searchW'].'%', $this->tables['applications']) . ')';
				}

				// Hook for additionnal filters
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['addSearchRestriction'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['addSearchRestriction'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$_procObj->addSearchRestriction($addWhere, $select_table, $parameter, $this->conf, $this);
					}
				}
				
				$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` > 0 ';
				$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` < '.time();
				$addWhere[] = '`'.$this->tables['applications'].'`.`lock_publication` = 0';
				$addWhere[] = '`'.$this->tables['applications'].'`.`publish` = 1 ';
				$addWhere[] = '1 '.$this->cObj->enableFields($this->tables['applications']);
				
				if (!empty($fav_applis)) {
					$addWhere[] = '`'.$this->tables['applications'].'`.`uid` IN (' . implode(',', $fav_applis) . ')';
				}
			break;

			case self::APPMODE_USER:
				if (!$parameter)
					$parameter = $GLOBALS['TSFE']->fe_user->user['uid'];

				$addWhere[] = ' `'.$this->tables['applications'].'`.`fe_cruser_id` = '.$parameter;
			break;

			case self::APPMODE_SINGLE:
				$addWhere[] = ' `'.$this->tables['applications'].'`.`uid` = '.$parameter;

				// USER CAN VIEW HIS APPLICATION
				if (!$GLOBALS['TSFE']->fe_user->user['uid']) {
					$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` > 0 ';
					$addWhere[] = '`'.$this->tables['applications'].'`.`release_date` < '.time();
					$addWhere[] = '`'.$this->tables['applications'].'`.`lock_publication` = 0';
					$addWhere[] = '`'.$this->tables['applications'].'`.`publish` = 1 ';
					$addWhere[] = '1 '.$this->cObj->enableFields($this->tables['applications']);
				}else{
					$addWhere[] = ' (
						`'.$this->tables['applications'].'`.`fe_cruser_id` = '.$GLOBALS['TSFE']->fe_user->user['uid'].'
						OR (
							`'.$this->tables['applications'].'`.`release_date` > 0 AND
							`'.$this->tables['applications'].'`.`release_date` < '.time().' AND
							`'.$this->tables['applications'].'`.`lock_publication` = 0 AND
							`'.$this->tables['applications'].'`.`publish` = 1 '.$this->cObj->enableFields($this->tables['applications']).'
						)
					)';
				}
				$addWhere[] = '1 '.$this->cObj->enableFields($this->tables['applications']);
				$limit = 1;
			break;

			case self::APPMODE_SINGLEUSER:
				$addWhere[] = ' `'.$this->tables['applications'].'`.`uid` = '.$parameter;
				$addWhere[] = ' `'.$this->tables['applications'].'`.`fe_cruser_id` = '.$GLOBALS['TSFE']->fe_user->user['uid'];
				$addWhere[] = '1 '.$this->cObj->enableFields($this->tables['applications']);
				$limit = 1;
			break;

			case self::APPMODE_SEARCH:
				if ($parameter && is_array($parameter) && !empty($parameter)) {
					foreach($parameter as $whereField => $value) {
						if (!is_numeric($value)) {
							$addWhere[] = '`'.$this->tables['applications'].'`.`'.$whereField.'` = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $this->tables['applications']) . ' ';
						}else{
							$addWhere[] = '`'.$this->tables['applications'].'`.`'.$whereField.'` = ' . $value . ' ';
						}
					}
				}
			break;
		}

		$where = '`'.$this->tables['applications'].'`.`deleted` = 0';
		if (!empty($addWhere)) {
			$where = implode(' AND ', $addWhere);
		}

		if (!$selectFields) {
			$select = 'DISTINCT `'.$this->tables['applications'].'`.`uid`,
				`'.$this->tables['applications'].'`.`crdate`,
				`'.$this->tables['applications'].'`.`apikey`,
				`'.$this->tables['applications'].'`.`title`,
				`'.$this->tables['applications'].'`.`hidden`,
				`'.$this->tables['applications'].'`.`description`,
				`'.$this->tables['applications'].'`.`platform`,
				`'.$this->tables['applications'].'`.`countcall`,
				`'.$this->tables['applications'].'`.`maxcall`,
				`'.$this->tables['applications'].'`.`release_date`,
				`'.$this->tables['applications'].'`.`publish`,
				`'.$this->tables['applications'].'`.`logo`,
				`'.$this->tables['applications'].'`.`screenshot`,
				`'.$this->tables['applications'].'`.`link`,
				`'.$this->tables['applications'].'`.`update_date`,
				`'.$this->tables['applications'].'`.`lock_publication`,
				`'.$this->tables['users'].'`.`name`,
				`'.$this->tables['users'].'`.`first_name`,
				`'.$this->tables['users'].'`.`last_name`,
				`'.$this->tables['applications'].'`.`fe_cruser_id`';
		}else{
			$selectFields = explode(',', $selectFields);
			$selectTab = array();
			if (is_array($selectFields) && !empty($selectFields)) {
				foreach ($selectFields as $field) {
					switch ($field) {
						case 'count':
							$selectTab[] = 'count(`'.$this->tables['applications'].'`.`uid`) as count';
							break;
						case 'name':
							$selectTab[] = '`'.$this->tables['users'].'`.`'.$field.'`';
							break;
						case 'first_name':
							$selectTab[] = '`'.$this->tables['users'].'`.`'.$field.'`';
							break;
						case 'last_name':
							$selectTab[] = '`'.$this->tables['users'].'`.`'.$field.'`';
							break;
						default:
							$selectTab[] = '`'.$this->tables['applications'].'`.`'.$field.'`';
							break;
					}
				}
			}
			$select = implode(',', $selectTab);
		}

		$apllications = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$select,
			$select_table,
			$where,
			$groupby,
			$order,
			$limit
		);

		// t3lib_div::debug(
			// $GLOBALS['TYPO3_DB']->selectQuery(
				// $select,
				// $select_table,
				// $where,
				// $groupby,
				// $order,
				// $limit
			// ),
			// 'query'
		// );
		
		if (is_array($apllications) && !empty($apllications))
			return $apllications;
		return false;
	}

	/**
	 * Check if columns exist in TCA applications
	 *
	 * @param	string		$field
	 * @return	boolean
	 */
	function existInApplicationTCA($field) {
		global $TCA;
		t3lib_div::loadTCA($this->tables['applications']);
		return array_key_exists($field, $TCA[$this->tables['applications']]['columns']);
	}

	/**
	 * Return application statistics
	 *
	 * @param	string		$selectFields: selected fields
	 * @param	array		$whereFields: restriction statistics fields
	 * @param	string		$addWhereText: restriction fields
	 * @param	string		$groupby: group by
	 * @param	string		$order: order by
	 * @param	string		$limit: limit
	 * @param	boolean		$debugger: activate debugger
	 * @return	array
	 */
	function getStatistics($selectFields = '', $whereFields = array(), $addWhereText = '', $groupby = '', $order = '', $limit = '', $debugger = false) {
		$addWhere = array();

		foreach($whereFields as $whereField => $value) {
			if (!is_numeric($value)) {
				$addWhere[] = '`'.$this->tables['statistics'].'`.`'.$whereField.'` = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $this->tables['statistics']) . ' ';
			}else{
				$addWhere[] = '`'.$this->tables['statistics'].'`.`'.$whereField.'` = ' . $value . ' ';
			}
		}

		if (!$selectFields) {
			$selectFields = '`'.$this->tables['statistics'].'`.`uid`, `'.$this->tables['statistics'].'`.`application`, `'.$this->tables['statistics'].'`.`cmd`, `'.$this->tables['statistics'].'`.`count`, `'.$this->tables['statistics'].'`.`date`';
		}

		if (!empty($addWhere))
			$addWhereText .= ' AND '.implode(' AND ', $addWhere);

		$statistics = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$selectFields,
			'`'.$this->tables['statistics'].'`',
			'1 '.$this->cObj->enableFields($this->tables['statistics']).' '.$addWhereText,
			$groupby,
			$order,
			$limit
		);

		if ($debugger) {
			var_dump($GLOBALS['TYPO3_DB']->SELECTquery(
				$selectFields,
				'`'.$this->tables['statistics'].'`',
				'1 '.$this->cObj->enableFields($this->tables['statistics']).' '.$addWhereText,
				'',
				$order,
				$limit
			));
			var_dump($statistics);
		}

		if (is_array($statistics) && !empty($statistics))
			return $statistics;
		return false;
	}

	/**
	 * Render logo content
	 *
	 * @param	string		$fieldname
	 * @param	array		$fieldconf
	 * @param	mixed		$value
	 * @return	string		content
	 */
	function renderLogo($fieldname, $fieldconf, $value) {
		global $TSFE;
		$files = t3lib_div::trimExplode(',', (is_array($value)) ? $value['files'] : $value, true);

		$imgTS = $this->conf['list.']['logo.'];
		if (count($files)>0 && file_exists( $fieldconf['config']['uploadfolder'] . '/' . $files[0]) ) {
			$imgTS['file'] = $fieldconf['config']['uploadfolder'] . '/' . $files[0];
		}

		$bigImage = $this->cObj->IMG_RESOURCE( $imgTS );
		if ($bigImage && $bigImage!="")
			return '<img src="' . $bigImage . '" alt="Logo" title="logo"/>';

		return '';
	}

	/**
	 * Retrieves application's platforms
	 *
	 * @param	array		$application	The application
	 * @return	resource
	 */
	function getAppPlatforms($application) {
		return $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'`'.$this->tables['platforms'].'`.`uid`,`'.$this->tables['platforms'].'`.`title`',
			$this->tables['applications'],
			$this->tables['apps_platforms_mm'],
			$this->tables['platforms'],
			' AND `' . $this->tables['applications'] . '`.`uid` = ' . $application['uid'] . ' ' . $this->cObj->enableFields($this->tables['applications']). ' ' . $this->cObj->enableFields($this->tables['platforms']),
			'',
			$this->tables['platforms'].'.title'
		);
	}

	/**
	 * Retrieves all platforms
	 *
	 * @return	mixed		All platforms
	 */
	function getAllPlatforms($params = null) {
		$groupBy = $params['groupBy']? $params['groupBy']: '';
		$orderBy = $params['orderBy']? $params['orderBy']: '';
		
		return $platforms_mm = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'`uid`, `title`',
			$this->tables['platforms'],
			'`title` != \'\' ' . $this->cObj->enableFields($this->tables['platforms']),
			$groupBy,
			$orderBy
		);
	}

	/**
	 * Retrieves all platforms
	 *
	 * @param	string		$search	The string to search
	 * @return	mixed		All platforms
	 */
	function getPlatforms($search) {
		return $platforms_mm = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'`uid`, `title`',
			$this->tables['platforms'],
			'1 AND (title LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($search . '%', $this->tables['platforms']) . ' OR uid LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($search . '%', $this->tables['platforms']) . ') ' . $this->cObj->enableFields($this->tables['platforms'])
		);
	}
	
	/**
	 * Get page bowser
	 *
	 * @param	int		$numberOfPages number of pages
	 * @return	void
	 */
	protected function getListGetPageBrowser($numberOfPages) {
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
		$conf += array(
			'pageParameterName' => $this->prefixId . '|page',
			'numberOfPages' => $numberOfPages,
		);

		// Get page browser
		$cObj = t3lib_div::makeInstance('tslib_cObj');

		/* @var $cObj tslib_cObj */
		$cObj->start(array(), '');
		return $cObj->cObjGetSingle('USER', $conf);
	}
}



?>