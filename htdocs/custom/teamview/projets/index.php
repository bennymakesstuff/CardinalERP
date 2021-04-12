<?php
//require_once('../../main.inc.php');
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");       // For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php"); // For "custom" directory
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/teamview/class/taches/teamview.class.php');
dol_include_once('/teamview/class/taches/elements_contacts.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

dol_include_once('/teamview/class/others/p_projets.class.php');
// print '<link rel="stylesheet" href= "'.DOL_MAIN_URL_ROOT.'/teamview/css/theme.css">';
// $langs->load('teamview@teamview');
$langs->load('teamview@teamview');

$modname = $langs->trans("etatprojetclient");
$var 				= true;
$form 				= new Form($db);
$formother      	= new FormOther($db);
$teamview 		= new teamview($db);
$projet 			= new p_projets($db);
$elements_contacts 	= new elements_contacts($db);
$userp 				= new User($db);
$tmpuser			= new User($db);
$project 			= new Project($db);

$sortfield 			= ($_GET['sortfield']) ? $_GET['sortfield'] : "rowid";
$sortorder 			= ($_GET['sortorder']) ? $_GET['sortorder'] : "ASC";

$id 				= $_GET['id'];
$action   			= $_GET['action'];
// print_r($user->rights->projet);
if (!$user->rights->projet->lire) {
	accessforbidden();
}
if (!$user->rights->modteamview->gestion->consulter) {
	accessforbidden();
}

$srch_year     		= GETPOST('srch_year');
$srch_libelle   	= GETPOST('srch_libelle');
$srch_debut     	= GETPOST('srch_debut');
$srch_fin     		= GETPOST('srch_fin');


$filter .= (!empty($srch_year) && $srch_year != -1) ? " AND YEAR(date_service) = ".$srch_year." " : "";
$filter .= (!empty($srch_libelle) && $srch_libelle != -1) ? " AND title like '%".$srch_libelle."%' " : "";


$srch_fin = (!empty($srch_fin)) ? $srch_fin : date('d/m/Y');
$srch_debut = (!empty($srch_debut)) ? $srch_debut : date('d/m/Y', strtotime('-1 years'));

if (!empty($srch_debut)) {
	$debut = explode("/",$srch_debut);
	$debut = $debut[2].'-'.$debut[1].'-'.$debut[0];
	$filter .= " AND DATE(datec) >= '".$debut."'";
}

if($srch_debut != $srch_fin){
	if (!empty($srch_fin)) {
		$fin = explode("/",$srch_fin);
		$fin = $fin[2].'-'.$fin[1].'-'.$fin[0];
		$filter .= " AND DATE(datec) <= '".$fin."'";
	}
}
// echo $filter;
// die();


$nbrtotal = $projet->fetchAll($sortorder, $sortfield, 0, 0, $filter);
// $limit 	= $conf->liste_limit;
$limit = (!empty( $_GET['limit'])) ?  $_GET['limit'] :  $conf->liste_limit;
$page = GETPOST("page");
$page = is_numeric($page) ? $page : 0;
$page = $page == -1 ? 0 : $page;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter") || $page < 0) {
	$filter = "";
	$filter = "";
	$srch_matricule 	= "";
	$srch_type 			= "";
	$srch_date_service 	= "";
	$srch_date_achat 	= "";
	$srch_affectation 	= "";
	$srch_ville 		= "";
	$srch_month 		= "";
	$srch_year 			= "";
}
if (empty($page) || $page == 0){
	$offset = 1;
}




$params = "";
$params .= "&srch_debut=".$srch_debut;
$params .= "&srch_fin=".$srch_fin;
$params .= "&limit=".$limit;


$morejs  = array("/teamview/js/jquery.slimscroll.min.js","/teamview/projets/js/projets.js","/includes/jquery/plugins/timepicker/jquery-ui-timepicker-addon.js");
llxHeader(array(), $modname,'','','','',$morejs,0,0);



// print_fiche_titre($modname);
$projet->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);
$num = count($projet->rows)+1;

print '<form method="get" action="'.$_SERVER["PHP_SELF"].'">'."\n";
// print_barre_liste($modname, $page, $_SERVER["PHP_SELF"], "", $sortfield, $sortorder, "", $num, $nbrtotal);
print_barre_liste($modname, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, "", $num, $nbrtotal,'title_generic.png',0, '', '', $limit, 0, 0);
print '<input type="hidden" value="'.$sortfield.'" id="sortfield_">';
print '<input type="hidden" value="'.$sortorder.'" id="sortorder_">';
print '<input type="hidden" value="'.$limit.'" id="limit_">';
print '<input type="hidden" value="'.$offset.'" id="offset_">';
print '<input type="hidden" value="'.$filter.'" id="filter_">';

print '<div class="filtrage_div">';
print '<table class="border centpercent">';
print '<tr>';
print '<td>';
	print $langs->trans("Date_création");
print '</td>';
print '<td class="filter_debut_fin">';
	print '<span class="debut">'.$langs->trans("Du").'<input id="debut" type="text" name="srch_debut" value="'.$srch_debut.'" class="date_inputs" autocomplete="off"/><button class="dpInvisibleButtons datenowlink" type="button" onclick="dateNowInInput(`debut`)">'.$langs->trans("Maintenant").'</button></span>';
	print '<span class="fin">'.$langs->trans("Au").'<input id="fin" type="text" name="srch_fin" value="'.$srch_fin.'" class="date_inputs" autocomplete="off"/><button class="dpInvisibleButtons datenowlink" type="button" onclick="dateNowInInput(`fin`)">'.$langs->trans("Maintenant").'</button></span>';
print '</td>';
print '<td rowspan="2">';
	print '<input type="submit" value="'.$langs->trans("Rechercher").'" class="butAction" />';
print '</td>';
print '</tr>';
print '<tr>';
print '<td>';
	print $langs->trans("Libellé");
print '</td>';
print '<td>';
	print '<input type="text" name="srch_libelle" value="'.$srch_libelle.'" class="" />';
print '</td>';
print '</tr>';
print '</table>';
print '</div>';
print '</form>';

print '<div class="etat_colors_in_filter">';
print '<label><span class="etat_colors etat_color_blue"></span> '.$langs->trans("Brouillon").'</label>';
print '<label><span class="etat_colors etat_color_green"></span> '.$langs->trans("Ouvert").'</label>';
print '<label><span class="etat_colors etat_color_red"></span> '.$langs->trans("Clôturé").'</label>';
print '</div>';

print '
<div class="todo_content" style="display: flex;">
	<div class="PROSPECTION_div columns_ fifth_width" id="PROSPECTION" data-etat="0" data-opp_status="1">
		<div class="todo_titre"><b>'.$langs->trans("Prospection").'</b><span class="filter_in_etat" id="nbr_PROSPECTION"/></span></div>
		<div class="contents">
			<div class="scroll_div" id="scroll_1">
			</div>
		</div>
	</div>
	<div class="QUALIFICATION_div columns_ fifth_width" id="QUALIFICATION" data-etat="1" data-opp_status="2">
		<div class="todo_titre"><b>'.$langs->trans("Qualification").'</b><span class="filter_in_etat" id="nbr_QUALIFICATION"/></span></div>
		<div class="contents">
			<div class="scroll_div" id="scroll_2">
			</div>
		</div>
	</div>
	<div class="PROPOSITION_div columns_ fifth_width" id="PROPOSITION" data-etat="2" data-opp_status="3">
		<div class="todo_titre"><b>'.$langs->trans("Proposition").'</b><span class="filter_in_etat" id="nbr_PROPOSITION"/></span></div>
		<div class="contents">
			<div class="scroll_div" id="scroll_3">
			</div>
		</div>
	</div>
	<div class="NEGOCIATION_div columns_ fifth_width" id="NEGOCIATION" data-etat="3" data-opp_status="4">
		<div class="todo_titre"><b>'.$langs->trans('Négociation').'</b><span class="filter_in_etat" id="nbr_NEGOCIATION"/></span></div>
		<div class="contents">
			<div class="scroll_div" id="scroll_4">
			</div>
		</div>
	</div>
	<div class="GAGNE_div columns_ fifth_width" id="GAGNE" data-etat="5" data-opp_status="6">
		<div class="todo_titre"><b>'.$langs->trans('Gagné').'</b><span class="filter_in_etat" id="nbr_GAGNE"/></span></div>
		<div class="contents">
			<div class="scroll_div" id="scroll_6">
			</div>
		</div>
	</div>
	<div class="PERDU_div columns_ fifth_width" id="PERDU" data-etat="6" data-opp_status="7">
		<div class="todo_titre"><b>'.$langs->trans('Perdu').'</b><span class="filter_in_etat" id="nbr_PERDU"/></span></div>
		<div class="contents">
			<div class="scroll_div" id="scroll_6">
			</div>
		</div>
	</div>
	<div class="STATUT_INCONNU_div columns_ fifth_width" id="STATUT_INCONNU" data-etat="7" data-opp_status="-1">
		<div class="todo_titre"><b>'.$langs->trans('Statut_inconnu').'</b><span class="filter_in_etat" id="nbr_STATUT_INCONNU"/></span></div>
		<div class="contents">
			<div class="scroll_div" id="scroll_6">
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>
';
print '</div><div class="hover_bkgr_fricc">
    <span class="helper"></span>
    <div class="windows_pop nc_pop">
        <div class="popupCloseButton" changed="no">X</div>
        <div class="window-header">
        	<span class="icon-lg icon-card"></span>
	        <div class="window-title" id="projet_title">
		        <h2 class="title"></h2>
	        </div>
	        <div>
	    		Tiers : <span id="projet_tiers"></span>
	        </div>
         	
	        
	        <hr style="margin: 25px 0 17px;">
	        <div>
		        <h3 class="title">'.$langs->trans("addcomment").'</h3>
	        	<p style="margin-bottom: 0;"><textarea id="projet_comment" class="textarea_comment" rows="2" onkeyup="comment_change()" placeholder="'.$langs->trans("Écrivez_un_commentaire").'…"></textarea></p>
	        	<input class="id_comment" value="" type="hidden" />
	        	<input class="id_comment" value="" type="hidden" />
	        	<input id="editornew_cmt" value="edit" type="hidden" />
	        	<form method="POST" action="'.$_SERVER["PHP_SELF"].'" class="photos" enctype="multipart/form-data" onsubmit="upload_file(this,event)">
					<div class="one_file">
	        			<span class="add_joint" onclick="trigger_upload_file(this)"><i class="fa fa-paperclip"></i></span>
		        		<input class="add_photo" type="file" name="photo[]" onchange="change_upload_file(this)"/>
        			</div>
        			<span class="add_plus" onclick="new_input_joint(this)"><i class="fa fa-plus"></i></span>
        			<div></div>
        			<hr>
        		</form>
		        <button class="comment_btn create_comment button button_save_ disabled" onclick="create_comment(this);" disabled>'.$langs->trans("save").'</button>
		        <br><br>
	        </div>
	        <div>
		        <div id="commentaires">
		        </div>
	        </div>
        </div>
    </div>
</div>';
print '<div id="lightbox" style="display:none;"><div id="content"><img src="" /></div></div>';

// print '</form>';
?>
<!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script> -->
<!-- <script src="http://code.jquery.com/ui/1.8.24/jquery-ui.min.js" type="text/javascript"></script> -->
<style type="text/css">
.scroll_div{
	position: relative;
}
.one_content.class2 {
	left:0 !important;
	top:auto !important;
	position: absolute;
}
.slimScrollDiv, .scroll_div {
    min-height: 490px;
}
.scroll_div.ui-droppable.ui-droppable-active.ui-droppable-hover{
	border: 1px dashed #c8c8c8;
}
.todo_content .columns_ .todo_titre{
	padding: 10px 4px 0;
}
.badges.comments{
	margin:0;
}
.todo_content .columns_ .one_content {
	cursor: all-scroll;
}

/*#PPAYEE .contents,
#RETARD .contents,
#PPAYEE .contents,
#PPAYEE .contents,
#PPAYEE .contents,
#PPAYEE .contents {
	cursor: no-drop;
}*/
</style>

<?php
dol_include_once('/teamview/projets/js/projets_functions.php');
dol_include_once('/teamview/js/all_functions.php');
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	load_all_projets();
	$("form#form_progress_tasks").submit(function(e) {
	    e.preventDefault();
	});
});
</script>

<?php
llxFooter();