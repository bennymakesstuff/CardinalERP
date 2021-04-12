<?php
/* Copyright (C) 2019 Peter Roberts <peter.roberts@finchmc.com.au>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file	wip/class/actions_wip.class.php
 * \ingroup wip
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

dol_include_once('/wip/class/report.class.php');

/**
 * Class ActionsWIP
 */
class ActionsWIP
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;
	/**
	 * @var string Error
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db	  Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject	$object		 The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action	  	'add', 'update', 'view'
	 * @return	int		 					<0 if KO,
	 *						   				=0 if OK but we want to process standard actions too,
	 *											>0 if OK and we want to replace standard actions.
	 */
	function getNomUrl($parameters,&$object,&$action)
	{
		global $db,$langs,$conf,$user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array		   $parameters	 Hook metadatas (context, etc...)
	 * @param   CommonObject	$object		 The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string		  $action		 Current action (if set). Generally create or edit or null
	 * @param   HookManager	 $hookmanager	Hook manager propagated to allow calling another hook
	 * @return  int							 < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			// Do what you want here...
			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
		}


		if ($action == "edit_author" && GETPOST('btn_edit_author','alpha') && $user->rights->wip->write) {
			$object->fetch(GETPOST('id', 'int'));
			$useroriginassign = $object->fk_user_author;
			$usertoassign = GETPOST('fk_user_author','int');

			if (!$error)
			{
				$ret = $object->assignUser($user, $usertoassign);
				if ($ret < 0) $error++;
			}


			if (! $error)
			{
				setEventMessages($langs->trans('ReportAssigned'), null, 'mesgs');
				header("Location: report_card.php?id=" . $object->id . "&action=view");
				exit;
			} else {
				array_push($this->errors, $object->error);
			}
			$action = 'view';
		}
		elseif ($action == 'setlabel')
		{
			if ($object->fetch(GETPOST('id', 'int'))) {
				$object->label = trim(GETPOST('label', 'alpha'));
				if (empty($object->label)) {
					$mesg .= ($mesg ? '<br>' : '') . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Subject"));
				}
				if (!$mesg) {
					if ($object->update($user) >= 0) {
						header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id);
						exit;
					}
					$mesg = $object->error;
				}
			}
		}
		elseif (in_array($action, array('setdate_report','setdate_planned','setdate_start','setdate_end')))	
		{
			if ($action == 'setdate_report')
			{
				$date	= dol_mktime(0,0,0,$_POST['date_report_month'],$_POST['date_report_day'],$_POST['date_report_year']);
				$field	= 'date_report';
			}
			elseif ($action == 'setdate_planned')
			{
				$date	= dol_mktime(0,0,0,$_POST['date_planned_month'],$_POST['date_planned_day'],$_POST['date_planned_year']);
				$field	= 'date_planned';
			}
			elseif ($action == 'setdate_start')
			{
				$date	= dol_mktime(0,0,0,$_POST['date_start_month'],$_POST['date_start_day'],$_POST['date_start_year']);
				$field	= 'date_start';
			}
			elseif ($action == 'setdate_end')
			{
				$date	= dol_mktime(0,0,0,$_POST['date_end_month'],$_POST['date_end_day'],$_POST['date_end_year']);
				$field	= 'date_end';
			}

			$result = $object->set_date($user, $date, $field);
			$action = 'view';
			if ($result < 0) {
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}

		if (! $error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}


	}


	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array		   $parameters	 Hook metadatas (context, etc...)
	 * @param   CommonObject	$object		 The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string		  $action		 Current action (if set). Generally create or edit or null
	 * @param   HookManager	 $hookmanager	Hook manager propagated to allow calling another hook
	 * @return  int							 < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			foreach($parameters['toselect'] as $objectid)
			{
				// Do action on each object id

			}
		}

		if (! $error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array		   $parameters	 Hook metadatas (context, etc...)
	 * @param   CommonObject	$object		 The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string		  $action		 Current action (if set). Generally create or edit or null
	 * @param   HookManager	 $hookmanager	Hook manager propagated to allow calling another hook
	 * @return  int							 < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			$this->resprints = '<option value="0"'.($disabled?' disabled="disabled"':'').'>'.$langs->trans("WIPMassAction").'</option>';
		}

		if (! $error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters		Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action	 	'add', 'update', 'view'
	 * @return  int 					<0 if KO,
	 *						  		=0 if OK but we want to process standard actions too,
	 *  								>0 if OK and we want to replace standard actions.
	 */
	function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs=$langs;

		$ret=0; $deltemp=array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{

		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters		Array of parameters
	 * @param   Object	$pdfhandler   	PDF builder handler
	 * @param   string	$action	 	'add', 'update', 'view'
	 * @return  int 					<0 if KO,
	 *						  		=0 if OK but we want to process standard actions too,
	 *  								>0 if OK and we want to replace standard actions.
	 */
	function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs=$langs;

		$ret=0; $deltemp=array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1','somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{

		}

		return $ret;
	}

	/* Add here any other hooked methods... */

}
