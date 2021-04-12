<?php
/* Copyright (C) 2005-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
/* Copyright (C) 2010 Regis Houssin  <regis.houssin@capnetworks.com>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/supplier_order/mod_wip_reportnum.php
 *	\ingroup    project
 *	\brief      File containing the Report numbering model class ReportNum
 */

require_once DOL_DOCUMENT_ROOT .'/core/modules/project/modules_project.php';


/**
 *	Reportnum report numbering model class
 */
class mod_wip_reportnum extends ModeleNumRefProjects
{
	/**
     * Dolibarr version of the loaded document
     * @public string
     */
	public $version = 'dolibarr';		// 'development', 'experimental', 'dolibarr'

	/**
     * @var string Error code (or message)
     */
    public $error = '';

	/**
	 * @var string Nom du modele
	 * @deprecated
	 * @see name
	 */
	public $nom='Reportnum';

	/**
	 * @var string model name
	 */
	public $name='Reportnum';

	public $prefix='PR';


	/**
	 * Constructor
	 */
	function __construct()
	{
	    global $conf;

	    if ((float) $conf->global->MAIN_VERSION_LAST_INSTALL >= 5.0) $this->prefix = 'PR';   // We use code "PR = Project Report"
	}

    /**
     * 	Return description of numbering module
     *
     *  @return     string      Text with description
     */
	function info()
    {
    	global $conf,$langs;

		$langs->load("projects");
		$langs->load("admin");

		$form = new Form($this->db);

		$texte = $langs->trans('GenericNumRefModelDesc')."<br>\n";
		$texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte.= '<input type="hidden" name="action" value="updateMask">';
		$texte.= '<input type="hidden" name="maskconstreport" value="REPORT_REPORTNUM_MASK">';
		$texte.= '<table class="nobordernopadding" width="100%">';

		$tooltip=$langs->trans("GenericMaskCodes",$langs->transnoentities("Project"),$langs->transnoentities("Project"));
		$tooltip.=$langs->trans("GenericMaskCodes2");
		$tooltip.=$langs->trans("GenericMaskCodes3");
		$tooltip.=$langs->trans("GenericMaskCodes4a",$langs->transnoentities("Project"),$langs->transnoentities("Project"));
		$tooltip.=$langs->trans("GenericMaskCodes5");

		// Setting the prefix
		$texte.= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte.= '<td align="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskvalue" value="'.$conf->global->REPORT_REPORTNUM_MASK.'">',$tooltip,1,1).'</td>';

		$texte.= '<td align="left" rowspan="2">&nbsp; <input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';

		$texte.= '</tr>';

		$texte.= '</table>';
		$texte.= '</form>';

		return $texte;
    }

    /**
     * 	Return a sample numbering
     *
     *  @return     string      Example
     */
    function getExample()
    {
    	global $conf,$langs,$mysoc;
    	$old_code_client=$mysoc->code_client;
    	$mysoc->code_client='CCCCCCCCCC';
    	$numExample = $this->getNextValue($mysoc,'');
		$mysoc->code_client=$old_code_client;

		if (! $numExample)
		{
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
    }


	/**
	 *  Test if the numbers already in force in the base do not cause conflicts
	 *  which will prevent this numbering from functioning
	 *
	 *  @return     boolean     false si conflit, true si ok
	 */
	function canBeActivated()
	{
    	global $conf,$langs,$db;

        $coyymm=''; $max='';

		$posindice=8;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
        $sql.= " FROM ".MAIN_DB_PREFIX."wip_report";
		$sql.= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
        $sql.= " AND entity = ".$conf->entity;
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) { $coyymm = substr($row[0],0,6); $max=$row[0]; }
        }
        if (! $coyymm || preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i',$coyymm))
        {
            return true;
        }
        else
        {
			$langs->load("errors");
			$this->error=$langs->trans('ErrorNumRefModel',$max);
            return false;
        }
    }


	/**
	 * 	Return next value
	 *
	 *  @param	Societe		$objsoc		Object third party
	 *  @param  Object		$object		Object
	 *  @return string					Value if OK, 0 if KO
	 */
    function getNextValue($objsoc,$object)
    {
		global $db,$conf;

		require_once DOL_DOCUMENT_ROOT .'/core/lib/functions2.lib.php';

		// We define criteria counter search
		$mask=$conf->global->REPORT_REPORTNUM_MASK;

		if (! $mask)
		{
			$this->error='NotConfigured';
			return 0;
		}
		$date=empty($object->date_creation)?dol_now():$object->date_creation;
		$numFinal=get_next_value($db,$mask,'wip_report','ref','',$objsoc->code_client,$date);

		return  $numFinal;
	}


    /**
     *  Return next reference not yet used as a reference
     *
     *  @param	Societe		$objsoc     Object third party
     *  @param  Object		$object		Object
     *  @return string      			Next not used reference
     */
    function report_get_num($objsoc=0,$object='')
    {
        return $this->getNextValue($objsoc,$object);
    }
}
