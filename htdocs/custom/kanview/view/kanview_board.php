<?php

/* Copyright (C) 2018  ProgSI  (contact@progsi.ma)
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
 * 	\file       board.php
 * 	\ingroup    module
 * 	\brief      Home page of module
 */
// __LOCKED__

include_once dirname(__DIR__) . '/main.inc.php';

// on affiche la vue kanban paramétrée dans la page setup comme page d'accueil
// par CONVENTION, le paramètre KANVIEW_HOME_PAGE doit correspondre au préfix de la page associée
$viewToDisplay = '';
if (!empty($conf->global->KANVIEW_HOME_PAGE)) {
	// si l'utilisateur n'a pas ke droit d'afficher la page d'accueil, on affiche la 1ère page à laquelle il a droit
	if (hasPermissionForKanbanView(strtolower($conf->global->KANVIEW_HOME_PAGE)))
		$viewToDisplay = strtolower($conf->global->KANVIEW_HOME_PAGE);
	else
		$viewToDisplay = getAutorizedKanbanView();
}
else {
	$viewToDisplay = getAutorizedKanbanView();
}

if(empty($viewToDisplay))
	$viewToDisplay = getAutorizedKanbanView();

if ((!empty($viewToDisplay)) && file_exists(KANVIEW_DOCUMENT_ROOT . '/view/' . $viewToDisplay . "_kb.php"))
	header("Location: " . KANVIEW_URL_ROOT . '/view/' . $viewToDisplay . "_kb.php");
else
	accessforbidden();

exit();

// ---------------------------------------------

function getAutorizedKanbanView() {
	global $user, $conf;
	$view = '';
	if (hasPermissionForKanbanView('projets'))
		$view = 'projets';
	elseif (hasPermissionForKanbanView('tasks'))
		$view = 'tasks';
	elseif (hasPermissionForKanbanView('propals'))
		$view = 'propals';
	elseif (hasPermissionForKanbanView('orders'))
		$view = 'orders';
	elseif (hasPermissionForKanbanView('invoices'))
		$view = 'invoices';
	elseif (hasPermissionForKanbanView('prospects'))
		$view = 'prospects';
	elseif (hasPermissionForKanbanView('invoices_suppliers'))
		$view = 'invoices_suppliers';
	else
		$view = '';
	
	return $view;
}

// ------------------------------------------


