<?php

	/*
		This class supports the creating of a standard lists.

		Per column: 
		- name 
		- label
		- hint
		- width (width of the column)

		- print_function
		- print_syntax (string containing {keys} to create dynamic values)

		- css_class (css class name)
		- css_style (css style tag)

		- string_limit (max text length)
		- decimal_limit (number of decimals displayed)

		- allow_sort (boolean)
		- allow_search (boolean)
		- default_sort_column (boolean)
		- default_sort_order (ASC|DESC)
	*/

	class clsListBuilder
	{
		public $sBaseUrl = '';
		public $iRecordsPerPage = 30;
		public $sPaginationUrl = '';
		public $bPaginationFirstAndLast = false;
		public $iPaginationCount = 11;

		// Column definitions
		public $aColumns = array();
		public $aSearchColumns = array();
		public $sCustomSearchHtml = false;
		public $iSearchLabelWidth = 75;
		public $aSortColumns = array();
		public $aFilterColumns = array();
		public $aFilterValues = array();
		public $sCustomFilterHtml = '';

		public $sDefaultSortColumn = 'id';
		public $sDefaultSortOrder = 'ASC';
		public $sDefaultOrderBy = '';

		public $sFilterHtml = '';

		// Query's
		public $sDataQuery = '';
		public $sCountQuery = '';

		public function __construct($sName, $aColumns = array())
		{
			$this->sName = $sName;
			$this->aColumns = $aColumns;

			if(clsText::start(SELF_URL, BACKEND_URL))
			{
				$this->sBaseUrl = BACKEND_URL;

				if(isset($_REQUEST['rsPage']['id']) && isset($_REQUEST['rsModule']['folder']))
				{
					$this->sPaginationUrl = $this->sBaseUrl . '/index.php?core[view]=' . clsGet::get(array('core', 'view')) . '&core[id]=' . clsGet::get(array('core', 'id')) . '&' . $_REQUEST['rsModule']['folder'] . '[view]=' . clsGet::get(array($_REQUEST['rsModule']['folder'], 'view'), true) . '&listbuilder[pagination]=__PAGE__';
				}
				else
				{
					$this->sPaginationUrl = $this->sBaseUrl . '/index.php?core[view]=' . VIEW_NAME . (isset($_GET['core']['id']) ? '&core[id]=' . $_GET['core']['id'] : '') . '&listbuilder[pagination]=__PAGE__';
				}
			}
			elseif(clsText::start(SELF_URL, CRM_URL))
			{
				$this->sBaseUrl = CRM_URL;
				$this->sPaginationUrl = $this->sBaseUrl . '/index.php?core[view]=' . VIEW_NAME . (isset($_GET['core']['id']) ? '&core[id]=' . $_GET['core']['id'] : '') . '&listbuilder[pagination]=__PAGE__';
			}
			else
			{
				$this->sBaseUrl = FRONTEND_URL;
				$this->sPaginationUrl = $this->sBaseUrl . '/index.php?core[page]=' . $_REQUEST['rsPage']['id'] . '&listbuilder[pagination]=__PAGE__';
			}
		}

		public function setFilterHtml($sFormName, $aFormFields = false, $aData = false)
		{
			if(is_array($aFormFields))
			{
				$this->sFilterHtml = self::getFilterHtml($sFormName, $aFormFields, $aData);
				return self::getFilterData($sFormName, $aFormFields);
			}
			else
			{
				$this->sFilterHtml = $sFormName;
			}
		}

		public function toHtml()
		{
			$html = '';

			if(isset($_SESSION['core']['listbuilder'][$this->sName]['sort']) == false)
			{
				$_SESSION['core']['listbuilder'][$this->sName]['sort'] = array('column' => '', 'order' => '');
			}

			if(isset($_SESSION['core']['listbuilder'][$this->sName]['search']) == false)
			{
				$_SESSION['core']['listbuilder'][$this->sName]['search'] = array('keyword' => '');
			}

			if(isset($_SESSION['core']['listbuilder'][$this->sName]['filter']) == false)
			{
				$_SESSION['core']['listbuilder'][$this->sName]['filter'] = array();
			}

			$sQueryOrderBy = "";

			// Lookup search & sort columns
			for($c = 0; $c < sizeof($this->aColumns); $c++)
			{
				if(isset($this->aColumns[$c]['allow_sort']) && ($this->aColumns[$c]['allow_sort'] === true))
				{
					$this->aSortColumns[] = $this->aColumns[$c]['name'];

					$sQueryOrderBy .= ", `" . $this->aColumns[$c]['name'] . "` " . (empty($this->aColumns[$c]['default_sort_order']) ? "ASC" : $this->aColumns[$c]['default_sort_order']);
				}

				if(isset($this->aColumns[$c]['allow_search']) && ($this->aColumns[$c]['allow_search'] === true))
				{
					$this->aSearchColumns[] = $this->aColumns[$c]['name'];
				}

				if(isset($this->aColumns[$c]['filter_values']) && is_array($this->aColumns[$c]['filter_values']) && sizeof($this->aColumns[$c]['filter_values']))
				{
					$this->aFilterColumns[] = $this->aColumns[$c];
				}

				if(isset($this->aColumns[$c]['default_sort_column']) && ($this->aColumns[$c]['default_sort_column'] === true))
				{
					$this->sDefaultSortColumn = $this->aColumns[$c]['name'];

					if(empty($this->aColumns[$c]['default_sort_order']))
					{
						$this->sDefaultSortOrder = 'ASC';
					}
					else
					{
						$this->sDefaultSortOrder = $this->aColumns[$c]['default_sort_order'];
					}
				}

				if(isset($this->aColumns[$c]['label']) && is_array($this->aColumns[$c]['label']))
				{
					if(isset($this->aColumns[$c]['label'][0]) && isset($this->aColumns[$c]['label'][1]))
					{
						$this->aColumns[$c]['label'] = clsLanguage::getTranslation($this->aColumns[$c]['label'][0], $this->aColumns[$c]['label'][1]);
					}
					else
					{
						error('Invalid label for field ' . $this->aColumns[$c]['name'], __FILE__, __LINE__);
					}
				}
			}

			if(empty($_SESSION['core']['listbuilder'][$this->sName]['sort']['column']))
			{
				$_SESSION['core']['listbuilder'][$this->sName]['sort'] = array('column' => $this->sDefaultSortColumn, 'order' => $this->sDefaultSortOrder);
			}

			// Store sort options in session
			if(isset($_GET['listbuilder']['sort']['column']))
			{
				$sSortColumn = clsGet::get(array('listbuilder', 'sort', 'column'), true);

				if(in_array($sSortColumn, $this->aSortColumns))
				{
					$sSortOrder = '';

					if(isset($_GET['listbuilder']['sort']['order']))
					{
						$sSortOrder = ((strcasecmp(clsGet::get(array('listbuilder', 'sort', 'order'), true), 'desc') === 0) ? 'DESC' : 'ASC');
					}

					$_SESSION['core']['listbuilder'][$this->sName]['sort'] = array('column' => $sSortColumn, 'order' => $sSortOrder);
				}
				else
				{
					$_SESSION['core']['listbuilder'][$this->sName]['sort'] = array('column' => $this->sDefaultSortColumn, 'order' => $this->sDefaultSortOrder);
				}
			}

			// Store search options in session
			if(clsPost::match('form', $this->sName))
			{
				$sKeyword = strtolower(clsPost::get('keyword', true));
				$_SESSION['core']['listbuilder'][$this->sName]['search'] = array('keyword' => $sKeyword);

				if(isset($_POST['filter']) && is_array($_POST['filter']))
				{
					foreach($this->aFilterColumns as $k => $v)
					{
						if(isset($_POST['filter'][$v['name']]) && in_array($_POST['filter'][$v['name']], $v['filter_values']))
						{
							$_SESSION['core']['listbuilder'][$this->sName]['filter'][$v['name']] = $_POST['filter'][$v['name']];
						}
					}

					$_SESSION['core']['listbuilder'][$this->sName]['filter'] = $_POST['filter'];
				}
			}


			$aQueryString = clsHttpQuery::toArray(SELF_URL);

			if(isset($aQueryString['listbuilder']))
			{
				unset($aQueryString['listbuilder']);
			}

			$sSortUrl = $this->sBaseUrl . '/index.php?' . clsArray::toHttpQuery($aQueryString) . (sizeof($aQueryString) ? '&' : '');




			// Create ORDER BY
			$sSqlOrder = BACKTICK . (empty($_SESSION['core']['listbuilder'][$this->sName]['sort']['column']) ? $this->sDefaultSortColumn : $_SESSION['core']['listbuilder'][$this->sName]['sort']['column']) . BACKTICK . ' ' . (empty($_SESSION['core']['listbuilder'][$this->sName]['sort']['order']) ? $this->sDefaultSortOrder : $_SESSION['core']['listbuilder'][$this->sName]['sort']['order']);

			if(!empty($this->sDefaultOrderBy))
			{
				$sSqlOrder .= ', ' . $this->sDefaultOrderBy;
			}

			$sSqlOrder .= $sQueryOrderBy;


			// Create WHERE
			$sSqlWhere = '';

			if(is_empty($_SESSION['core']['listbuilder'][$this->sName]['search']['keyword']) === false)
			{
				$aKeywords = clsText::getKeywords($_SESSION['core']['listbuilder'][$this->sName]['search']['keyword']);

				foreach($aKeywords as $sKeyword)
				{
					$sSqlKeyword = "";

					for($c = 0; $c < sizeof($this->aColumns); $c++)
					{
						if(in_array($this->aColumns[$c]['name'], $this->aSearchColumns))
						{
							if(strlen($sSqlKeyword))
							{
								$sSqlKeyword .= " OR ";
							}

							$sSqlKeyword .= "(`" . $this->aColumns[$c]['name'] . "` LIKE '%" . clsText::escapeSql($sKeyword, true) . "%')";
						}
					}

					if(strlen($sSqlKeyword))
					{
						if(strlen($sSqlWhere))
						{
							$sSqlWhere .= " AND ";
						}

						$sSqlWhere .= "(" . $sSqlKeyword . ")";
					}
				}
			}

			if(sizeof($_SESSION['core']['listbuilder'][$this->sName]['filter']))
			{
				foreach($_SESSION['core']['listbuilder'][$this->sName]['filter'] as $k => $v)
				{
					if(strlen($sSqlWhere))
					{
						$sSqlWhere .= " AND ";
					}

					if(is_numeric($v))
					{
						$sSqlWhere .= "(`" . $k . "` = '" . clsText::escapeSql($v) . "')";
					}
					else
					{
						$sSqlWhere .= "(`" . $k . "` LIKE '%" . clsText::escapeSql($v, true) . "%')";
					}
				}
			}




			if(!empty($this->sFilterHtml) || is_empty($sSqlWhere))
			{
				$sSqlWhere = '1';
			}

			// Count available records
			$sql = str_replace('{order}', $sSqlOrder, $this->sCountQuery);
			$sql = str_replace('{where}', $sSqlWhere, $sql);
			$rsCount = clsDatabase::getRecord($sql);
			$iRecordsInQuery = $rsCount['count'];


			// Lookup PAGINATION
			if(isset($_GET['listbuilder']['pagination']))
			{
				$iPaginationId = clsGet::get(array('listbuilder', 'pagination'), true, true);

				if(($iPaginationId < 1) || ($iPaginationId >= ceil($iRecordsInQuery / $this->iRecordsPerPage)))
				{
					$iPaginationId = 0;
				}
			}
			else
			{
				$iPaginationId = 0;
			}


			// Create LIMIT
			$sSqlLimit = ($iPaginationId * $this->iRecordsPerPage) . ', ' . $this->iRecordsPerPage;


			// Lookup records
			$sql = str_replace('{order}', $sSqlOrder, $this->sDataQuery);
			$sql = str_replace('{where}', $sSqlWhere, $sql);
			$sql = str_replace('{limit}', $sSqlLimit, $sql);
			$rsData = clsDatabase::getRecords($sql);


			// Create pagination
			$sPagination = clsPagination::create($this->sPaginationUrl, $iRecordsInQuery, $iPaginationId, $this->iRecordsPerPage, $this->iPaginationCount, ' ', $this->bPaginationFirstAndLast);

			if(!empty($this->sFilterHtml))
			{
				$html .= $this->sFilterHtml;
			}
			elseif(!empty($this->aSearchColumns))
			{
				$sSearchHintText = 'Deze zoekbox doorzoekt de volgende velden:';

				for($i = 0; $i < sizeof($this->aSearchColumns); $i++)
				{
					$bFieldFound = false;

					for($c = 0; $c < sizeof($this->aColumns); $c++)
					{
						if(isset($this->aColumns[$c]['name']) && (strcmp($this->aSearchColumns[$i], $this->aColumns[$c]['name']) === 0))
						{
							$sSearchHintText .= LF . '- ' . $this->aColumns[$c]['label'];
							$bFieldFound = true;
							break;
						}
					}

					if($bFieldFound === false)
					{
						$sSearchHintText .= LF . '- ' . $this->aSearchColumns[$i];
					}
				}

				$html .= '
<form action="' . clsText::escapeHtml(SELF_URL) . '" method="post">
' . clsFormBuilder::createFormField('form', 'hidden', $this->sName) . '
	<table border="0" cellpadding="0" cellspacing="0" class="form">';

				if($this->sCustomFilterHtml)
				{
					$html .= $this->sCustomFilterHtml;
				}

				if(sizeof($this->aFilterColumns))
				{
					foreach($this->aFilterColumns as $k => $v)
					{
						$sFilterValue = (empty($_SESSION['core']['listbuilder'][$this->sName]['filter'][$v['name']]) ? '' : $_SESSION['core']['listbuilder'][$this->sName]['filter'][$v['name']]);

						$html .= '
		<tr>
			<td align="left" valign="top" width="' . $this->iSearchLabelWidth . '"><div class="label">' . clsText::escapeHtml($v['label']) . ':</div></td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="left" valign="top"><div class="input"><select class="select select_single" name="filter[' . clsText::escapeHtml($v['name']) . ']"><option value="">-</option>' . clsFormBuilder::createSelectOptions($v['filter_values'], $sFilterValue) . '</select></td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="right" valign="top"></td>
		</tr>';
					}
				}

				$html .= '
		<tr>
			<td align="left" valign="top" width="' . $this->iSearchLabelWidth . '"><div class="label">' . clsLanguage::getTranslation('listbuilder', 'Search') . ':</div></td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="left" valign="top"><div class="input"><input class="textfield textfield_s" name="keyword" type="text" value="' . clsText::escapeHtml($_SESSION['core']['listbuilder'][$this->sName]['search']['keyword']) . '"></td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="left" valign="top"><div class="input"><input class="button" type="submit" value="&raquo;"></td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="right" valign="top"><div class="icon"><img height="16" width="16" alt="Hint" border="0" class="hint" src="' . clsText::escapeHtml($this->sBaseUrl . '/images/hint.png') . '" onclick="javascript: alert(\'' . clsText::escapeJavascript($sSearchHintText) . '\');"></td>
		</tr>
	</table>
</form>';
			}

			if($this->sCustomSearchHtml !== false)
			{
				$html .= $this->sCustomSearchHtml;
			}

			$html .= '
' . ($sPagination ? '<div class="pagination">' . $sPagination . '</div>' : '') . '
<table border="0" cellpadding="0" cellspacing="0" class="list" width="100%">
	<tr class="head">
		<td align="left" valign="top" width="30"><div class="label">&nbsp;</div></td>';

			$iColumnCount = 1;

			for($c = 0; $c < sizeof($this->aColumns); $c++)
			{
				if((isset($this->aColumns[$c]['print_function']) == false) || ($this->aColumns[$c]['print_function'] === false))
				{
					// Ignore column
				}
				else
				{
					$iColumnCount++;

					if($this->aColumns[$c]['label'])
					{
						if(isset($this->aColumns[$c]['allow_sort']) && $this->aColumns[$c]['allow_sort'])
						{
							if(strcmp($_SESSION['core']['listbuilder'][$this->sName]['sort']['column'], $this->aColumns[$c]['name']) === 0)
							{
								if(strcasecmp($_SESSION['core']['listbuilder'][$this->sName]['sort']['order'], 'desc') === 0)
								{
									$html .= '<td align="left" valign="top"' . (empty($this->aColumns[$c]['width']) ? '' : ' width="' . $this->aColumns[$c]['width'] . '"') . '><table border="0" cellpadding="0" cellspacing="0"><tr><td align="left" valign="top"><div class="label"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=asc') . '">' . clsText::escapeHtml($this->aColumns[$c]['label']) . '</a></div></td><td align="right" valign="top"><div class="sort-icon"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=asc') . '"><img height="6" width="18" alt="desc" border="0" src="' . clsText::escapeHtml($this->sBaseUrl . '/images/sort_desc.gif') . '"></a></div></td></tr></table></td>';
								}
								else
								{
									$html .= '<td align="left" valign="top"' . (empty($this->aColumns[$c]['width']) ? '' : ' width="' . $this->aColumns[$c]['width'] . '"') . '><table border="0" cellpadding="0" cellspacing="0"><tr><td align="left" valign="top"><div class="label"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=desc') . '">' . clsText::escapeHtml($this->aColumns[$c]['label']) . '</a></div></td><td align="right" valign="top"><div class="sort-icon"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=desc') . '"><img height="6" width="18" alt="asc" border="0" src="' . clsText::escapeHtml($this->sBaseUrl . '/images/sort_asc.gif') . '"></a></div></td></tr></table></td>';
								}
							}
							elseif(isset($this->aColumns[$c]['default_sort_order']) && (strcasecmp($this->aColumns[$c]['default_sort_order'], 'desc') === 0))
							{
								$html .= '<td align="left" valign="top"' . (empty($this->aColumns[$c]['width']) ? '' : ' width="' . $this->aColumns[$c]['width'] . '"') . '><table border="0" cellpadding="0" cellspacing="0"><tr><td align="left" valign="top"><div class="label"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=desc') . '">' . clsText::escapeHtml($this->aColumns[$c]['label']) . '</a></div></td><td align="right" valign="top"><div class="sort-icon"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=asc') . '"><img height="6" width="18" alt="none" border="0" src="' . clsText::escapeHtml($this->sBaseUrl . '/images/sort_none.gif') . '"></a></div></td></tr></table></td>';
							}
							else
							{
								$html .= '<td align="left" valign="top"' . (empty($this->aColumns[$c]['width']) ? '' : ' width="' . $this->aColumns[$c]['width'] . '"') . '><table border="0" cellpadding="0" cellspacing="0"><tr><td align="left" valign="top"><div class="label"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=asc') . '">' . clsText::escapeHtml($this->aColumns[$c]['label']) . '</a></div></td><td align="right" valign="top"><div class="sort-icon"><a href="' . clsText::escapeHtml($sSortUrl . 'listbuilder[sort][column]=' . $this->aColumns[$c]['name'] . '&listbuilder[sort][order]=asc') . '"><img height="6" width="18" alt="none" border="0" src="' . clsText::escapeHtml($this->sBaseUrl . '/images/sort_none.gif') . '"></a></div></td></tr></table></td>';
							}
						}
						else
						{
							$html .= '<td align="left" valign="top"' . (empty($this->aColumns[$c]['width']) ? '' : ' width="' . $this->aColumns[$c]['width'] . '"') . '><div class="label">' . clsText::escapeHtml($this->aColumns[$c]['label']) . '</div></td>';
						}
					}
					else
					{
						$html .= '<td align="left" valign="top"' . (empty($this->aColumns[$c]['width']) ? '' : ' width="' . $this->aColumns[$c]['width'] . '"') . '><div class="label">&nbsp;</div></td>';
					}
				}
			}

			$html .= '</tr>';

			if(sizeof($rsData))
			{
				for($i = 0; $i < sizeof($rsData); $i++)
				{
					$html .= '
<tr class="' . (($i % 2) ? 'odd' : 'even') . '">';

					$html .= '<td align="left" valign="top" width="30"><div class="icon"><img height="16" width="16" alt="Status" border="0" src="' . clsText::escapeHtml($this->sBaseUrl . '/images/enabled.' . (isset($rsData[$i]['enabled']) ? $rsData[$i]['enabled'] : '1') . '.png') . '" title="Status"></div></td>';


					// Display columns
					for($c = 0; $c < sizeof($this->aColumns); $c++)
					{
						if((isset($this->aColumns[$c]['print_function']) == false) || ($this->aColumns[$c]['print_function'] === false))
						{
							// Ignore column
						}
						else
						{
							$html .= '<td align="left" valign="top"' . (empty($this->aColumns[$c]['width']) ? '' : ' width="' . $this->aColumns[$c]['width'] . '"') . '><div class="' . (empty($this->aColumns[$c]['css_class']) ? 'text' : $this->aColumns[$c]['css_class']) . '"' . (empty($this->aColumns[$c]['css_style']) ? '' : ' style="' . $this->aColumns[$c]['css_style'] . '"') . '>';

							if(empty($this->aColumns[$c]['name']))
							{
								$html .= call_user_func($this->aColumns[$c]['print_function'], '', $this->aColumns[$c], $rsData[$i]);
							}
							elseif(isset($rsData[$i][$this->aColumns[$c]['name']]))
							{
								$html .= call_user_func($this->aColumns[$c]['print_function'], $rsData[$i][$this->aColumns[$c]['name']], $this->aColumns[$c], $rsData[$i]);
							}
							else
							{
								$html .= '&nbsp;';
							}

							$html .= '</div></td>';
						}
					}

					$html .= '</tr>';
				}
			}
			else
			{
				$html .= '
<tr class="even"><td align="left" colspan="' . $iColumnCount . '" valign="top"><div class="text">Geen items beschikbaar.</div></td></tr>';
			}

			$html .= '
</table>
' . ($sPagination ? '<div class="pagination">' . $sPagination . '</div>' : '') . '';

			return $html;
		}






		public function getCustomSearchField($sFormName, $sFieldLabel, $sFieldInput, $sSubmitLabel = '&raquo;', $sHint = false)
		{
			$sHtml = '
<form action="' . clsText::escapeHtml(SELF_URL) . '" method="post">
' . clsFormBuilder::createFormField('form', 'hidden', $sFormName) . '
	<table border="0" cellpadding="0" cellspacing="0" class="form">
		<tr>
			<td align="left" valign="top" width="' . $this->iSearchLabelWidth . '"><div class="label">' . $sFieldLabel . ':</div></td>
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="left" valign="top"><div class="input">' . $sFieldInput . '</td>';

			if($sSubmitLabel !== false)
			{
				$sHtml .= '
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="left" valign="top"><div class="input"><input class="button" type="submit" value="' . $sSubmitLabel . '"></td>';
			}

			if($sHint !== false)
			{
				$sHtml .= '
			<td align="left" valign="top"><div class="label">&nbsp;</td>
			<td align="right" valign="top"><div class="icon"><img height="16" width="16" alt="Hint" border="0" class="hint" src="' . clsText::escapeHtml($this->sBaseUrl . '/images/hint.png') . '" onclick="javascript: alert(\'' . clsText::escapeJavascript($sHint) . '\');"></td>';
			}

			$sHtml .= '
		</tr>
	</table>
</form>';

			return $sHtml;
		}

		public static function getFilterHtml($sFormName, $aFormFields = array(), $aData = false)
		{
			$oForm = new clsFormBuilder($sFormName, $aFormFields, $aData);
			$oForm->sButtonLabel = clsLanguage::getTranslation('listbuilder', 'Search');
			$oForm->setPostProtection(false);
			$oForm->initFields();

			// Process post data (if any)
			$oForm->postFields();

			return $oForm->formToHtml();
		}

		public static function getFilterData($sFormName, $aFormFields = array(), $bCheckPost = true)
		{
			$oForm = new clsFormBuilder($sFormName, $aFormFields);
			$oForm->sButtonLabel = clsLanguage::getTranslation('listbuilder', 'Search');
			$oForm->setPostProtection(false);
			$oForm->initFields();

			if(($bCheckPost === false) || $oForm->postFields())
			{
				return $oForm->getData();
			}

			return false;
		}
	}



	/*
		LISTBUILDER FORMATTING FUNCTIONS
	*/

	// Treat value as country code (ISO-2)
	function lb_formatCountry($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif($rsCountry = clsBackend::getCountry($sValue))
		{
			return clsText::escapeHtml($rsCountry['name']);
		}

		return strtoupper(substr($sValue, 0, 2));
	}

	// Treat value as CSV
	function lb_formatCsv($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : nl2br(clsText::escapeHtml($sValue)));
	}

	// Treat value as CSVSTRING
	function lb_formatCsvstring($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : clsText::escapeHtml(clsCsvstring::toText($sValue)));
	}

	// Treat value as DATE
	function lb_formatDate($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : date('d-m-Y', clsInt::toDate($sValue)));
	}

	// Treat value as DATETIME
	function lb_formatDateTime($sValue, $aColumn, $aRecord)
	{
		return (empty($sValue) ? '&nbsp;' : date('d-m-Y, H:i:s', $sValue));
	}

	// Treat value as EMAIL
	function lb_formatEmail($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : '<a href="mailto:' . clsText::escapeHtml($sValue) . '" title="Stuur een email naar: ' . clsText::escapeHtml($sValue) . '">' . clsText::escapeHtml($sValue) . '</a>');
	}

	// Treat value as LINK-TO-FILE
	function lb_formatFile($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : '<a href="' . clsText::escapeHtml($sValue) . '"' . (in_array(clsFile::getExtension($sValue), array('gif', 'jpg', 'png')) ? ' target="lightbox"' : ' target="_blank"') . '>' . clsText::escapeHtml(clsFile::getName($sValue)) . '</a>');
	}

	// Treat value as FLOAT
	function lb_formatFloat($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : clsFloat::toText($sValue, ((isset($aColumn['decimal_limit']) && ($aColumn['decimal_limit'] !== false)) ? $aColumn['decimal_limit'] : false)));
	}

	// Treat value as GROUP_ID
	function lb_formatGroup($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif($rsGroup = clsBackend::getGroup($sValue))
		{
			return '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=group-default&core[id]=' . $rsGroup['id']) . '" title="Groep beheren">' . clsText::escapeHtml($rsGroup['name']) . '</a>';
		}
		else
		{
			return '#' . $sValue . ' (Groep niet beschikbaar)';
		}
	}

	// Treat value as GROUP_ID
	function lb_formatGroups($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		else
		{
			$aGroupIds = clsCsvstring::toArray($sValue);
			$sHtml = '';

			foreach($aGroupIds as $iGroupId)
			{
				if($rsGroup = clsBackend::getGroup($iGroupId))
				{
					$sHtml .= ($sHtml ? ', ' : '') . '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=group-default&core[id]=' . $rsGroup['id']) . '" title="Groep beheren">' . clsText::escapeHtml($rsGroup['name']) . '</a>';
				}
			}

			return $sHtml;
		}
	}

	// Treat value as LIST
	function lb_formatList($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : clsText::escapeHtml(str_replace('', '; ', $sValue)));
	}

	// Treat value as MEMBER_ID
	function lb_formatMember($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif($rsMember = clsBackend::getMember($sValue))
		{
			return '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=member-default&core[id]=' . $rsMember['id']) . '" title="Lid beheren">' . clsText::escapeHtml($rsMember['name']) . ' - ' . clsText::escapeHtml($rsMember['email']) . '</a>';
		}
		else
		{
			return '#' . $sValue . ' (Lid niet beschikbaar)';
		}
	}

	// Treat value as MEMBER_ID
	function lb_formatMembers($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		else
		{
			$aMemberIds = clsCsvstring::toArray($sValue);
			$sHtml = '';

			foreach($aMemberIds as $iMemberId)
			{
				if($rsMember = clsBackend::getMember($iMemberId))
				{
					$sHtml .= ($sHtml ? ', ' : '') . '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=member-default&core[id]=' . $rsMember['id']) . '" title="Lid beheren">' . clsText::escapeHtml($rsMember['name']) . ' - ' . clsText::escapeHtml($rsMember['email']) . '</a>';
				}
			}

			return $sHtml;
		}
	}

	// Treat value as MEMBER_ID
	function lb_formatMemberName($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif($rsMember = clsBackend::getMember($sValue))
		{
			return '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=member-default&core[id]=' . $rsMember['id']) . '" title="Lid beheren">' . clsText::escapeHtml($rsMember['name']) . '</a>';
		}
		else
		{
			return '#' . $sValue . ' (Lid niet beschikbaar)';
		}
	}

	// Treat value as MEMBER_ID
	function lb_formatMemberEmail($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif($rsMember = clsBackend::getMember($sValue))
		{
			return '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=member-default&core[id]=' . $rsMember['id']) . '" title="Lid beheren">' . clsText::escapeHtml($rsMember['email']) . '</a>';
		}
		else
		{
			return '#' . $sValue . ' (Lid niet beschikbaar)';
		}
	}

	// Treat value as MODERATOR_ID
	function lb_formatModerator($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif($rsModerator = clsBackend::getModerator($sValue))
		{
			return '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=moderator-default&core[id]=' . $rsModerator['id']) . '" title="Beheerder beheren">' . clsText::escapeHtml($rsModerator['name']) . '</a>';
		}
		else
		{
			return '#' . $sValue . ' (Beheerder niet beschikbaar)';
		}
	}

	// Treat value as PAGE_ID
	function lb_formatPage($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif($rsPage = clsBackend::getPage($sValue))
		{
			return '<a href="' . clsText::escapeHtml(BACKEND_URL . '/index.php?core[view]=website-page-info&core[id]=' . $rsPage['id']) . '" title="Pagina beheren">' . clsText::escapeHtml($rsPage['menu_label']) . '</a>';
		}
		else
		{
			return '#' . $sValue . ' (Pagina niet beschikbaar)';
		}
	}

	// Format special syntax (containing {keys}) to text
	function lb_formatSyntax($sValue, $aColumn, $aRecord)
	{
		$sSyntax = '';

		if(isset($aColumn['print_syntax']) && is_string($aColumn['print_syntax']) && strlen($aColumn['print_syntax']))
		{
			$sSyntax = $aColumn['print_syntax'];

			foreach($aRecord as $k => $v)
			{
				$sSyntax = str_replace('{' . $k . '}', $v, $sSyntax);
			}

			return $sSyntax;
		}
		else
		{
			return '&nbsp';
		}
	}

	// Treat value as TEXT
	function lb_formatText($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif(empty($aColumn['string_limit']))
		{
			return $sValue;
		}
		else
		{
			return clsText::limit($sValue, $aColumn['string_limit']);
		}
	}

	// Treat value as TIME
	function lb_formatTime($sValue, $aColumn, $aRecord)
	{
		return (is_empty($sValue) ? '&nbsp;' : date('H:i:s', clsInt::toTime($sValue)));
	}

	// Treat value as select
	function lb_formatTranslate($sValue, $aColumn, $aRecord)
	{
		if(isset($aColumn['values']))
		{
			if(isset($aColumn['values'][$sValue]))
			{
				if(is_empty($aColumn['values'][$sValue]))
				{
					return '&nbsp;';
				}
				else
				{
					return clsText::escapeHtml($aColumn['values'][$sValue]);
				}
			}
		}

		return clsText::escapeHtml($sValue);
	}

	// Treat value as select
	function lb_formatSelect($sValue, $aColumn, $aRecord)
	{
		if(isset($aColumn['field_values']) && is_array($aColumn['field_values']))
		{
			foreach($aColumn['field_values'] as $k => $v)
			{
				if(isset($v['label']) && isset($v['value']) && strcasecmp($v['value'], $sValue) === 0)
				{
					return clsText::escapeHtml($v['label']);
				}
			}
		}
		elseif(isset($aColumn['values']) && is_array($aColumn['values']))
		{
			foreach($aColumn['values'] as $k => $v)
			{
				if(isset($v['label']) && isset($v['value']) && strcasecmp($v['value'], $sValue) === 0)
				{
					return clsText::escapeHtml($v['label']);
				}
			}
		}

		return clsText::escapeHtml($sValue);
	}

	// Treat value as URL
	function lb_formatUrl($sValue, $aColumn, $aRecord)
	{
		if(is_empty($sValue))
		{
			return '&nbsp;';
		}
		elseif(empty($aColumn['string_limit']))
		{
			return '<a href="' . clsText::escapeHtml($sValue) . '" target="_blank" title="Start deze link in een nieuw venster.">' . clsText::escapeHtml($sValue) . '</a>';
		}
		else
		{
			return '<a href="' . clsText::escapeHtml($sValue) . '" target="_blank" title="Start deze link in een nieuw venster.">' . clsText::escapeHtml(clsText::limit($sValue, $aColumn['string_limit'])) . '</a>';
		}
	}

?>