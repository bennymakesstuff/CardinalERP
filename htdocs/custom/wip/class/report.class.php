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
 * \file		class/report.class.php
 * \ingroup		wip
 * \brief		This file is a CRUD class file for Report (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
dol_include_once('/wip/core/modules/modules_wipreport.php');
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/wip/class/reportdet.class.php';

/**
 * Class to manage Reports
 */
class Report extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'report';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'wip_report';

	/**
	 * @var int  Does report support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does report support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for report. Must be the part after the 'object_' into object_report.png
	 */
	public $picto = 'report@wip';


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
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object", 'showoncombobox'=>'1',),
		'entity' => array('type'=>'integer', 'label'=>'MulticompanyEntity', 'enabled'=>1, 'visible'=>-1, 'position'=>20, 'notnull'=>1, 'default'=>'1', 'index'=>1,),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'amount' => array('type'=>'double(24,8)', 'label'=>'Amount', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'discounted_amount' => array('type'=>'double(24,8)', 'label'=>'DiscountedAmount', 'enabled'=>1, 'visible'=>1, 'position'=>41, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'fk_project' => array('type'=>'integer:Project:projet/class/project.class.php', 'label'=>'Project', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToProject",),
		'fk_user_author' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>1, 'position'=>60, 'notnull'=>-1,),
		'date_report' => array('type'=>'date', 'label'=>'DateReport', 'enabled'=>1, 'visible'=>1, 'position'=>70, 'notnull'=>-1,),
		'date_planned' => array('type'=>'date', 'label'=>'DateDeliveryPlanned', 'enabled'=>1, 'visible'=>1, 'position'=>71, 'notnull'=>-1,),
		'date_start' => array('type'=>'date', 'label'=>'ReportPeriodStart', 'enabled'=>1, 'visible'=>1, 'position'=>72, 'notnull'=>-1,),
		'date_end' => array('type'=>'date', 'label'=>'ReportPeriodEnd', 'enabled'=>1, 'visible'=>1, 'position'=>73, 'notnull'=>-1,),
		'sec1_title' => array('type'=>'varchar(255)', 'label'=>'Section1Title', 'enabled'=>1, 'visible'=>1, 'position'=>110, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'sec1_description' => array('type'=>'text', 'label'=>'Section2Description', 'enabled'=>1, 'visible'=>-1, 'position'=>111, 'notnull'=>-1,),
		'sec2_title' => array('type'=>'varchar(255)', 'label'=>'Section2Title', 'enabled'=>1, 'visible'=>1, 'position'=>120, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'sec3_title' => array('type'=>'varchar(255)', 'label'=>'Section3Title', 'enabled'=>1, 'visible'=>1, 'position'=>130, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'sec3_description' => array('type'=>'text', 'label'=>'Section3Description', 'enabled'=>1, 'visible'=>-1, 'position'=>131, 'notnull'=>-1,),
		'sec4_title' => array('type'=>'varchar(255)', 'label'=>'Section4Title', 'enabled'=>1, 'visible'=>1, 'position'=>140, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'sec4_description' => array('type'=>'text', 'label'=>'Section4Description', 'enabled'=>1, 'visible'=>-1, 'position'=>141, 'notnull'=>-1,),
		'sec5_title' => array('type'=>'varchar(255)', 'label'=>'Section5Title', 'enabled'=>1, 'visible'=>1, 'position'=>150, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'sec5_description' => array('type'=>'text', 'label'=>'Section5Description', 'enabled'=>1, 'visible'=>-1, 'position'=>151, 'notnull'=>-1,),
		'sec6_title' => array('type'=>'varchar(255)', 'label'=>'Section6Title', 'enabled'=>1, 'visible'=>1, 'position'=>160, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'sec6_description' => array('type'=>'text', 'label'=>'Section6Description', 'enabled'=>1, 'visible'=>-1, 'position'=>161, 'notnull'=>-1,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'visible'=>-1, 'position'=>200, 'notnull'=>-1,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'visible'=>-1, 'position'=>201, 'notnull'=>-1,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'PDFModel', 'enabled'=>1, 'visible'=>1, 'position'=>210, 'notnull'=>-1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserCreate', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>1, 'position'=>1050, 'notnull'=>1, 'default'=>'0', 'index'=>1, 'arrayofkeyval'=>array('-1'=>'Cancel', '0'=>'Draft', '1'=>'Validated', '2'=>'Approved', '3'=>'Transmitted', '4'=>'Partly Invoiced', '5'=>'Invoiced', '6'=>'Administrative')),
	);
	public $rowid;
	public $ref;
	public $entity;
	public $label;
	public $amount;
	public $discounted_amount;
	public $fk_project;
	public $fk_user_author;
	public $date_report;
	public $date_start;
	public $date_end;
	public $sec1_title;
	public $sec1_description;
	public $sec2_title;
	public $sec3_title;
	public $sec3_description;
	public $sec4_title;
	public $sec4_description;
	public $sec5_title;
	public $sec5_description;
	public $sec6_title;
	public $sec6_description;
	public $note_private;
	public $note_public;
	public $model_pdf;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;
	// END MODULEBUILDER PROPERTIES



	// If this object has a subtable with lines

	/**
	 * @var Reportline[]
	 * @var int		Name of subtable line
	 */
	//public $table_element_line = 'reportdet';
	public $table_element_line = 'wip_reportdet';

	/**
	 * @var int		Field with ID of parent key if this field has a parent
	 */
	public $fk_element = 'fk_report';

	/**
	 * @var int		Name of subtable class that manage subtable lines
	 */
	public $class_element_line = 'ReportDet';

	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('reportdet');
	protected $childtables=array('wip_reportdet');

	/**
	 * @var ReportLine[]	Array of subtable lines
	 */
	public $lines = array();


	/**
	 * Report cancelled
	 * '-1'=>'Cancel', '0'=>'Draft', '1'=>'Validated', '2'=>'Approved', '3'=>'Transmitted', '4'=>'Partly Invoiced', '5'=>'Invoiced', '6'=>'Administrative'
	 */
	const STATUS_CANCELLED = -1;
	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated
	 */
	const STATUS_VALIDATED = 1;
	/**
	 * Approveed
	 */
	const STATUS_APPROVED = 2;
	/**
	 * Report sent to customer
	 */
	const STATUS_TRANSMITTED = 3;
	/**
	 * Invoiced partially
	 */
	const STATUS_INVOICED_PARTLY = 4;
	/**
	 * Invoiced completely
	 */
	const STATUS_INVOICED_COMPLETELY = 5;
	/**
	 * Administrative
	 */
	const STATUS_ADMINISTRATIVE = 6;


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
	 * @param  User $user		User that creates
	 * @param  bool $notrigger	false=launch triggers after, true=disable triggers
	 * @return int				<0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone and object into another one
	 *
	 * @param	User	$user	User that creates
	 * @param	int		$fromid	Id of object to clone
	 * @return 	mixed			New object created, <0 if KO
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
	 * @param int		$id		Id object
	 * @param string	$ref	Ref
	 * @return int				<0 if KO, 0 if not found, >0 if OK
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
	 * @return int				<0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines($product_type = null, $product_cat = null)
	{
//		$this->lines=array();

		// Load lines with object ReportLine

		//$result=$this->fetch_lines();
		$this->lines=array();

		$sql = 'SELECT';
		$sql.= ' l.rowid as lineid';
		$sql.= ', l.fk_report';
		$sql.= ', l.fk_task';
		$sql.= ', l.fk_parent_line';
		$sql.= ', l.fk_assoc_line';
		$sql.= ', l.fk_product';
		$sql.= ', l.price';
		$sql.= ', l.product_type';
		$sql.= ', l.ref';
		$sql.= ', l.label';
		$sql.= ', l.date_start';
		$sql.= ', l.date_end';
		$sql.= ', l.description';
		$sql.= ', l.qty';
		$sql.= ', l.discount_percent';
		$sql.= ', l.discounted_qty';
		$sql.= ', l.special_code';
		$sql.= ', l.rang';
		$sql.= ', l.rang_task';
		$sql.= ', l.date_creation';
		$sql.= ', l.tms';
		$sql.= ', l.fk_user_creat';
		$sql.= ', l.fk_user_modif';
		$sql.= ', l.import_key';
		$sql.= ', l.direct_amortised';
		$sql.= ', l.work_type';
		$sql.= ', l.billable';
		$sql.= ', l.status';
		$sql.= ', p.rowid as product_id, p.ref as product_ref, p.label as product_label, p.description as product_desc';

		$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet as l';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON l.fk_product = p.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product as cp ON p.rowid = cp.fk_product';
		$sql.= ' WHERE 1=1';
		$sql.= ' AND l.fk_report = '.$this->id;
		if ($product_type) $sql .= ' AND p.fk_product_type = '.$product_type;
		if ($product_cat) $sql .= ' AND cp.categorie = '.$product_cat;
		$sql.= " ORDER BY l.direct_amortised, l.rang, l.rowid";
		//print $sql;

		dol_syslog(get_class($this)."::fetch get lines", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;

			while ($i < $num)
			{
				$objp						= $this->db->fetch_object($result);

				$line						= new ReportDet($this->db);

				$line->rowid				= $objp->lineid;
				$line->lineid				= $objp->lineid;
				$line->fk_report			= $objp->fk_report;
				$line->fk_task				= $objp->fk_task;
				$line->fk_parent_line		= $objp->fk_parent_line;
				$line->fk_assoc_line		= $objp->fk_assoc_line;
				$line->fk_product			= $objp->fk_product;
				$line->price				= $objp->price;
				$line->product_type			= $objp->product_type;
				$line->ref					= $objp->ref;
				$line->label				= $objp->label;
				$line->date_start			= $objp->date_start;
				$line->date_end				= $objp->date_end;
				$line->description			= $objp->description;
				$line->qty					= $objp->qty;
				$line->discount_percent		= $objp->discount_percent;
				$line->discounted_qty		= $objp->discounted_qty;
				$line->special_code			= $objp->special_code;
				$line->rang					= $objp->rang;
				$line->rang_task			= $objp->rang_task;
				$line->date_creation		= $objp->date_creation;
				$line->tms					= $objp->tms;
				$line->fk_user_creat		= $objp->fk_user_creat;
				$line->fk_user_modif		= $objp->fk_user_modif;
				$line->import_key			= $objp->import_key;
				$line->direct_amortised		= $objp->direct_amortised;
				$line->work_type			= $objp->work_type;
				$line->billable				= $objp->billable;
				$line->status				= $objp->status;

				// Retreive all extrafield
				// fetch optionals attributes and labels
				$line->fetch_optionals();

				$this->lines[$i] = $line;

				$i++;
			}
			$this->db->free($result);

			return $num;
		}
		else
		{
			$this->error=$this->db->error()." sql=".$sql;
			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param	User	$user		User that modifies
	 * @param	bool	$notrigger	false=launch triggers after, true=disable triggers
	 * @return	int					<0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param	User	$user		User that deletes
	 * @param	bool	$notrigger	false=launch triggers after, true=disable triggers
	 * @return	int					<0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Return a link to the object card (with optional picto)
	 *
	 *	@param	int		$withpicto				Include picto (0=No picto, 1=Include picto, 2=Only picto)
	 *	@param	string	$option					'withproject' or ''
	 *  @param	int  	$notooltip				1=Disable tooltip
	 *  @param	string  $morecss				Add more css on link
	 *  @param	int		$save_lastsearch_value	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values when clicking
	 *  @param	string	$mode					Mode 'report', 'time', 'contact', 'note', document' define page to link to.
	 * 	@param	int		$addlabel				0=Default, 1=Add label into string, >1=Add first chars into string
	 *  @param	string	$sep					Separator between ref and label if option addlabel is set
	 *	@return	string							String with URL
	 */
	function getNomUrl($withpicto=0, $option='', $notooltip=0, $morecss='', $save_lastsearch_value=-1, $mode='report_card', $addlabel=0, $sep=' - ')
	{
		global $db, $conf, $langs, $hookmanager;
		global $dolibarr_main_authentication, $dolibarr_main_demo;
		global $menumanager;

		if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

		$result = '';
		$companylink = '';

		$label = '<u>' . $langs->trans("Report") . '</u>';
		if (! empty($this->ref))
			$label.= '<br><b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		if (! empty($this->label))
			$label.= '<br><b>' . $langs->trans('LabelReport') . ':</b> ' . $this->label;
/*
		if ($this->date_start || $this->date_end)
		{
			$label .= "<br>".get_date_range($this->date_start,$this->date_end,'',$langs,0);
		}
*/

		$url = dol_buildpath('/wip/'.$mode.'.php',1).'?id='.$this->id.($option=='withproject'?'&withproject=1':'');

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
				$label=$langs->trans("ShowReport");
				$linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';

			/*
			$hookmanager->initHooks(array('reportdao'));
			$parameters=array('id'=>$this->id);
			$reshook=$hookmanager->executeHooks('getnomurltooltip',$parameters,$this,$action);	// Note that $action and $object may have been modified by some hooks
			if ($reshook > 0) $linkclose = $hookmanager->resPrint;
			*/
		}
		else $linkclose = ($morecss?' class="'.$morecss.'"':'');

		$linkstart = '<a href="'.$url.'"';
		$linkstart.=$linkclose.'>';
		$linkend='</a>';

		$picto=($this->picto?$this->picto:'generic');

		$result .= $linkstart;
		if ($withpicto) $result.=img_object(($notooltip?'':$label), $picto, ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
		if ($withpicto != 2) $result.= $this->ref;
		$result .= $linkend;
		if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action;
		$hookmanager->initHooks(array('reportdao'));
		$parameters=array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook=$hookmanager->executeHooks('getNomUrl',$parameters,$this,$action);	// Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 * Return list of reports for all projects or for one particular project
	 * Sort order is on project, then on position of report, and finally on creation date of report
	 *
	 * @param	int		$projectid			Project id
	 * @param	int		$socid				Third party id
	 * @param	int		$mode				0=Return list of reports and their projects, 1=Return projects and reports if exists
	 * @param	string	$filteronproj		Filter on project ref or label
	 * @param	string	$filteronprojstatus	Filter on project status ('-1'=no filter, '0,1'=Draft+Validated only)
	 * @param	string	$morewherefilter	Add more filter into where SQL request (must start with ' AND ...')
	 * @return 	array						Array of reports
	 */
	public function getReportsArray($projectid=0, $socid=0, $mode=0, $filteronproj='', $filteronprojstatus='-1', $morewherefilter='')
	{
		global $conf;
		$reports = array();

		// List of reports (does not care about permissions. Filtering will be done later)
		$sql = "SELECT ";
		$sql.= " p.rowid as projectid, p.ref as projectref, p.title as plabel, p.public, p.fk_statut as projectstatus,";
		$sql.= " r.rowid as reportid, r.ref as reportref, r.label as rlabel, r.fk_statut as rstatus,";
		$sql.= " r.model_pdf, r.date_report as date_report, r.rang,";
		$sql.= " s.rowid as thirdparty_id, s.nom as thirdparty_name, s.email as thirdparty_email";
		$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON p.fk_soc = s.rowid";
		if ($mode == 0)
		{
			$sql.= " WHERE p.entity IN (".getEntity('project').")";
			$sql.= " AND r.fk_project = p.rowid";
		}
		elseif ($mode == 1)
		{
			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."wip_report as r on r.fk_project = p.rowid";
			$sql.= " WHERE p.entity IN (".getEntity('project').")";
		}
		else return 'BadValueForParameterMode';

		if ($socid)	$sql.= " AND p.fk_soc = ".$socid;
		if ($projectid) $sql.= " AND p.rowid in (".$projectid.")";
		if ($filteronproj) $sql.= natural_search(array("p.ref", "p.title"), $filteronproj);
		if ($filteronprojstatus && $filteronprojstatus != '-1') $sql.= " AND p.fk_statut IN (".$filteronprojstatus.")";
		if ($morewherefilter) $sql.=$morewherefilter;
		$sql.= " ORDER BY p.ref, r.rang, r.date_creation";

		//print $sql;exit;
		dol_syslog(get_class($this)."::getReportsArray", LOG_DEBUG);
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
					$reports[$i] = new Report($this->db);
					$reports[$i]->rowid				= $obj->reportid;
					$reports[$i]->ref				= $obj->reportref;
					$reports[$i]->fk_project		= $obj->projectid;
					$reports[$i]->projectref		= $obj->projectref;
					$reports[$i]->projectlabel		= $obj->plabel;
					$reports[$i]->projectstatus		= $obj->projectstatus;
					$reports[$i]->label				= $obj->rlabel;

					$reports[$i]->model_pdf 		= $obj->model_pdf;
					$reports[$i]->status			= $obj->rstatus;
					$reports[$i]->date_report		= $this->db->jdate($obj->date_report);
					$reports[$i]->rang	   			= $obj->rang;

					$reports[$i]->socid 			= $obj->thirdparty_id;	// For backward compatibility
					$reports[$i]->thirdparty_id		= $obj->thirdparty_id;
					$reports[$i]->thirdparty_name	= $obj->thirdparty_name;
					$reports[$i]->thirdparty_email	= $obj->thirdparty_email;
				}

				$i++;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}

		return $reports;
	}


	/**
	 *
	 * @param	int		$reportid			Packet id
	 * @param	int		$timeperiod			Billing period in minutes - round up to bill in minimum blocks of time
	 * @return 	int		$reportsum			Sum of times in decimal hours
	 */
	public function getReportTimeSum($reportid=0)
	{
		global $conf;
		$reportsumarray = array();

		$sql = 'SELECT SUM(qty) as qtysum, SUM(discounted_qty) as discsum';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet';
		$sql.= " WHERE 1=1";
		$sql.= ' AND fk_report = '.$reportid;

		dol_syslog(get_class($this)."::getReportTimeSum", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$reportsums = $this->db->fetch_object($resql);
			$reportsumarray[0] = $reportsums->qtysum;
			$reportsumarray[1] = $reportsums->discsum;
			$this->db->free($resql);
		} else {
			dol_print_error($this->db);
		}
		return $reportsumarray;
	}


	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode		  0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 				   Label of status
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the status
	 *
	 *  @param	int		$status		Id status
	 *  @param  int		$mode		  0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 				   Label of status
	 */
	function LibStatut($status, $mode=0)
	{
		//if (empty($this->labelstatus))
		//{
			global $langs;
			$langs->load('wip@wip');

			//$this->labelstatus[1] = $langs->trans('Enabled');
			//$this->labelstatus[0] = $langs->trans('Disabled');
			$this->labelstatus[-1]= 'StatusReportCancelled';
			$this->labelstatus[0] = 'StatusReportDraft';
			$this->labelstatus[1] = 'StatusReportValidated';
			$this->labelstatus[2] = 'StatusReportApproved';
			$this->labelstatus[3] = 'StatusReportTransmitted';
			$this->labelstatus[4] = 'StatusReportPartlyInvoiced';	// Some Time Packets in report are not invoiced
			$this->labelstatus[5] = 'StatusReportFullyInvoiced';	// Everything is invoiced
			$this->labelstatus[6] = 'StatusReportAdministrative';
		//}
		$wip_picto10	= dol_buildpath('/wip/img/statut10.png',1);	// purple circle
		$wip_picto11	= dol_buildpath('/wip/img/statut11.png',1);	// purple dot
		$wip_picto12	= dol_buildpath('/wip/img/statut12.png',1);	// purple roundel
		$wip_picto13	= dol_buildpath('/wip/img/statut13.png',1);	// purple piggy bank
		$wip_picto14	= dol_buildpath('/wip/img/statut14.png',1);	// black letter 'A' in circle

		if ($mode == 0)
		{
			return $this->labelstatus[$status];
		}
		elseif ($mode == 1)
		{
			return $this->labelstatus[$status];
		}
		elseif ($mode == 2)
		{
			//if ($status == 1) return img_picto($this->labelstatus[$status],'statut4').' '.$this->labelstatus[$status];
			//if ($status == 0) return img_picto($this->labelstatus[$status],'statut5').' '.$this->labelstatus[$status];

			   if ($status==-1) return img_picto($langs->trans($this->labelstatus[$status]),'statut8').' '.$langs->trans($this->labelstatus[$status]);
			//elseif ($status==0) return img_picto($langs->trans($this->labelstatus[$status]),$wip_picto14, '', 1).' '.$langs->trans($this->labelstatus[$status]); // Tester
			elseif ($status==0) return img_picto($langs->trans($this->labelstatus[$status]),'statut6').' '.$langs->trans($this->labelstatus[$status]);
			elseif ($status==1) return img_picto($langs->trans($this->labelstatus[$status]),'statut3').' '.$langs->trans($this->labelstatus[$status]);
			elseif ($status==2) return img_picto($langs->trans($this->labelstatus[$status]),'statut1').' '.$langs->trans($this->labelstatus[$status]);
			elseif ($status==3) return img_picto($langs->trans($this->labelstatus[$status]),'statut4').' '.$langs->trans($this->labelstatus[$status]);
			elseif ($status==4) return img_picto($langs->trans($this->labelstatus[$status]),$wip_picto10, '', 1).' '.$langs->trans($this->labelstatus[$status]);
			elseif ($status==5) return img_picto($langs->trans($this->labelstatus[$status]),$wip_picto11, '', 1).' '.$langs->trans($this->labelstatus[$status]);
			elseif ($status==6) return img_picto($langs->trans($this->labelstatus[$status]),$wip_picto14, '', 1).' '.$langs->trans($this->labelstatus[$status]);
		}
		elseif ($mode == 3)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5');
		}
		elseif ($mode == 4)
		{
			if ($status == 1) return img_picto($this->labelstatus[$status],'statut4').' '.$this->labelstatus[$status];
			if ($status == 0) return img_picto($this->labelstatus[$status],'statut5').' '.$this->labelstatus[$status];
		}
		elseif ($mode == 5)
		{
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5');
		}
/*
		if ($mode == 6)
		{
			if ($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4');
			if ($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5');
		}
*/
		elseif ($mode == 6)	// '-1'=>'Cancel', '0'=>'Draft', '1'=>'Validated', '2'=>'Approved', '3'=>'Transmitted', '4'=>'Partly Invoiced', '5'=>'Invoiced', '6'=>'Administrative'
		{
			// if($status == 1) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut4');
			// if($status == 0) return $this->labelstatus[$status].' '.img_picto($this->labelstatus[$status],'statut5');
			   if ($status==-1) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),'statut8');
//			elseif ($status==0) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),$wip_picto14, '', 1); // Tester
			elseif ($status==0) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),'statut6');
			elseif ($status==1) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),'statut3');
			elseif ($status==2) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),'statut1');
			elseif ($status==3) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),'statut4');
			elseif ($status==4) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),$wip_picto10, '', 1);
			elseif ($status==5) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),$wip_picto11, '', 1);
			elseif ($status==6) return $langs->trans($this->labelstatus[$status]).' '.img_picto($langs->trans($this->labelstatus[$status]),$wip_picto14, '', 1);
		}
	}


	/**
	 *	Charge les informations d'ordre info dans l'objet commande
	 *
	 *	@param	int		$id	   Id of order
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

				$this->date_creation		= $this->db->jdate($obj->datec);
				$this->date_modification	= $this->db->jdate($obj->datem);
				$this->date_validation		= $this->db->jdate($obj->datev);
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


	/* This is to show array of line of details */


	/**
	 *	Return HTML table for object lines
	 *	TODO Move this into an output class file (htmlline.class.php)
	 *	If lines are into a template, title must also be into a template
	 *	But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
	 *
	 *	@param	string		$action				Action code
	 *	@param	string		$seller				Object of seller third party
	 *	@param	string		$buyer			 	Object of buyer third party
	 *	@param	int			$selected		   	Object line selected
	 *	@param	int			$dateSelector	  	1=Show also date range input fields
	 *	@return	void
	 */
	function printReportLines($action, $seller, $buyer, $selected=0, $dateSelector=0)
	{
		global $conf, $hookmanager, $langs, $user;
		// TODO We should not use global var for this !
		global $inputalsopricewithtax, $usemargins, $disableedit, $disablemove, $disableremove, $outputalsopricetotalwithtax;

		// Define usemargins
		$usemargins=0;
		if (! empty($conf->margin->enabled) && ! empty($this->element) && in_array($this->element,array('facture','propal','commande'))) $usemargins=1;

		$num = count($this->lines);

		// Line extrafield
		require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafieldsline = new ExtraFields($this->db);
		$extralabelslines=$extrafieldsline->fetch_name_optionals_label($this->table_element_line);

		$parameters = array('num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'buyer'=>$buyer,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline);
		$reshook = $hookmanager->executeHooks('printObjectLineTitle', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if (empty($reshook))
		{
			// Title line
			print "<thead>\n";

			print '<tr class="liste_titre nodrag nodrop">';

			// Adds a line numbering column
			if (! empty($conf->global->MAIN_VIEW_LINE_NUMBER)) print '<td class="wiplinecolnum" align="center" width="5">&nbsp;</td>';

			// Description
			print '<td class="linecoldescription">'.$langs->trans('Description').'</td>';

			if ($this->element == 'report' || $this->element == 'supplier_proposal' || $this->element == 'invoice_supplier')
			{
				print '<td class="linerefsupplier"><span id="title_fourn_ref">'.$langs->trans("SupplierRef").'</span></td>';
			}

			// VAT
			print '<td class="linecolvat" align="right" width="80">'.$langs->trans('VAT').'</td>';

			// Price HT
			print '<td class="linecoluht" align="right" width="80">'.$langs->trans('PriceUHT').'</td>';

			// Multicurrency
			if (!empty($conf->multicurrency->enabled) && $this->multicurrency_code != $conf->currency) print '<td class="linecoluht_currency" align="right" width="80">'.$langs->trans('PriceUHTCurrency', $this->multicurrency_code).'</td>';

			if ($inputalsopricewithtax) print '<td align="right" width="80">'.$langs->trans('PriceUTTC').'</td>';

			// Qty
			print '<td class="linecolqty" align="right">'.$langs->trans('Qty').'</td>';

			if($conf->global->PRODUCT_USE_UNITS)
			{
				print '<td class="linecoluseunit" align="left">'.$langs->trans('Unit').'</td>';
			}

			// Reduction short
			print '<td class="linecoldiscount" align="right">'.$langs->trans('ReductionShort').'</td>';

			if ($this->situation_cycle_ref) {
				print '<td class="linecolcycleref" align="right">' . $langs->trans('Progress') . '</td>';
			}

			if ($usemargins && ! empty($conf->margin->enabled) && empty($user->societe_id))
			{
				if (!empty($user->rights->margins->creer))
				{
					if ($conf->global->MARGIN_TYPE == "1")
						print '<td class="linecolmargin1 margininfos" align="right" width="80">'.$langs->trans('BuyingPrice').'</td>';
					else
						print '<td class="linecolmargin1 margininfos" align="right" width="80">'.$langs->trans('CostPrice').'</td>';
				}

				if (! empty($conf->global->DISPLAY_MARGIN_RATES) && $user->rights->margins->liretous)
					print '<td class="linecolmargin2 margininfos" align="right" width="50">'.$langs->trans('MarginRate').'</td>';
				if (! empty($conf->global->DISPLAY_MARK_RATES) && $user->rights->margins->liretous)
					print '<td class="linecolmargin2 margininfos" align="right" width="50">'.$langs->trans('MarkRate').'</td>';
			}

			// Total HT
			print '<td class="linecolht" align="right">'.$langs->trans('TotalHTShort').'</td>';

			// Multicurrency
			if (!empty($conf->multicurrency->enabled) && $this->multicurrency_code != $conf->currency) print '<td class="linecoltotalht_currency" align="right">'.$langs->trans('TotalHTShortCurrency', $this->multicurrency_code).'</td>';

			if ($outputalsopricetotalwithtax) print '<td align="right" width="80">'.$langs->trans('TotalTTCShort').'</td>';

			print '<td class="linecoledit"></td>';  // No width to allow autodim

			print '<td class="linecoldelete" width="10"></td>';

			print '<td class="linecolmove" width="10"></td>';

			if($action == 'selectlines')
			{
				print '<td class="linecolcheckall" align="center">';
				print '<input type="checkbox" class="linecheckboxtoggle" />';
				print '<script type="text/javascript">$(document).ready(function() {$(".linecheckboxtoggle").click(function() {var checkBoxes = $(".linecheckbox");checkBoxes.prop("checked", this.checked);})});</script>';
				print '</td>';
			}

			print "</tr>\n";
			print "</thead>\n";
		}

		$var = true;
		$i	 = 0;

		print "<tbody>\n";
		foreach ($this->lines as $line)
		{
			//Line extrafield
			$line->fetch_optionals();

			//if (is_object($hookmanager) && (($line->product_type == 9 && ! empty($line->special_code)) || ! empty($line->fk_parent_line)))
			if (is_object($hookmanager))   // Old code is commented on preceding line.
			{
				if (empty($line->fk_parent_line))
				{
					$parameters = array('line'=>$line,'var'=>$var,'num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'buyer'=>$buyer,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline);
					$reshook = $hookmanager->executeHooks('printObjectLine', $parameters, $this, $action);	// Note that $action and $object may have been modified by some hooks
				}
				else
				{
					$parameters = array('line'=>$line,'var'=>$var,'num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'buyer'=>$buyer,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline, 'fk_parent_line'=>$line->fk_parent_line);
					$reshook = $hookmanager->executeHooks('printObjectSubLine', $parameters, $this, $action);	// Note that $action and $object may have been modified by some hooks
				}
			}
			if (empty($reshook))
			{
				$this->printObjectLine($action,$line,$var,$num,$i,$dateSelector,$seller,$buyer,$selected,$extrafieldsline);
			}

			$i++;
		}
		print "</tbody>\n";
	}

	/**
	 *
	 *
	 */
	public function showWIPdocuments($modulepart,$modulesubdir,$filedir,$urlsource,$genallowed,$delallowed=0,$modelselected='',$allowgenifempty=1,$forcenomultilang=0,$iconPDF=0,$notused=0,$noform=0,$param='',$title='',$buttonlabel='',$codelang='',$morepicto='',$object=null,$hideifempty=0)
	{
		// Deprecation warning
		if (! empty($iconPDF)) {
			dol_syslog(__METHOD__ . ": passing iconPDF parameter is deprecated", LOG_WARNING);
		}

		global $langs, $conf, $user, $hookmanager;
		global $form;

		if (! is_object($form)) $form=new Form($this->db);

		include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		// For backward compatibility
		if (! empty($iconPDF)) {
			return $this->getDocumentsLink($modulepart, $modulesubdir, $filedir);
		}

		// Add entity in $param if not already exists
		if (!preg_match('/entity\=[0-9]+/', $param)) {
			$param.= 'entity='.(!empty($object->entity)?$object->entity:$conf->entity);
		}

		$hookmanager->initHooks(array('formfile'));

		// Get list of files
		$file_list=null;
		if (! empty($filedir))
		{
			$file_list=dol_dir_list($filedir,'files',0,'','(\.meta|_preview.*.*\.png)$','date',SORT_DESC);
		}
		if ($hideifempty && empty($file_list)) return '';

		$out='';
		$forname='builddoc';
		$headershown=0;
		$showempty=0;
		$i=0;

		$out.= "\n".'<!-- Start show_document -->'."\n";
		//print 'filedir='.$filedir;

		if (preg_match('/massfilesarea_/', $modulepart))
		{
		 $out.='<div id="show_files"><br></div>'."\n";
		 $title=$langs->trans("MassFilesArea").' <a href="" id="togglemassfilesarea" ref="shown">('.$langs->trans("Hide").')</a>';
		 $title.='<script type="text/javascript" language="javascript">
			jQuery(document).ready(function() {
				jQuery(\'#togglemassfilesarea\').click(function() {
				if (jQuery(\'#togglemassfilesarea\').attr(\'ref\') == "shown")
				{
					jQuery(\'#'.$modulepart.'_table\').hide();
					jQuery(\'#togglemassfilesarea\').attr("ref", "hidden");
					jQuery(\'#togglemassfilesarea\').text("('.dol_escape_js($langs->trans("Show")).')");
				}
				else
				{
					jQuery(\'#'.$modulepart.'_table\').show();
					jQuery(\'#togglemassfilesarea\').attr("ref","shown");
					jQuery(\'#togglemassfilesarea\').text("('.dol_escape_js($langs->trans("Hide")).')");
				}
				return false;
				});
			});
			</script>';
		}

		$titletoshow=$langs->trans("Documents");
		if (! empty($title)) $titletoshow=$title;

		// Show table
		if ($genallowed)
		{
		$modellist=array();

		if ($modulepart == 'wip_report')
		{
			if (is_array($genallowed)) $modellist=$genallowed;
			else
			{
				include_once DOL_DOCUMENT_ROOT.'/custom/wip/core/modules/modules_wip_report.php';
				$modellist=ModelePDFWipReports::liste_modeles($this->db);
			}
		}
		else
		{
			dol_print_error($this->db,'Bad value for modulepart');
			return -1;
		}

		// Set headershown to avoid to have table opened a second time later
		$headershown=1;

		$buttonlabeltoshow=$buttonlabel;
		if (empty($buttonlabel)) $buttonlabel=$langs->trans('Generate');

		if ($conf->browser->layout == 'phone') $urlsource.='#'.$forname.'_form';   // So we switch to form after a generation
		if (empty($noform)) $out.= '<form action="'.$urlsource.(empty($conf->global->MAIN_JUMP_TAG)?'':'#builddoc').'" id="'.$forname.'_form" method="post">';
		$out.= '<input type="hidden" name="action" value="builddoc">';
		$out.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

		$out.= load_fiche_titre($titletoshow, '', '');
		$out.= '<div class="div-table-responsive-no-min">';
		$out.= '<table class="liste formdoc noborder" summary="listofdocumentstable" width="100%">';

		$out.= '<tr class="liste_titre">';

		$addcolumforpicto=($delallowed || $printer || $morepicto);
		$out.= '<th align="center" colspan="'.(3+($addcolumforpicto?1:0)).'" class="formdoc liste_titre maxwidthonsmartphone">';

		// Model
		if (! empty($modellist))
		{
			$out.= '<span class="hideonsmartphone">'.$langs->trans('Model').' </span>';
			if (is_array($modellist) && count($modellist) == 1)	// If there is only one element
			{
				$arraykeys=array_keys($modellist);
				$modelselected=$arraykeys[0];
			}
			$out.= $form->selectarray('model', $modellist, $modelselected, $showempty, 0, 0, '', 0, 0, 0, '', 'minwidth100');
			if ($conf->use_javascript_ajax)
			{
				$out.= ajax_combobox('model');
			}
		}
		else
		{
			$out.= '<div class="float">'.$langs->trans("Files").'</div>';
		}

		// Language code (if multilang)
		if (($allowgenifempty || (is_array($modellist) && count($modellist) > 0)) && $conf->global->MAIN_MULTILANGS && ! $forcenomultilang && (! empty($modellist) || $showempty))
		{
			include_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
			$formadmin=new FormAdmin($this->db);
			$defaultlang=$codelang?$codelang:$langs->getDefaultLang();
			$morecss='maxwidth150';
			if ($conf->browser->layout == 'phone') $morecss='maxwidth100';
			$out.= $formadmin->select_language($defaultlang, 'lang_id', 0, 0, 0, 0, 0, $morecss);
		}
		else
		{
			$out.= '&nbsp;';
		}

		// Button
		$genbutton = '<input class="button buttongen" id="'.$forname.'_generatebutton" name="'.$forname.'_generatebutton"';
		$genbutton.= ' type="submit" value="'.$buttonlabel.'"';
		if (! $allowgenifempty && ! is_array($modellist) && empty($modellist)) $genbutton.= ' disabled';
		$genbutton.= '>';
		if ($allowgenifempty && ! is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid')
		{
			$langs->load("errors");
			$genbutton.= ' '.img_warning($langs->transnoentitiesnoconv("WarningNoDocumentModelActivated"));
		}
		if (! $allowgenifempty && ! is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid') $genbutton='';
		if (empty($modellist) && ! $showempty && $modulepart != 'unpaid') $genbutton='';
		$out.= $genbutton;
		$out.= '</th>';

		if (!empty($hookmanager->hooks['formfile']))
		{
			foreach($hookmanager->hooks['formfile'] as $module)
			{
				if (method_exists($module, 'formBuilddocLineOptions')) $out .= '<th></th>';
			}
		}
		$out.= '</tr>';

		// Execute hooks
		$parameters=array('socid'=>(isset($GLOBALS['socid'])?$GLOBALS['socid']:''),'id'=>(isset($GLOBALS['id'])?$GLOBALS['id']:''),'modulepart'=>$modulepart);
		if (is_object($hookmanager))
		{
			$reshook = $hookmanager->executeHooks('formBuilddocOptions',$parameters,$GLOBALS['object']);
			$out.= $hookmanager->resPrint;
		}
	}

	// Get list of files
	if (! empty($filedir))
	{
		$link_list = array();
		if (is_object($object))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
			$link = new Link($this->db);
			$sortfield = $sortorder = null;
			$res = $link->fetchAll($link_list, $object->element, $object->id, $sortfield, $sortorder);
		}

		$out.= '<!-- html.formfile::showdocuments -->'."\n";

		// Show title of array if not already shown
		if ((! empty($file_list) || ! empty($link_list) || preg_match('/^massfilesarea/', $modulepart))
			&& ! $headershown)
		{
			$headershown=1;
			$out.= '<div class="titre">'.$titletoshow.'</div>'."\n";
			$out.= '<div class="div-table-responsive-no-min">';
			$out.= '<table class="noborder" summary="listofdocumentstable" id="'.$modulepart.'_table" width="100%">'."\n";
		}

		// Loop on each file found
		if (is_array($file_list))
		{
			foreach($file_list as $file)
			{
				// Define relative path for download link (depends on module)
				$relativepath=$file["name"];					// Cas general
				if ($modulesubdir) $relativepath=$modulesubdir."/".$file["name"]; // Cas propal, facture...
				if ($modulepart == 'export') $relativepath = $file["name"];	// Other case

				$out.= '<tr class="oddeven">';

				$documenturl = DOL_URL_ROOT.'/document.php';
				if (isset($conf->global->DOL_URL_ROOT_DOCUMENT_PHP)) $documenturl=$conf->global->DOL_URL_ROOT_DOCUMENT_PHP;	// To use another wrapper

				// Show file name with link to download
				$out.= '<td class="minwidth200">';
				$out.= '<a class="documentdownload paddingright" href="'.$documenturl.'?modulepart='.$modulepart.'&amp;file='.urlencode($relativepath).($param?'&'.$param:'').'"';
				$mime=dol_mimetype($relativepath,'',0);
				if (preg_match('/text/',$mime)) $out.= ' target="_blank"';
				$out.= ' target="_blank">';
				$out.= img_mime($file["name"],$langs->trans("File").': '.$file["name"]);
				$out.= dol_trunc($file["name"], 150);
				$out.= '</a>'."\n";
				$out.= $this->showPreview($file,$modulepart,$relativepath,0,$param);
				$out.= '</td>';

				// Show file size
				$size=(! empty($file['size'])?$file['size']:dol_filesize($filedir."/".$file["name"]));
				$out.= '<td align="right" class="nowrap">'.dol_print_size($size,1,1).'</td>';

				// Show file date
				$date=(! empty($file['date'])?$file['date']:dol_filemtime($filedir."/".$file["name"]));
				$out.= '<td align="right" class="nowrap">'.dol_print_date($date, 'dayhour', 'tzuser').'</td>';

				if ($delallowed || $printer || $morepicto)
				{
					$out.= '<td class="right nowraponall">';
					if ($delallowed)
					{
						$tmpurlsource = preg_replace('/#[a-zA-Z0-9_]*$/', '', $urlsource);
						$out.= '<a href="'.$tmpurlsource.(strpos($tmpurlsource,'?')?'&amp;':'?').'action=remove_file&amp;file='.urlencode($relativepath);
						$out.= ($param?'&amp;'.$param:'');
						//$out.= '&modulepart='.$modulepart; // TODO obsolete ?
						//$out.= '&urlsource='.urlencode($urlsource); // TODO obsolete ?
						$out.= '">'.img_picto($langs->trans("Delete"), 'delete').'</a>';
					}
					if ($printer)
					{
						//$out.= '<td align="right">';
						$out.= '<a class="paddingleft" href="'.$urlsource.(strpos($urlsource,'?')?'&amp;':'?').'action=print_file&amp;printer='.$modulepart.'&amp;file='.urlencode($relativepath);
						$out.= ($param?'&amp;'.$param:'');
						$out.= '">'.img_picto($langs->trans("PrintFile", $relativepath),'printer.png').'</a>';
					}
					if ($morepicto)
					{
						$morepicto=preg_replace('/__FILENAMEURLENCODED__/',urlencode($relativepath),$morepicto);
						$out.=$morepicto;
					}
					$out.='</td>';
				}

				if (is_object($hookmanager))
				{
					$parameters=array('socid'=>(isset($GLOBALS['socid'])?$GLOBALS['socid']:''),'id'=>(isset($GLOBALS['id'])?$GLOBALS['id']:''),'modulepart'=>$modulepart,'relativepath'=>$relativepath);
					$res = $hookmanager->executeHooks('formBuilddocLineOptions',$parameters,$file);
					if (empty($res))
					{
						$out.= $hookmanager->resPrint;	// Complete line
						$out.= '</tr>';
					}
					else $out = $hookmanager->resPrint;   // Replace line
					}
				}
				$this->numoffiles++;
			}
			// Loop on each link found
			if (is_array($link_list))
			{
				$colspan=2;

				foreach($link_list as $file)
				{
					$out.='<tr class="oddeven">';
					$out.='<td colspan="'.$colspan.'" class="maxwidhtonsmartphone">';
					$out.='<a data-ajax="false" href="' . $link->url . '" target="_blank">';
					$out.=$file->label;
					$out.='</a>';
					$out.='</td>';
					$out.='<td align="right">';
					$out.=dol_print_date($file->datea,'dayhour');
					$out.='</td>';
					if ($delallowed || $printer || $morepicto) $out.='<td></td>';
					$out.='</tr>'."\n";
				}
				$this->numoffiles++;
			}

			if (count($file_list) == 0 && count($link_list) == 0 && $headershown)
			{
				$out.='<tr><td colspan="'.(3+($addcolumforpicto?1:0)).'" class="opacitymedium">'.$langs->trans("None").'</td></tr>'."\n";
			}
		}

		if ($headershown)
		{
			// Affiche pied du tableau
			$out.= "</table>\n";
			$out.= "</div>\n";
			if ($genallowed)
			{
				if (empty($noform)) $out.= '</form>'."\n";
			}
		}
		$out.= '<!-- End show_document -->'."\n";
		//return ($i?$i:$headershown);
		return $out;
	}



	/**
	 *  Create an intervention document on disk using template defined into WIP_ADDON_PDF
	 *
	 *  @param	string		$modele			force the model to be used ('' by default)
	 *  @param	Translate	$outputlangs	lang object used for translation
	 *  @param	int			$hidedetails	Hide details of lines
	 *  @param	int			$hidedesc		Hide description
	 *  @param	int			$hideref		Hide ref
	 *  @return	int							0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails=0, $hidedesc=0, $hideref=0)
	{
		global $conf,$langs;

		$langs->load("projects");

		if (! dol_strlen($modele)) {
			$modele = 'nodefault';
			if ($this->modelpdf) {
				$modele = $this->modelpdf;
			} elseif (! empty($conf->global->WIP_ADDON_PDF)) {
				$modele = $conf->global->WIP_ADDON_PDF;
			}
		}
		$modelpath = "core/modules/pdf/";
		//$modele = 'muscadet';
		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref);
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


	/**
	 * Show task lines with a particular parent
	 *
	 * @param	string	   	$inc				Line number (start to 0, then increased by recursive call)
	 * @param   string		$parent				Id of parent project to show (0 to show all)
	 * @param   Task[]		$lines				Array of lines
	 * @param   int			$level				Level (start to 0, then increased/decrease by recursive call), or -1 to show all level in order of $lines without the recursive groupment feature.
	 * @param 	string		$var				Color
	 * @param 	int			$showproject		Show project columns
	 * @param	int			$taskrole			Array of roles of user for each tasks
	 * @param	int			$projectsListId		List of id of project allowed to user (string separated with comma)
	 * @param	int			$addordertick		Add a tick to move task
	 * @param   int			$projectidfortotallink	0 or Id of project to use on total line (link to see all time consumed for project)
	 * @param   string		$filterprogresscalc	filter text
	 * @return	void
	 */
//	reportLinesa(			$j, 	0, 			$object->lines,	$level, 	true,	0,				$tasksrole,	$object->id,		1,					$object->id,				$filterprogresscalc)
//	function reportLinesa(	&$inc,	$parent,	&$lines, 		&$level,	$var,	$showproject,	&$taskrole,	$projectsListId='',	$addordertick=0,	$projectidfortotallink=0,	$filterprogresscalc='')
	function reportLinesa($action, $selected = 0, $arrayoftotals, &$inc, $parent, &$lines, &$level, $var, $showproject, $addordertick=0, $direct_amortised=-1)
	{
		global $user, $bc, $langs, $conf, $db;
		global $object, $projectstatic, $taskstatic;
		// We declare counter as global because we want to edit them into recursive call
		global $total_reportlinesa_spent,$total_reportlinesa_planned,$total_reportlinesa_spent_if_planned, $total_reportlinesa_spent_if_billable, $total_reportlinesa_wip, $total_reportlinesa_processed, $total_reportlinesa_billed;

		$form=new Form($this->db);
		$numlines=count($object->lines);

		if ($level == 0)
		{
			$total_reportlinesa_spent=0;
			$total_reportlinesa_planned=0;
			$total_reportlinesa_spent_if_planned=0;
			$total_reportlinesa_spent_if_billable=0;
			$total_reportlinesa_wip=0;
			$total_reportlinesa_processed=0;
			$total_reportlinesa_billed=0;
		}

//		for ($jj = 0 ; $jj < 2 ; $jj++) {
			for ($i = 0 ; $i < $numlines ; $i++) {
//				if ($object->lines[$i]->direct_amortised == $jj) {

					$colspan = 2/*($level>0?1:2)*/;
					if ($parent == 0 && $level >= 0) $level = 0;			  // if $level = -1, we dont' use sublevel recursion, we show all lines

					// Process line
					// print "i:".$i."-".$object->lines[$i]->fk_project.'<br>';

					if ($object->lines[$i]->fk_parent_line == $parent || $level < 0)	   // if $level = -1, we dont' use sublevel recursion, we show all lines
					{
						// Show task line.
						$showline=1;
						$showlineingray=0;
						$wiprowcss = 'wip';
						$wiprowcss.= $object->lines[$i]->direct_amortised == 0 ? 'direct' : 'amortised';
						$wiprowcss.= $level>0?'lev1':'lev0';

						$total_direct				= $arrayoftotals['total_direct'];
						$total_direct_adjusted		= $arrayoftotals['total_direct_adjusted'];
						$total_amortised			= $arrayoftotals['total_amortised'];
						$total_amortised_adjusted	= $arrayoftotals['total_amortised_adjusted'];

						$sql = 'SELECT SUM(wrd.qty) as family_total, SUM(wrd.discounted_qty) as family_adjusted';
						$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet as wrd';
						$sql.= ' WHERE wrd.rowid = '.$object->lines[$i]->rowid.' OR wrd.fk_parent_line = '.$object->lines[$i]->rowid;

						$resql = $db->query($sql);
						if (! $resql) dol_print_error($db);

						$res = $db->fetch_object($resql);

						//$family_total			= $res->family_total;
						$family_adjusted		= $res->family_adjusted;
						//$family_discounted	= $family_total - $family_discounted;
						$family_amortised		= ($total_direct_adjusted == 0 ? 0 : $family_adjusted * $total_amortised_adjusted / $total_direct_adjusted);

						$family_total			= ($family_adjusted + $family_amortised);

						$db->free($resql);

						if ($showline)
						{
							// add html5 elements
							$domData  = ' data-element="'.$object->lines[$i]->element.'"';
							$domData .= ' data-id="'.$object->lines[$i]->rowid.'"';
							$domData .= ' data-qty="'.$object->lines[$i]->qty.'"';
							$domData .= ' data-product_type="'.$object->lines[$i]->product_type.'"';

							$packet = new ReportDet($db);
							$packet->fetch($object->lines[$i]->rowid);

							if (! $object->lines[$i]->direct_amortised == $direct_amortised && level == 0 ) {
								if ($object->lines[$i]->direct_amortised == 0 ) {
									print '<tr class="wipdirectlev0"><td>&nbsp;</td><td colspan="10"><strong>';
									print 'Directly reported Time Packets';
									print '</td></tr>';
								} else {
									print '<tr class="wipamortisedlev0"><td>&nbsp;</td><td colspan="10"><strong>';
									print 'Amortised Time Packets';
									print '</td></tr>';
								}
								$direct_amortised = $object->lines[$i]->direct_amortised;
							}

							//print '<tr class = "valigntop" '.$bc[$var].' id="row-'.$object->lines[$i]->rowid.'">';
							print '<tr class = "valigntop '. $wiprowcss .'" id="row-'.$object->lines[$i]->rowid.'" '.$bcdd[$var].$domData.'>';
							if (! empty($conf->global->MAIN_VIEW_LINE_NUMBER)) {
								$coldisplay++;
								print '<td class="wiplinecolnum" align="center" rowspan="1">'. ($i+1).'</td>';
							}
							$coldisplay++;

							//if ($level>0) print '<td></td>';
							//print '<td class="wiplinecoldescription titlefieldmiddle '/*.($level>0?'wiplev1':'wiplev0')*/.'" colspan="1"><div id="line_'.$object->lines[$i]->rowid.'"></div>';
//							print '<div id="line_'.$object->lines[$i]->rowid.'"></div>';
							print '<td class="wiplinecoldescription titlefieldmiddle '/*.($level>0?'wiplev1':'wiplev0')*/.'" colspan="1"><div id="line_'.$object->lines[$i]->rowid.'"></div>';
// ************************************ 	function packetCard($packetid, $action = '', $selected = 0)
							print $packet->packetCard($object->lines[$i]->rowid, $action, $selected);
// ************************************
							print '</td>';

							// Spacer Row
							print '<td class="wiplinecolspacer maxwidth25 center">&nbsp</td>';

							if ($object->lines[$i]->direct_amortised == 0){
	
								// Subtotal Hours
								print '<td class="wiplinecolht maxwidth50 center wippaddingtopbottom">';
								print (! $level>0 ? number_format($family_adjusted,2) : '&nbsp;');
								print '</td>';
	
								// Amortised Hours
								print '<td class="wiplinecolht maxwidth50 center wippaddingtopbottom">';
								print (! $level>0 ?  number_format($family_amortised,2) : '&nbsp;');
								print '</td>';
	
								// Total Hours
								print '<td class="wiplinecolht maxwidth50 center wippaddingtopbottom">';
								print (! $level>0 ? number_format($family_adjusted + $family_amortised,2) : '&nbsp;');
								print '</td>';
	
								// Price HT ex GST
								print '<td class="wiplinecoluht maxwidth75 center wippaddingtopbottom">';
								print (! $level>0 ? '$'.number_format($object->lines[$i]->price * $family_total) : '&nbsp;');
								print '</td>';
	
								// Price inc GST
								print '<td class="wiplinecoluttc maxwidth75 center wippaddingtopbottom">';
								print (! $level>0 ? '$'.number_format($object->lines[$i]->price * $family_total * 1.1) : '&nbsp;');
								print '</td>';
							} else {
								print '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
							}
	
							$tstatut = 0;
							//if ($tstatut == 0  && ($object_rights->creer) && $action != 'selectlines' ) {
							if ($tstatut == 0  && $action != 'selectlines' ) {
								print '<td class="wiplinecoledit center wippaddingtopbottom">';
								$coldisplay++;
								if (($object->lines[$i]->info_bits & 2) == 2 || ! empty($disableedit)) {
								} else {
									print '<a href="'. $_SERVER["PHP_SELF"].'?id='.$object->lines[$i]->fk_report.'&amp;action=editline&amp;lineid='.$object->lines[$i]->rowid.'#line_'.$object->lines[$i]->rowid.'">';
									echo img_edit();
									print '</a>';
								}
								print '</td>';
								print '<td class="wiplinecolremove center wippaddingtopbottom">';
								$coldisplay++;
								print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->lines[$i]->fk_report . '&amp;action=ask_removeline&amp;lineid=' . $object->lines[$i]->rowid . '">';
									print img_picto($langs->trans('RemovePacket'), 'disable', 'class="marginleftonly"');
									print '</a>';
								/*
								print '</td>';
								print '<td class="wiplinecolmassaction" align="center">';
								$coldisplay++;
								//if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
								//{
									$selected=0;
									//if (in_array($obj->id, $arrayofselected)) $selected=1;
									//print '<input id="cb'.$obj->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->id.'"'.($selected?' checked="checked"':'').'>';
									if (in_array($object->lines[$i]->rowid, $arrayofselected)) $selected=1;
									print '<input id="cb'.$object->lines[$i]->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$task_time->rowid.'"'.($selected?' checked="checked"':'').'>';
								//}
								*/

								print '</td>';

								if ($num > 1 && empty($conf->browser->phone) && ($this->situation_counter == 1 || !$this->situation_cycle_ref) && empty($disablemove)) {
									print '<td class="wiplinecolmove tdlineupdown center">';
									$coldisplay++;
									if ($i > 0) {
										print '<a class="lineupdown" href="'. $_SERVER["PHP_SELF"].'?id='.$object->lines[$i]->fk_report.'&amp;action=up&amp;rowid='.$object->lines[$i]->rowid.'">';
										img_up('default',0,'imgupforline');
										print '</a>';
									}
									if ($i < $num-1) {
										print '<a class="lineupdown" href="'. $_SERVER["PHP_SELF"].'?id='.$object->lines[$i]->fk_report.'&amp;action=down&amp;rowid='.$object->lines[$i]->rowid.'">';
										echo img_down('default',0,'imgdownforline');
										print '</a>';
									}
									print '</td>';
								} else {
									print '<td '. ((empty($conf->browser->phone) && empty($disablemove)) ?' class="wiplinecolmove tdlineupdown wipvtop center"':' class="wiplinecolmove wipvtop center"').'>';
									$coldisplay++;
									print '</td>';
								}
							} else {
								print '<td colspan="3">';
								$coldisplay=$coldisplay+3;
								print '</td>';
							}
							if($action == 'selectlines'){
								print '<td class="wiplinecolcheck center"><input type="checkbox" class="linecheckbox" name="line_checkbox['.($i+1).']" value="'.$object->lines[$i]->rowid.'" ></td>';
							}

							/*
							// Tick to drag and drop
							if ($addordertick)
							{
								print '<td align="center" rowspan="1" class="tdlineupdown hideonsmartphone valigntop">&nbsp;</td>';
							}*/

							print '</tr>';

							if (! $showlineingray) $inc++;

							if ($level >= 0)	// Call sublevels
							{
								$level++;
								if ($object->lines[$i]->rowid) $xxx=$object->reportLinesa($action, $selected, $arrayoftotals, $inc, $object->lines[$i]->rowid, $lines, $level, true, 0, $taskrole, '', 0, $direct_amortised);
								$level--;
							}

							$total_reportlinesa_spent += $object->lines[$i]->duration;
							$total_reportlinesa_planned += $object->lines[$i]->planned_workload;
							if ($object->lines[$i]->planned_workload) $total_reportlinesa_spent_if_planned += $object->lines[$i]->duration;
							if ($object->lines[$i]->billable == 1) $total_reportlinesa_spent_if_billable += $object->lines[$i]->duration;

							$total_reportlinesa_wip += $object->lines[$i]->wip;
							$total_reportlinesa_processed += $object->lines[$i]->processed;
							$total_reportlinesa_billed += $object->lines[$i]->billed;

						}
					}
//				}
//			}
		}

		if (($total_reportlinesa_planned > 0 || $total_reportlinesa_spent > 0) && $level <= 0)
		{
			print '<tr class="liste_total nodrag nodrop">';
			print '<td class="liste_total">'.$langs->trans("Total").'</td>';
			if ($showproject) print '<td></td><td></td>';
			print '<td></td>';
			print '<td></td>';
			print '<td></td>';
			print '<td></td>';
			print '<td align="right" class="nowrap liste_total">';
			if ($total_reportlinesa_planned) print round(100 * $total_reportlinesa_spent / $total_reportlinesa_planned,2).' %';
			print '</td>';
			print '<td align="right" class="nowrap liste_total">';
			print convertSecondToTime($total_reportlinesa_planned, 'allhourmin');
			print '</td>';
			print '<td align="right" class="nowrap liste_total">';
			if ($projectidfortotallink > 0) print '<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?projectid='.$projectidfortotallink.($showproject?'':'&withproject=1').'">';
			print convertSecondToTime($total_reportlinesa_spent, 'allhourmin');
			if ($projectidfortotallink > 0) print '</a>';
			print '</td>';
			print '<td align="right" class="nowrap liste_total">';
			print convertSecondToTime($total_reportlinesa_spent_if_billable, 'allhourmin');
			print '</td>';

			print '<td align="right" class="nowrap liste_total">';
			print convertSecondToTime($total_reportlinesa_wip, 'allhourmin');
			print '</td>';

			print '<td align="right" class="nowrap liste_total">';
			print convertSecondToTime($total_reportlinesa_processed, 'allhourmin');
			print '</td>';

			print '<td align="right" class="nowrap liste_total">';
			print convertSecondToTime($total_reportlinesa_billed, 'allhourmin');
			print '</td>';

			print '<td></td>';	// Invoice
			// Contacts of task
			if (! empty($conf->global->PROJECT_SHOW_CONTACTS_IN_LIST))
			{
				print '<td></td>';
			}
			if ($addordertick) print '<td class="hideonsmartphone"></td>';
			print '</tr>';
		}

		return $inc;
	}

	/**
	 *  Return if at least one photo is available
	 *
	 *  @param	  string		$sdir	   Directory to scan
	 *  @return	 boolean	 			True if at least one photo is available, False if not
	 */
	function is_photo_available($sdir)
	{
		include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
		include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';

		global $conf;

		$dir = $sdir;
		if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO)) $dir .= '/'. get_exdir($this->id,2,0,0,$this,'product') . $this->id ."/photos/";
		else $dir .= '/'.get_exdir(0,0,0,0,$this,'product').dol_sanitizeFileName($this->ref).'/';

		$nbphoto=0;

		$dir_osencoded=dol_osencode($dir);
		if (file_exists($dir_osencoded))
		{
			$handle=opendir($dir_osencoded);
			if (is_resource($handle))
			{
				while (($file = readdir($handle)) !== false)
				{
					if (! utf8_check($file)) $file=utf8_encode($file);	// To be sure data is stored in UTF8 in memory
					if (dol_is_file($dir.$file) && image_format_supported($file) > 0) return true;
				}
			}
		}
		return false;
	}

	/**
	 *	Assign a user
	 *
	 *	@param	User	$user				Object user
	 *	@param	int 	$id_assign_user		ID of user assigned
	 *	@param	string 	$field				Field user is to be assigned to
	 *	@param	int 	$notrigger			Disable trigger
	 *	@return   int							<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function assignUser($user, $id_assign_user,  $field = 'fk_user_author', $notrigger = 0)
	{
		global $conf, $langs;

		$this->db->begin();

		$sql = "UPDATE " . MAIN_DB_PREFIX . "wip_report";
		if ($id_assign_user > 0)
		{
			$sql .= " SET ".$field."=".$id_assign_user;
		}
		else
		{
			$sql .= " SET ".$field."=null";
		}
		$sql .= " WHERE rowid = " . $this->id;

		dol_syslog(get_class($this) . "::assignUser sql=" . $sql);
		$resql = $this->db->query($sql);
		if ($resql) {
			if (! $notrigger) {
				// Call trigger
				$result = $this->call_trigger('USER_ASSIGNED', $user);
				if ($result < 0) {
					$error ++;
				}
				// End call triggers
			}

			if (! $error) {
				$this->db->commit();
				return 1;
			} else {
				$this->db->rollback();
				$this->error = join(',', $this->errors);
				dol_syslog(get_class($this) . "::assignUser " . $this->error, LOG_ERR);
				return - 1;
			}
		} else {
			$this->db->rollback();
			$this->error = $this->db->lasterror();
			dol_syslog(get_class($this) . "::assignUser " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 *	Set the date
	 *
	 *	@param	User	$user		Object user making change
	 *	@param	int		$date		Date
	 *	@param	string	$field		Date field to be set
	 * 	@param	int		$notrigger	1=Does not execute triggers, 0= execute triggers
	 *	@return	int					<0 if KO, >0 if OK
	 */
	function set_date($user, $date, $field = 'date_report', $notrigger=0)
	{
		if ($user->rights->wip->write)
		{
			$error=0;

			$this->db->begin();

			$sql = "UPDATE ".MAIN_DB_PREFIX."wip_report";
			$sql.= " SET ".$field." = ".($date ? "'".$this->db->idate($date)."'" : 'null');
			$sql.= " WHERE rowid = ".$this->id." AND status = 0";

			dol_syslog(__METHOD__, LOG_DEBUG);
			$resql=$this->db->query($sql);
			if (!$resql)
			{
				$this->errors[]=$this->db->error();
				$error++;
			}

			if (! $error)
			{
				$this->oldcopy= clone $this;
				$this->date = $date;
			}

			if (! $notrigger && empty($error))
			{
				// Call trigger
				$result=$this->call_trigger('ORDER_MODIFY',$user);
				if ($result < 0) $error++;
				// End call triggers
			}

			if (! $error)
			{
				$this->db->commit();
				return 1;
			}
			else
			{
				foreach($this->errors as $errmsg)
				{
					dol_syslog(__METHOD__.' Error: '.$errmsg, LOG_ERR);
					$this->error.=($this->error?', '.$errmsg:$errmsg);
				}
				$this->db->rollback();
				return -1*$error;
			}
		}
		else
		{
			return -2;
		}
	}


}
