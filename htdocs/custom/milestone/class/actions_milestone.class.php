<?php
/* Copyright (C) 2010-2018 Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2014-2018 Philippe Grand <philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *	\file       htdocs/milestone/class/actions_milestone.class.php
 *	\ingroup    milestone
 *	\brief      Fichier de la classe des jalons
 */

dol_include_once('/milestone/class/dao_milestone.class.php');

/**
 *	\class      ActionsMilestone
 *	\brief      Classe permettant la gestion des jalons
 */
class ActionsMilestone
{
	/**
     * @var DoliDb Database handler
     */
    public $db;

	/**
	 * @var string 		Error string
     * @deprecated		Use instead the array of error strings
     * @see             errors
	 */
	public $error = '';

	/**
	 * @var string[] Array of error strings
	 */
	public $errors= array();

	/**
     * @var string instance of class
     */
    public $dao;

	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'milestone';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'milestone';

	/**
	 * @var Id of module
	 */
	public $module_number=1790;

	/**
	 * @var
	 */
	public $id;

	/**
	 * @var
	 */
	public $label;

	/**
	 * @var
	 */
	public $description;

	/**
	 * @var
	 */
	public $priority;

	/**
	 * @var
	 */
	public $object;

	/**
	 * @var
	 */
	public $objParent;

	/**
	 * @var
	 */
	public $elementid;

	/**
	 * @var
	 */
	public $elementtype;

	/**
	 * @var
	 */
	public $rang;

	/**
	 * @var
	 */
	public $rangtouse;

	/**
	 * @var
	 */
	public $datec;
	/**
	 * @var
	 */
	public $dateo;

	/**
	 * @var
	 */
	public $datee;

	/**
	 * @var
	 */
	public $tpl=array();

	/**
	 * @var Tableau en memoire des jalons
	 */
	public $lines=array();

	/**
	 * @var	string String displayed by executeHook() immediately after return
	 */
	public $resprints = '';

	/**
	 * @var	array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results=array();


	/**
	 *	Constructor
	 *
	 *	@param	DoliDB	$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 *
	 */
	public function getInstanceDao()
	{
		if (! is_object($this->dao))
		{
			$this->dao=new DaoMilestone($this->db);
		}

		return $this->dao;
	}

	/**
	 * Return action of hook
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters=false, &$object, &$action='')
	{
		global $conf, $user, $langs;

		$dao = $this->getInstanceDao();

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$element = $object->element;

		/*
		 * 	Add milestone
		 */
		if (GETPOST('addmilestone') && $action == 'addline' && $user->rights->milestone->creer && $user->rights->$element->creer)
		{
			$error=0;

			if (! GETPOST('milestone_label','alpha') || GETPOST('milestone_label','alpha') == $langs->transnoentities('Label'))
			{
				$langs->load('milestone@milestone');
				$this->errors[] = $langs->trans("ErrorMilestoneFieldRequired",$langs->transnoentities("Label"));
				$error++;
			}

			if (! $error)
			{
				// Clean parameters
				$label			= trim(GETPOST('milestone_label','alpha'));
				$description	= trim(GETPOST('milestone_desc'));
				$product_type	= GETPOST('product_type','int');
				$special_code	= GETPOST('special_code','int');
				$pagebreak		= (GETPOST('pagebreak','int') ? GETPOST('pagebreak','int') : 0);

				$linemax = $object->line_max();
				$rangtouse = $linemax+1;

				if ($element == 'propal') $fields = array($description,0,0,0,0,0,0,0,"HT",0,0,$product_type,$rangtouse,$special_code,0,0,0,$label);
				if ($element == 'commande') $fields = array($description,0,0,0,0,0,0,0,0,0,'HT',0,null,null,$product_type,$rangtouse,$special_code,0,null,0,$label);
				if ($element == 'facture') $fields = array($description,0,0,0,0,0,0,0,null,null,0,0,0,'HT',0,$product_type,$rangtouse,$special_code,'',0,0,null,0,$label);

				$result = $object->addline($fields[0],$fields[1],$fields[2],$fields[3],$fields[4],$fields[5],$fields[6],$fields[7],$fields[8],$fields[9],$fields[10],$fields[11],$fields[12],$fields[13],$fields[14],$fields[15],$fields[16],$fields[17],$fields[18],$fields[19],$fields[20],$fields[21],$fields[22],$fields[23]);

				if ($result < 0)
				{
					$this->errors[] = $object->error;
				}
				else
				{
					// Set object extraparams
					if (!is_array($object->extraparams)) $object->extraparams=array();
					if (!isset($object->extraparams['milestone'])) $object->extraparams['milestone'] = array();
					$object->extraparams['milestone'][$result]['pagebreak'] = $pagebreak;
					$ret = $object->setValueFrom('extraparams', json_encode($object->extraparams), $object->table_element, $object->id);

					if ($object->element != 'facture') Header ('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
					else Header ('Location: '.$_SERVER["PHP_SELF"].'?facid='.$object->id);

					exit;
				}
			}
		}

		/*
		 * 	Update Milestone
		 */
		else if ($action == 'updatemilestone' && $user->rights->milestone->creer && $user->rights->$element->creer && $_POST["save"] == $langs->trans("Save"))
		{
			// Clean parameters
			$id				= GETPOST('lineid','int');
			$label 			= trim(GETPOST('label','alpha'));
			$description	= trim(GETPOST('description'));
			$pagebreak		= (GETPOST('pagebreak','int') ? GETPOST('pagebreak','int') : 0);

			// Set objectline label
			$ret = $object->setValueFrom('label', $label, $object->table_element_line, $id, '', '', 'none');
			if ($ret > 0)
			{
				// Set objectline description
				$ret = $object->setValueFrom('description', $description, $object->table_element_line, $id, '', '', 'none');
				if ($ret > 0)
				{
					// Set object extraparams
					if (!is_array($object->extraparams)) $object->extraparams=array();
					if (!isset($object->extraparams['milestone'])) $object->extraparams['milestone'] = array();
					$object->extraparams['milestone'][$id]['pagebreak'] = $pagebreak;
					$ret = $object->setValueFrom('extraparams', json_encode($object->extraparams), $object->table_element, $object->id);
				}
			}
		}

		// Remove line
		else if ($action == 'confirm_deletemilestone' && GETPOST('confirm') == 'yes' && $user->rights->milestone->creer && $user->rights->$element->creer)
		{
			$error=0;
			$lineid = GETPOST('lineid', 'int');
			$delete_method = GETPOST('delete_method', 'int');
			$select_milestone = GETPOST('select_milestone', 'int');
			$new_milestone = GETPOST('new_milestone', 'alpha');

			if (isset($_GET['delete_method']))
			{
				if (!empty($delete_method))
				{
					$this->db->begin();

					foreach($object->lines as $line)
					{
						if ($line->fk_parent_line == $lineid)
						{
							$line->rowid = (!empty($line->rowid)?$line->rowid:$line->id);

							// delete all
							if ($delete_method == 1)
							{
								if ($element == 'commande') {
									$ret = $object->deleteline($user, $line->rowid);
								} else {
									$ret = $object->deleteline($line->rowid);
								}

								if ($ret < 0) $error++;
							}
							// remove product lines from milestone
							else if ($delete_method == 2)
							{
								$ret = $object->setValueFrom('fk_parent_line', null, $object->table_element_line, $line->rowid, '', '', 'none');
								if ($ret < 0) $error++;
							}
							// move products line to a new milestone
							else if ($delete_method == 3)
							{
								if (isset($_GET['select_milestone']))
								{
									if ($select_milestone > 0)
									{
										$ret = $object->setValueFrom('fk_parent_line', $select_milestone, $object->table_element_line, $line->rowid, '', '', 'none');
										if ($ret < 0) $error++;

										if (! $error)
										{
											// change with new fk_parent_line
											$line->fk_parent_line = $select_milestone;

											// Call trigger
											include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
											$interface=new Interfaces($this->db);
											$result=$interface->run_triggers('MILESTONE_MIGRATE_CHILD',$line,$user,$langs,$conf);
											if ($result < 0) $error++;
											// End call triggers
										}
									}
									else
									{
										$error++;
										setEventMessage($langs->trans("ErrorMilestoneSelectAnotherMilestone"), 'errors');
									}
								}
								else if (isset($_GET['new_milestone']))
								{
									if (!empty($new_milestone))
									{
										$error++;
										setEventMessage($langs->trans("FeatureNotYetAvailable"), 'errors');
									}
									else
									{
										$error++;
										setEventMessage($langs->trans("ErrorMilestoneNewMilestone"), 'errors');
									}
								}
							}
						}

						if (!empty($error)) break;
					}

					if (!$error)
					{
						if ($element == 'commande') {
							$ret = $object->deleteline($user, $lineid);
						} else {
							$ret = $object->deleteline($lineid);
						}
						if ($ret < 0) $error++;
					}

					if (!$error)
					{
						$this->db->commit();

						// reorder lines
						$object->line_order(true);

						setEventMessage($langs->trans("MilestoneDeleted"));
					}
					else
					{
						$this->db->rollback();
						setEventMessage($langs->trans("ErrorMilestoneNotDeleted"), 'errors');
					}
				}
				else
					setEventMessage($langs->trans("ErrorMilestoneDeleteSelectMethod"), 'errors');
			}
			else
			{
				if ($element == 'commande') {
					$ret = $object->deleteline($user, $lineid);
				} else {
					$ret = $object->deleteline($lineid);
				}
				if ($ret < 0) $error++;

				if (!$error)
				{
					$this->db->commit();

					// reorder lines
					$object->line_order(true);

					setEventMessage($langs->trans("MilestoneDeleted"));
				}
				else
				{
					$this->db->rollback();
					setEventMessage($langs->trans("ErrorMilestoneNotDeleted"), 'errors');
				}
			}

			if ($object->element != 'facture') Header ('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
			else Header ('Location: '.$_SERVER["PHP_SELF"].'?facid='.$object->id);

			exit;
		}

		// Builddoc options
		else if ($action == 'builddoc' && $user->rights->$element->creer)
		{
			$tag = GETPOST('modulepart') . '_' . (GETPOST('facid') ? GETPOST('facid') : GETPOST('id'));

			if (GETPOST('hidedetails'))
			{
				$_SESSION['milestone_hidedetails_' . $tag] = true;
			}
			else
			{
				$_SESSION['milestone_hidedetails_' . $tag] = false;
			}

			if (GETPOST('hidedesc'))
			{
				$_SESSION['milestone_hidedesc_' . $tag] = true;
			}
			else
			{
				$_SESSION['milestone_hidedesc_' . $tag] = false;
			}

			if (GETPOST('hideamount'))
			{
				$_SESSION['milestone_hideamount_' . $tag] = true;
			}
			else
			{
				$_SESSION['milestone_hideamount_' . $tag] = false;
			}
		}
	}

	/**
	 *
	 */
	function selectMilestoneLines($object,$selected='',$htmlname='fk_parent_line', $exclude=array(), $return_array=false)
	{
		global $langs;

		$langs->load('milestone@milestone');

		$milestones=array();

		foreach($object->lines as $line)
		{
			if ($line->product_type == 9 && $line->special_code == $this->module_number)
			{
				if (is_array($exclude) && !empty($exclude) && in_array($line->rowid, $exclude)) continue;

				$line->rowid = (!empty($line->rowid)?$line->rowid:$line->id);
				$milestones[$line->rowid] = $line->label;
			}
		}

		if (empty($return_array))
		{
			$out = '<select id="select_'.$htmlname.'" class="flat" name="'.$htmlname.'"'.(empty($milestones)?' disabled="disabled"':'').'>';
			if (empty($milestones))
				$out.= '<option value="" selected="selected">'.$langs->trans('NoMilestone').'</option>';
			else
			{
				$out.= '<option value=""></option>';
				foreach($milestones as $key => $value)
				{
					$out.= '<option value="'.$key.'"'.((!empty($selected) && $selected == $key)?' selected="selected"':'').'>'.$value.'</option>';
				}
			}
			$out.= '</select>';
		}
		else
			$out = $milestones;

		return $out;
	}

	/**
	 *
	 */
	function selectObjectLines($object,$htmlname='product_id')
	{
		$out = '<select id="select_'.$htmlname.'" class="flat" name="'.$htmlname.'">';
		$out.= '<option value="-1" selected="selected"></option>';
		foreach($object->lines as $line)
		{
			if ($line->product_type < 3 && empty($line->fk_parent_line))
			{
				$line->rowid = (!empty($line->rowid)?$line->rowid:$line->id);
				$out.= '<option value="'.$line->rowid.'">';
				$out.= (empty($line->ref) ? '' : $line->ref.' - ').$line->product_label;
				$out.= '</option>';
			}
		}
		$out.= '</select>';

		$this->resprints = $out;
		return 0;
	}

	/**
	 *
	 */
	function formCreateProductOptions($parameters=false, &$object, &$action='')
	{
		global $langs;

		$out='';

		$langs->load('milestone@milestone');

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out.= '&nbsp;<span>';
		$out.= $langs->trans('AddTo').' '.$this->selectMilestoneLines($object,$selected);
		$out.= '</span>';

		print $out;
	}

	/**
	 *
	 */
	function formCreateSupplierProductOptions($parameters=false, &$object, &$action='')
	{
		return $this->formCreateProductOptions($parameters, $object, $action);
	}

	/**
	 *
	 */
	function formCreateProductSupplierOptions($parameters=false, &$object, &$action='')
	{
		return $this->formCreateProductOptions($parameters, $object, $action);
	}

	/**
	 *
	 */
	function formEditProductOptions($parameters=false, &$object, &$action='')
	{
		global $langs;

		$out='';

		$langs->load('milestone@milestone');

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out.= '&nbsp;<div>';
		$out.= $langs->trans('MoveTo').' '.$this->selectMilestoneLines($object,$fk_parent_line);
		$out.= '</div>';

		print $out;
	}

	/**
	 * 	Return HTML form for add a milestone
	 */
	function formAddObjectLine($parameters=false)
	{
		global $conf, $langs, $user;
		global $object, $bcnd, $var;

		dol_include_once('/milestone/lib/milestone.lib.php');

		// Check current version
		if (!checkMilestoneVersion())
		{
			dol_htmloutput_mesg($langs->trans("MilestoneUpgradeIsNeeded"),'','error',1);
		}
		else
		{
			if ($user->rights->milestone->creer)
			{
				$langs->load('milestone@milestone');

				if (is_array($parameters) && ! empty($parameters))
				{
					foreach($parameters as $key=>$value)
					{
						$$key=$value;
					}
				}

				dol_include_once('/milestone/tpl/addmilestoneform.tpl.php');
			}
		}
	}

	/**
	 *
	 */
	function formAddSupplierObjectLine($parameters=false)
	{
		$this->formAddObjectLine($parameters);
	}

	/**
	 * 	Return HTML form for builddoc bloc
	 */
	function formBuilddocOptions($parameters=false)
	{
		global $conf, $langs;

		$langs->load('milestone@milestone');

		$out='';

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out.= '<input type="hidden" name="modulepart" value="' . $modulepart . '">';

		$checkedHideDetails = '';
		$checkedHideDesc = '';
		$checkedHideAmount = '';
		$tag = $modulepart . '_' . $id;

		if (isset($_SESSION['milestone_hidedetails_' . $tag]))
		{
			$checkedHideDetails = (!empty($_SESSION['milestone_hidedetails_' . $tag]) ? ' checked="checked"' : '');
		}
		else
		{
			$checkedHideDetails = (!empty($conf->global->MILESTONE_HIDE_PRODUCT_DETAILS) ? ' checked="checked"' : '');
		}

		if (isset($_SESSION['milestone_hidedesc_' . $tag]))
		{
			$checkedHideDesc = (!empty($_SESSION['milestone_hidedesc_' . $tag]) ? ' checked="checked"' : '');
		}
		else
		{
			$checkedHideDesc = (!empty($conf->global->MILESTONE_HIDE_PRODUCT_DESC) ? ' checked="checked"' : '');
		}

		if (isset($_SESSION['milestone_hideamount_' . $tag]))
		{
			$checkedHideAmount = (!empty($_SESSION['milestone_hideamount_' . $tag]) ? ' checked="checked"' : '');
		}
		else
		{
			$checkedHideAmount = (!empty($conf->global->MILESTONE_HIDE_MILESTONE_AMOUNT) ? ' checked="checked"' : '');
		}

		$out.= '<tr class="oddeven">';
		$out.= '<td colspan="4"><input type="checkbox" name="hidedetails" value="1"' . $checkedHideDetails . ' /> '.$langs->trans('HideDetails').'</td>';
		$out.= '</tr>';
		$out.= '<tr class="oddeven">';
		$out.= '<td colspan="4"><input type="checkbox" name="hidedesc" value="1"' . $checkedHideDesc . ' /> '.$langs->trans('HideDescription').'</td>';
		$out.= '</tr>';
		$out.= '<tr class="oddeven">';
		$out.= '<td colspan="4"><input type="checkbox" name="hideamount" value="1"' . $checkedHideAmount . ' /> '.$langs->trans('HideMilestoneAmount').'</td>';
		$out.= '</tr>';

		$this->resprints = $out;
		return 0;
	}

	/**
	 * 	Return HTML with selected milestone
	 * 	@param		object			Parent object
	 * 	TODO mettre le html dans un template
	 */
	function printObjectLine($parameters=false, &$object, &$action='viewline')
	{
		global $conf,$langs,$user,$hookmanager;
		global $form,$bc,$bcnd;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		if ( (isset($line->product_type) && $line->product_type == 9 && ! empty($line->special_code) && $line->special_code == $this->module_number) )
		{
			$lineId = (!empty($line->rowid)?$line->rowid:$line->id);

			$element = $object->element;

			// Ligne en mode visu
			if ($action != 'editline' || $selected != $line->rowid)
			{
				print '<tr id="row-'.$lineId.'" class="oddeven">';

				$colspan=(! empty($conf->global->MAIN_VIEW_LINE_NUMBER) ? 7 : 6);
				if($conf->global->PRODUCT_USE_UNITS) $colspan++;
				$subcolspan=5;
				if($conf->global->MAIN_VIEW_LINE_NUMBER) $subcolspan++;
				if($conf->global->PRODUCT_USE_UNITS) $subcolspan++;
				$usemargins=0;
				if (! empty($conf->margin->enabled) && ! empty($object->element) && in_array($object->element,array('facture','propal', 'askpricesupplier','commande'))) { $usemargins=1; $colspan++; $subcolspan++;}
				if (! empty($usemargins) && ! empty($conf->global->DISPLAY_MARGIN_RATES) && $user->rights->margins->liretous) { $colspan++; $subcolspan++;}
				if (! empty($usemargins) && ! empty($conf->global->DISPLAY_MARK_RATES) && $user->rights->margins->liretous) { $colspan++; $subcolspan++;}
				if ( ! empty($conf->multicurrency->enabled)) { $colspan+=2; $subcolspan+=2;}

				print '<td colspan="' . $colspan . '">';

				print '<a name="'.$lineId.'"></a>'; // ancre pour retourner sur la ligne;

				$text = img_object($langs->trans('Milestone'),'milestone@milestone');
				$text.= ' '.$line->label.(! empty($object->extraparams['milestone'][$lineId]['pagebreak']) ? ' ('.$langs->trans('PageBreak').')' : '').'<br>';
				$description=(!empty($conf->global->PRODUIT_DESC_IN_FORM)?'':dol_htmlentitiesbr($line->description));
				print $form->textwithtooltip($text,$description,3,'','',$i);

				// Show range
				//print_date_range($line->date_start,$line->date_end);

				// Add description in form
				if (!empty($conf->global->PRODUIT_DESC_IN_FORM))
				{
					print ($line->description?'<br>'.dol_htmlentitiesbr($line->description):'');
				}

				print "</td>\n";

				// Icone d'edition et suppression
				if ($object->statut == 0 && $user->rights->$element->creer)
				{
					$colspan='';

					if ($user->rights->milestone->creer)
					{
						print '<td align="center">';
						print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$lineId.'#'.$lineId.'">';
						print img_edit();
						print '</a>';
						print '</td>';
					}
					else
					{
						print '<td>&nbsp;</td>';
					}

					if ($user->rights->milestone->supprimer)
					{
						print '<td align="center">';
						if (1==2 && ! empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))
						{
							print '<span id="action-delete-milestone" lineid="'.$lineId.'" class="linkobject">'.img_delete().'</span>'."\n";
						}
						else
						{
							print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=deletemilestone&amp;lineid='.$lineId.'">'.img_delete().'</a>';
						}
						print '</td>';
					}
					else
					{
						print '<td>&nbsp;</td>';
					}

					if ($num > 1)
					{
						print '<td align="center" class="tdlineupdown">';
						if ($i > 0)
						{
							print '<a class="lineupdown" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=up&amp;rowid='.$lineId.'">';
							print '</a>';
						}
						if ($i < $num-1)
						{
							print '<a class="lineupdown" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=down&amp;rowid='.$lineId.'">';
							print '</a>';
						}
						print '</td>';
					}
				}
				else
				{
					print '<td colspan="3">&nbsp;</td>';
				}

				print '</tr>';

				$subtotal=0;
				foreach($object->lines as $objectline)
				{
					if ($objectline->fk_parent_line == $lineId)
					{
						// Line extrafield
						$objectline->fetch_optionals($objectline->id);
						// Show line
						$object->printObjectLine($action,$objectline,$var,$num,$i,$dateSelector,$seller,$buyer,$selected,$extrafieldsline);
						$subtotal++;
					}
				}

				if ($subtotal)
				{
					print '<tr>';
					print '<td align="right" colspan="'.$subcolspan.'"><b>'.$langs->trans("SubTotal").' :</b></td>';
					print '<td align="right" nowrap="nowrap"><b>'.price($line->total_ht).'</b></td>';
					print '<td colspan="3">&nbsp;</td>';
					print '</tr>';
				}
			}

			// Ligne en mode update
			if ($object->statut == 0 && $action == 'editline' && $user->rights->$element->creer && $selected == $lineId)
			{
				print '<form action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'#'.$lineId.'" method="POST">';
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				print '<input type="hidden" name="action" value="updatemilestone">';
				print '<input type="hidden" name="id" value="'.$object->id.'">';
				print '<input type="hidden" name="lineid" value="'.$lineId.'">';
				print '<input type="hidden" name="special_code" value="'.$line->special_code.'">';
				print '<input type="hidden" name="product_type" value="'.$line->product_type.'">';

				// Label
				print '<tr class="oddeven">';
				print '<td colspan="5">';
				print '<a name="'.$lineId.'"></a>'; // ancre pour retourner sur la ligne
				print '<input size="30" type="text" id="label" name="label" value="'.$line->label.'"> ';
				$checked=(! empty($object->extraparams['milestone'][$lineId]['pagebreak']) ? ' checked="checked"' : '');
				print '<input type="checkbox" name="pagebreak" value="1"'.$checked.' /> '.$langs->transnoentities('AddPageBreak').'</td>';
				print '<td align="center" colspan="4" rowspan="2" valign="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
				print '<br><input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></td>';
				print '</tr>';

				// Description
				print '<tr class="oddeven">';
				print '<td colspan="5">';

				// Editor wysiwyg
				require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
				$nbrows=ROWS_2;
				if (! empty($conf->global->MAIN_INPUT_DESC_HEIGHT)) $nbrows=$conf->global->MAIN_INPUT_DESC_HEIGHT;
				$doleditor=new DolEditor('description',$line->description,'',100,'dolibarr_details','',false,true,$conf->fckeditor->enabled && $conf->global->FCKEDITOR_ENABLE_DETAILS,$nbrows,70);
				$doleditor->Create();

				print '</td>';
				print '</tr>' . "\n";

				print "</form>\n";
			}

			return 1;
		}
	}

	/**
	 * 	Return HTML with selected child line
	 * 	@param		object			Parent object
	 */
	function printObjectSubLine($parameters=false, &$object, &$action='viewline')
	{
		return 1;
	}

	/**
	 * 	Return HTML with origin selected milestone
	 * 	@param		object			Parent object
	 * 	TODO mettre le html dans un template
	 */
	function printOriginObjectLine($parameters=false, &$object, &$action='')
	{
		global $conf,$langs;
		global $form;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$lineId = (!empty($line->rowid)?$line->rowid:$line->id);

		print '<tr class="oddeven"><td colspan="6">';
		$text = img_object($langs->trans('Milestone'),'milestone@milestone');
		$text.= ' '.$line->label.'<br>';
		$description=($conf->global->PRODUIT_DESC_IN_FORM?'':dol_htmlentitiesbr($line->desc));
		print $form->textwithtooltip($text,$description,3,'','',$i);
		print "</td></tr>\n";

		$subtotal=0;
		foreach($object->lines as $objectline)
		{
			if ($objectline->fk_parent_line == $lineId)
			{
				$object->printOriginLine($objectline,$var);
				$subtotal++;
			}
		}

		if ($subtotal)
		{
			print "\n".'<tr>';
			print '<td align="right" colspan="3">'.$langs->trans("SubTotal").' :</td>';
			print '<td align="right" nowrap="nowrap">'.price($line->total_ht).'</td>';
			print '<td colspan="2">&nbsp;</td>';
			print '</tr>'."\n";
		}
	}

	/**
	 * 	Form confirm
	 *
	 *	@param	array	$parameters		Extra parameters
	 *	@param	object	$object			Object
	 *	@param	string	$action			Type of action
	 *	@return	void
	 */
	function formConfirm($parameters=false, &$object, &$action)
	{
		global $conf, $langs;
		global $form;

		$langs->load('milestone@milestone');

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';
		$childs=0;
		$formquestion='';

		/*
		 * 	Delete milestone confirmation
		 */
		if ($action == 'deletemilestone')
		{
			foreach($object->lines as $line)
			{
				if ($line->fk_parent_line == $lineid) $childs++;
			}

			$height = (empty($childs) ? 170 : 250);
			$width = (empty($childs) ? 500 : 600);

			// Define confirmation messages
			if (!empty($childs))
			{
				$milestones = $this->selectMilestoneLines($object,'','',array($lineid), true); // return an array

				$formquestion=array(
					'text' => $langs->trans("ConfirmDeleteMilestoneOption"),
					array(
						'type' => 'radio',
						'name' => 'delete_method',
						'values' => array(
							1 => $langs->trans("MilestoneDeleteAll"),
							2 => $langs->trans('MilestoneDeleteMoveProductLineOut'),
							3 => (!empty($milestones)?$langs->trans("MilestoneDeleteMoveProductLineInAnotherMilestone"):$langs->trans("FeatureNotYetAvailable")/*$langs->trans("MilestoneDeleteMoveProductLineCreateNewMilestone")*/)
						)
					),
					array(
						'type'		=> (!empty($milestones)?'select':'text'),
						'name'		=> (!empty($milestones)?'select_milestone':'new_milestone'),
						'label'		=> (!empty($milestones)?$langs->trans("MilestoneDeleteSelectAnotherMilestone"):$langs->trans("MilestoneDeleteNameOfNewMilestone")),
						'values'	=> $milestones,
						'value'		=> '',
						'size'		=> 24
					)
				);
			}

			$out=$form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteMilestone'), $langs->trans('ConfirmDeleteMilestone'), 'confirm_deletemilestone',$formquestion,0,1,$height,$width);

			$this->resprints = $out;
			return 1;
		}

		$this->resprints = $out;
		return (empty($out)?0:1);
	}

	/**
	 *	Return line description translated in outputlangs and encoded in UTF8
	 *
	 *	@param		array	$parameters		Extra parameters
	 *	@param		object	$object			Object
	 *	@param    	string	$action			Type of action
	 *	@return		void
	 */
	function pdf_writelinedesc($parameters=false, &$object, &$action='')
	{
		global $conf;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$return = 0;
		
		//Settings for Jalon
		if ($object->lines[$i]->product_type == 9 && $object->lines[$i]->special_code == $this->module_number)
		{
			$backgroundcolor = array('230','230','230');

			if (!empty($conf->global->MILESTONE_BACKGROUND_COLOR))
			{
				require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
				$backgroundcolor = colorStringToArray($conf->global->MILESTONE_BACKGROUND_COLOR);
			}

			$object->lines[$i]->rowid = (!empty($object->lines[$i]->rowid)?$object->lines[$i]->rowid:$object->lines[$i]->id);			
			
			$subject=$object->modelpdf;
			$pattern='/^ultimate_/';

			if ($conf->ultimatepdf->enabled && preg_match($pattern, $subject)==1)
			{
				$linenumber = array(
				'propal' => array(
					'upperconst' => 'PROPOSALS'
				),
				'facture' => array(
					'upperconst' => 'INVOICES'
				),
				'commande' => array(
					'upperconst' => 'ORDERS'
					)
				);
				$upperconst=$linenumber[$object->element]['upperconst'];
				$constname='ULTIMATE_'.$upperconst.'_WITH_LINE_NUMBER';
				if (array_key_exists($object->element, $linenumber) && ! empty($object->id) && ! empty($conf->global->$constname))
				{
					$pdf->SetXY ($posx, $posy-1);
					$pdf->SetFillColor($backgroundcolor[0],$backgroundcolor[1],$backgroundcolor[2]);
					$pdf->MultiCell($pdf->page_largeur-$posx-$pdf->marge_droite, $h+2.5, '', 0, '', 1);

					$posy = $pdf->GetY();
					$pdf->SetFont('', 'BU', 9);
					$pdf->writeHTMLCell($w, $h-2, $posx+2, $posy-$h-2.5, $outputlangs->convToOutputCharset($object->lines[$i]->label), 0, 1);
				}
				else
				{
					$pdf->SetXY ($posx-1, $posy-1);
					$pdf->SetFillColor($backgroundcolor[0],$backgroundcolor[1],$backgroundcolor[2]);
					$pdf->MultiCell($pdf->page_largeur-$posx-$pdf->marge_droite, $h+2.5, '', 0, '', 1);

					$posy = $pdf->GetY();
					$pdf->SetFont('', 'BU', 9);
					$pdf->writeHTMLCell($w, $h-2, $posx, $posy-$h-2.5, $outputlangs->convToOutputCharset($object->lines[$i]->label), 0, 1);
				}
				
			}
			else
			{
				$pdf->SetXY ($posx, $posy-1);
				$pdf->SetFillColor($backgroundcolor[0],$backgroundcolor[1],$backgroundcolor[2]);
				$pdf->MultiCell($pdf->page_largeur-$posx-$pdf->marge_droite, $h+2.5, '', 0, '', 1);

				$posy = $pdf->GetY();
				$pdf->SetFont('', 'BU', 9);
				$pdf->writeHTMLCell($w, $h-2, $posx, $posy-$h-2.5, $outputlangs->convToOutputCharset($object->lines[$i]->label), 0, 1);
			}

			$nexy = $pdf->GetY();

			$pdf->SetFont('', 'I', 9);
			$description = dol_htmlentitiesbr($object->lines[$i]->desc, 1);

			if ($object->lines[$i]->date_start || $object->lines[$i]->date_end)
	        {
	        	// Show duration if exists
	        	if ($object->lines[$i]->date_start && $object->lines[$i]->date_end)
	        	{
	        		$period='('.$outputlangs->transnoentitiesnoconv('DateFromTo',dol_print_date($object->lines[$i]->date_start, $format, false, $outputlangs),dol_print_date($object->lines[$i]->date_end, $format, false, $outputlangs)).')';
	        	}
	        	if ($object->lines[$i]->date_start && ! $object->lines[$i]->date_end)
	        	{
	        		$period='('.$outputlangs->transnoentitiesnoconv('DateFrom',dol_print_date($object->lines[$i]->date_start, $format, false, $outputlangs)).')';
	        	}
	        	if (! $object->lines[$i]->date_start && $object->lines[$i]->date_end)
	        	{
	        		$period='('.$outputlangs->transnoentitiesnoconv('DateUntil',dol_print_date($object->lines[$i]->date_end, $format, false, $outputlangs)).')';
	        	}

	        	$description.="<br>".dol_htmlentitiesbr($period, 1);
	        }
			
			//Jalon description
	        if (! empty($description))
	        {
	        	$pdf->writeHTMLCell($w, $h, $posx, $nexy+1, $outputlangs->convToOutputCharset($description), 0, 1);
	        }

			$pdf->SetFont('', '', 9);

			$return++;
		}
		//Product label and description
		else if (!empty($object->lines[$i]->fk_parent_line) && $this->module_number == $object->getSpecialCode($object->lines[$i]->fk_parent_line))
		{
			if ($conf->global->MILESTONE_HIDE_DISPLAY_PICTO)
			$labelproductservice= img_picto("Linked to Jalon",'rightarrow');
			$labelproductservice.=pdf_getlinedesc($object, $i, $outputlangs, $hideref, $hidedesc, $issupplierline);
			$pdf->writeHTMLCell($w, $h, $posx+1, $posy, $outputlangs->convToOutputCharset($labelproductservice), 0, 1);

			$return++;
		}
		else if (empty($object->lines[$i]->fk_parent_line) && !empty($object->extraparams['milestone'][$object->lines[$i+1]->rowid]['pagebreak']))
		{
			$labelproductservice=pdf_getlinedesc($object, $i, $outputlangs, $hideref, $hidedesc, $issupplierline);
			$pdf->writeHTMLCell($w, $h, $posx+1, $posy, $outputlangs->convToOutputCharset($labelproductservice), 0, 1);
		}

		$linenumber = array(
			'propal' => array(
				'upperconst' => 'PROPOSALS',
				'path' => '/ultimatepdf/core/modules/propale/doc/pdf_ultimate_propal1',
				'classname' => 'pdf_ultimate_propal1'
			),
			'facture' => array(
				'upperconst' => 'INVOICES',
				'path' => '/ultimatepdf/core/modules/facture/doc/pdf_ultimate_invoice1',
				'classname' => 'pdf_ultimate_invoice1'
			),
			'commande' => array(
				'upperconst' => 'ORDERS',
				'path' => '/ultimatepdf/core/modules/commande/doc/pdf_ultimate_order1.modules.php',
				'classname' => 'pdf_ultimate_order1'
				)
			);

		if (array_key_exists($object->element, $linenumber) && $object->lines[$i+1]->product_type == 9 && $object->lines[$i+1]->special_code == $this->module_number && !empty($object->extraparams['milestone'][$object->lines[$i+1]->rowid]['pagebreak']))
		{
			if ($conf->ultimatepdf->enabled && isset($_SESSION['ultimatepdf_model']) && !empty($object->lines[$i]->product_ref))
			{
					$modelpath = $linenumber[$object->element]['path'];
					$classname = $linenumber[$object->element]['classname'];
					dol_include_once($modelpath);
					if (class_exists($classname))
					{
						$objectultimatepdf = new $classname($db);
					}
					$tab_top_newpage = (empty($conf->global->ULTIMATE_.$linenumber[$object->element]['upperconst']._PDF_DONOTREPEAT_HEAD)?$objectultimatepdf->marge_haute+pdf_getUltimateHeightForLogo($logo)+15:10);

				$pdf->SetXY ($posx, $tab_top_newpage);
				$object->lines[$i+1]->pagebreak = true;

				$return++;
			}
			else
			{
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?pdf_getHeightForLogo($logo)+20:10);
				$pdf->SetXY ($posx, $tab_top_newpage);
				$object->lines[$i+1]->pagebreak = true;

				$return++;
			}
		}

		return $return;
	}

	/**
	 *	Return line description translated in outputlangs and encoded in UTF8
	 *
	 *	@param		array	$parameters		Extra parameters
	 *	@param		object	$object			Object
	 *	@param    	string	$action			Type of action
	 *	@return		void
	 */
	function pdf_writelinedesc_ref($parameters=false, &$object, &$action='')
	{
		global $conf;

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$return = 0;

		if ($object->lines[$i]->product_type == 9 && $object->lines[$i]->special_code == $this->module_number)
		{
			$backgroundcolor = array('230','230','230');

			if (!empty($conf->global->MILESTONE_BACKGROUND_COLOR))
			{
				require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
				$backgroundcolor = colorStringToArray($conf->global->MILESTONE_BACKGROUND_COLOR);
			}

			$element = $object->element;

			if ($conf->ultimatepdf->enabled)
			{
				if ($element == 'propal')
				{
					dol_include_once('/ultimatepdf/core/modules/propale/doc/pdf_ultimate_propal1.modules.php');
					$objectultimatepdf = new pdf_ultimate_propal1($db);
				}
				if ($element == 'commande')
				{
					dol_include_once('/ultimatepdf/core/modules/commande/doc/pdf_ultimate_order1.modules.php');
					$objectultimatepdf = new pdf_ultimate_order1($db);
				}
				if ($element == 'facture')
				{
					dol_include_once('/ultimatepdf/core/modules/facture/doc/pdf_ultimate_invoice1.modules.php');
					$objectultimatepdf = new pdf_ultimate_invoice1($db);
				}

				$object->lines[$i]->rowid = (!empty($object->lines[$i]->rowid)?$object->lines[$i]->rowid:$object->lines[$i]->id);
				if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes")
				{
					$objectultimatepdf->posxref=$objectultimatepdf->marge_gauche+$objectultimatepdf->number_width;
					$objectultimatepdf->posxdesc=$objectultimatepdf->posxref + (isset($conf->global->ULTIMATE_DOCUMENTS_WITH_REF_WIDTH)?$conf->global->ULTIMATE_DOCUMENTS_WITH_REF_WIDTH:22);
					$posx=$objectultimatepdf->posxdesc;
					$posy = $pdf->GetY();
					$pdf->SetXY ($posx, $posy-1);
					$pdf->SetFont('', 'BU', 9);
					$pdf->writeHTMLCell($w, $h-2, $posx+2, $posy, $outputlangs->convToOutputCharset($object->lines[$i]->label), 0, 1);
				}
				else
				{
					
					$objectultimatepdf->posxdesc=$objectultimatepdf->marge_gauche+$objectultimatepdf->number_width;
					$posx=$objectultimatepdf->posxdesc;
					$posy = $pdf->GetY();
					$pdf->SetXY ($posx, $posy-1);
					$pdf->SetFont('', 'BU', 9);
					$pdf->writeHTMLCell($w, $h-2, $posx+2, $posy, $outputlangs->convToOutputCharset($object->lines[$i]->label), 0, 1);
				}

				$pdf->SetXY ($posx-1, $posy-1);
				$pdf->SetFillColor($backgroundcolor[0],$backgroundcolor[1],$backgroundcolor[2]);
				$pdf->MultiCell($objectultimatepdf->page_largeur-$objectultimatepdf->marge_droite+1-$posx, $h+2.5, '', 0, '', 1);
			}

			$nexy = $pdf->GetY();

			$pdf->SetFont('', 'I', 9);
			$description = dol_htmlentitiesbr($object->lines[$i]->desc, 1);

			if ($object->lines[$i]->date_start || $object->lines[$i]->date_end)
	        {
	        	// Show duration if exists
	        	if ($object->lines[$i]->date_start && $object->lines[$i]->date_end)
	        	{
	        		$period='('.$outputlangs->transnoentitiesnoconv('DateFromTo',dol_print_date($object->lines[$i]->date_start, $format, false, $outputlangs),dol_print_date($object->lines[$i]->date_end, $format, false, $outputlangs)).')';
	        	}
	        	if ($object->lines[$i]->date_start && ! $object->lines[$i]->date_end)
	        	{
	        		$period='('.$outputlangs->transnoentitiesnoconv('DateFrom',dol_print_date($object->lines[$i]->date_start, $format, false, $outputlangs)).')';
	        	}
	        	if (! $object->lines[$i]->date_start && $object->lines[$i]->date_end)
	        	{
	        		$period='('.$outputlangs->transnoentitiesnoconv('DateUntil',dol_print_date($object->lines[$i]->date_end, $format, false, $outputlangs)).')';
	        	}

	        	$description.="<br>".dol_htmlentitiesbr($period, 1);
	        }

	        if (! empty($description))
	        {
	        	$pdf->writeHTMLCell($w, $h, $posx, $nexy+1, $outputlangs->convToOutputCharset($description), 0, 1);
	        }

			$pdf->SetFont('', '', 9);

			$return++;
		}
		else if (!empty($object->lines[$i]->fk_parent_line) && $this->module_number == $object->getSpecialCode($object->lines[$i]->fk_parent_line))
		{
			if ($conf->global->MILESTONE_HIDE_DISPLAY_PICTO)
			$labelproductservice= img_picto("Linked to Jalon",'rightarrow');
			$labelproductservice.=pdf_getlinedesc_ref($object, $i, $outputlangs, $hideref, $hidedesc, $issupplierline,$type);
			// Description
			if ($type=='ref')
			{
				$pdf->writeHTMLCell($w, $h, $posx, $posy, $outputlangs->convToOutputCharset($labelproductservice), 0, 1, false, true, 'J',true);
			}
			elseif ($type=='label')
			{
				$pdf->writeHTMLCell($w, $h, $posx, $posy, $outputlangs->convToOutputCharset($labelproductservice), 0, 1, false, true, 'J',true);
			}

			$return++;
		}

		return $return;
	}

	/**
	 * 	Return line total excluding tax
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlinetotalexcltax($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$tag = GETPOST('modulepart') . '_' . (GETPOST('facid') ? GETPOST('facid') : GETPOST('id'));

		$out='';

		if ( $object->lines[$i]->product_type == 9 && $object->lines[$i]->special_code == $this->module_number && $object->lines[$i]->total_ht != 0)
		{
			if ($_SESSION['milestone_hideamount_' . $tag] == false)
				$out = price($object->lines[$i]->total_ht);
		}
		else if ( $object->lines[$i]->product_type != 9 && (empty($hidedetails) || $hidedetails > 1) )
		{
			$out = price($object->lines[$i]->total_ht);
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * 	Return line total including tax
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlinetotalwithtax($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$tag = GETPOST('modulepart') . '_' . (GETPOST('facid') ? GETPOST('facid') : GETPOST('id'));

		$out='';

		if ( $object->lines[$i]->product_type == 9 && $object->lines[$i]->special_code == $this->module_number && $object->lines[$i]->total_ttc != 0)
		{
			if ($_SESSION['milestone_hideamount_' . $tag] == false)
				$out = price($object->lines[$i]->total_ttc);
		}
		else if ( $object->lines[$i]->product_type != 9 && (empty($hidedetails) || $hidedetails > 1) )
		{
			$out = price($object->lines[$i]->total_ttc);
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * 	Return line vat rate
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlinevatrate($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';

		if ((empty($hidedetails) || $hidedetails > 1) && $object->lines[$i]->product_type != 9 && (empty($object->lines[$i]->special_code) || $object->lines[$i]->special_code == 3))
		{
			$out = vatrate($object->lines[$i]->tva_tx,1,$object->lines[$i]->info_bits);
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * 	Return line unit price excluding tax
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlineupexcltax($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';

		if ((empty($hidedetails) || $hidedetails > 1) && $object->lines[$i]->product_type != 9 && (empty($object->lines[$i]->special_code) || $object->lines[$i]->special_code == 3))
		{
			$out = price($object->lines[$i]->subprice);
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * 	Return line quantity
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlineqty($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';

		if ((empty($hidedetails) || $hidedetails > 1) && $object->lines[$i]->product_type != 9 && empty($object->lines[$i]->special_code))
		{
			$out = $object->lines[$i]->qty;
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * 	Return line weight
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlineweight($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';

		if ((empty($hidedetails) || $hidedetails > 1) && $object->lines[$i]->product_type != 9 && empty($object->lines[$i]->special_code))
		{
			$out = $object->lines[$i]->weight;
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * 	Return line unit
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlineunit($parameters=false, $object, $action='')
	{
		global $langs;
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';

		if ((empty($hidedetails) || $hidedetails > 1) && $object->lines[$i]->product_type != 9 && empty($object->lines[$i]->special_code))
		{
			$out = $langs->transnoentitiesnoconv($object->lines[$i]->getLabelOfUnit('short'));
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * 	Return line remise percent
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlineremisepercent($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';

		if ((empty($hidedetails) || $hidedetails > 1) && $object->lines[$i]->product_type != 9 && empty($object->lines[$i]->special_code))
		{
			$out = dol_print_reduction($object->lines[$i]->remise_percent,$outputlangs);
		}

		$this->resprints = $out;
		return 1;
	}

	/**
	 * Return line progress
	 *
	 * 	@param		object				Object
	 * 	@param		$i					Current line number
	 *  @param    	outputlang			Object lang for output
	 */
	function pdf_getlineprogress($parameters=false,$object,$action='')
	{
		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$out='';

		if ((empty($hidedetails) || $hidedetails > 1) && $object->lines[$i]->product_type != 9 && empty($object->lines[$i]->special_code))
		{
			$out = $object->lines[$i]->situation_percent . '%';
		}

		$this->resprints = $out;
		return 1;
	}

}
