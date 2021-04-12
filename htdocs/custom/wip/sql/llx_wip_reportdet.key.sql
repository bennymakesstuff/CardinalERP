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


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_wip_reportdet ADD INDEX idx_wip_reportdet_rowid (rowid);
ALTER TABLE llx_wip_reportdet ADD INDEX idx_wip_reportdet_ref (ref);
ALTER TABLE llx_wip_reportdet ADD CONSTRAINT llx_wip_reportdet_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_wip_reportdet ADD INDEX idx_wip_reportdet_status (status);
ALTER TABLE llx_wip_reportdet ADD INDEX idx_wip_reportdet_fk_report (fk_report);
ALTER TABLE llx_wip_reportdet ADD INDEX idx_wip_reportdet_fk_task (fk_task);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_wip_reportdet ADD UNIQUE INDEX uk_wip_reportdet_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_wip_reportdet ADD CONSTRAINT llx_wip_reportdet_fk_field FOREIGN KEY (fk_field) REFERENCES llx_wip_myotherobject(rowid);

