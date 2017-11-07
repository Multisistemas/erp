-- Copyright (C) 2013  Jean-François FERRY <jfefe@aternatik.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
--

create table llx_c_ticketsup_type
(
  rowid			integer AUTO_INCREMENT PRIMARY KEY,
  code			varchar(32)				NOT NULL,
  pos			varchar(32)				NOT NULL,
  label			varchar(128)			NOT NULL,
  active		integer DEFAULT 1,
  use_default	integer DEFAULT 1,
  description	varchar(255) ,
  UNIQUE KEY code (code)
)ENGINE=innodb;