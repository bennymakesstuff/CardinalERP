-- ===========================================================================
-- Copyright (C) 2013	Florian HENRY  <florian.henry@open-concept.pro>
-- Copyright (C) 2014-2017   Philippe Grand <philippe.grand@atoo-net.com>
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
-- ===========================================================================

CREATE TABLE IF NOT EXISTS llx_ultimatepdf_supplierproposal_merged_pdf (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  fk_supplier_proposal integer NOT NULL,
  file_name varchar(200) NOT NULL,
  fk_user_author integer DEFAULT NULL,
  fk_user_mod integer NOT NULL,
  datec datetime NOT NULL,
  tms timestamp NOT NULL,
  import_key varchar(14) DEFAULT NULL
) ENGINE=InnoDB;

