<?php

//require_once('../main.inc.php');
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");       // For root directory
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php"); // For "custom" directory
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

dol_include_once('/teamview/class/taches/teamview.class.php');
dol_include_once('/teamview/class/taches/teamview_comments.class.php');
dol_include_once('/teamview/class/taches/taches.class.php');
dol_include_once('/teamview/class/taches/projets.class.php');
dol_include_once('/teamview/class/taches/projets_task_time.class.php');
dol_include_once('/teamview/class/taches/elements_contacts.class.php');

include_once DOL_DOCUMENT_ROOT . "/societe/class/societe.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

$societe = new Societe($db);
$projet = new Project($db);
$projet_own = new projets($db);
$projets_task_time = new projets_task_time($db);
$task = new Task($db);
$taskstatic = new Task($db);

$teamview = new teamview($db);
$comments 	= new teamview_comments($db);
$taches 	= new taches($db);
$tmpuser	= new User($db);
$elements_contacts 	= new elements_contacts($db);

$form 				= new Form($db);

$action 		= $_POST['action'];
$id_projet 		= $_POST['id_projet'];
$id_tache 		= $_POST['id_tache'];

$etats = [
	"ToDo" => $langs->trans("To_Do"),
	"EnCours" => $langs->trans("EnCours"),
	"AValider" => $langs->trans("a_Valider"),
	"Validé" => $langs->trans("Validé")
];


$dircustom = DOL_DOCUMENT_ROOT.'/teamview/';
$customtxt = "";
if (!is_dir($dircustom)) {
	$customtxt = "/custom";
}









// $action = "getallcomments";
if ($action == "getallcomments") {
	$comments->fetchAll("DESC", "created_at", "", "", " AND id_tache = ".$id_tache);
	$content = "";
	
	// print $img;
	if (count($comments->rows) > 0) {
		for ($i=0; $i < count($comments->rows) ; $i++) {
			$item = $comments->rows[$i];
			$tmpuser->fetch($item->id_user);
			$img = $form->showphoto('userphoto',$tmpuser,100);
			$content .= '
				<div class="one_comment" id="comment_'.$item->rowid.'">
					<input type="hidden" class="id_comment" value="'.$item->rowid.'"/>
					<input type="hidden" class="id_user" value="'.$item->id_user.'"/>
					<span class="image" >'.$img.'</span>
					<b class="name" >'.$tmpuser->getNomUrl(0,"",0,1).'</b>
					<span class="cm_created_at">'.$item->created_at.'</span>
					<span class="cm_created_at">';
					if ($item->modified > 0) {
			$content .= '('.$langs->trans("modifié").')';
					}
			$content .='</span>';
					if ($tmpuser->id == $user->id) {
			$content .='<a class="actions_cmt supprimer_cmt" onclick="delete_comment(this);" href="#">'.$langs->trans("Supprimer").'</a>
					<a class="actions_cmt modifier_cmt" onclick="edit_comment(this);" href="#">'.$langs->trans("Modifier").'</a><br>';
					}
			$content .='<div class="commentaire_txt">';
			$content .= nl2br($item->comment);

			$dire = DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$item->rowid.'/';
			if (file_exists($dire)){
			$images = scandir($dire);
			$content .= '<div class="files_joints"><ul class="list_joints">';
			$dire = DOL_MAIN_URL_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$item->rowid.'/';
			foreach ($images as $img) {
			    if (!in_array($img,array(".",".."))) 
			    { 
			        $ext = explode(".", $img);
			        $ext = $ext[count($ext) - 1];
			        $filename = explode("_uplodnc_", $img);
			        $picto = DOL_MAIN_URL_ROOT.$customtxt.'/teamview/images/'.$ext.'.png';
			        $nopicto = DOL_MAIN_URL_ROOT.$customtxt.'/teamview/images/file.png';
			        if ($ext == "pdf") {
			            $content .= '<li>';
			                $content .= '<a target="_blank" href="'.$dire.$img.'" class=""  title="'.$filename[1].'"><img src="'.$picto.'" /></a>';
			            $content .= '</li>';
			        }elseif (strtolower($ext) == "png" || strtolower($ext) == "jpg" || strtolower($ext) == "jpeg") {
			            $content .= '<li class="png">';
			                $content .= '<a href="'.$dire.$img.'" class="lightbox_trigger" onclick="consulter_img(this,event)"  title="'.$filename[1].'"><img src="'.$dire.$img.'" /></a>';
			            $content .= '</li>';
			        }else{
			            $content .= '<li>';
			            if (file_exists(DOL_DOCUMENT_ROOT.$customtxt.'/teamview/images/'.$ext.'.png')) {
			                $content .= '<a href="'.$dire.$img.'" class="" download  title="'.$filename[1].'"><img src="'.$picto.'" /></a>';
			            }else{
			                $content .= '<a href="'.$dire.$img.'" class="" download  title="'.$filename[1].'"><img src="'.$nopicto.'" /></a>';
			            }
			            $content .= '</li>';
			        }
			    }
			}
			$content .= '</ul><div style="clear:both;"></div></div>';
			}
			$content .= '</div>';
			if ($tmpuser->id == $user->id) {
			$content .= '
						<div class="commentaire_txt_input" style="display:none;">
							<textarea class="textarea_comment tache_comment_edit" rows="4" onkeyup="comment_change_edit(this)"></textarea>
							<form method="POST" action="'.$_SERVER["PHP_SELF"].'" class="photos" enctype="multipart/form-data" onsubmit="upload_file(this,event)">';
							$dire = DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$item->rowid.'/';
							if (file_exists($dire)){
							$images = scandir($dire);
							$content .= '<div class="files_joints edit"><ul class="list_joints">';
							$dire = DOL_MAIN_URL_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$item->rowid.'/';
							foreach ($images as $img) {
							    if (!in_array($img,array(".",".."))) 
							    { 
							        $ext = explode(".", $img);
							        $filename = explode("_uplodnc_", $img);
							        $ext = $ext[count($ext) - 1];
							        $picto = DOL_MAIN_URL_ROOT.$customtxt.'/teamview/images/'.$ext.'.png';
							        $nopicto = DOL_MAIN_URL_ROOT.$customtxt.'/teamview/images/file.png';
							        if ($ext == "pdf") {
							            $content .= '<li>';
							                $content .= '<a href="'.$dire.$img.'" datafile="'.$img.'" class="delete_file" onclick="to_delete_file(this,event,'.$item->rowid.')"  title="'.$filename[1].'"><span><i class="fa fa-times"></i></span><img src="'.$picto.'" /></a>';
							            $content .= '</li>';
							        }elseif (strtolower($ext) == "png" || strtolower($ext) == "jpg" || strtolower($ext) == "jpeg") {
							            $content .= '<li class="png">';
							                $content .= '<a href="'.$dire.$img.'" datafile="'.$img.'" class="delete_file" onclick="to_delete_file(this,event,'.$item->rowid.')"  title="'.$filename[1].'"><span><i class="fa fa-times"></i></span><img src="'.$dire.$img.'" /></a>';
							            $content .= '</li>';
							        }else{
							            $content .= '<li>';
							            if (file_exists(DOL_DOCUMENT_ROOT.$customtxt.'/teamview/images/'.$ext.'.png')) {
							                $content .= '<a href="'.$dire.$img.'" datafile="'.$img.'" class="delete_file" onclick="to_delete_file(this,event,'.$item->rowid.')"  title="'.$filename[1].'"><span><i class="fa fa-times"></i></span><img src="'.$picto.'" /></a>';
							            }else{
							                $content .= '<a href="'.$dire.$img.'" datafile="'.$img.'" class="delete_file" onclick="to_delete_file(this,event,'.$item->rowid.')"  title="'.$filename[1].'"><span><i class="fa fa-times"></i></span><img src="'.$nopicto.'" /></a>';
							            }
							            $content .= '</li>';
							        }
							    }
							}
							$content .= '</ul><div style="clear:both;"></div>';
							$content .= '<input type="hidden" name="files_deleted" class="files_deleted" />';
							$content .= '</div>';
							}
			$content .= '			<div class="one_file">
				        			<span class="add_joint" onclick="trigger_upload_file(this)"><i class="fa fa-paperclip"></i></span>
					        		<input class="add_photo" type="file" name="photo[]" onchange="change_upload_file(this)"/>
			        			</div>
			        			<span class="add_plus" onclick="new_input_joint(this)"><i class="fa fa-plus"></i></span>
			        			<div></div>
			        			<hr>
			        		</form>
							<button class="comment_btn update_comment button button_save_" onclick="update_comment(this);">'.$langs->trans("save").'</button> 
							<span class="cancel_cmt" onclick="cancel_cmt(this);" title="'.$langs->trans("Annuler").'"><i class="fa fa-times"></i></span>
						</div>';
			}
	$content .= '</div>';

		}
	}
	echo json_encode($content);
}

if ($action == "create_comment") {
	$id_user 		= $_POST['id_user'];
	$created_at 	= $_POST['created_at'];
	$comment 		= $_POST['comment'];

	$created_at 	= date('Y-m-d H:i:s');

	$data = array(
        'id_tache' =>  $id_tache,
        'id_user' =>  $id_user,
        'modified' =>  0,
        'created_at' =>  $created_at,
        'comment' =>  $comment
    );
	$id = $comments->create(0,$data);

	if(isset($_FILES['files'])) {  
		$dire_file = DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$id.'/';
	    mkdir($dire_file, 0777, true);
	    $names = array();
		foreach ($_FILES["files"]["name"] as $key => $value) {
	        if ($error == UPLOAD_ERR_OK) {
	            $tmp_name = $_FILES["files"]["tmp_name"][$key];
	            $name = $_FILES["files"]["name"][$key];

	            if(in_array($name,$names))
	                $name = $key.'-'.$name;

	            $names[$name] = $name;

	            $newfile=$dire_file.uniqid().'_uplodnc_'.dol_sanitizeFileName($name);
	            // dol_move_uploaded_file($tmp_name, $newfile, 1);
             	move_uploaded_file( $tmp_name, $newfile );
	        }
	    }
    }
	$html = "done";
	echo json_encode($html);
}

if ($action == "update_comment") {
	$id_comment 	= $_POST['id_comment'];
	$modified 		= $_POST['modified'];
	$comment 		= $_POST['comment'];
	$files_deleted 	= $_POST['files_deleted'];
	$data = array(
        'modified' =>  $modified,
        'comment' =>  $comment
    );
    $dire = DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$id_comment.'/';
    if($files_deleted){
        $files_deleted = explode(',', $files_deleted);
        foreach ($files_deleted as $d) {
            unlink($dire.$d);
        }
    }
	$comments->update($id_comment,$data);
	$html = "done";
	echo json_encode($html);
}

if ($action == "delete_comment") {
	$id_comment = $_POST['id_comment'];
	$dire = DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$id_comment.'/';
	$files = glob(DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$id_comment.'/*');
	if (file_exists($dire)){
		foreach($files as $file){
		    unlink($file);
		}
		rmdir($dire);
	}
	$comments->fetch($id_comment);
	$comments->delete();
	$html = "done";
	echo json_encode($html);
}

if ($action == "upload_file") {
	$id_comment 	= $_POST['id_comment'];
	if(isset($_FILES['files'])) {  
		$dire_file = DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$id_comment.'/';
	    mkdir($dire_file, 0777, true);
	    $names = array();
		foreach ($_FILES["files"]["name"] as $key => $value) {
	        if ($error == UPLOAD_ERR_OK) {
	            $tmp_name = $_FILES["files"]["tmp_name"][$key];
	            $name = $_FILES["files"]["name"][$key];

	            if(in_array($name,$names))
	                $name = $key.'-'.$name;

	            $names[$name] = $name;

	            $newfile=$dire_file.uniqid().'_uplodnc_'.dol_sanitizeFileName($name);
	            // dol_move_uploaded_file($tmp_name, $newfile, 1);
             	move_uploaded_file( $tmp_name, $newfile );
	        }
	    }
    }

	// if(isset($_FILES['files']['name']))
	// {  
	//     $uploads_dir =  DOL_DOCUMENT_ROOT.$customtxt.'/teamview/files_commentaire/taches/'.$id_comment.'/';
	//     mkdir($uploads_dir, 0777, true);
	// 	$target_path = $uploads_dir.uniqid() . basename( $_FILES[ 'files' ][ 'name' ] );
	// 	if ( move_uploaded_file( $_FILES[ 'files' ][ 'tmp_name' ], $target_path ) )
	// 	{
	// 	    echo 'File uploaded: ' . $target_path;
	// 	}
	// 	else
	// 	{
	// 	    echo 'Error in uploading files ' . $target_path;
	// 	}
	// }
	$html = "done";
	echo json_encode($html);
}

if ($action == "update_times_tasks") {
	$id_task = $_POST['id_task'];
	$id_time = $_POST['id_time'];
	$inputhour = $_POST['inputhour'];
	$inputminute = $_POST['inputminute'];

	$duration = ($inputhour * 3600) + ($inputminute * 60);


	if ($user->rights->projet->creer){
		$sql = "UPDATE " . MAIN_DB_PREFIX ."projet_task_time SET task_duration = ".$duration." WHERE rowid = " . $id_time."; \n";
		$resql = $projets_task_time->db->query($sql);
	}

	$html = "done";
	echo json_encode($html);
}

if ($action == "update_avanc_tasks") {
	$progress_tasks = $_POST['progress_tasks'];

	$params = array();
	parse_str($progress_tasks, $params);
	// print_r($params);
    $sql = "";
    if ($user->rights->projet->creer){
		foreach ($params['progress_tasks'] as $key => $value) {
			if ($value < 0) {
				$value = "NULL";
			}
			$sql = "UPDATE " . MAIN_DB_PREFIX ."projet_task SET progress = ".$value." WHERE rowid = " . $key."; \n";
			$taches->update_task_avanc($sql);
		} 
    }

	$html = "done";
	echo json_encode($html);
}

if ($action == "oneTache") {
	$teamview->fetchAll("", "", "", "", " AND id_tache = ".$id_tache);
	$item1 = $teamview->rows[0];
	if ($item1) {
		if ($item1->etat_tache == "Validé") {
			$etat = $langs->trans("Validé");
		}elseif ($item1->etat_tache == "AValider") {
			$etat = $langs->trans("a_Valider");
		}else{
			$etat = $langs->trans("To_Do");
		}
		$disabl = "disabled";
		if ($user->rights->projet->creer)
			$disabl = "";
		$html["etat"] = '<select '.$disabl.' name="etat_tache" id="select_etat_tache" onchange="slct_etat_change()">';
		foreach ($etats as $key => $etat) {
			$slctd = ($key == $item1->etat_tache) ? "selected" : "";
			$html["etat"] .= '<option value="'.$key.'" '.$slctd.'>'.$etat.'</option>';
		}
		$html["etat"] .= '</select>';
		$html["etat"] .= '<input id="editornew" value="edit" type="hidden" />';
		$html["etat"] .= '<input id="todo_id" value="'.$item1->rowid.'" type="hidden" />';

		$html["description"] = $item1->description;
	}else{
		$html["etat"] = '<select name="etat_tache" id="select_etat_tache" onchange="slct_etat_change()">';
		foreach ($etats as $key => $etat) {
			$slctd = ($key == "ToDo") ? "selected" : "";
			$html["etat"] .= '<option value="'.$key.'" '.$slctd.'>'.$etat.'</option>';
		}
		$html["etat"] .= '</select>';
		$html["etat"] .= '<input id="editornew" value="create" type="hidden" />';
		$html["etat"] .= '<input id="todo_id" value="" type="hidden" />';
		$html["description"] = "";
	}

	$timesTaskHtml .= '<table class="noborder times_task_table parent">';
	$timesTaskHtml .= '<tr align="center">';
	$timesTaskHtml .= '<th align="center">'.$langs->trans("Date").'</th>';
	$timesTaskHtml .= '<th align="center">'.$langs->trans("Par").'</th>';
	$timesTaskHtml .= '<th align="right" class="durre_text_th">'.$langs->trans("Durée").'</th>';
	$timesTaskHtml .= '<th align="center" class="">'.$langs->trans("Action").'</th>';
	$timesTaskHtml .= '</tr>';
	$timesTaskHtml .= $projets_task_time->getAllTimesTask($id_tache,$user->rights->projet->creer);
	$html["tache_temps_consomme"] = $timesTaskHtml;
	$timesTaskHtml .= '</table>';

	if ($user->rights->projet->creer)
		$html["disabledornot"] = "no";
	else
		$html["disabledornot"] = "yes";

	$taches->fetchAll("", "", "", "", " AND rowid = ".$id_tache);
	$item = $taches->rows[0];

	$html["ref"] = '<h2 class="title" id="'.$item->rowid.'" projet-id="'.$item->fk_projet.'" >'.$langs->trans("Tâche_Parent").' : <span style="color: #5780ca;"><a target="_blank" href="'.DOL_URL_ROOT.'/projet/tasks/task.php?id='. $item->rowid .'">'.$item->ref.' - '.$item->label.'</a></span></h2>';

	$selecte_html = '
			<option value="-1">&nbsp;</option>
			<option value="0">0 % </option>
			<option value="5">5 % </option>
			<option value="10">10 % </option>
			<option value="15">15 % </option>
			<option value="20">20 % </option>
			<option value="25">25 % </option>
			<option value="30">30 % </option>
			<option value="35">35 % </option>
			<option value="40">40 % </option>
			<option value="45">45 % </option>
			<option value="50">50 % </option>
			<option value="55">55 % </option>
			<option value="60">60 % </option>
			<option value="65">65 % </option>
			<option value="70">70 % </option>
			<option value="75">75 % </option>
			<option value="80">80 % </option>
			<option value="85">85 % </option>
			<option value="90">90 % </option>
			<option value="95">95 % </option>
			<option value="100">100 % </option>
		</select>';

	if ($user->rights->projet->creer){
		$html["avance"] = '<select class="flat parent_tasks task_'.$item->rowid.'" name="progress_tasks['.$item->rowid.']" onchange="slct_etat_change()">';
		$html["avance"] .= str_replace(' value="'.$item->progress.'"', ' value="'.$item->rowid.'" selected="selected"', $selecte_html);
	}else{
		if ($item->progress > 0) {
			$html["avance"] .= $item->progress. ' %';
		}else{
			$html["avance"] .='-';
		}
	}

	// print_r($html);
	echo json_encode($html);
}

if ($action == "createorupdatetask") {
	$todo_id 		= $_POST['todo_id'];
	$etat_tache 	= $_POST['etat_tache'];
	$avance_tache 	= $_POST['avance_tache'];
	$description 	= $_POST['description'];
	$editornew 		= $_POST['editornew'];

	// $todo_id 		= 4;
	// $id_tache 		= 6;
	// $id_projet 		= 2;
	// $etat_tache 		= "AValider";
	// $editornew 		= "update";

	if ($user->rights->projet->creer){
		if ($avance_tache < 0) {
			$avance_tache = "NULL";
		}
		$sql = "UPDATE " . MAIN_DB_PREFIX ."projet_task SET progress = ".$avance_tache." WHERE rowid = " . $id_tache.";";
		// echo $sql;

		$taches->update_task_avanc($sql);
	}
	$data = array(
        'id_tache' =>  $id_tache,
        'id_projet' =>  $id_projet,
        'etat_tache' =>  $etat_tache,
        'description' =>  $description,
    );
	if ($editornew == "create") {
		$teamview->create(0,$data);
	}else{
		$teamview->update($todo_id,$data);
	}
	$html = "done";
	echo json_encode($html);
}

if ($action == "getallTasks") {
	$arr = [];
	$teamview->fetchAll("", "", "", "", " AND id_projet = ".$id_projet);
	if (count($teamview->rows) > 0) {
		for ($i=0; $i < count($teamview->rows) ; $i++) {
			$item = $teamview->rows[$i];
			$arr[$item->id_tache] = $item->etat_tache;
		}
	}
	$taches->fetchAll("", "", "", "", " AND fk_projet = ".$id_projet." AND fk_task_parent = 0");
	if (count($taches->rows) > 0) {



		$tasksarray=$taskstatic->getTasksArray(0, 0, $id_projet, $filteronthirdpartyid, 0);
		$tmpuser=new User($db);
		if ($search_user_id > 0) $tmpuser->fetch($search_user_id);
		$tasksrole=($tmpuser->id > 0 ? $taskstatic->getUserRolesForProjectsOrTasks(0, $tmpuser, $id_projet, 0) : '');



		for ($i=0; $i < count($taches->rows) ; $i++) {
			$item2 = $taches->rows[$i];

			$tot_arr = [];
			if (count($tasksarray) > 0)
			{
				$j=0; $level=0;
				$taskstot = $teamview->projectLinesa($j,$item2->rowid, $tasksarray, $level, true, 0, $tasksrole, $id_projet, 1, $id_projet,true);
			}

			$clss = "";
			if (empty($taskstot['tot_c'])) {
				$taskstot['tot_c'] = 0;

				if (empty($taskstot['tot'])){
					$taskstot['tot'] = 0;
				}

			}


			if(($taskstot['tot_c'] == $taskstot['tot']) && ($taskstot['tot'] > 0)){
				$clss = "completed";
			}elseif($taskstot['tot']>0){
				$clss = "not-empty";
			}

			$display = "block";
			if (($taskstot['tot_c'] == $taskstot['tot']) && ($taskstot['tot'] == 0))
				$display = "none";
			$tot_cmnt = 0;
			$comments->fetchAll("", "", "", "", " AND id_tache = ".$item2->rowid);
			$tot_cmnt = count($comments->rows);

			$opacity2 = "1";
			$classNoComment = "classNoComment";
			if ($tot_cmnt > 0){
				$opacity2 = "1";
				$classNoComment = "";
			}
			$content = '
				<div class="one_content" id="tache_'.$item2->rowid.'" style="position:relative;">
					<div class="showpopupspan" onclick="OpenTachePop(this)"></div>
					<h4 id_tache="'.$item2->rowid.'">
					<a target="_blank" href="'.DOL_URL_ROOT.'/projet/tasks/task.php?id='. $item2->rowid .'">'.$item2->ref.' - '.$item2->label.'</a>
					</h4>
					<input type="hidden" class="id_tache" value="'.$item2->rowid.'"/>
					<div class="badges" style="display:'.$display.';">
						<div class="badge '.$clss.'" title="'.$langs->trans("Sous_taches").'">
							<span class="badge-icon icon-sm icon-checklist"></span>
							<span class="badge-text">'.$taskstot['tot_c'].'/'.$taskstot['tot'].'</span>
						</div>
					</div>
					<div class="badges comments taches '.$classNoComment.'" style="opacity:'.$opacity2.'; ">
						<div class="badge" title="'.$langs->trans("Commentaire").'">
							<i class="fa fa-comment"></i>
							<span class="badge-text">'.$tot_cmnt.'</span>
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>
			';
			

			if(isset($arr[$item2->rowid])){
				$html['"'.$arr[$item2->rowid].'"']["content"] .= $content;
			}else{
				$html['"ToDo"']["content"] .= $content;
			}

			$taskstot['tot']=0;
			$taskstot['tot_c']=0;

			$html['tototot']=count($taches->rows);
		}
	}
	// print_r($html);


	// $teamview->fetchAll("", "", "", "", " AND id_projet = ".$id_projet);
	// $html['"contacts"']["content"] .= $content;

	echo json_encode($html);
}

if ($action == "getchildtasks") {

	// $taches->fetchAll("", "", "", "", " AND fk_projet = ".$id_projet);
	// if (count($taches->rows) > 0) {
	// 	for ($i=1; $i < count($taches->rows) ; $i++) {
	// 		$item = $taches->rows[$i];
	// 		echo 'parent :'.$item->fk_task_parent.' | '.$item->rowid.' : '.$item->ref.' - '.$item->label."<br>";
	// 		$arr0[$item->fk_task_parent] = array('id' => $item->rowid, 'parent' => $item->fk_task_parent);
	// 	}
	// }
	// echo "<br>--------------------------------------------------------</br>";

	// print_r($arr0);

	// echo "<br>--------------------------------------------------------</br>";

	// $arr2[1] = array('id' => 4, 'parent' => 0);
	// $arr2[2] = array('id' => 8, 'parent' => 1);
	// $arr2[3] = array('id' => 9, 'parent' => 1);
	// $arr2[4] = array('id' => 10, 'parent' => 1);
	// $arr2[5] = array('id' => 11, 'parent' => 2);

	
	// // $arr2[1] = array('id' => 1, 'parent' => 0);
	// // $arr2[2] = array('id' => 2, 'parent' => 1);
	// // $arr2[3] = array('id' => 3, 'parent' => 2);

	// // print_r($arr2);
	// $children = array();
	// foreach($arr0 as $key => $page){
	//     $parent = (int)$page['parent'];
	//     if(!isset($children[$parent]))
	//         $children[$parent] = array();
	//     $children[$parent][$key] = array('id' => $page['id']);
	// }

	// $new_pages = $teamview->recursive_append_children($children[0], $children);
	// print_r($new_pages);




	$html = "";

	// global $taskallows;
	// $taskallows = $taches->arrayofallowstasks($user->id);

	$tasksarray=$taskstatic->getTasksArray(0, 0, $id_projet, $filteronthirdpartyid, 0);

	$tmpuser=new User($db);
	if ($search_user_id > 0) $tmpuser->fetch($search_user_id);
	$tasksrole=($tmpuser->id > 0 ? $taskstatic->getUserRolesForProjectsOrTasks(0, $tmpuser, $id_projet, 0) : '');


	if (count($tasksarray) > 0)
	{
	    // Show all lines in taskarray (recursive function to go down on tree)
		$j=0; $level=0;
		$html .= $teamview->projectLinesa($j, $id_tache, $tasksarray, $level, true, 0, $tasksrole, $id_projet, 1, $id_projet);
	}
	else
	{
		$html .= '<tr class="oddeven"><td colspan="2" class="opacitymedium" align="center">Aucune sous-tâche</td></tr>';
	}



	// echo $html;
	echo json_encode($html);
}

if ($action == "get_contacts_users_project") {
	$projet_own->fetch($id_projet);
	$html['others'] = '';
	$other = 0;
	$html['contacts'] = '<div class="visiblite"><b>'.$langs->trans("Visibilité").' :</b></div>';
	if ($projet_own->public == 0) {
		$elements_contacts->fetchAll("DESC", "", "", "", " AND element_id = ".$id_projet." AND fk_c_type_contact in (SELECT rowid from ".MAIN_DB_PREFIX."c_type_contact where element = 'project') ORDER BY rowid ASC");
		if (count($elements_contacts->rows) > 0) {
			for ($i=0; $i < count($elements_contacts->rows) ; $i++) {
				$item = $elements_contacts->rows[$i];
				$user_id = $item->fk_socpeople;
				$tmpuser->fetch($user_id);
				$img = $form->showphoto('userphoto',$tmpuser,100);
				if ($i > 5) {
					$other++;
					$html['others'] .= "- ".$tmpuser->lastname." ".$tmpuser->firstname."\n";
				}else{
					$html['contacts'] .= '<span title="'.$tmpuser->lastname.' '.$tmpuser->firstname.'">'.$img.'</span>';
				}
			}
		}
		if ($other > 0) {
			$html['allcontacts'] = $html['contacts'].'<span class="number_other" title="'.$html['others'].'">'.$other.'</span>';
		}else{
			$html['allcontacts'] = $html['contacts'];
		}
	}else{
		$html['contacts'] .= '<div class="tous"><b>Tout le monde</b></div>';
		$html['allcontacts'] = $html['contacts'];
	}
	// Projet info ----------------------
	if ($projet_own->fk_statut == 2)
		$html['etat'] = "<span class='etat_color cloturer_st'></span> ".$langs->trans("Closed");
	elseif ($projet_own->fk_statut == 0)
		$html['etat'] = "<span class='etat_color brouillon_st'></span> ".$langs->trans("Draft");
	elseif ($projet_own->fk_statut == 1)
		$html['etat'] = "<span class='etat_color ouvert_st'></span> ".$langs->trans("Opened");
	else
		$html['etat'] = "";

	if (!empty($projet_own->fk_soc)) {
		$societe->fetch($projet_own->fk_soc);
		$html['tiers'] = $societe->nom;
	}else{
		$html['tiers'] = "-";
	}
	$debut = "";
	if ($projet_own->dateo) {
		$debut = $projet_own->dateo;
		$debut = explode('-', $debut);
		$debut = $debut[2]."/".$debut[1]."/".$debut[0];
	}
	$fin = "";
	if ($projet_own->datee) {
		$fin = $projet_own->datee;
		$fin = explode('-', $fin);
		$fin = $fin[2]."/".$fin[1]."/".$fin[0];
	}

	$html['dates'] = $debut.' - '.$fin;
	// End Projet info ----------------------
	echo json_encode($html);
}

if ($action == "check_user_permission_projet") {
	if ($user->rights->projet->creer)
		$result = "yes";
	else
		$result = "no";
	echo json_encode($result);
}