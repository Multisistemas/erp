/*
 * Multisistemas Gconnect - A Google authentication module for Dolibarr
 * Copyright (C) 2017 Herson Cruz <herson@multisistemas.com.sv>
 * Copyright (C) 2017 Luis Medrano <lmedrano@multisistemas.com.sv>
 *
 */
 
CREATE TABLE IF NOT EXISTS llx_gconnect (
	rowid    INTEGER      NOT NULL AUTO_INCREMENT PRIMARY KEY,
	token    TEXT         NULL,
	scopes   VARCHAR(255) NULL,
	email    VARCHAR(255) NULL,
	oauth_id VARCHAR(255) NULL
)
	ENGINE = InnoDB;