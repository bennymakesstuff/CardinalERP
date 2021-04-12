-- Copyright (C) 2019       Peter Roberts			<webmaster@finchmc.com.au>
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
-- along with this program.  If not, see http://www.gnu.org/licenses/.


CREATE TABLE llx_wip_reportdet(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_report integer, 
	fk_task integer NOT NULL, 
	fk_parent_line integer, 
	fk_assoc_line integer, 
	fk_product integer, 
	product_type integer, 
	ref varchar(128) NOT NULL, 
	label varchar(255), 
	date_start date, 
	date_end date, 
	description text, 
	qty double(24,8) DEFAULT NULL, 
	discount_percent double(24,8) DEFAULT NULL, 
	discount_qty double(24,8) DEFAULT NULL, 
	special_code integer, 
	rang integer, 
	rang_task integer, 
	date_creation datetime NOT NULL, 
	tms timestamp NOT NULL, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	direct_amortised integer NOT NULL, 
	work_type integer NOT NULL, 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;