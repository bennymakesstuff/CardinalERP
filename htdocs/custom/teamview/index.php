<?php
//require_once('../main.inc.php');
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");       // For root directory
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php"); // For "custom" directory
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/teamview/class/taches/teamview.class.php');
dol_include_once('/teamview/class/taches/elements_contacts.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';


// print '<link rel="stylesheet" href= "'.DOL_MAIN_URL_ROOT.'/teamview/css/theme.css">';
$langs->load('teamview@teamview');
$langs->loadLangs(array('projects'));
$modname = $langs->trans("Tâches");
$var 				= true;
$form 				= new Form($db);
$formother      	= new FormOther($db);
$teamview 			= new teamview($db);
$elements_contacts 	= new elements_contacts($db);
$userp 				= new User($db);
$tmpuser			= new User($db);
$project 			= new Project($db);

$sortfield 			= ($_GET['sortfield']) ? $_GET['sortfield'] : "date_service";
$sortorder 			= ($_GET['sortorder']) ? $_GET['sortorder'] : "DESC";
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

$filter .= (!empty($srch_year) && $srch_year != -1) ? " AND YEAR(date_service) = ".$srch_year." " : "";

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

$teamview->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);

$morejs  = array("/teamview/js/jquery.slimscroll.min.js","/teamview/js/teamview.js","/includes/jquery/plugins/timepicker/jquery-ui-timepicker-addon.js");

llxHeader(array(), $modname,'','','','',$morejs,0,0);
print_fiche_titre($modname);
// <div>
// <h2 class="title">Pièces jointes</h2>
// <div id="files_joints">
// </div>
// <form method="POST" action="'.$_SERVER["PHP_SELF"].'" id="photos" enctype="multipart/form-data">
// 	<input id="add_photo" type="file" name="photo[]" onchange="change_upload_file(this)"/>
// 	<span class="add_joint" onclick="trigger_upload_file()">Ajouter une pièce jointe…</span>
// 	<br><br>
// </form>
// <br>
// <input class="id_comment" value="" type="hidden" />
// <input class="id_comment" value="" type="hidden" />
// <input id="editornew_cmt" value="edit" type="hidden" />
// </div>
// <hr style="margin: 25px 0 17px;">
print '

<div style="min-height: 45px;">
<div class="projet_choose">';
	if ($user->admin == 0)
		print '<b>'.$langs->trans("Choisir_un_Projet").' :</b> '.$teamview->select_all_projets(0,'projet',0,"rowid","ref",'',true,$user->id);
	else
		print '<b>'.$langs->trans("Choisir_un_Projet").' :</b> '.$teamview->select_all_projets(0,'projet',0,"rowid","ref",'',false,$user->id);
print '</div>';
print '<div class="contacts_projets" id="contacts_projets">';
print '</div>';
print '<div class="filter_taches" id="filter_taches">';
print '<span><input type="text" class="filter_in_tasks" placeholder="'.$langs->trans("Rechercher").'..." onkeyup="filter_tasks_content(this)"/></span>';
print '</div>';
print '</div>';
print '<div style="clear:both;"></div>';

print '<div class="projet_info" id="projet_info">';
print '<div class="quarter" id="etat_projet_">'.$langs->trans("État_du_projet").' : <b><span></span></b></div>';
print '<div class="quarter" id="tier_projet">'.$langs->trans("Tiers").' : <b><span></span></b></div>';
print '<div class="quarter" id="nbr_task_parent">'.$langs->trans("Nombre_des_tâche").' : <b><span></span></b></div>';
print '<div class="quarter" id="dates_begin_end">'.$langs->trans("Période").' : <b><span></span></b></div>';

print '<div style="clear:both;"></div>';
print '</div>';
print '

<div class="todo_content">
	<div class="todo_div columns_ fourth_width" id="ToDo">
		<div class="todo_titre"><b>'.$langs->trans("To_Do").'</b><span class="filter_in_etat" id="nbr_todo"/></span></div>
		<div class="contents">
			<div class="scroll_div">
			</div>
		</div>
	</div>
	<div class="avalide_div columns_ fourth_width" id="EnCours">
		<div class="todo_titre"><b>'.$langs->trans("EnCours").'</b><span class="filter_in_etat" id="nbr_encours"/></span></div>
		<div class="contents">
			<div class="scroll_div">
			</div>
		</div>
	</div>
	<div class="avalide_div columns_ fourth_width" id="AValider">
		<div class="todo_titre"><b>'.$langs->trans("a_Valider").'</b><span class="filter_in_etat" id="nbr_avalider"/></span></div>
		<div class="contents">
			<div class="scroll_div">
			</div>
		</div>
	</div>
	<div class="valide_div columns_ fourth_width" id="Validé">
		<div class="todo_titre"><b>'.$langs->trans("Validé").'</b><span class="filter_in_etat" id="nbr_valider"/></span></div>
		<div class="contents">
			<div class="scroll_div">
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
	        <div class="window-title" id="tache_title">
		        <h2 class="title"></h2>
	        </div>
	        <div>
	    		'.$langs->trans("dans_la_liste").' : <span id="tache_etat"></span>
	    		<span class="tache_avance" style="float: right;">'.$langs->trans("Progression_déclarée").' : <span id="tache_avance" ></span>
	        </div>
	        <div>
		        <button class="createorupdatetask button button_todo" onclick="createorupdatetask();"  style="display:none;">'.$langs->trans("save").'</button>
		        <h3 class="title">'.$langs->trans("Description").'</h3>
	        	<p><textarea id="tache_description" class="" rows="4" onkeyup="slct_etat_change()" placeholder="'.$langs->trans("write_description").'…"></textarea></p>
	        </div>
	        <div style="overflow: auto;">
		        <h3 class="title">'.$langs->trans("Temps_consommé").'</h3>
	        	<span id="tache_temps_consomme"></span>
	        </div>
	        <hr style="margin: 25px 0 17px;">
	        <div>
		        <h3 class="title">'.$langs->trans("Sous-tâches").'</h3>
	        	<div id="sous_taches" style="overflow: auto;">
	        		<form method="POST" action="'.$_SERVER["PHP_SELF"].'" id="form_progress_tasks">
		        		<table id="tablelines" class="noborder" width="100%">
							<thead><tr class="oddeven"><th class="">'.$langs->trans("Réf_Libellé_Tâche").'</th><th class="" align="right">'.$langs->trans("Progression_déclarée").'</th></tr></thead>
							<tbody>
							</tbody>
						</table>
						<div style="text-align:right;margin-top: 12px;">
							<button type="submit" class="button_avanc_tasks button button_save_" onclick="update_avanc_tasks();" disabled="disabled">'.$langs->trans("save").'</button>
						</div>
					</form>
	        	</div>
	        </div>
	        <hr style="margin: 25px 0 17px;">
	        <div>
		        <h3 class="title">'.$langs->trans("addcomment").'</h3>
	        	<p style="margin-bottom: 0;"><textarea id="tache_comment" class="textarea_comment" rows="2" onkeyup="comment_change()" placeholder="'.$langs->trans("Écrivez_un_commentaire").'…"></textarea></p>
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
<style type="text/css">
#s2id_select_projet {
    width: 100% !important;
    max-width: 293px;
}
div.showpopupspan {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    /*background: red;*/
    z-index: 0;
    display: block;
}

.todo_content .columns_ .one_content>h4:not(.showpopupspan) {
    z-index: 25;
    position:  relative;
}
tr.noborder_taskunder {
    background: transparent !important;
}
tr.noborder_taskunder>td {
    border: none !important;
}
th.durre_text_th {
    padding-right: 20px !important;
}
table.times_task_table{
	width:90% !important;
	border-bottom: none !important;
}
table.times_task_table.parent{
	width:100% !important;
}
table.times_task_table .inputs_hm{
	white-space: nowrap;
}
td.show_times_task button.button.button_times_ {
    font-size: 9px;
}
td.show_times_task {
    /*max-width: 75px;*/
}
.ActiveTask, .tr_of_times_in_subtask {
    background: #c3cad8 !important;
}
tr.noborder_taskunder>td {
    padding: 3px;
}
.hover_bkgr_fricc b.name div {
    font-weight: bold !important;
    padding-bottom: 3px !important;
}
</style>

<?php
dol_include_once('/teamview/js/teamview_functions.php');
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	$("form#form_progress_tasks").submit(function(e) {
	    e.preventDefault();
	});
});
</script>
<?php
llxFooter();