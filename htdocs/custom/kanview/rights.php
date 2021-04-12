<?php

if (!function_exists('hasPermissionForKanbanView')) {

	function hasPermissionForKanbanView($kanbanView, $returnPermsCode = false) {
		global $user, $conf;
		$rightsOK_part1_code = '';	// rights if not use advanced perms
		$rightsOK_part2_code = '';	// rights if use advanced perms
		$rightsOK_code			 = '';	// rights 

		$rightsOK_part1	 = 0;	// rights if not use advanced perms
		$rightsOK_part2	 = 0;	// rights if use advanced perms
		$rightsOK				 = 0;	// rights 

		if (!$user->rights->kanview->canuse)
			return false;

		switch ($kanbanView) {
			// ------------------------------------------ 1 - projets
			case 'projets':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire && $user->rights->projet->creer && $user->rights->projet->all->lire && $user->rights->projet->all->creer';
				$rightsOK_part2_code = '$rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_projects';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire && $user->rights->projet->creer && $user->rights->projet->all->lire && $user->rights->projet->all->creer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_projects;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ---------------------------------------- 2 - tasks
			case 'tasks':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire && $user->rights->projet->creer && $user->rights->projet->all->lire && $user->rights->projet->all->creer';
				$rightsOK_part2_code = '$rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_tasks';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->projet->enabled && $user->rights->projet->lire && $user->rights->projet->creer && $user->rights->projet->all->lire && $user->rights->projet->all->creer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_tasks;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// --------------------------------------- 3 - propals
			case 'propals':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->propal->enabled && $user->rights->propale->lire && $user->rights->propale->creer && $user->rights->propale->cloturer';
				$rightsOK_part2_code = '$rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_propals && $user->rights->propale->propal_advance->validate';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->propal->enabled && $user->rights->propale->lire && $user->rights->propale->creer && $user->rights->propale->cloturer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_propals && $user->rights->propale->propal_advance->validate;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// --------------------------------------- 4 - orders
			case 'orders':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->commande->enabled && $user->rights->commande->lire && $user->rights->commande->creer && $user->rights->commande->cloturer';
				$rightsOK_part2_code = '$rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_orders && $user->rights->commande->order_advance->validate && $user->rights->commande->order_advance->annuler';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->commande->enabled && $user->rights->commande->lire && $user->rights->commande->creer && $user->rights->commande->cloturer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_orders && $user->rights->commande->order_advance->validate && $user->rights->commande->order_advance->annuler;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ------------------------------------ 5 - invoices
			case 'invoices':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->facture->enabled && $user->rights->facture->lire && $user->rights->facture->creer && $user->rights->facture->paiement';
				$rightsOK_part2_code = '$rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_invoices && $user->rights->facture->invoice_advance->unvalidate && $user->rights->facture->invoice_advance->validate';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->facture->enabled && $user->rights->facture->lire && $user->rights->facture->creer && $user->rights->facture->paiement;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_invoices && $user->rights->facture->invoice_advance->unvalidate && $user->rights->facture->invoice_advance->validate;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ------------------------------------ 6 - prospects
			case 'prospects':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->societe->enabled && $user->rights->societe->lire && $user->rights->societe->creer';
				$rightsOK_part2_code = '$rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_prospects';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->societe->enabled && $user->rights->societe->lire && $user->rights->societe->creer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_prospects;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			// ------------------------------------ 7 - invoices_suppliers
			case 'invoices_suppliers':
				$rightsOK_part1_code = '$conf->kanview->enabled && $user->rights->kanview->canuse && $conf->fournisseur->enabled && $user->rights->fournisseur->facture->lire && $user->rights->fournisseur->facture->creer';
				$rightsOK_part2_code = '$rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_invoices_suppliers && $user->rights->fournisseur->supplier_invoice_advance->validate';
				$rightsOK_code			 = '((!$conf->global->MAIN_USE_ADVANCED_PERMS) && ' . $rightsOK_part1_code . ') || ($conf->global->MAIN_USE_ADVANCED_PERMS && ' . $rightsOK_part2_code . ')';

				$rightsOK_part1	 = $conf->kanview->enabled && $user->rights->kanview->canuse && $conf->fournisseur->enabled && $user->rights->fournisseur->facture->lire && $user->rights->fournisseur->facture->creer;
				$rightsOK_part2	 = $rightsOK_part1 && $user->rights->kanview->kanview_advance->canuse_invoices_suppliers && $user->rights->fournisseur->supplier_invoice_advance->validate;
				$rightsOK				 = ((!$conf->global->MAIN_USE_ADVANCED_PERMS) && $rightsOK_part1) || ($conf->global->MAIN_USE_ADVANCED_PERMS && $rightsOK_part2);

				if ($returnPermsCode)
					return $rightsOK_code;
				else
					return $rightsOK;
				break;

			default:
				return false;
				break;
		}
	}

}