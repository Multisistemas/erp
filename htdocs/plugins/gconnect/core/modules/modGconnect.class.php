<?php
/*
 * Multisistemas Gconnect - A Google authentication module for Dolibarr
 * Copyright (C) 2017 Herson Cruz <herson@multisistemas.com.sv>
 * Copyright (C) 2017 Luis Medrano <lmedrano@multisistemas.com.sv>
 *
 */

/**
 * Multisistemas Gconnect module for Dolibarr
 *
 * Manages the OAuth 2 authentication process for Google APIs.
 *
 */

/**
 * \file core/modules/modGconnect.class.php
 * Multisistemas Team
 *
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module Gconnect
 */
class modGconnect extends DolibarrModules {
	/**
	 * @param DoliDB $db Database handler
	 *
	 */
	public function __construct($db) {
    global $langs,$conf;

    $this->db = $db;
		$this->numero = 555000;
		$this->rights_class = 'gconnect';
		$this->family = "Multisistemas";
		$this->module_position = 001;
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "OAuth 2 module authentication with Google";
		$this->version = '1.0.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto='multisistemas@gconnect';
		$this->module_parts = array(
			'login' => 1,
			'tpl' => 1,
			'css' => array('/gconnect/css/gconnect.css.php'),
			'js' => array('/gconnect/js/gconnect.js')
		);
		$this->dirs = array();
		$this->config_page_url = array("conf.php@gconnect");
		$this->hidden = false;
		$this->phpmin = array(5,3);
		$this->need_dolibarr_version = array(3,2);
		$this->langfiles = array("gconnect@gconnect");
		$this->const = array(
			0	=> array('GC_OAUTH_CLIENT_ID','string','','Oauth client id value',0,'current',0),
			1	=> array('GC_OAUTH_CLIENT_SECRET','string','','Oauth client secret value',0,'current',0),
			2 => array('GC_EMAIL_DOMAIN','string','','Email domain allowed',1),
			3 => array('GC_ORIGIN_CALL','string','','Origin Oauth call',1)
		);
    $this->tabs = array(
      'user:+google:Google:@gconnect:$user->rights->gconnect->use'
      . ':/gconnect/initoauth.php?id=__ID__'
    );

		if (!isset($conf->gconnect) || !isset($conf->gconnect->enabled)) {
        	$conf->gconnect = new stdClass();
        	$conf->gconnect->enabled = 0;
    }

		$this->rights = array();
    $this->rights[0][0] = 7345701;
    $this->rights[0][1] = 'Use GConnect';
    $this->rights[0][3] = 0;
    $this->rights[0][4] = 'use';
    $this->menus = array();
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
   *    @param      string	$options
	 *    @return     int             	
	 */
	public function init($options = '')
	{
		$sql = array();

		//$this->_load_tables('/gconnect/sql/');

		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param      string	$options
	 * @return     int             	
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

}

