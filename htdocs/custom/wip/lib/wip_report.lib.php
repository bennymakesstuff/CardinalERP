<?php
/* Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software: you can redistribute it and/or modify
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
 * \file	lib/wip_report.lib.php
 * \ingroup wip
 * \brief   Library files with common functions for Report
 */

/**
 * Prepare array of tabs for Report
 *
 * @param	Report	$object		Report
 * @return 	array				Array of tabs
 */
function reportPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("wip@wip");

	$h = 0;
	$head = array();

	// Card
	$head[$h][0] = dol_buildpath("/wip/report_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'report_card';
	$h++;

	// Photographs
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

	$projectstatic = new Project($db);
	$result = $projectstatic->fetch($object->fk_project);
	$objref = dol_sanitizeFileName($object->ref);
	$projref = dol_sanitizeFileName($projectstatic->ref);
	$upload_dir = $conf->projet->dir_output.'/'.$projref.'/Images';
//	$upload_dir = $conf->wip->dir_output . "/report/" . dol_sanitizeFileName($object->ref);
	$nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview.*\.png)$'));
	$nbLinks=Link::count($db, $object->element, $object->id);
	$head[$h][0] = dol_buildpath("/wip/report_document.php", 1).'?id='.$object->id.'&withproject=1';
	$head[$h][1] = $langs->trans('Photographs');
	if (($nbFiles+$nbLinks) > 0) $head[$h][1].= ' <span class="badge">'.($nbFiles+$nbLinks).'</span>';
	$head[$h][2] = 'report_document';
	$h++;

	// Prices
	$head[$h][0] = dol_buildpath("/wip/report_price.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Prices");
	$head[$h][2] = 'report_price';
	$h++;

	// Notes
	if (isset($object->fields['note_public']) || isset($object->fields['note_private']))
	{
		$nbNote = 0;
		if (!empty($object->note_private)) $nbNote++;
		if (!empty($object->note_public)) $nbNote++;
		$head[$h][0] = dol_buildpath('/wip/report_note.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
		$head[$h][2] = 'report_note';
		$h++;
	}

	// Events
	$head[$h][0] = dol_buildpath("/wip/report_agenda.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Events");
	$head[$h][2] = 'report_agenda';
	$h++;

	// Events
	$head[$h][0] = dol_buildpath("/wip/report_about.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Instructions");
	$head[$h][2] = 'report_about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@wip:/wip/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@wip:/wip/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'report@wip');

	return $head;
}

/**
 *  Show tab footer of a card.
 *  Note: $object->next_prev_filter can be set to restrict select to find next or previous record by $form->showrefnav.
 *
 *  @param	Object	$object			Object to show
 *  @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
 *  @param	string	$morehtml  		More html content to output just before the nav bar
 *  @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
 *  @param	string	$fieldid   		Nom du champ en base a utiliser pour select next et previous (we make the select max and min on this field). Use 'none' for no prev/next search.
 *  @param	string	$fieldref   	Nom du champ objet ref (object->ref) a utiliser pour select next et previous
 *  @param	string	$morehtmlref  	More html to show after ref
 *  @param	string	$moreparam  	More param to add in nav link url.
 *	@param	int		$nodbprefix		Do not include DB prefix to forge table name
 *	@param	string	$morehtmlleft	More html code to show before ref
 *	@param	string	$morehtmlstatus	More html code to show under navigation arrows
 *  @param  int	 $onlybanner	 Put this to 1, if the card will contains only a banner (this add css 'arearefnobottom' on div)
 *	@param	string	$morehtmlright	More html code to show before navigation arrows
 *  @return	void
 */
function wip_banner_tab($object, $paramid, $morehtml='', $shownav=1, $fieldid='rowid', $fieldref='ref', $morehtmlref='', $moreparam='', $nodbprefix=0, $morehtmlleft='', $morehtmlstatus='', $onlybanner=0, $morehtmlright='')
{
	global $conf, $form, $user, $langs;
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
	$error = 0;

	$maxvisiblephotos=1;
	$showimage=1;
	$entity=(empty($object->entity)?$conf->entity:$object->entity);
	$modulepart='unknown';

	if ($object->element == 'report')		$modulepart='wip_report';
	if ($object->element == 'reportdet')	$modulepart='wip_reportdet';
//	if ($object->element == 'product')		$modulepart='product';

	if ($object->element == 'report')
	{
		$width=80; $cssclass='photoref';
		$showimage=$object->is_photo_available($conf->product->multidir_output[$entity]);
		$maxvisiblephotos=(isset($conf->global->PRODUCT_MAX_VISIBLE_PHOTO)?$conf->global->PRODUCT_MAX_VISIBLE_PHOTO:5);
		if ($conf->browser->layout == 'phone') $maxvisiblephotos=1;
		if ($showimage) $morehtmlleft.='<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('product', $conf->product->multidir_output[$entity],'small',$maxvisiblephotos,0,0,0,$width,0).'</div>';
		else
		{
			if (!empty($conf->global->PRODUCT_NODISPLAYIFNOPHOTO)) {
				$nophoto='';
				$morehtmlleft.='<div class="floatleft inline-block valignmiddle divphotoref"></div>';
			}
			//elseif ($conf->browser->layout != 'phone') {	// Show no photo link
				$nophoto='/custom/wip/img/title_report_264x264.png';
				$morehtmlleft.='<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass?' '.$cssclass:'').'" alt="No photo" border="0"'.($width?' width="'.$width.'"':'').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
			//}
		}
	}
	else
	{
		if ($showimage)
		{
			if ($modulepart != 'unknown')
			{
				$phototoshow='';
				// Check if a preview file is available
				if (in_array($modulepart, array('propal', 'commande', 'facture', 'ficheinter', 'contract', 'supplier_order', 'supplier_proposal', 'supplier_invoice', 'expensereport')) && class_exists("Imagick"))
				{
					$objectref = dol_sanitizeFileName($object->ref);
					$dir_output = (empty($conf->$modulepart->multidir_output[$entity]) ? $conf->$modulepart->dir_output : $conf->$modulepart->multidir_output[$entity]) . "/";
					if (in_array($modulepart, array('invoice_supplier', 'supplier_invoice')))
					{
						$subdir = get_exdir($object->id, 2, 0, 1, $object, $modulepart);
						$subdir.= ((! empty($subdir) && ! preg_match('/\/$/',$subdir))?'/':'').$objectref;		// the objectref dir is not included into get_exdir when used with level=2, so we add it at end
					}
					else
					{
						$subdir = get_exdir($object->id, 0, 0, 1, $object, $modulepart);
					}
					if (empty($subdir)) $subdir = 'errorgettingsubdirofobject';	// Protection to avoid to return empty path

					$filepath = $dir_output . $subdir . "/";

					$file = $filepath . $objectref . ".pdf";
					$relativepath = $subdir.'/'.$objectref.'.pdf';

					// Define path to preview pdf file (preview precompiled "file.ext" are "file.ext_preview.png")
					$fileimage = $file.'_preview.png';			  // If PDF has 1 page
					$fileimagebis = $file.'_preview-0.png';		 // If PDF has more than one page
					$relativepathimage = $relativepath.'_preview.png';

					// Si fichier PDF existe
					if (file_exists($file))
					{
						$encfile = urlencode($file);
						// Conversion du PDF en image png si fichier png non existant
						if ( (! file_exists($fileimage) || (filemtime($fileimage) < filemtime($file)))
						  && (! file_exists($fileimagebis) || (filemtime($fileimagebis) < filemtime($file)))
						   )
						{
							if (empty($conf->global->MAIN_DISABLE_PDF_THUMBS))		// If you experienc trouble with pdf thumb generation and imagick, you can disable here.
							{
								include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
								$ret = dol_convert_file($file, 'png', $fileimage);
								if ($ret < 0) $error++;
							}
						}

						$heightforphotref=70;
						if (! empty($conf->dol_optimize_smallscreen)) $heightforphotref=60;
						// Si fichier png PDF d'1 page trouve
						if (file_exists($fileimage))
						{
							$phototoshow = '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref">';
							$phototoshow.= '<img height="'.$heightforphotref.'" class="photo photowithmargin photowithborder" src="'.DOL_URL_ROOT . '/viewimage.php?modulepart=apercu'.$modulepart.'&amp;file='.urlencode($relativepathimage).'">';
							$phototoshow.= '</div></div>';
						}
						// Si fichier png PDF de plus d'1 page trouve
						elseif (file_exists($fileimagebis))
						{
							$preview = preg_replace('/\.png/','',$relativepathimage) . "-0.png";
							$phototoshow = '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref">';
							$phototoshow.= '<img height="'.$heightforphotref.'" class="photo photowithmargin photowithborder" src="'.DOL_URL_ROOT . '/viewimage.php?modulepart=apercu'.$modulepart.'&amp;file='.urlencode($preview).'"><p>';
							$phototoshow.= '</div></div>';
						}
					}
				}
				else if (! $phototoshow)
				{
					$phototoshow = $form->showphoto($modulepart,$object,0,0,0,'photoref','small',1,0,$maxvisiblephotos);
				}

				if ($phototoshow)
				{
					$morehtmlleft.='<div class="floatleft inline-block valignmiddle divphotoref">';
					$morehtmlleft.=$phototoshow;
					$morehtmlleft.='</div>';
				}
			}

			if (! $phototoshow)	  // Show No photo link (picto of pbject)
			{
				$morehtmlleft.='<div class="floatleft inline-block valignmiddle divphotoref">';
				if ($object->element == 'action')
				{
					$width=80;
					$cssclass='photorefcenter';
					$nophoto=img_picto('', 'title_agenda', '', false, 1);
				}
				else
				{
					$width=14; $cssclass='photorefcenter';
					$picto = $object->picto;
					if ($object->element == 'project' && ! $object->public) $picto = 'project'; // instead of projectpub
					$nophoto=img_picto('', 'object_'.$picto, '', false, 1);
				}
				$morehtmlleft.='<!-- No photo to show -->';
				$morehtmlleft.='<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$modulepart.($cssclass?' '.$cssclass:'').'" alt="No photo" border="0"'.($width?' width="'.$width.'"':'').' src="'.$nophoto.'"></div></div>';

				$morehtmlleft.='</div>';
			}
		}
	}

	if ($object->element == 'product')
	{
		//$morehtmlstatus.=$langs->trans("Status").' ('.$langs->trans("Sell").') ';
		if (! empty($conf->use_javascript_ajax) && $user->rights->produit->creer && ! empty($conf->global->MAIN_DIRECT_STATUS_UPDATE)) {
			$morehtmlstatus.=ajax_object_onoff($object, 'status', 'tosell', 'ProductStatusOnSell', 'ProductStatusNotOnSell');
		} else {
			$morehtmlstatus.='<span class="statusrefsell">'.$object->getLibStatut(5,0).'</span>';
		}
		$morehtmlstatus.=' &nbsp; ';
		//$morehtmlstatus.=$langs->trans("Status").' ('.$langs->trans("Buy").') ';
		if (! empty($conf->use_javascript_ajax) && $user->rights->produit->creer && ! empty($conf->global->MAIN_DIRECT_STATUS_UPDATE)) {
			$morehtmlstatus.=ajax_object_onoff($object, 'status_buy', 'tobuy', 'ProductStatusOnBuy', 'ProductStatusNotOnBuy');
		} else {
			$morehtmlstatus.='<span class="statusrefbuy">'.$object->getLibStatut(5,1).'</span>';
		}
	}
	elseif ($object->element == 'project_task')
	{
		$object->fk_statut = 1;
		if ($object->progress > 0) $object->fk_statut = 2;
		if ($object->progress >= 100) $object->fk_statut = 3;
		$tmptxt=$object->getLibStatut(5);
		$morehtmlstatus.=$tmptxt;		// No status on task
	}
	else { // Generic case
		$tmptxt=$object->getLibStatut(6);
		if (empty($tmptxt) || $tmptxt == $object->getLibStatut(3) || $conf->browser->layout=='phone') $tmptxt=$object->getLibStatut(5);
		$morehtmlstatus.=$tmptxt;
	}
/*
	// Add label
	if ($object->element == 'product' || $object->element == 'report' || $object->element == 'project_task')
	{
		if (! empty($object->label)) $morehtmlref.='<div class="refidno">'.$object->label.'</div>';
	}
	if (method_exists($object, 'getBannerAddress') && $object->element != 'product' && $object->element != 'bookmark' && $object->element != 'ecm_directories' && $object->element != 'ecm_files')
	{
		$morehtmlref.='<div class="refidno">';
		$morehtmlref.=$object->getBannerAddress('refaddress',$object);
		$morehtmlref.='</div>';
	}
	if (! empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && in_array($object->element, array('report', 'societe', 'contact', 'member', 'product')))
	{
		$morehtmlref.='<div style="clear: both;"></div><div class="refidno">';
		$morehtmlref.=$langs->trans("TechnicalID").': '.$object->id;
		$morehtmlref.='</div>';
	}
*/
	
	$fieldref = 'none';
	print '<div class="'.($onlybanner?'arearefnobottom ':'arearef ').'heightref valignmiddle" width="100%">';
	print $form->showrefnav($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, $morehtmlref, $moreparam, $nodbprefix, $morehtmlleft, $morehtmlstatus, $morehtmlright);
	print '</div>';
	print '<div class="underrefbanner clearboth"></div>';
}

/**
 *	Show tab header of a card
 *
 *	@param	array	$links				Array of tabs. Currently initialized by calling a function xxx_admin_prepare_head
 *	@param	string	$active     		Active tab name (document', 'info', 'ldap', ....)
 *	@param  string	$title      		Title
 *	@param  int		$notab				-1 or 0=Add tab header, 1=no tab header. If you set this to 1, using dol_fiche_end() to close tab is not required.
 * 	@param	string	$picto				Add a picto on tab title
 *	@param	int		$pictoisfullpath	If 1, image path is a full path. If you set this to 1, you can use url returned by dol_buildpath('/mymodyle/img/myimg.png',1) for $picto.
 *  @param	string	$morehtmlright		Add more html content on right of tabs title
 *  @param	string	$morecss			More Css
 * 	@return	void
 */
function wip_report_head($links=array(), $active='0', $title='', $notab=0, $picto='', $pictoisfullpath=0, $morehtmlright='', $morecss='')
{
	print wip_get_report_head($links, $active, $title, $notab, $picto, $pictoisfullpath, $morehtmlright, $morecss);
}

/**
 *  Show tab header of a card
 *
 * @param 	string 	$head				Optional head lines
 * @param 	string 	$page_title			HTML page title
 * @param	string	$help_url			Url links to help page
 * 		                            	Syntax is: For a wiki page: EN:EnglishPage|FR:FrenchPage|ES:SpanishPage
 *                                  	For other external page: http://server/url
 * @param	string	$target				Target to use on links
 *
 * @param	string	$tabactive     		Active tab name (document', 'info', 'ldap', ....)
 * @param	string	$tabtitle      		Title
 * @param	int		$withproject		0=no project header, or 1=add project header, 1=no tab header. If you set this to 1, using dol_fiche_end() to close tab is not required.
 *
 * @return	void
 */
function wip_get_report_head($object, $projectstatic, $page_title='', $help_url='', $target='', $tabactive='', $tabtitle='', $withproject)
{
	global $conf, $form, $user, $langs;
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

	llxHeader('', $page_title, $help_url);

	if ($withproject == 1)
	{
		// =================
		// Tabs for project
		// =================

		$head=project_prepare_head($projectstatic);
		dol_fiche_head($head, 'reports', $langs->trans("Project"), -1, ($projectstatic->public?'projectpub':'project'), 0, '', '');

		// =================
		// Project Banner
		// =================
		$param='';
		$linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
		$morehtmlref='<div class="refidno">';
		// Title
		$morehtmlref.=$projectstatic->title;
		// Thirdparty
		if ($projectstatic->thirdparty->id > 0)
		{
			$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $projectstatic->thirdparty->getNomUrl(1, 'project');
		}
		$morehtmlref.='</div>';
		$moreparam='&gotolist=1'; // used to switch to list of projects
		// Define a complementary filter for search of next/prev ref.
		if (! $user->rights->projet->all->lire)
		{
			$objectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,0);
			$projectstatic->next_prev_filter=" rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
		}
		dol_banner_tab($projectstatic, 'project', $linkback, 1, 'ref', 'ref', $morehtmlref, $moreparam);
		print '<div class="underbanner clearboth"></div>';
	}

	// =================
	// Report Card Banner
	// =================

	$head = reportPrepareHead($object);
	$tabtitle=$langs->trans("Instructions");
	$tabpicto=dol_buildpath('/wip/img/object_report.png',1);

	// Tabs for reports
	$tabpicto = dol_buildpath('/wip/img/object_report.png',1);
	dol_fiche_head($head, $tabactive, $tabtitle, -1, $tabpicto, 1, '', 'reposition');

	// ------------------------------------------------------------

	$object->next_prev_filter=" fk_projet = ".$projectstatic->id;

	$morehtmlref='';

	// Project
	if ($withproject == 1)
	{
		if ($object->status == 0) $morehtmlref.= '('.$langs->trans("Prov").') ';
		$morehtmlref.= $object->ref;
		if (!empty($object->label)) $morehtmlref.= ' - '.$object->label;
		$morehtmlref.='<div class="refidno">';
		$morehtmlref.= '<div class="maxwidth500">'.$object->sec1_description.'</div>';
		//$morehtmlref.='<br>';
		//if ($object->date_start || $object->date_end) $morehtmlref.='<div class="clearboth nowraponall">'.get_date_range($object->date_start, $object->date_end, 'day', '' ,0).'</div>';
		if ($object->date_report) $morehtmlref.='Report Date: ' . dol_print_date($object->date_report, 'day');
		$morehtmlref.='</div>';
	} else {
		$morehtmlref.='<div class="refidno">';
		$morehtmlref.=$langs->trans("Project").': ';
		$morehtmlref.=$projectstatic->getNomUrl(1);
		$morehtmlref.='<br>';
		// Third party
		$morehtmlref.=$langs->trans("ThirdParty").': ';
		if (!empty($projectstatic->thirdparty)) $morehtmlref.=$projectstatic->thirdparty->getNomUrl(1);
		$morehtmlref.='</div>';
	}

	wip_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $param);

	print '<div class="underbanner clearboth"></div>';
	//print '</div>';							// instead of dol_fiche_end() to avoid extra carriage return
	//if ($withproject == 1) print '</div>';	// instead of dol_fiche_end() to avoid extra carriage return
}
