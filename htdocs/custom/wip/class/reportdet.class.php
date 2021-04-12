<?php
/* Copyright (C) 2017	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2018	Peter Roberts		<webmaster@finchmc.com.au>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file        class/reportdet.class.php
 * \ingroup     wip
 * \brief       This file is a CRUD class file for ReportDet (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class to manage Time Packets
 */
class ReportDet extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'reportdet';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'wip_reportdet';
	/**
	 * @var int  Does reportdet support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does reportdet support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for reportdet. Must be the part after the 'object_' into object_reportdet.png
	 */
	public $picto = 'reportdet@wip';
//	public $picto = 'projectpub';


	/**
	 *  'type' if the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
		'fk_report' => array('type'=>'integer', 'label'=>'Report', 'enabled'=>1, 'visible'=>1, 'position'=>5, 'notnull'=>-1, 'index'=>1,),
		'fk_task' => array('type'=>'integer:Task:projet/class/task.class.php', 'label'=>'Task', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1,),
		'fk_parent_line' => array('type'=>'integer', 'label'=>'ReportParentLine', 'enabled'=>1, 'visible'=>1, 'position'=>15, 'notnull'=>-1,),
		'fk_assoc_line' => array('type'=>'integer', 'label'=>'TaskAssocLine', 'enabled'=>1, 'visible'=>1, 'position'=>20, 'notnull'=>-1,),
		'fk_product' => array('type'=>'integer:Product:product/class/product.class.php', 'label'=>'Product', 'enabled'=>1, 'visible'=>1, 'position'=>25, 'notnull'=>-1,),
		'price' => array('type'=>'double', 'label'=>'ProductPrice', 'enabled'=>1, 'visible'=>1, 'position'=>26, 'notnull'=>-1,),
		'product_type' => array('type'=>'integer', 'label'=>'ProductType', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1,),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'visible'=>1, 'position'=>35, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object", 'showoncombobox'=>'1',),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'date_start' => array('type'=>'date', 'label'=>'DateStart', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1,),
		'date_end' => array('type'=>'date', 'label'=>'DateEnd', 'enabled'=>1, 'visible'=>1, 'position'=>51, 'notnull'=>-1,),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>1, 'visible'=>-1, 'position'=>60, 'notnull'=>-1,),
		'duration' => array('type'=>'double', 'label'=>'DurationSeconds', 'enabled'=>1, 'visible'=>1, 'position'=>68, 'notnull'=>-1,),
		'time_block' => array('type'=>'integer', 'label'=>'TimeBlock', 'enabled'=>1, 'visible'=>1, 'position'=>69, 'notnull'=>1,),
		'qty' => array('type'=>'double', 'label'=>'Quantity', 'enabled'=>1, 'visible'=>1, 'position'=>70, 'notnull'=>-1, 'isameasure'=>'1',),
		'discount_percent' => array('type'=>'double', 'label'=>'DiscountPercent', 'enabled'=>1, 'visible'=>1, 'position'=>75, 'notnull'=>-1,),
		'discounted_qty' => array('type'=>'double', 'label'=>'DiscountedQuantity', 'enabled'=>1, 'visible'=>1, 'position'=>76, 'notnull'=>-1, 'isameasure'=>'1',),
		'special_code' => array('type'=>'integer', 'label'=>'SpecialCode', 'enabled'=>1, 'visible'=>-2, 'position'=>150, 'notnull'=>-1,),
		'rang' => array('type'=>'integer', 'label'=>'Order', 'enabled'=>1, 'visible'=>-2, 'position'=>160, 'notnull'=>-1,),
		'rang_task' => array('type'=>'integer', 'label'=>'OrderTask', 'enabled'=>1, 'visible'=>-2, 'position'=>161, 'notnull'=>-1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'direct_amortised' => array('type'=>'integer', 'label'=>'DirectAmortised', 'enabled'=>1, 'visible'=>1, 'position'=>1010, 'notnull'=>1, 'default'=>'0', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Direct', '1'=>'Amortised')),
		'billable' => array('type'=>'integer', 'label'=>'Billable', 'enabled'=>1, 'visible'=>1, 'position'=>1020, 'notnull'=>1, 'default'=>'1', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Non-billable', '1'=>'Billable', '2'=>'Discounted')),
		'work_type' => array('type'=>'integer', 'label'=>'WorkType', 'enabled'=>1, 'visible'=>1, 'position'=>1030, 'notnull'=>1, 'default'=>'0', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Billable - Full', '1'=>'Billable - Apprentice Discount', '2'=>'Billable - Efficiency Discount', '5'=>'Non-billable - Rework', '6'=>'Non-billable - Goodwill')),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>1, 'position'=>1050, 'notnull'=>1, 'default'=>'0', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Orphan', '1'=>'In Process', '2'=>'Transmitted', '3'=>'Invoiced')),
	);
	public $rowid;
	public $fk_report;
	public $fk_task;
	public $fk_parent_line;
	public $fk_assoc_line;
	public $fk_product;
	public $price;
	public $product_type;
	public $ref;
	public $label;
	public $date_start;
	public $date_end;
	public $description;
	public $duration;
	public $time_block;
	public $qty;
	public $discount_percent;
	public $discounted_qty;
	public $special_code;
	public $rang;
	public $rang_task;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $direct_amortised;
	public $billable;
	public $work_type;
	public $status;
	// END MODULEBUILDER PROPERTIES



	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'reportdetdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_reportdet';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ReportDetline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('reportdetdet');
	/**
	 * @var ReportDetLine[]     Array of subtable lines
	 */
	//public $lines = array();

	/**
	 * Orphan status
	 * '0'=>'Orphan', '1'=>'In Process', '2'=>'Transmitted', '3'=>'Invoiced'
	 */
	const STATUS_ORPHAN = 0;
	/**
	 * In process
	 */
	const STATUS_IN_PROCESS = 1;
	/**
	 * Transmitted
	 */
	const STATUS_TRANSMITTED = 2;
	/**
	 * Invoiced
	 */
	const STATUS_INVOICED = 3;


	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $user;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']=0;

		// Unset fields that are disabled
		foreach($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
//		return $this->createCommon($user, $notrigger);
		global $langs;

		$error = 0;

		$now=dol_now();

		$fieldvalues = $this->setSaveQuery();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation']=$this->db->idate($now);
		if (array_key_exists('fk_user_creat', $fieldvalues) && ! ($fieldvalues['fk_user_creat'] > 0)) $fieldvalues['fk_user_creat']=$user->id;
		unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into insert.

		$keys=array();
		$values = array();
		foreach ($fieldvalues as $k => $v) {
			$keys[$k] = $k;
			$value = $this->fields[$k];
			$values[$k] = $this->quote($v, $value);
		}

		// Clean and check mandatory
		foreach($keys as $key)
		{
			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && $values[$key] == '-1') $values[$key]='';
			if (! empty($this->fields[$key]['foreignkey']) && $values[$key] == '-1') $values[$key]='';

			//var_dump($key.'-'.$values[$key].'-'.($this->fields[$key]['notnull'] == 1));
			if (isset($this->fields[$key]['notnull']) && $this->fields[$key]['notnull'] == 1 && ! isset($values[$key]) && is_null($val['default']))
			{
				$error++;
				$this->errors[]=$langs->trans("ErrorFieldRequired", $this->fields[$key]['label']);
			}

			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && empty($values[$key])) $values[$key]='null';
			if (! empty($this->fields[$key]['foreignkey']) && empty($values[$key])) $values[$key]='null';
		}

		if ($error) return -1;

		$this->db->begin();

		if (! $error)
		{
			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.$this->table_element;
			$sql.= ' ('.implode( ", ", $keys ).')';
			$sql.= ' VALUES ('.implode( ", ", $values ).')';

			$res = $this->db->query($sql);
			if ($res===false) {
				$error++;
				$this->errors[] = $this->db->lasterror();
			}
		}

		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
		}

		// Create extrafields
		if (! $error)
		{
			$result=$this->insertExtraFields();
			if ($result < 0) $error++;
		}

		// Triggers
		if (! $error && ! $notrigger)
		{
			// Call triggers
			$result=$this->call_trigger(strtoupper(get_class($this)).'_CREATE',$user);
			if ($result < 0) { $error++; }
			// End call triggers
		}

		// Commit or rollback
		if ($error) {
			$this->db->rollback();
			return -1;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}

	/**
	 * Clone and object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $hookmanager, $langs;
	    $error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

	    $this->db->begin();

	    // Load source object
		$object->fetchCommon($fromid);
		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		$object->ref = "copy_of_".$object->ref;
		$object->title = $langs->trans("CopyOf")." ".$object->title;
	    // ...

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	/*public function fetchLines()
	{
		$this->lines=array();

		// Load lines with object ReportDetLine

		return count($this->lines)?1:0;
	}*/

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Return a link to the object card (with optional picto)
	 *
	 *	@param	int		$withpicto				Include picto (0=No picto, 1=Include picto, 2=Only picto)
	 *	@param	string	$option					On what the link point to ('nolink', ...)
     *  @param	int  	$notooltip				1=Disable tooltip
     *  @param  string  $morecss            	Add more css on link
     *  @param  int     $save_lastsearch_value	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values when clicking
	 *  @param	string	$mode					Mode 'report', 'time', 'contact', 'note', document' define page to link to.
	 * 	@param	int		$trunclabel				0=Default, 1=Add label into string, >1=Add first chars into string
	 *  @param	string	$sep					Separator between ref and label if option addlabel is set
	 *	@return	string							String with URL
	 */
	function getNomUrl($withpicto=0, $option='', $notooltip=0, $morecss='', $save_lastsearch_value=-1, $mode='report_card', $trunclabel=0, $sep=' - ')
	{
		global $db, $conf, $langs, $hookmanager;
		global $dolibarr_main_authentication, $dolibarr_main_demo;
		global $menumanager;

		if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

		$result = '';
		$newref = (! empty($this->ref) ? $this->ref : 'TP'.sprintf("%06d", $this->id));

		$label = '<u>' . $langs->trans("ReportDet") . '</u>';
		$label.= '<br><strong>' . $langs->trans('Ref') . ':</strong> ' . $newref;

		if (! empty($this->label))
			$label.= '<br><strong>' . $langs->trans('LabelReport') . ':</strong> ' . $this->label;

		$url = dol_buildpath('/wip/reportdet_card.php',1).'?id='.$this->id;

//		if ($option != 'nolink')
//		{
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values=($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/',$_SERVER["PHP_SELF"])) $add_save_lastsearch_values=1;
			if ($add_save_lastsearch_values) $url.='&save_lastsearch_values=1';
//		}

		$linkclose = '';
		if (empty($notooltip))
		{
			if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				$label=$langs->trans("ShowReportDet");
				$linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';

			/*
			$hookmanager->initHooks(array('reportdetdao'));
			$parameters=array('id'=>$this->id);
			$reshook=$hookmanager->executeHooks('getnomurltooltip',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
			if ($reshook > 0) $linkclose = $hookmanager->resPrint;
			*/
		}
		else $linkclose = ($morecss?' class="'.$morecss.'"':'');

		$linkstart = '<a href="'.$url.'"';
		$linkstart.=$linkclose.'>';
		$linkend='</a>';

//		$picto=($this->picto?$this->picto:'generic');
		$picto='projectpub';

		$result .= $linkstart;
		if ($withpicto) $result.=img_object(($notooltip?'':$label), ($this->picto?$this->picto:'generic'), ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
		if ($withpicto != 2) $result.= $newref;
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($trunclabel && $this->label) ? $sep . dol_trunc($this->label, ($trunclabel > 1 ? $trunclabel : 0)) : '');

		global $action;
		$hookmanager->initHooks(array('reportdetdao'));
		$parameters=array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook=$hookmanager->executeHooks('getNomUrl',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	function getDirectStatut($mode=0)
	{
		return $this->LibStatut($this->direct_amortised, $mode);
	}

	function getBillableStatut($mode=0)
	{
		return $this->LibStatut($this->billable, $mode);
	}

	function getWorkTypeStatut($mode=0)
	{
		return $this->LibStatut($this->work_type, $mode);
	}

	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	function LibStatut($status, $mode=0)
	{
		global $langs;
		$langs->load('wip@wip');

		switch ($mode)
		{
			case 7:
				$this->labelstatus[0] = $langs->trans('StatusReportDetAmortised');
				$this->labelstatus[1] = $langs->trans('StatusReportDetDirect');
				break;
			case 8:
				$this->labelstatus[0] = $langs->trans('StatusReportDetNonBillable');
				$this->labelstatus[1] = $langs->trans('StatusReportDetBillable');
				$this->labelstatus[2] = $langs->trans('StatusReportDetDiscounted');
				break;
			case 9:
				$this->labelstatus[0] = $langs->trans('StatusReportDetBillable');
				$this->labelstatus[1] = $langs->trans('StatusReportDetApprenticeDiscount');
				$this->labelstatus[2] = $langs->trans('StatusReportDetEfficiencyDiscount');
				$this->labelstatus[5] = $langs->trans('StatusReportDetNonBillableRework');
				$this->labelstatus[6] = $langs->trans('StatusReportDetNonBillableGoodwill');
				break;
			default:
				// '0'=>'Orphan', '1'=>'In Process', '2'=>'Transmitted', '3'=>'Invoiced')),
				$this->labelstatus[0] = $langs->trans('StatusReportDetOrphan');
				$this->labelstatus[1] = $langs->trans('StatusReportDetInProcess');
				$this->labelstatus[2] = $langs->trans('StatusReportDetTransmitted');
				$this->labelstatus[3] = $langs->trans('StatusReportDetInvoiced');
				$this->labelstatus[4] = $langs->trans('StatusReportAdministrative');
				break;
		}

		if ($mode == 0)
		{
			return $this->labelstatus[$status];
		}
		if ($mode == 1)
		{
			return $this->labelstatus[$status];
		}
		if ($mode == 2)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4').' '.$this->labelstatus[$status];
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5').' '.$this->labelstatus[$status];
		}
		if ($mode == 3)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5');
		}
		if ($mode == 4)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4').' '.$this->labelstatus[$status];
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5').' '.$this->labelstatus[$status];
		}
		if ($mode == 5)
		{
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5');
		}
		if ($mode == 6)
		{
				// '0'=>'Orphan', '1'=>'In Process', '2'=>'Transmitted', '3'=>'Invoiced')),
				if ($status==0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut8');
//			    if ($status==0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$wip_picto14, '', 1); // Tester
			elseif ($status==1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut1');
			elseif ($status==2) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4');
			elseif ($status==3) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$wip_picto11, '', 1);
			elseif ($status==4) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],$wip_picto14, '', 1);
		}
		if ($mode == 7)
		{
			if ($status == 0) return $this->labelstatus[0].' '.img_picto($this->labelstatus[0], 'statut7', 'class="pictostatus"').'&nbsp;&nbsp;&nbsp;'.$this->labelstatus[1].' '.img_picto($this->labelstatus[1],'statut4', 'class="pictostatus"');
			if ($status == 1) return $this->labelstatus[0].' '.img_picto($this->labelstatus[0], 'statut4', 'class="pictostatus"').'&nbsp;&nbsp;&nbsp;'.$this->labelstatus[1].' '.img_picto($this->labelstatus[1],'statut7', 'class="pictostatus"');
		}
		if ($mode == 8)
		{
			if ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut8', 'class="pictostatus"');
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4', 'class="pictostatus"');
			if ($status == 2) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut1', 'class="pictostatus"');
		}
		if ($mode == 9)
		{
		//'0'=>'Billable - Full', '1'=>'Billable - Apprentice Discount', '2'=>'Billable - Efficiency Discount', '5'=>'Non-billable - Rework', '6'=>'Non-billable - Goodwill')),
		// PJR TODO - add to language file
			if ($status == 0) return 'Billable';				// $this->labelstatus[0] = $langs->trans('StatusReportDetBillable');
			if ($status == 1) return 'Apprentice Discount';		// $this->labelstatus[1] = $langs->trans('StatusReportDetApprenticeDiscount');
			if ($status == 2) return 'Efficiency Discount';		// $this->labelstatus[2] = $langs->trans('EfficiencyDiscount');
			if ($status == 5) return 'Non-billable - Rework';	// $this->labelstatus[5] = $langs->trans('NonBillableRework');
			if ($status == 6) return 'Non-billable - Goodwill';	// $this->labelstatus[6] = $langs->trans('NonBillableGoodwill');
		}
	}

	/**
	 *	Charge les informations d'ordre info dans l'objet commande
	 *
	 *	@param  int		$id       Id of order
	 *	@return	void
	 */
	function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql.= ' fk_user_creat, fk_user_modif';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' WHERE t.rowid = '.$id;
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture   = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);

		}
		else
		{
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}




	/**
	 *	Update line
	 *
	 *	@param	 	int			$rowid		   	Id of line to update
	 *	@return		int		 					< 0 if error, > 0 if ok
	 */
	public function updateline($user,
		$rowid,
		$fk_report,
		$fk_task,
		$fk_parent_line,
		$fk_assoc_line,
		$fk_product,
		$price,
		$product_type,
		$ref,
		$label,
		$date_start,
		$date_end,
		$description,
		$qty,
		$discount_percent,
		$discounted_qty,
		$special_code,
		$rang,
		$rang_task,
		$fk_user_creat,
		$fk_user_modif,
		$import_key,
		$direct_amortised,
		$billable,
		$work_type,
		$status
		)
	{
//		global $mysoc, $conf;
		global $conf, $user;
		dol_syslog(get_class($this)."::updateline $rowid,
		$fk_report,
		$fk_task,
		$fk_parent_line,
		$fk_assoc_line,
		$fk_product,
		$price,
		$product_type,
		$ref,
		$label,
		$date_start,
		$date_end,
		$description,
		$qty,
		$discount_percent,
		$discounted_qty,
		$special_code,
		$rang,
		$rang_task,
		$fk_user_creat,
		$fk_user_modif,
		$import_key,
		$direct_amortised,
		$billable,
		$work_type,
		$status
		");

		$error = 0;

//		if ($this->brouillon)
		if (1 == 1)
		{
			$this->db->begin();

			// Clean parameters
			if (empty($qty)) $qty=0;
			if (empty($discount_percent)) $discount_percent=0;
			if (empty($discounted_qty)) $discounted_qty=0;

			$discount_percent=price2num($discount_percent);
			$discounted_qty=price2num($discounted_qty);
			$qty=price2num($qty);

			// Check parameters
//			if ($type < 0) return -1;

			//Fetch current line from the database and then clone the object and set it in $oldline property
			$this->line=new ReportDet($this->db);
			$this->line->fetch($rowid);
			$oldline = clone $this->line;
			$this->line->oldline = $oldline;

			$this->line->context = $this->context;

			// Reorder if fk_parent_line change
/*			if (! empty($fk_parent_line)) {
				$rangmax = $this->line_max($fk_parent_line);
				$this->line->rang = $rangmax + 1;
			}
*/
/*
			$this->line->fk_report			= $fk_report;
			$this->line->fk_task			= $fk_task;
			$this->line->fk_parent_line		= $fk_parent_line;
			$this->line->fk_assoc_line		= $fk_assoc_line;
			$this->line->fk_product			= $fk_product;
			$this->line->price				= $price;
			$this->line->product_type		= $product_type;
			$this->line->ref				= $ref;
			$this->line->label				= $label;
			$this->line->date_start			= $date_start;
			$this->line->date_end			= $date_end;
			$this->line->description		= $description;
			$this->line->qty				= $qty;
			$this->line->discount_percent	= $discount_percent;
			$this->line->discounted_qty		= $discounted_qty;
			$this->line->special_code		= $special_code;
			$this->line->rang				= $rang;
			$this->line->rang_task			= $rang_task;
				$fk_user_creat,
				$fk_user_modif,
				$import_key,
				$direct_amortised,
				$billable,
			$this->line->work_type			= $work_type;
			$this->line->status				= $status;
*/
//			$result=$this->line->update($user, $notrigger);

		// Update line in database
			$sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
			$sql.= ' SET';
			$sql.= ' fk_user_modif = '.$user->id;

		//	$sql.= ', fk_report ='.$fk_report;			// not changed by updateline
		//	$sql.= ', fk_task ='.$fk_task;				// not changed by updateline
			$sql.= ', fk_parent_line ='.$fk_parent_line;
		//	$sql.= ', fk_assoc_line ='.$fk_assoc_line;	// not used // PJR TODO
			$sql.= ', fk_product ='.$fk_product;
			$sql.= ', price ='.$price;
			$sql.= ', product_type ='.$product_type;
		//	$sql.= ', ref = "'.$ref.'"';				// not used // PJR TODO
			$sql.= ', label = "'.$label.'"';
			if ($date_start == '')
			{
				$sql.= ', date_start = NULL';
			} else {
				$sql.= ', date_start = "'.$this->db->idate($date_start).'"';
			}
			if ($date_end == '')
			{
				$sql.= ', date_end = NULL';
			} else {
				$sql.= ', date_end = "'.$this->db->idate($date_end).'"';
			}
			$sql.= ', description = "'.$description.'"';
		//	$sql.= ', qty ='.$qty;						// not changed by updateline
			$sql.= ', discount_percent ='.$discount_percent;
			$sql.= ', discounted_qty ='.$discounted_qty;
		//	$sql.= ', special_code ='.$special_code;	// not changed by updateline
		//	$sql.= ', rang ='.$rang;					// not changed by updateline
		//	$sql.= ', rang_task ='.$rang_task;			// not changed by updateline
		//	$sql.= ', fk_user_creat ='.$fk_user_creat;	// not changed by updateline
		//	$sql.= ', fk_user_modif ='.$fk_user_modif;	// Modifying User set at start
		//	$sql.= ', import_key ='.$import_key;		// not changed by updateline

			$sql.= ', direct_amortised ='.$direct_amortised;
			$sql.= ', work_type ='.$work_type;
			$sql.= ', billable ='.$billable;
			$sql.= ', status ='.$status;

			$sql.= " WHERE rowid = ".$rowid;
/*
print '<br><br>SQL = '.$sql.'<br><br>';
//print '<br>Hello World1 - Got to Here 854  - ReportDet Updateline - $direct_amortised = '.$direct_amortised.'<br>';
print '<br>Hello World1 - Got to Here 855  - ReportDet Updateline - $discounted_qty = '.$discounted_qty.'<br>';
print_r($_REQUEST);
print '<br><br>';
exit;
*/
	
			dol_syslog(get_class($this)."::updateline", LOG_DEBUG);
			$result = $this->db->query($sql);
			if ($result > 0)
			{
				if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
				{
					$result=$this->insertExtraFields();
					if ($result < 0)
					{
						$error++;
					}
				}
	
				if (! $error && ! $notrigger)
				{
					global $user;
					// Call trigger
					$result=$this->call_trigger('LINEORDER_SUPPLIER_UPDATE',$user);
					if ($result < 0)
					{
						$this->db->rollback();
						return -1;
					}
					// End call triggers
				}
	
				if (!$error)
				{
					$this->db->commit();
	 				return $result;
				}
				else
				{
					$this->db->rollback();
					return -1;
				}
			}
			else
			{
				$this->error=$this->db->lasterror();
				$this->db->rollback();
				return -1;
			}

/*
			// Update information denormalized at invoice level
			if ($result >= 0)
			{
				// Reorder if child line
				if (! empty($fk_parent_line)) $this->line_order(true,'DESC');

				// Mise a jour info denormalisees
				$this->update_price('','auto');
				$this->db->commit();
				return $result;
			}
			else
			{
				$this->error=$this->db->lasterror();
				$this->db->rollback();
				return -1;
			}
*/

		}
		else
		{
			$this->error="Status makes operation forbidden";
			dol_syslog(get_class($this)."::updateline ".$this->error, LOG_ERR);
			return -2;
		}
	}


	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, paramerts come from the schedule job setup field 'Parameters'
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	//public function doScheduledJob($param1, $param2, ...)
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error='';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}


	function selectServices($selected='', $htmlname='serviceid', $category=0, $mode=0, $morecss='', $morewherefilter='', $useempty=0)
	{
		global $user, $langs;

		$servicetmp=new ReportDet($this->db);
		$servicesarray=$servicetmp->getServicesArray($mode, $category, $morewherefilter);
		if ($servicesarray)
		{
			print '<select class="flat'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'" id="'.$htmlname.'">';
			if ($useempty) print '<option value="0">&nbsp;</option>';
			$numlines=count($servicesarray);
			for ($i = 0 ; $i < $numlines ; $i++)
			{
				// Print Service
					print '<option value="'.$servicesarray[$i][rowid].'_'.$servicesarray[$i][ref].'_'.$servicesarray[$i][label].'"';
					if (($servicesarray[$i][rowid] == $selected) || ($servicesarray[$i][rowid].'_'.$servicesarray[$i][ref].'_'.$servicesarray[$i][label] == $selected)) print ' selected';
					print '>';
					print $servicesarray[$i][ref].' > '.$servicesarray[$i][label]."</option>\n";
					$inc++;
			}
			print '</select>';
 
			print ajax_combobox($htmlname);
		}
		else
		{
			print '<div class="warning">'.$langs->trans("NoServices").'</div>';
		}
	}

	/**
	 * Return list of services
	 * Sort order is on project, then on position of task, and last on start date of first level task
	 *
	 * @param	int		$mode				0=Return list of services in a cetgory
	 * @param	int		$category			Category id
	 * @param	string	$morewherefilter	Add more filter into where SQL request (must start with ' AND ...')
	 * @return 	array						Array of services
	 */
	public function getServicesArray($mode=0, $category=0, $morewherefilter='')
	{
		global $conf;

		$services = array();

		// List of sevices
		$sql = "SELECT";
		$sql.= " s.rowid, s.ref, s.label";
		$sql.= ", cp.fk_categorie, cp.fk_product";
		$sql.= " FROM ".MAIN_DB_PREFIX."product as s";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."categorie_product as cp on cp.fk_product = s.rowid";
		$sql.= " WHERE 1=1";

		if ($mode == 0)
		{
			$sql.= ' AND cp.fk_categorie = '.$category;
		}
		else return 'BadValueForParameterMode';

		if ($morewherefilter) $sql.=$morewherefilter;
		$sql.= ' ORDER BY s.ref ASC';

		//print $sql;exit;
		dol_syslog(get_class($this)."::getServicesArray", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			// Loop on each record found, so each couple (project id, task id)
			while ($i < $num)
			{
				$error=0;

				$obj = $this->db->fetch_object($resql);

				if (! $error)
				{
					//$services[$i] = new ReportDet($this->db);
					$services[$i][rowid]			= $obj->rowid;
					$services[$i][ref]				= $obj->ref;
					$services[$i][label]			= $obj->label;
				}

				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}

		return $services;
	}


	/**
	 * Return list of services
	 * Sort order is on project, then on position of task, and last on start date of first level task
	 *
	 * @param	int		$mode				0=Return list of services in a cetgory
	 * @param	int		$category			Category id
	 * @param	string	$morewherefilter	Add more filter into where SQL request (must start with ' AND ...')
	 * @return 	array						Array of services
	 */
	public function getExtendedServicesArray($mode=0, $category=0, $morewherefilter='')
	{
		global $conf;
		$services = array();

		// List of sevices
		$sql = 'SELECT DISTINCT';
		$sql.= ' s.rowid as rid, s.ref, s.label';
		$sql.= ', pp.date_price as pdate_price, pcp.datec as cdate_price';
		$sql.= ', pp.price as pprice, pp.price_ttc as pprice_ttc, pp.price_min as pprice_min, pp.price_min_ttc as pprice_min_ttc';
		$sql.= ', pp.price_base_type as pprice_base_type, pp.default_vat_code as pdefault_vat_code, pp.tva_tx as ptva_tx, pp.recuperableonly as precuperableonly';
		$sql.= ', pcp.price as cprice, pcp.price_ttc as cprice_ttc, pcp.price_min as cprice_min, pcp.price_min_ttc as cprice_min_ttc';
		$sql.= ', pcp.price_base_type as cprice_base_type, pcp.default_vat_code as cdefault_vat_code, pcp.tva_tx as ctva_tx, pcp.recuperableonly as crecuperableonly';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'product AS s';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product AS cp ON cp.fk_product = s.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product_price AS pp ON pp.fk_product = s.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product_customer_price AS pcp ON pcp.fk_product = s.rowid';
		$sql.= ' INNER JOIN ';
			$sql.= ' (SELECT';
			$sql.= ' pp.fk_product AS pfk_product, MAX(date_price) as max_date_price';		
			$sql.= ' FROM '.MAIN_DB_PREFIX.'product_price AS pp';
			$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product AS cp ON cp.fk_product = pp.fk_product';
			$sql.= ' WHERE 1=1';
			$sql.= ' AND cp.fk_categorie = '.$category;
			if ($morewherefilter) $sql.=$morewherefilter;
			$sql.= ' GROUP BY pp.fk_product) groupedprice';
		$sql.= ' ON pp.fk_product = groupedprice.pfk_product AND pp.date_price = groupedprice.max_date_price';
		$sql.= ' GROUP BY s.rowid';
		$sql.= ' ORDER BY s.ref ASC';

		//print $sql;exit;
		dol_syslog(get_class($this)."::getExtendedServicesArray", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			// Loop on each record 
			for ($i = 0 ; $i < $num ; $i++)
			{
				$error=0;
				$obj = $this->db->fetch_object($resql);
				if (! $error)
				{
					//$services[$i] = new ReportDet($this->db);
					$rid								= $obj->rid;
					$services[$rid][rowid]				= $obj->rid;
					$services[$rid][ref]				= $obj->ref;
					$services[$rid][label]				= $obj->label;

					/*
					$services[$rid][id]					= $obj->rowid;
					$services[$rid][entity]				= $obj->entity;
					$services[$rid][datec]				= $db->jdate($obj->cdate_price);
					$services[$rid][tms]				= $db->jdate($obj->tms);
					$services[$rid][fk_product]			= $obj->fk_product;
					$services[$rid][fk_soc]				= $obj->fk_soc;
					*/
					$services[$rid][pdate_price]		= $obj->pdate_price;
					$services[$rid][pprice]				= $obj->pprice;
					$services[$rid][pprice_ttc]			= $obj->pprice_ttc;
					$services[$rid][pprice_min]			= $obj->pprice_min;
					$services[$rid][pprice_min_ttc]		= $obj->pprice_min_ttc;
					$services[$rid][pprice_base_type]	= $obj->pprice_base_type;
					$services[$rid][pdefault_vat_code]	= $obj->pdefault_vat_code;
					$services[$rid][ptva_tx]			= $obj->ptva_tx;
					$services[$rid][precuperableonly]	= $obj->precuperableonly;

					$services[$rid][cdate_price]		= $obj->datec;
					$services[$rid][cdate_price1]		= $obj->cdate_price;
					$services[$rid][cprice]				= $obj->cprice;
					$services[$rid][cprice_ttc]			= $obj->cprice_ttc;
					$services[$rid][cprice_min]			= $obj->cprice_min;
					$services[$rid][cprice_min_ttc]		= $obj->cprice_min_ttc;
					$services[$rid][cprice_base_type]	= $obj->cprice_base_type;
					$services[$rid][cdefault_vat_code]	= $obj->cdefault_vat_code;
					$services[$rid][ctva_tx]			= $obj->ctva_tx;
					$services[$rid][crecuperableonly]	= $obj->crecuperableonly;
					/*
					$services[$rid][localtax1_tx]		= $obj->localtax1_tx;
					$services[$rid][localtax2_tx]		= $obj->localtax2_tx;
					$services[$rid][fk_user]			= $obj->fk_user;
					$services[$rid][import_key]			= $obj->import_key;
					*/
				}
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}

		return $services;
	}



//			print $packet->selectReportPackets(	$object->lines[$i]->rowid, 0, 				0, 			$object->lines[$i]->fk_report,	$object->lines[$i]->fk_parent_line, 'newparentpkt_id', 3, 		0,		
//	0,						  	'',						'minwidth400 maxwidth200onsmartphone');
//	function selectReportPackets(				$selectedpacket='', 		$projectid=0,	$taskid=0,	$reportid=0,					$parentid=0,						$htmlname='',		$mode=0, $useempty=0, 
//	$disablechildoftaskid=0,	$filteronprojstatus='',	$morecss='',  $morewherefilter='')
	
	function selectReportPackets($selectedpacket='', $projectid=0, $taskid=0, $reportid=0, $parentid=0, $htmlname='', $mode=0, $useempty=0, $disablechildoftaskid=0, $filteronprojstatus='', $morecss='',  $morewherefilter='')
	{
		global $user, $langs;

		//require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';

		$packettmp=new ReportDet($this->db);
		$packetsarray=$packettmp->getPacketsArray($projectid, $taskid, $reportid, '', $mode, $filteronproj, $filteronprojstatus, $morewherefilter);
		if ($packetsarray)
		{
			print '<select class="flat'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'" id="'.$htmlname.'">';
			if ($useempty) print '<option value="0">&nbsp;</option>';
			$j=0;
			$level=0;
			$lastreportid=0;
			$numlines=count($packetsarray);
			for ($i = 0 ; $i < $numlines ; $i++)
			{
				if ($packetsarray[$i]->reportid != $lastreportid) // Break found on task
				{
					if ($i > 0) print '<option value="0" disabled>----------</option>';
					print '<option value="'.$packetsarray[$i]->reportref.'_0"';
//					if ($reportid == $packetsarray[$i]->reportid && $mode == 3) print ' selected';
					if ($parentid == 0 && $mode == 3) print ' selected';
					//if ($mode == 1) print ' disabled';
					print '>';  // Task -> Packet
					print /*$langs->trans("Task").*/$packetsarray[$i]->reportref;
					//   print ' ('.$langs->trans("Visibility").': '.$langs->trans("SharedTask").')';
					//print '-'.$parent.'-'.$lines[$i]->fk_project.'-'.$lastprojectid;
					print "</option>\n";
					
					$lastreportid=$packetsarray[$i]->reportid;
//					$inc++;
				}

				// Print packet
				if (isset($packetsarray[$i]->rowid))    // We use isset because $packetsarray[$i]->id may be null if task has no time packet and are on root report (packets may be caught by a left join). We enter here only if '0' or >0
				{
					print '<option value="'.$packetsarray[$i]->reportref.'_'.$packetsarray[$i]->rowid.'_'.$packetsarray[$i]->label.'"';
					if (($packetsarray[$i]->rowid == $selectedpacket) || ($packetsarray[$i]->reportref.'_'.$packetsarray[$i]->rowid.'_'.$packetsarray[$i]->label == $selectedpacket)) print ' disabled';
					if ($packetsarray[$i]->rowid == $parentid) print ' selected';
					print '>';
					print /*$langs->trans("Task").*/$packetsarray[$i]->reportref;
				//	print ' '.$packetsarray[$i]->projectlabel;
				//	print ' ('.$langs->trans("Visibility").': '.$langs->trans("SharedProject").')';
				
					if ($packetsarray[$i]->rowid) print ' > ';
				
				//		print "&nbsp;&nbsp;&nbsp;";
				
					print 'TP'.sprintf("%06d", $packetsarray[$i]->rowid).' '.$packetsarray[$i]->label."</option>\n";
//					$inc++;
				}
			}
			
			//$this->_pLineSelect($j, 0, $packetsarray, $level, $selectedtask, $projectid, $disablechildoftaskid);
			print '</select>';
 
			print ajax_combobox($htmlname);
		}
		else
		{
			print '<div class="warning">'.$langs->trans("NoPackets").'</div>';
		}
	}

	function selectTaskPackets($selectedpacket='', $projectid=0, $taskid=0, $reportid=0, $htmlname='', $mode=0, $useempty=0, $disablechildoftaskid=0, $filteronprojstatus='', $morecss='',  $morewherefilter='')
	{
		global $user, $langs;

		//require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
		$trunc_length = 50;
		$sep = ' - ';
		$packettmp=new ReportDet($this->db);
		$packetsarray=$packettmp->getPacketsArray($projectid, $taskid, $reportid, '', $mode, $filteronproj, $filteronprojstatus, $morewherefilter);
		if ($packetsarray)
		{
			print '<select class="flat'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'" id="'.$htmlname.'">';
			if ($useempty) print '<option value="0">&nbsp;</option>';
			$j=0;
			$level=0;
			$lasttaskid=0;
			$numlines=count($packetsarray);
			for ($i = 0 ; $i < $numlines ; $i++)
			{
				if ($packetsarray[$i]->fk_task != $lasttaskid) // Break found on task
				{
					if ($i > 0) print '<option value="0" disabled>----------</option>';
					print '<option value="'.$packetsarray[$i]->fk_task.'_0"';
					if ($taskid == $packetsarray[$i]->fk_task && $mode == 0) print ' selected';
					if ($mode == 1) print ' disabled';
					print '>';  // Task -> Packet
					print /*$langs->trans("Task").*/'W/O '.$packetsarray[$i]->taskref;
					print $sep . dol_trunc($packetsarray[$i]->tasklabel, ($trunc_length > 1 ? $trunc_length : 0));
					
					//   print ' ('.$langs->trans("Visibility").': '.$langs->trans("SharedTask").')';
					//print '-'.$parent.'-'.$lines[$i]->fk_project.'-'.$lastprojectid;
					print "</option>\n";
					
					$lasttaskid=$packetsarray[$i]->fk_task;
					$inc++;
				}

				// Print packet
				// We use isset because $packetsarray[$i]->id may be null if task has no time packet and are on root task (packets may be caught by a left join). We enter here only if '0' or >0
				if (isset($packetsarray[$i]->rowid))    
				{
					print '<option value="'.$packetsarray[$i]->fk_task.'_'.$packetsarray[$i]->rowid.'_'.$packetsarray[$i]->label.'"';
					if (($packetsarray[$i]->rowid == $selectedpacket) || ($packetsarray[$i]->fk_task.'_'.$packetsarray[$i]->rowid.'_'.$packetsarray[$i]->label == $selectedpacket)) print ' selected';
					print '>';
					print /*$langs->trans("Task").*/'W/O '.$packetsarray[$i]->taskref;
				//	print ' '.$packetsarray[$i]->projectlabel;
				//	print ' ('.$langs->trans("Visibility").': '.$langs->trans("SharedProject").')';
				
					if ($packetsarray[$i]->rowid) print ' > ';
				
				//		print "&nbsp;&nbsp;&nbsp;";
				
					print 'TP'.sprintf("%06d", $packetsarray[$i]->rowid).' '.dol_trunc($packetsarray[$i]->label, ($trunc_length > 1 ? $trunc_length : 0))."</option>\n";
					$inc++;
				}
			}
			
			//$this->_pLineSelect($j, 0, $packetsarray, $level, $selectedtask, $projectid, $disablechildoftaskid);
			print '</select>';
 
			print ajax_combobox($htmlname);
		}
		else
		{
			print '<div class="warning">'.$langs->trans("NoPackets").'</div>';
		}
	}

	/**
	 * Return list of time packets for one particular project or for one particular task
	 * Sort order is on project, then on position of task, and last on start date of first level task
	 *
	 * @param	int		$projectid			Project id
	 * @param	int		$taskid				Task id
	 * @param	int		$tasktimelist		List of time entries
	 * @param	int		$mode				0=Return list of packets in a task, 1=Return list of packets in a project 2=Return list of packets associated with a list of time entries
	 * @param	string	$filteronproj    	Filter on project ref or label
	 * @param	string	$filteronprojstatus	Filter on project status ('-1'=no filter, '0,1'=Draft+Validated only)
	 * @param	string	$morewherefilter	Add more filter into where SQL request (must start with ' AND ...')
	 * @return 	array						Array of tasks
	 */
	public function getPacketsArray($projectid=0, $taskid=0, $reportid=0, $tasktimelist='', $mode=0, $filteronproj='', $filteronprojstatus='-1', $morewherefilter='')
	{
		global $conf;

		$packets = array();

		// List of time packets
		$sql = "SELECT DISTINCT";
//		if ($mode == 1) $sql = "SELECT";
		$sql.= " wrd.rowid as packetid, wrd.fk_report as fk_report, wrd.fk_task as fk_task, wrd.fk_parent_line as fk_parent_line";
		$sql.= ", wrd.fk_assoc_line as fk_assoc_line, wrd.fk_product as fk_product, wrd.product_type as product_type";
		$sql.= ", wrd.ref as packetref, wrd.label as packetlabel, wrd.date_start as date_start, wrd.date_end as date_end";
		$sql.= ", wrd.description as packetdescription, wrd.qty as qty, wrd.discount_percent as discount_percent";
		$sql.= ", wrd.special_code as special_code, wrd.rang as rang, wrd.rang_task as rang_task";
		$sql.= ", wrd.date_creation as date_creation, wrd.tms as tms, wrd.fk_user_creat as fk_user_creat";
		$sql.= ", wrd.fk_user_modif as fk_user_modif, wrd.import_key as import_key, wrd.status as packetstatus";
		$sql.= ", t.rowid as taskid, t.ref as taskref, t.label as tasklabel, t.description, t.fk_task_parent, t.duration_effective, t.progress, t.fk_statut as status";
		$sql.= ", p.rowid as projectid, p.ref as pref, p.title as plabel, p.public, p.fk_statut as projectstatus";
		$sql.= ", wr.rowid as reportid, wr.ref as reportref";
		$sql.= " FROM ".MAIN_DB_PREFIX."wip_reportdet as wrd";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task as t on t.rowid = wrd.fk_task";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet as p on p.rowid = t.fk_projet";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task_time as tt on tt.fk_reportdet = wrd.rowid";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."wip_report as wr on wr.rowid = wrd.fk_report";
		$sql.= " WHERE 1=1";

		if ($mode == 0)
		{
			$sql.= ' AND wrd.fk_task = '.$taskid;
		}
		elseif ($mode == 1)
		{
			$sql.= ' AND p.rowid = '.$projectid;
			$sql.= ' AND wrd.fk_report IS NULL';
		}
		elseif ($mode == 2)
		{
			$sql.= ' AND tt.rowid IN('.$tasktimelist.')';
		}
		elseif ($mode == 3)
		{
			$sql.= ' AND wrd.fk_report = '.$reportid;
			$sql.= ' AND wrd.fk_parent_line IS NULL';	// This will only allow parent packets to be selected
		}
		else return 'BadValueForParameterMode';

		if ($filteronproj) $sql.= natural_search(array("p.ref", "p.title"), $filteronproj);
		if ($filteronprojstatus && $filteronprojstatus != '-1') $sql.= " AND p.fk_statut IN (".$filteronprojstatus.")";
		if ($morewherefilter) $sql.=$morewherefilter;
		
		if ($mode == 1) $sql.= " ORDER BY t.ref, wrd.ref, wrd.rowid, wrd.date_start";
		else $sql.= " ORDER BY t.rang, t.ref, wrd.rang_task, wrd.ref, wrd.rowid, wrd.date_start";

		//print $sql;exit;
		dol_syslog(get_class($this)."::getPacketsArray", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			// Loop on each record found, so each couple (project id, task id)
			while ($i < $num)
			{
				$error=0;

				$obj = $this->db->fetch_object($resql);

				if (! $error)
				{
					$packets[$i] = new ReportDet($this->db);
					$packets[$i]->rowid				= $obj->packetid;
					$packets[$i]->fk_report			= $obj->fk_report;
					$packets[$i]->fk_task			= $obj->fk_task;
					$packets[$i]->fk_parent_line	= $obj->fk_parent_line;
					$packets[$i]->fk_assoc_line		= $obj->fk_assoc_line;
					$packets[$i]->fk_product		= $obj->fk_product;
					$packets[$i]->price				= $obj->price;
					$packets[$i]->product_type		= $obj->product_type;
					$packets[$i]->ref				= $obj->packetref;
					$packets[$i]->label				= $obj->packetlabel;
					$packets[$i]->date_start		= $obj->date_start;
					$packets[$i]->date_end			= $obj->date_end;
					$packets[$i]->description		= $obj->packetdescription;
					$packets[$i]->qty				= $obj->qty;
					$packets[$i]->discount_percent	= $obj->discount_percent;
					$packets[$i]->discounted_qty	= $obj->discounted_qty;
					$packets[$i]->special_code		= $obj->special_code;
					$packets[$i]->rang				= $obj->rang;
					$packets[$i]->rang_task			= $obj->rang_task;
					$packets[$i]->date_creation		= $obj->date_creation;
					$packets[$i]->tms				= $obj->tms;
					$packets[$i]->fk_user_creat		= $obj->fk_user_creat;
					$packets[$i]->fk_user_modif		= $obj->fk_user_modif;
					$packets[$i]->import_key		= $obj->import_key;
					$packets[$i]->direct_amortised	= $obj->direct_amortised;
					$packets[$i]->billable			= $obj->billable;
					$packets[$i]->work_type			= $obj->work_type;
					$packets[$i]->status			= $obj->packetstatus;

					$packets[$i]->taskref			= $obj->taskref;					
					$packets[$i]->tasklabel			= $obj->tasklabel;
					$packets[$i]->reportid			= $obj->reportid;
					$packets[$i]->reportref			= $obj->reportref;
				}

				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}

		return $packets;
	}

	/**
	 *
	 * @param	int		$packetid					Packet id
	 * @param	int		$billperiod					Billing period in minutes - round up to bill in minimum blocks of time
	 * @param	int		$mode						0=Return sum of hours for packet, !0=Return sum of discounted hours for packet
	 * @return 	int		$packetsum/$packetdiscsum	Sum of times in decimal hours
	 */
//	public function getPacketTimeSum($packetid=0, $billperiod=1, $mode=0)
	public function getPacketTimeSum($packetid=0, $mode=0)
	{
		global $conf;

		if (! $billperiod > 0) return 'BadValueForParameterBillPeriod';

		$packetsum = 0;
		$packetdiscsum = 0;
		$packetsumarray = array();

		// List of time packets
		$sql = 'SELECT';
		$sql.= ' tt.fk_reportdet, tt.task_duration';
		$sql.= ', wrd.time_block, wrd.discount_percent';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'projet_task_time as tt';
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."wip_reportdet as wrd on wrd.rowid = tt.fk_reportdet";
		$sql.= " WHERE 1=1";
		$sql.= ' AND tt.fk_reportdet = '.$packetid;

		//print $sql;exit;
		dol_syslog(get_class($this)."::getPacketTimeSum", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			// Loop on each record found, so each couple (project id, task id)
			while ($i < $num) {
				$error=0;
				$task_time = $this->db->fetch_object($resql);
				if (! $error) {
					$timeminutes = $task_time->task_duration / 60;
//					$billminutes = ceil($timeminutes / $billperiod) * $billperiod; // Billperiod now deprecated - now storing current billperiod in database table as it may be changed in settings by users at different times
					$billminutes = ceil($timeminutes / $task_time->time_block) * $task_time->time_block;
					$billhours = ceil($billminutes / (60/100))/100;	// includes rounding up to two decimal figures
					$discountedhours = ceil($billhours * (100 - $task_time->discount_percent))/100; // includes rounding up to two decimal figures
	    			$packetsum  += $billhours;
	    			$packetdiscsum  += $discountedhours;
				}
				$i++;
			}
			$this->db->free($resql);
		} else {
			dol_print_error($this->db);
		}

		return ($mode==0?$packetsum:$packetdiscsum);
	}


	/**
	 *
	 * @param	int		$packetid			Packet id
	 * @return 	int		$packetsum			Sum of times in decimal hours
	 */
	public function getPacketTimeSumArray($packetid=0)
	{
		global $conf;

		$packetsum = 0;
		$packetdiscsum = 0;
		$packetsumarray = array();

		// List of time packets
		$sql = 'SELECT';
		$sql.= ' tt.fk_reportdet, tt.task_duration';
		$sql.= ', wrd.time_block, wrd.discount_percent';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'projet_task_time as tt';
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."wip_reportdet as wrd on wrd.rowid = tt.fk_reportdet";
		$sql.= " WHERE 1=1";
		$sql.= ' AND tt.fk_reportdet = '.$packetid;

		//print $sql;exit;
		dol_syslog(get_class($this)."::getPacketTimeSumArray", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			// Loop on each record found, so each couple (project id, task id)
			while ($i < $num) {
				$error=0;
				$task_time = $this->db->fetch_object($resql);
				if (! $error) {
					$timeminutes = $task_time->task_duration / 60;
					$billminutes = ceil($timeminutes / $task_time->time_block) * $task_time->time_block;
					$billhours = ceil($billminutes / (60/100))/100;	// includes rounding up to two decimal figures
					$discountedhours = ceil($billhours * (100 - $task_time->discount_percent))/100; // includes rounding up to two decimal figures
	    			$packetsum  += $billhours;
	    			$packetdiscsum  += $discountedhours;
				}
				$i++;
			}
			$this->db->free($resql);
		} else {
			dol_print_error($this->db);
		}
		$packetsumarray[0] = $packetsum;
		$packetsumarray[1] = $packetdiscsum;

		return $packetsumarray;
	}


	/**
	 *	Update Report Sums
	 *
	 *  @param	Array	$packetsarray	Array of time packets to be updated
	 *  @return	int								<0 if KO, >0 if OK
	 */
	function updateReportSums($packetsarray)
	{
		global $conf,$langs;

		$ret = 0;

		$this->db->begin();
		$packettemp = new ReportDet($this->db);
		$packetslist = implode(", ", $packetsarray);

		/* ============================================================ */
		/*			Update total times for each affected Packet			*/
		/* ============================================================ */

		$i=0;
		//$billperiod = $conf->global->WIP_TIME_BLOCK;	// Time billing block
		$numlines=count($packetsarray);
		for ($i = 0 ; $i < $numlines ; $i++) {
			$packetsumarray = array();
			$packetsumarray = $packettemp->getPacketTimeSumArray($packetsarray[$i]);
			$packetsum = $packetsumarray[0];
			$packetdiscsum = $packetsumarray[1];

			$sql= 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet SET qty = '.$packetsum.', discounted_qty = '.$packetdiscsum.' WHERE rowid = '.$packetsarray[$i];
			$res=$this->db->query($sql);
			if (! $res) {
				setEventMessages($sql, null, 'errors');
				$error++;
			}
		}

		/* ============================================================ */
		/*			Update total times for the affected Report			*/
		/* ============================================================ */

		if (! $error) {
			$sql= 'SELECT DISTINCT ';
			$sql.= ' wr.rowid as reportid';
			$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_report as wr';
			$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'wip_reportdet as wrd ON wrd.fk_report = wr.rowid';
			$sql.= ' WHERE wrd.rowid IN ('.$packetslist.')';

			$res=$this->db->query($sql);
			if (! $res) {
				setEventMessages($sql, null, 'errors');
				$error++;
			} else {
				$i = 0;
				$num = $this->db->num_rows($res);

				// Loop on each record found
				for ($i = 0 ; $i < $num ; $i++) {
					$obj = $this->db->fetch_object($res);
					$reporttmp=new Report($this->db);
					$reportsumarray = array();
					$reportsumarray = $reporttmp->getReportTimeSum($obj->reportid);
					$reportsum = $reportsumarray[0];
					$reportdiscsum = $reportsumarray[1];

					$sql2= 'UPDATE '.MAIN_DB_PREFIX.'wip_report SET amount = '.$reportsum.', discounted_amount = '.$reportdiscsum.' WHERE rowid = '.$obj->reportid;
					$res2=$this->db->query($sql2);
					if (! $res2) {
						setEventMessages($sql2, null, 'errors');
						setEventMessages($sql2, null, 'mesgs');
						$error++;
					}
				}
			//	$this->db->free($res);
			}
		}
		/* ============================================================ */
		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::updateReportSums ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}


	/**
	 * Show in-line card file for a particular Time Packet
	 *
	 * @param	int		$packetid	Packet number
	 * @param   string	$action		Action
	 * @param	int		$selected	Packetid to be edited
	 * @param	string	$mode		Mode 'report_card', 'time' define page being inserted in.
	 * @return	void
	 */
	function packetCard($packetid, $action = '', $selected = 0, $mode = 'report_card')
	{
		global $user, $langs, $conf, $db;
//		global $object, $projectstatic, $taskstatic;

		// Show task line.
		$showline=1;
		$showlineingray=0;

//		$colspan = ($level>0?1:2);
		$userstatic = new User($db);
		$form=new Form($this->db);
		$packet = new ReportDet($this->db);
		$packet->fetch($packetid);

		$trunclabel = 60;	// truncation length
		$sep = ' - ';

		// Line in view mode
		// --------------------------------------------------------------------
		if ($action != 'editline' || $selected != $packetid)
		{
			$wiprowcss = 'wip';
			$wiprowcss.= $packet->direct_amortised == 0 ? 'direct' : 'amortised';
			$wiprowcss.= empty($packet->fk_parent_line) ? 'lev0' : 'lev1';
	
			// --------------------------------------------------------------------
			// ---------------------------  TABLE START ---------------------------
			// --------------------------------------------------------------------
			print '<table class="' . $wiprowcss . '" width="100%">';
	
			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------

			print '<tr class="valigntop ' . $wiprowcss . '">';
			print '<td class="nowrap" colspan = "3">';

			// Packet Ref and Label
			if (! empty($packet->fk_parent_line)) print img_picto('', 'rightarrow');
			print $packet->getNomUrl(1).'&nbsp;';
			print '<strong> - '.dol_htmlentitiesbr($packet->label).'</strong>';
	
			// Show date range
			if (($packet->date_start || $packet->date_end) && $action != 'editline') {
				print '<br><br>';
				if (!empty($packet->fk_parent_line)) print '&nbsp;&nbsp;';
				print '<div class="clearboth nowraponall">'.get_date_range($packet->date_start, $packet->date_end, 'day').'</div>';
			}
	
			// Show parent
			if (($action != 'editline' || $selected != $packetid) && !empty($packet->fk_parent_line)) {
				$parentpkt = new ReportDet($this->db);
				$parentpkt->fetch($packet->fk_parent_line);
				print '<br><br>&nbsp;&nbsp;Child of: '.$parentpkt->getNomUrl(1);
			}
	
			print '</td>';

			print '<td class="nowrap right" colspan = "2" rowspan = "1">';
			// '0'=>'Orphan', '1'=>'In Process', '2'=>'Transmitted', '3'=>'Invoiced')),
			print $packet->getLibStatut(6);
			// Direct / Amortised Status
			print '<br><br>';
			print $packet->getDirectStatut(7);
			// Non-Billable / Billable / Discounted
			print '<br><br>';
			print $packet->getBillableStatut(8);
	
			print '</td>';
			print '</tr>';
	
			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Show Service
			print '<tr class = "valignbottom">';
			print '<td class="titlefieldcreate">Labour Type</td>';

			print '<td class="nowrap" colspan = "2">';
			if ($packet->fk_product > 0)
			{
				$text=''; $description=''; $type=0;
				$type=(! empty($packet->product_type)?$packet->product_type:$packet->fk_product_type);
	
				// Try to enhance type detection using date_start and date_end for free lines where type was not saved.
				if (! empty($packet->date_start)) $type=1; // deprecated
				if (! empty($packet->date_end)) $type=1; // deprecated
	
				$product_static = new Product($db);
				$product_static->fetch($packet->fk_product);
				$label = $product_static->label;
				$text =  $product_static->getNomUrl(1);
				$text.=  (($trunclabel && $product_static->label) ? $sep . dol_trunc($product_static->label, ($trunclabel > 1 ? $trunclabel : 0)) : '');
	
				$description.=(! empty($conf->global->PRODUIT_DESC_IN_FORM)?'':dol_htmlentitiesbr($packet->description));	// Description is shown on popup. Show nothing if already into desc.
	
				$format = $conf->global->MAIN_USE_HOURMIN_IN_DATE_RANGE?'dayhour':'day';
	
				if ($packet->fk_product > 0)
				{
					//echo $form->textwithtooltip($text,$description,3,'','',$i,0,(!empty($packet->fk_parent_line)?img_picto('', 'rightarrow'):''));
					echo $form->textwithtooltip($text,$description,3,'','',$i,0,'');
				}
				else
				{
					if ($type==1) $text = img_object($langs->trans('Service'),'service');
					else $text = img_object($langs->trans('Product'),'product');
	
					if (! empty($packet->label)) {
						$text.= ' <strong>'.$packet->label.'</strong>';
						echo $form->textwithtooltip($text,dol_htmlentitiesbr($packet->description),3,'','',$i,0,(!empty($packet->fk_parent_line)?img_picto('', 'rightarrow'):''));
					} else {
						if (! empty($packet->fk_parent_line)) echo img_picto('', 'rightarrow');
						echo $text.' '.dol_htmlentitiesbr($packet->description);
					}
				}
			}
			print '</td>';

			print '<td class = "nowrap right" colspan = "2">';
			print 'Hourly rate (excl GST): $';
			print number_format($packet->price,2).' p.h.';
			print '</td>';

			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			print '<tr class="valigntop">';
			print '<td class="titlefieldcreate">Work Order</td>';

			// Task
			// **************************************
			$task = new Task($db);
			$task->fetch($packet->fk_task);
	
			$result = '';
			$label = '<u>' . $langs->trans("ShowTask") . '</u>';
			if (! empty($task->ref))
				$label .= '<br><strong>' . $langs->trans('Ref') . ':</strong> ' . $task->ref;
			if (! empty($task->label))
				$label .= '<br><strong>' . $langs->trans('LabelTask') . ':</strong> ' . $task->label;
			if ($task->date_start || $task->date_end)
			{
				$label .= "<br>".get_date_range($task->date_start,$task->date_end,'',$langs,0);
			}

			$url = DOL_URL_ROOT.'/custom/wip/time.php?id='.$task->id.'&withproject=1';

			$linkclose = '';
			if (empty($notooltip))
			{
				if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
				{
					$label=$langs->trans("ShowTask");
					$linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
				}
				$linkclose.= ' title="'.dol_escape_htmltag($label, 1).'"';
				$linkclose.=' class="classfortooltip"';
			}

			$linkstart = '<a href="'.$url.'"';
			$linkstart.=$linkclose.'>';
			$linkend='</a>';

			$picto='projecttask';

			$result .= $linkstart;
			$result .= img_object($label, $picto, 'class="classfortooltip"', 0, 0, 0);
			$result .= $task->ref;
			$result .= $linkend;
			$result.=(($trunclabel && $task->label) ? $sep . dol_trunc($task->label, ($trunclabel > 1 ? $trunclabel : 0)) : '');

			print '<td class="nowrap" colspan = "3">';
			print $result;
			print '</td>';

			// Show Direct Hours
			print '<td class="linecolqty nowrap right">';
			print $langs->trans('DirectHours').' '.(! empty($packet->qty) ? number_format($packet->qty,2).' ':'-.- ');
			print '</td>';
			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Show Discount if applicable
			if ($packet->billable != 1) {
				print '<tr>';					
				print '<td class="right" colspan="5">';
				print $packet->getWorkTypeStatut(9);
				if ($packet->billable == 2) print ' '.number_format($packet->discount_percent,2).'%';
				print '&nbsp;&nbsp;-&nbsp;&nbsp;';
				print $langs->trans('AdjustedHours').' '.(! empty($packet->discounted_qty) ? number_format($packet->discounted_qty,2).' ':'-.- ');
				print '</td></tr>';
			}

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Show last modified details
			print '<tr class = "valignbottom">';
			print '<td class="titlefieldcreate">Last Modified By</td>';

			print '<td class="nowrap" colspan = "4">';
			if ($packet->fk_user_modif)
			{
				$userstatic->id = $packet->fk_user_modif;
				$userstatic->fetch($packet->fk_user_modif);
				print $userstatic->getNomUrl(-1);
			}
//			print '</td>';
//			print '<td class = "nowrap" colspan = "3">';
			print '&nbsp;&nbsp;Modified date: ';
			print dol_print_date($packet->tms,'dayhour', 'tzuser');
			print '</td>';

			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Description
			print '<tr>';
			print '<td colspan = "5">';
			print $langs->trans("Description").':<br><br>';
			print ($txt?' - ':'').dol_htmlentitiesbr($packet->description);
			print '</td>';
			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------

			// Show Concat Times
			$resArray=$this->getReportArray($packetid);
			$num=count($resArray);
			if ($num >0) {
				print '<tr>';
				print '<td colspan = "5">';
				print '<table class = "noborder" width = "100%">';
				if ($mode == 'time')
				{
					print '<tr>';
					print '<td colspan = "4">';
					print dol_htmlentitiesbr($this->getConcatReport($packetid));
					print '</td>';
					print '</tr>';
				}
				else
				{
					for ($i = 0 ; $i < $num ; $i++)
					{
						print '<tr class = "valigntop">';
						print '<td>'.dol_print_date($resArray[$i]['date'],'day').'</td>';
						print '<td class = "minwidth100imp">'.dol_trunc($resArray[$i]['username'],11).'</td>';
						print '<td class = "minwidth75imp right nowrap">'.number_format($resArray[$i]['duration']/3600,2).' hrs</td>';
						//print '<td>'.$resArray[$i]['userid'].'</td>';
						print '<td>'.$resArray[$i]['note'].'</td>';
						print '</tr>';
					}
				}
				print '</table>';
				print '</td></tr>';
			}
		}
		// Line in update mode
		// --------------------------------------------------------------------
		else if (/*$this->statut == 0 && */$action == 'editline' && $selected == $packetid)
		{
			$wiprowcss = 'wipltgreen';

			// --------------------------------------------------------------------
			// ---------------------------  TABLE START ---------------------------
			// --------------------------------------------------------------------
			print '<table class="' . $wiprowcss . '" width="100%">';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------

			print '<tr class="valigntop ' . $wiprowcss . '">';
			print '<td class="nowrap" colspan = "3">';

			// Packet Ref and Label
			if (! empty($packet->fk_parent_line)) print img_picto('', 'rightarrow');
			print $packet->getNomUrl(1).'&nbsp;';
			print '<input type="text" name="packet_label" autofocus class="flat minwidth400 maxwidth100onsmartphone" id="packet_label" maxlength="255" value="'.$packet->label.'">';
			print '</td>';

			print '<td class="nowrap right" colspan = "2" rowspan = "2">';
			// '0'=>'Orphan', '1'=>'In Process', '2'=>'Transmitted', '3'=>'Invoiced')),
			print $packet->getLibStatut(6);
			// Direct / Amortised Status
			print '<br><br>';
			print $langs->trans("Amortised").'  <input type="radio" name="DirectStatut" value="1" ';
			print ($packet->direct_amortised==1?'checked':'').'> '.'&nbsp;&nbsp;';
			print $langs->trans("Direct").'  <input type="radio" name="DirectStatut" value="0" ';
			print ($packet->direct_amortised==0?'checked':'').'>';
			// Non-Billable / Billable / Discounted
			print '<br><br>';
			print $langs->trans("StatusReportDetBillable").'  <input type="radio" name="WorkTypeStatut" value="0" ';
			print ($packet->work_type==0?'checked':'').'>';
			print '<br>'.$langs->trans("StatusReportDetApprenticeDiscount").'  <input type="radio" name="WorkTypeStatut" value="1" ';
			print ($packet->work_type==1?'checked':'').'>';
			print '<br>'.$langs->trans("StatusReportDetEfficiencyDiscount").'  <input type="radio" name="WorkTypeStatut" value="2" ';
			print ($packet->work_type==2?'checked':'').'>';	
			print '<br>'.$langs->trans("StatusReportDetNonBillableRework").'  <input type="radio" name="WorkTypeStatut" value="5" ';
			print ($packet->work_type==5?'checked':'').'>';
			print '<br>'.$langs->trans("StatusReportDetNonBillableGoodwill").'  <input type="radio" name="WorkTypeStatut" value="6" ';
			print ($packet->work_type==6?'checked':'').'>';	
			print '</td>';
			print '</tr>';

			// Update Parent
			print '<tr>';
			print '<td class="titlefieldcreate">Child of Report/Packet</td>';
			print '<td class="nowrap" colspan = "2">';
			$packettmp=new ReportDet($db); 
			print $packettmp->getNomUrl(2).' '; //'reportdet@wip'
			print $packettmp->selectReportPackets($packetid, 0, 0, $packet->fk_report, $packet->fk_parent_line, 'newparentpkt_id', 3,  0,  0, '', 'minwidth400 maxwidth200onsmartphone', '');
			print '</td>';
			print '</tr>';

			// Update Start and End Dates
			print '<tr>';
			print '<td class="titlefieldcreate">Reporting period for packet</td>';

			print '<td colspan = "4">'/*.$langs->trans('ServiceLimitedDuration').'<br>'*/.$langs->trans('From').'  ';
			print $form->selectDate(($packet->date_start?$packet->date_start:''), 'date_start', 0, 0, 1, "updateline", 1, 0);
			print ' '.$langs->trans('to').'  ';
			print $form->selectDate(($packet->date_end?$packet->date_end:''), 'date_end', 0, 0, 1, "updateline", 1, 0);
			print "<br>Dates are optional. Will be printed as either a 'from/to', 'from' or 'to' range as appropriate.";
			print '</td>';
			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Update Service
			print '<tr class = "valignbottom">';
			print '<td class="titlefieldcreate">Labour Type</td>';

			print '<td class="nowrap" colspan = "2">';
			print img_object($langs->trans('ShowService'),'service').'  ';
			$packettmp->selectServices($packet->fk_product, 'serviceid', $conf->global->WIP_SERVICE_CATEGORY, 0, '', '', 0);
			print '</td>';

			print '<td class = "nowrap right" colspan = "2">';
			print 'Hourly rate (excl GST): $';
			print '<input size="1" type="text" class="flat left" name="price" id="price" value="' . $packet->price . '"> p.h.';
			print '</td>';

			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			print '<tr class="valigntop">';
			print '<td class="titlefieldcreate">Work Order</td>';

			// Task
			// **************************************
			$task = new Task($db);
			$task->fetch($packet->fk_task);
	
			$result = '';
			$label = '<u>' . $langs->trans("ShowTask") . '</u>';
			if (! empty($task->ref))
				$label .= '<br><strong>' . $langs->trans('Ref') . ':</strong> ' . $task->ref;
			if (! empty($task->label))
				$label .= '<br><strong>' . $langs->trans('LabelTask') . ':</strong> ' . $task->label;
			if ($task->date_start || $task->date_end)
			{
				$label .= "<br>".get_date_range($task->date_start,$task->date_end,'',$langs,0);
			}

			$url = DOL_URL_ROOT.'/custom/wip/time.php?id='.$task->id.'&withproject=1';

			$linkclose = '';
			if (empty($notooltip))
			{
				if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
				{
					$label=$langs->trans("ShowTask");
					$linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
				}
				$linkclose.= ' title="'.dol_escape_htmltag($label, 1).'"';
				$linkclose.=' class="classfortooltip"';
			}

			$linkstart = '<a href="'.$url.'"';
			$linkstart.=$linkclose.'>';
			$linkend='</a>';

			$picto='projecttask';

			$result .= $linkstart;
			$result .= img_object($label, $picto, 'class="classfortooltip"', 0, 0, 0);
			$result .= $task->ref;
			$result .= $linkend;
			$result.=(($trunclabel && $task->label) ? $sep . dol_trunc($task->label, ($trunclabel > 1 ? $trunclabel : 0)) : '');

			print '<td class="nowrap" colspan = "3">';
			print $result;
			print '</td>';

			// Show Direct Hours
			print '<td class="linecolqty nowrap right">';
			print $langs->trans('DirectHours').' '.(! empty($packet->qty) ? number_format($packet->qty,2).' ':'-.- ');
			print '</td>';
			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Show Discount if applicable
			if (1 == 1) {
				print '<tr>';					
				print '<td class="right" colspan="5">';
				print 'Discount (if appl.) <input size="1" type="text" class="flat right" name="discount_percent" id="discount_percent" value="' . $packet->discount_percent . '"> %';
				print '&nbsp;&nbsp;-&nbsp;&nbsp;';
				print $langs->trans('AdjustedHours').' '.(! empty($packet->discounted_qty) ? number_format($packet->discounted_qty,2).' ':'-.- ');
				print '</td></tr>';
			}

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Show last modified details
			print '<tr class = "valignbottom">';
			print '<td class="titlefieldcreate">Last Modified By</td>';

			print '<td class="nowrap" colspan = "4">';
			if ($packet->fk_user_modif)
			{
				$userstatic->id = $packet->fk_user_modif;
				$userstatic->fetch($packet->fk_user_modif);
				print $userstatic->getNomUrl(-1);
			}
//			print '</td>';
//			print '<td class = "nowrap" colspan = "3">';
			print '&nbsp;&nbsp;Modified date: ';
			print dol_print_date($packet->tms,'dayhour', 'tzuser');
			//			print '<td>'.dol_print_date($resArray[$i]['date'],'dayhour').'</td>';
			print '</td>';

			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Description
			print '<tr>';
			print '<td colspan = "5">';
			print $langs->trans("Description").':<br><br>';

			// Do not allow editing during a situation cycle
			if ($packet->fk_prev_id == null )
			{
				// editor wysiwyg
				require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
				$nbrows=ROWS_4;
				$enable=(isset($conf->global->FCKEDITOR_ENABLE_DETAILS)?$conf->global->FCKEDITOR_ENABLE_DETAILS:0);
				$toolbarname='dolibarr_details';
//				$toolbarname='dolibarr_notes';
				//if (! empty($conf->global->FCKEDITOR_ENABLE_DETAILS_FULL)) $toolbarname='dolibarr_notes';
				$doleditor=new DolEditor('packet_desc',$packet->description,'',340,$toolbarname,'',false,true,$enable,$nbrows,'100%');
				$doleditor->Create();
			} else {
				print '<textarea id="packet_desc" class="flat" name="packet_desc" readonly style="width: 200px; height:80px;">' . $packet->description . '</textarea>';
			}
			print '</td>';
			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------
			// Save Button
			print '<tr>';
			print '<td align="center" colspan="5" valign="middle">';
			print '<br>';
			print '<div class="center">';
			print '<input type="submit" class="button" id="savelinebutton" name="save" value="'.$langs->trans("Save").'">';
			print '&nbsp;';
			print '<input type="submit" class="button" id="cancellinebutton" name="cancel" value="'.$langs->trans("Cancel").'">';

			print '</div>';
			print '<br>';
			print '</td>';
			print '</tr>';

			// -----------------------------  NEW ROW -----------------------------
			// --------------------------------------------------------------------

			// Show Concat Times
			$resArray=$this->getReportArray($packetid);
			$num=count($resArray);
			if ($num >0) {
				print '<tr>';
				print '<td colspan = "5">';
				print '<table class = "noborder" width = "100%">';
				if (1 == 1)
				{
					print '<tr>';
					print '<td colspan = "4">';
					print dol_htmlentitiesbr($this->getConcatReport($packetid));
					print '</td>';
					print '</tr>';
				}
				if (1 == 1)
				{
					for ($i = 0 ; $i < $num ; $i++)
					{
						print '<tr class = "valigntop">';
						print '<td>'.dol_print_date($resArray[$i]['date'],'day').'</td>';
						print '<td class = "minwidth100imp">'.dol_trunc($resArray[$i]['username'],11).'</td>';
						print '<td class = "minwidth75imp right nowrap">'.number_format($resArray[$i]['duration']/3600,2).' hrs</td>';
						//print '<td>'.$resArray[$i]['userid'].'</td>';
						print '<td>'.$resArray[$i]['note'].'</td>';
						print '</tr>';
					}
				}
				print '</table>';
				print '</td></tr>';
			}
		}

		print '</table>';

		// --------------------------------------------------------------------
		// ----------------------------  TABLE END ----------------------------
		// --------------------------------------------------------------------

	}	// End function packetCard


/* Function to concatenate task time notes for packet
 * @param	int		$packetid		Packet id
 * @param	string	$sep			Seperator
 * @return	string	$concat_note
 */   
	public function getConcatReport($packetid, $sep = '<br><br>'){
		global $conf;

		$sql = '';
		$sql.= ' SELECT pttfk_reportdet, GROUP_CONCAT(tnote SEPARATOR "\n") as concat_note';
		$sql.= ' FROM (';
		$sql.= ' SELECT';
		$sql.= ' ptt.fk_reportdet AS pttfk_reportdet';
		$sql.= ', GROUP_CONCAT(ptt.note';
		$sql.= ' ORDER BY ptt.task_datehour ASC';
		$sql.= ' SEPARATOR ". ") as tnote';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'projet_task_time as ptt ';
		$sql.= ' GROUP BY ptt.fk_reportdet, ptt.task_date, ptt.fk_user';
		$sql.= ' ORDER BY ptt.task_date ASC, ptt.fk_user ASC';
		$sql.= ' ) tbl';
		$sql.= ' WHERE 1 = 1';
		$sql.= ' AND tbl.pttfk_reportdet = '.$packetid;
		$sql.= ' GROUP BY tbl.pttfk_reportdet';

		//print $sql;exit;
		dol_syslog(get_class($this)."::getConcatReport", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			$concat_note = dol_htmlentitiesbr($obj->concat_note);
			$this->db->free($resql);
			return $concat_note;
		} else {
			dol_print_error($this->db);
			return -1;
		}
	}		// End function getConcatReport



/* Function to generate array for the resport
 * @param	int			$packetid	Packet id
 * @param	array(int)   $taskarray   return the report only for those tasks
 * @param   string  $sqltail	sql tail after the where
 * @return array()
 */   
	public function getReportArray($packetid){
		global $conf;

		$sql = 'SELECT';
		$sql.= ' usr.rowid as userid';
		$sql.= ', CONCAT(usr.firstname,"  ",usr.lastname) as username';
		$sql.= ', GROUP_CONCAT(ptt.note';
		//$sql.= ' ORDER BY ptt.task_date ASC, ptt.fk_user ASC, ptt.task_datehour ASC';
		$sql.= ' ORDER BY ptt.task_datehour ASC';
		$sql.= ' SEPARATOR ". ") as note';
		$sql.= ', ptt.task_date, SUM(ptt.task_duration) as duration ';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'projet_task_time as ptt ';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'user as usr ON ptt.fk_user= usr.rowid ';   
		$sql.= ' WHERE 1 = 1';
		$sql.= ' AND ptt.fk_reportdet = '.$packetid;
		$sql.= ' GROUP BY ptt.fk_reportdet, ptt.task_date, ptt.fk_user';
		$sql.= ' ORDER BY ptt.task_date ASC, ptt.fk_user ASC';

		//print $sql;exit;
		dol_syslog(get_class($this)."::getReportArray", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$resArray=array();
			$i = 0;
			// Loop on each record found
			while ($i < $num) {
				$error=0;
				$obj = $this->db->fetch_object($resql);
				if (! $error) {
					$resArray[$i]=array(
						'date'		=>$this->db->jdate($obj->task_date),
						'duration'	=>$obj->duration,
						'userid'	=>$obj->userid,
						'username'	=>trim($obj->username),
						//'note'		=>$this->db->escape($obj->note)
						'note'		=>$obj->note
						);
				}
				$i++;
			}
			$this->db->free($resql);
			return $resArray;
		} else {
			dol_print_error($this->db);
			return array();
		}
	}		// End function getReportArray


	/*
	*  Function to generate HTML for the report
	* @param   date	$startDay   start date for the query
	* @param   date	$stopDay   start date for the query
	* @param   string   $mode	   specify the query type
	* @param   
	* @param   string  $sqltail	sql tail after the where
	* @return string   

	  * mode layout PTD project/task /day , PDT, DPT
	  * periodeTitle give a name to the report
	  * timemode show time using day or hours (==0)
	  */
	public function getHTMLreport($short,$periodTitle,$hoursperdays,$reportfriendly=0){
	// HTML buffer
	global $langs;
	$lvl1HTML='';
	$lvl3HTML='';
	$lvl2HTML='';
	// partial totals
	$lvl3Total=0;
	$lvl2Total=0;
	$lvl1Total=0;

	$Curlvl1=0;
	$Curlvl2=0;
	$Curlvl3=0;

	$lvl3Notes="";
	//mode 1, PER USER
	//get the list of user
	//get the list of task per user
	//sum user
	//mode 2, PER TASK
	//list of task
	//list of user per 
	$title=array('projectLabel'=>'Project','date'=>'Day','taskLabel'=>'Tasks','userName'=>'User');
	$titleWidth=array('4'=>'120','7'=>'200');
	$sqltail='';

	$resArray=$this->getReportArray();
	$numTaskTime=count($resArray);


		if($numTaskTime>0) 
		{	   
		   // current

		if(! $reportfriendly)
 		{
		foreach($resArray as $key => $item)
		{
			if($Curlvl1==0){
				$Curlvl1=$key;
				$Curlvl2=$key;
			}
			// reformat date to avoid UNIX time
			$resArray[$key]['date']=dol_print_date($item['date'],'day');
			//add the LVL 2 total when  change detected in Lvl 2 & 1
			if(($resArray[$Curlvl2][$this->lvl2Key]!=$resArray[$key][$this->lvl2Key])
					||($resArray[$Curlvl1][$this->lvl1Key]!=$resArray[$key][$this->lvl1Key]))
			{
				//creat the LVL 2 Title line
				$lvl2HTML.='<tr class="oddeven" align="left"><th></th><th>'
						.$resArray[$Curlvl2][$this->lvl2Title].'</th>';
				// add an empty cell on row if short version (in none short mode there is an additionnal column
				if(!$short)$lvl2HTML.='<th></th>';
				// add the LVL 3 total hours on the LVL 2 title
				$lvl2HTML.='<th>'.formatTime($lvl3Total,0).'</th>';
				// add the LVL 3 total day on the LVL 2 title
				$lvl2HTML.='<th>'.formatTime($lvl3Total,$hoursperdays).'</th><th>';
				if($short){
					$lvl2HTML.=$lvl3Notes;
				}
				$lvl3Notes='';
				$lvl2HTML.='</th></tr>';
				//add the LVL 3 content (details)
				$lvl2HTML.=$lvl3HTML;
				//empty lvl 3 HTML to start anew
				$lvl3HTML='';
				//add the LVL 3 total to LVL3
				$lvl2Total+=$lvl3Total;
				//empty lvl 3 total to start anew
				$lvl3Total=0;
				// save the new lvl2 ref
				$Curlvl2=$key;
				//creat the LVL 1 Title line when lvl 1 change detected
				if(($resArray[$Curlvl1][$this->lvl1Key]!=$resArray[$key][$this->lvl1Key]))
				{
					 //creat the LVL 1 Title line
					$lvl1HTML.='<tr class="oddeven" align="left"><th >'
							.$resArray[$Curlvl1][$this->lvl1Title].'</th><th></th>';
					// add an empty cell on row if short version (in none short mode there is an additionnal column
					if(!$short)$lvl1HTML.='<th></th>';
					$lvl1HTML.='<th>'.formatTime($lvl2Total,0).'</th>';
					$lvl1HTML.='<th>'.formatTime($lvl2Total,$hoursperdays).'</th></th><th></tr>';
					//add the LVL 3 HTML content in lvl1
					$lvl1HTML.=$lvl2HTML;
					 //empty lvl 3 HTML to start anew
					$lvl2HTML='';
					//addlvl 2 total to lvl1
					$lvl1Total+=$lvl2Total;
					//empty lvl 3 total tyo start anew
					$lvl2Total=0;   
					// save the new lvl1 ref
					$Curlvl1=$key;
				}
			}
			// show the LVL 3 only if not short
			if(!$short)
			{
				$lvl3HTML.='<tr class="oddeven" align="left"><th></th><th></th><th>'
					.$resArray[$key][$this->lvl3Title].'</th><th>';
				$lvl3HTML.=formatTime($item['duration'],0).'</th><th>';
				$lvl3HTML.=formatTime($item['duration'],$hoursperdays).'</th><th>';  
				$lvl3HTML.=$resArray[$key]['note'];
				$lvl3HTML.='</th></tr>';
			   /*
				if($hoursperdays==0)
				{
					$lvl3HTML.=date('G:i',mktime(0,0,$resArray[$key]['duration'])).'</th></tr>';
				}else{
					$lvl3HTML.=$resArray[$key]['duration']/3600/$hoursperdays.'</th></tr>';
				}*/
			}else if (!empty ($resArray[$key]['note']))
			{
				$lvl3Notes.="</br>".$resArray[$key]['note'];
			}
			$lvl3Total+=$resArray[$key]['duration'];
		   


		}
	   //handle the last line : print LV1 & LVL 2 title
		//creat the LVL 2 Title line
		$lvl2HTML.='<tr class="oddeven" align="left"><th></th><th>'
				.$resArray[$Curlvl2][$this->lvl2Title].'</th>';
		// add an empty cell on row if short version (in none short mode there is an additionnal column
		if(!$short)$lvl2HTML.='<th></th>';
		// add the LVL 3 total hours on the LVL 2 title
		$lvl2HTML.='<th>'.formatTime($lvl3Total,0).'</th>';
		// add the LVL 3 total day on the LVL 2 title
		$lvl2HTML.='<th>'.formatTime($lvl3Total,$hoursperdays).'</th><th>';
		if($short){
			$lvl2HTML.=$lvl3Notes;
		}
		$lvl2HTML.='</th></tr>';
		//add the LVL 3 content (details)
		$lvl2HTML.=$lvl3HTML;
		//add the LVL 3 total to LVL3
		$lvl2Total+=$lvl3Total;

		//creat the LVL 1 Title line
		$lvl1HTML.='<tr class="oddeven" align="left"><th >'
			  .$resArray[$Curlvl1][$this->lvl1Title].'</th><th></th>';
		// add an empty cell on row if short version (in none short mode there is an additionnal column
		if(!$short)$lvl1HTML.='<th></th>';
		$lvl1HTML.='<th>'.formatTime($lvl2Total,0).'</th>';
		$lvl1HTML.='<th>'.formatTime($lvl2Total,$hoursperdays).'</th></tr>';
		//add the LVL 3 HTML content in lvl1
		$lvl1HTML.=$lvl2HTML;
		//empty lvl 3 HTML to start anew
		$lvl2HTML='';
		//addlvl 2 total to lvl1
		$lvl1Total+=$lvl2Total;
		// make the whole result
		 $HTMLRes='<br><div class="titre">'.$this->name.', '.$periodTitle.'</div>';
		 $HTMLRes.='<table class="noborder" width="100%">';
		 $HTMLRes.='<tr class="liste_titre"><th>'.$langs->trans($title[$this->lvl1Title]).'</th><th>'
				.$langs->trans($title[$this->lvl2Title]).'</th>';
		 $HTMLRes.=(!$short)?'<th>'.$langs->trans($title[$this->lvl3Title]).'</th>':'';
		 $HTMLRes.='<th>'.$langs->trans('Duration').':'.$langs->trans('hours').'</th>';
		 $HTMLRes.='<th>'.$langs->trans('Duration').':'.$langs->trans('Days').'</th><th>'.$langs->trans('Note').'</th></tr>';

		 $HTMLRes.='<tr class="liste_titre">'.((!$short)?'<th></th>':'').'<th colspan=2> TOTAL</th>';
		 $HTMLRes.='<th>'.formatTime($lvl1Total,0).'</th>';
		 $HTMLRes.='<th>'.formatTime($lvl1Total,$hoursperdays).'</th><th></th></tr>';
		$HTMLRes.=$lvl1HTML;
		$HTMLRes.='</table>';
		} // end else reportfiendly
	  } // end is numtasktime




	return $HTMLRes;

	}





}



/**
 * Class ReportDetLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class ReportDetLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
*/