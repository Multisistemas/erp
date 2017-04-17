<?php

/**
 * Multisistemas Gconnect - A Google authentication module for Dolibarr
 * Copyright (C) 2017 Herson Cruz <herson@multisistemas.com.sv>
 * Copyright (C) 2017 Luis Medrano <lmedrano@multisistemas.com.sv>
 *
 */

/**
 * \file admin/control.class.php
 * Multisistemas Team
 * Module file for users control access
 */

define('OPAUTH_LIB_DIR', dirname(__FILE__).'/../vendor/autoload.php');

require_once(OPAUTH_LIB_DIR);

class Control {

  public $global_conf;
  public $global_db;

  function __construct($conf, $db) {
    $this->global_conf = $conf;
    $this->global_db = $db;
  }

  public function buildOpauth(){
  	$Opauth = new Opauth($this->SetOpauthConfig());
  	return $Opauth;
	}

	public function rebuildOpauth(){
		$Opauth = new Opauth($this->SetOpauthConfig());
  	return $Opauth;	
	}

	public function SetOpauthConfig(){

		$host = ((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'];

		$temp = $this->global_conf->global->GC_ORIGIN_CALL;

		if ($temp !== NULL && !empty($temp)) {
			if ($temp == '/'){
				$path = '/index.php/';
			} else {
				$path = '/'.$temp.'/index.php/';	
			}
		} else {
			$path = '/index.php/';
		}

		return $config = array(
				'path' => $path,
				'callback_url' => $host,
				'security_salt' => 'LHFm11lYf3Fyw5W10a44aa5x4W1KsVrieQCnpBzzpTBMA5vJidQKDo8pMJbmw22A1C8v',
			  'debug' =>true,
				'Strategy' => array(
			      'Google' => array(
			          'client_id' => $this->global_conf->global->GC_OAUTH_CLIENT_ID,
			          'client_secret' => $this->global_conf->global->GC_OAUTH_CLIENT_SECRET
			      ),
				),
		);
	}

	// Validate the domain 
	public function validate($email) {

		$text = $this->global_conf->global->GC_EMAIL_DOMAIN;
		$finded = false;

		$trimmed = trim($text);
		$domains = str_replace(' ', '', $trimmed);
		$array = explode(',', $domains);	

		foreach ($array as $domain) {
			$thisdomain = '@'.$domain;
			$email_domain = strstr($email, '@');

			if ($thisdomain == $email_domain){
				$finded = true;
			}
		}

		if (false != $finded) {
			return true;
		} else {
			return false;
		}

	}


	public function findContacts(){

		$this->global_db->begin();

		$contacts = array();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."user ORDER BY rowid ASC";

		$resql = $this->global_db->query($sql);

		if ($resql) {
			$num = $this->global_db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->global_db->fetch_object($resql);
					if ($obj) {
						$contacts[$i] = $obj->email;
					}
					$i++;
				}
			}

			return $contacts;
			} else {
				$error++;
				dol_print_error($this->global_db);
				return false;
			}
			
		$this->global_db->close();

	}

	public function findSingleContact($email){
		$this->global_db->begin();

		$sql = "SELECT pass, email FROM ".MAIN_DB_PREFIX."user";
		$sql.= " WHERE email = "."'".$email."'";

		//dol_syslog($script_file, LOG_DEBUG);

		$resql = $this->global_db->query($sql);

		if ($resql) {
			$obj = $this->global_db->fetch_object($resql);
			$user = array(
			    "email" => $obj->email,
			    "pass" => $obj->pass
			);

			return $user;

			} else {
				return false;
			}

		$this->global_db->close();
	}

}
