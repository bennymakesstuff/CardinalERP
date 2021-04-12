-- ============================================================================
-- Copyright (C) 2014-2016   Philippe Grand		<philippe.grand@atoo-net.com>
-- Copyright (C) 2014-2017   Regis Houssin		<regis.houssin@capnetworks.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

INSERT INTO llx_c_ultimatepdf_line(rowid,code,label,description,active) VALUES (1,'TEXTE1','Garantie 2 ans pièces et main d''œuvre, retour en atelier (Hors filtre et pièce d''usure)','texte de garantie',1);

INSERT INTO llx_c_ultimatepdf_title(rowid,code,label,description,active) VALUES (1,'TITLE1','Facture Proforma','Facture proforma',1);

INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active ) VALUES (42, 'propal',  'external', 'SHIPPING', 'Contact client livraison', 1);

INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newline', __ENTITY__, 'propal', '2014-03-31 12:30:27', 'New line', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:29:"c_ultimatepdf_line:label:code";N;}}', 0, 0) ;
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newtitle', __ENTITY__, 'propal', '2015-10-21 12:30:27', 'New title', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:30:"c_ultimatepdf_title:label:code";N;}}', 0, 0) ;
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newline', __ENTITY__, 'commande', '2014-03-31 12:30:27', 'New line', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:29:"c_ultimatepdf_line:label:code";N;}}', 0, 0) ;
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newtitle', __ENTITY__, 'commande', '2015-10-21 12:30:27', 'New title', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:30:"c_ultimatepdf_title:label:code";N;}}', 0, 0) ;
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newline', __ENTITY__, 'facture', '2014-03-31 12:30:27', 'New line', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:29:"c_ultimatepdf_line:label:code";N;}}', 0, 0);
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newtitle', __ENTITY__, 'facture', '2015-10-21 12:30:27', 'New title', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:30:"c_ultimatepdf_title:label:code";N;}}', 0, 0) ;
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newline', __ENTITY__, 'expedition', '2014-03-31 12:30:27', 'New line', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:29:"c_ultimatepdf_line:label:code";N;}}', 0, 0);
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newtitle', __ENTITY__, 'expedition', '2015-10-21 12:30:27', 'New title', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:30:"c_ultimatepdf_title:label:code";N;}}', 0, 0) ;
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newline', __ENTITY__, 'fichinter', '2014-03-31 12:30:27', 'New line', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:29:"c_ultimatepdf_line:label:code";N;}}', 0, 0);
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newtitle', __ENTITY__, 'fichinter', '2015-10-21 12:30:27', 'New title', 'sellist', '', 0, 'a:1:{s:7:"options";a:1:{s:30:"c_ultimatepdf_title:label:code";N;}}', 0, 0) ;

INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newprice', __ENTITY__, 'fichinter', '2014-03-31 12:30:27', 'Shifting', 'price', '', 0, 'a:1:{s:7:"options";a:1:{s:0:"";N;}}', 0, 0);
INSERT INTO llx_extrafields (name, entity, elementtype, tms, label, type, size, pos, param, fieldunique, fieldrequired) VALUES
('newrdv', __ENTITY__, 'fichinter', '2014-03-31 12:30:27', 'New RDV', 'radio', '', 0, 'a:1:{s:7:"options";a:2:{i:1;s:3:"Oui";i:2;s:3:"Non";}}', 0, 0);



ALTER TABLE llx_propal_extrafields ADD COLUMN newline text NULL;
ALTER TABLE llx_propal_extrafields ADD COLUMN newtitle text NULL;
ALTER TABLE llx_commande_extrafields ADD COLUMN newline text NULL;
ALTER TABLE llx_commande_extrafields ADD COLUMN newtitle text NULL;
ALTER TABLE llx_facture_extrafields ADD COLUMN newline text NULL;
ALTER TABLE llx_facture_extrafields ADD COLUMN newtitle text NULL;
ALTER TABLE llx_expedition_extrafields ADD COLUMN newline text NULL;
ALTER TABLE llx_expedition_extrafields ADD COLUMN newtitle text NULL;
ALTER TABLE llx_fichinter_extrafields ADD COLUMN newline text NULL;
ALTER TABLE llx_fichinter_extrafields ADD COLUMN newtitle text NULL;
ALTER TABLE llx_fichinter_extrafields ADD COLUMN newprice double(24,8) NULL;
ALTER TABLE llx_fichinter_extrafields ADD COLUMN newrdv text NULL;
ALTER TABLE llx_contract_merged_pdf RENAME llx_ultimatepdf_contract_merged_pdf;
ALTER TABLE llx_invoice_merged_pdf RENAME llx_ultimatepdf_invoice_merged_pdf;
ALTER TABLE llx_order_merged_pdf RENAME llx_ultimatepdf_order_merged_pdf;
ALTER TABLE llx_propal_merged_pdf RENAME llx_ultimatepdf_propal_merged_pdf;




