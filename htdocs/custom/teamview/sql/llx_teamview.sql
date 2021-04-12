CREATE TABLE IF NOT EXISTS `llx_teamview` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_projet` int(11) NOT NULL,
  `id_tache` int(11) NOT NULL,
  `etat_tache` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `llx_teamview_comments` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_tache` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `comment` text NOT NULL,
  `modified` int(11) NOT NULL DEFAULT '0'
);
CREATE TABLE IF NOT EXISTS `llx_todo_propal_comments` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_propal` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `comment` text NOT NULL,
  `modified` int(11) NOT NULL DEFAULT '0'
);
CREATE TABLE IF NOT EXISTS `llx_todo_facture_comments` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_facture` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `comment` text NOT NULL,
  `modified` int(11) NOT NULL DEFAULT '0'
);
CREATE TABLE IF NOT EXISTS `llx_todo_commande_comments` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_commande` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `comment` text NOT NULL,
  `modified` int(11) NOT NULL DEFAULT '0'
);
CREATE TABLE IF NOT EXISTS `llx_todo_prospect_comments` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_prospect` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `comment` text NOT NULL,
  `modified` int(11) NOT NULL DEFAULT '0'
);
CREATE TABLE IF NOT EXISTS `llx_todo_projet_comments` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_projet` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `comment` text NOT NULL,
  `modified` int(11) NOT NULL DEFAULT '0'
);