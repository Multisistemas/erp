<?php

/**
 * Multisistemas Gconnect - A Google authentication module for Dolibarr
 * Copyright (C) 2017 Herson Cruz <herson@multisistemas.com.sv>
 * Copyright (C) 2017 Luis Medrano <lmedrano@multisistemas.com.sv>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file admin/control.class.php
 * Multisistemas Team
 * Module file for users control access
 */

define('OPAUTH_LIB_DIR', dirname(__FILE__).'/../vendor/autoload.php');

require_once(OPAUTH_LIB_DIR);
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';

class Control {

  public $global_conf;
  public $global_db;

  /**
   * Start the object
   */
  function __construct($conf, $db) {
    $this->global_conf = $conf;
    $this->global_db = $db;
  }

  /**
   * Build the Opauth object for the first call
   */
  public function buildOpauth(){

  	try {

    	$Opauth = new Opauth($this->SetOpauthConfig(), false);

		} catch (Exception $e) {

    	return false;
		}
  		
  	return $Opauth;
	}

	/**
   * Build the Opauth object for the answer in the second call
   */
	public function rebuildOpauth(){
		try {

    	$Opauth = new Opauth($this->SetOpauthConfig());

		} catch (Exception $e) {
			
    	return false;
		}
  		
  	return $Opauth;	
	}

	/**
   * Set the Opauth config
   */
	public function SetOpauthConfig(){

		$temp = $this->global_conf->global->GC_ORIGIN_CALL;

		if ($temp !== NULL && !empty($temp)) {
			if ($temp == '/'){
				$path = '/index.php/';
				$host = ((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'];
			} else {
				$path = '/'.$temp.'/index.php/';
				$host = ((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/'.$temp.'/';	
			}
		} else {
			$path = '/index.php/';
			$host = ((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'];
		}

		return $config = array(
				'path' => $path,
				'callback_url' => $host,
				'strategy_dir' => '../vendor/opauth/',
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

	/**
   * Validate the email domain
   */
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

	/**
   * Get all users registered
   */
	public function findContacts(){

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

	}

	/**
   * Insert a new User with default rights
   */
	public function insertUser($data){
		$date = new DateTime();
		$date->getTimestamp();

		$cdate = $date->format('Y-m-d H:i:s');
		$login = $data['auth']['info']['email'];
		$pass = $this->generatePass();
		$lastname = $data['auth']['info']['last_name'];
		$firstname = $data['auth']['info']['first_name'];
		$email = $data['auth']['info']['email'];
		$pass_crypted = dol_hash($pass);
		$thegender = 'NULL';

		if (isset($data['auth']['raw']['gender']) && $data['auth']['raw']['gender'] != NULL) {
			$gender = $data['auth']['raw']['gender'];
			if ($gender == 'male') {
				$thegender = 'man';
			} else if($gender == 'female'){
				$thegender == 'woman';
			}
		}

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."user (datec, login, pass, pass_crypted, lastname, firstname, email, gender)"; 
		$sql.= " VALUES ('".$cdate."','".$login."','".$pass."','".$pass_crypted."','".$lastname."','".$firstname."','".$email."','".$thegender."')";
		if(!$result = $this->global_db->query($sql)){
      return false;
		}
		$theID = $this->global_db->last_insert_id(MAIN_DB_PREFIX."user");
		$this->setDefautPrivileges($theID);

		return true;
	}

	/**
   * Start a new session in the sistem
   */
	public function session_init($email){
		global $conf;

		$theuser = $this->getTheUser($email);
		
		$_SESSION["dol_login"]= trim($theuser['login']);
    $_SESSION["dol_authmode"]='dolibarr';
    $_SESSION["dol_tz"]='';
    $_SESSION["dol_tz_string"]=trim(date_default_timezone_get());
    $_SESSION["dol_dst"]='0';
    $_SESSION["dol_dst_observed"]='0';
    $_SESSION["dol_dst_first"]='';
    $_SESSION["dol_dst_second"]='';
    $_SESSION["dol_screenwidth"]='';
    $_SESSION["dol_screenheight"]='';
    $_SESSION["dol_company"]=$conf->global->MAIN_INFO_SOCIETE_NOM;
    $_SESSION["dol_entity"]=$conf->entity;

	}

	/**
   * Get the login for the user
   */
	private function getTheUser($email){

		$sql = "SELECT login FROM ".MAIN_DB_PREFIX."user";
		$sql.= " WHERE email = "."'".$email."'";

		$resql = $this->global_db->query($sql);

		$obj = $this->global_db->fetch_object($resql);
		$user = array("login" => $obj->login);

		return $user;

	}

	/**
   * Build a ramdom string
   */
	public function generatePass(){
		$generated_password = '';
    $generated_password = getRandomPassword(false);
    return $generated_password;
	}

	/**
   * Build a new login if this allready exists
   */
	private function findLogin($email){
		$login = strstr($email, '@', true);

		$sql = "SELECT login FROM ".MAIN_DB_PREFIX."user";
		$sql.= " WHERE login ='".$login."'";

		$resql= $this->global_db->query($sql);

		$num = $this->global_db->num_rows($resql);

		if ($num) {
			$thelogin = $login.$num;
		} else {
			$thelogin = $login;
		}

		return $login;
	}

	/**
   * Set default privileges for the new user
   */
	private function setDefautPrivileges($id){

		$sql = "SELECT id FROM ".MAIN_DB_PREFIX."rights_def";
		$sql.= " WHERE bydefault = 1";
		$sql.= " AND entity = ".$this->global_conf->entity;

		$resql = $this->global_db->query($sql);
		if ($resql)
		{
			$num = $this->global_db->num_rows($resql);
			$i = 0;
			$rd = array();

			while ($i < $num)
			{
				$row = $this->global_db->fetch_row($resql);
				$rd[$i] = $row[0];
				$i++;
			}

			$this->global_db->free($resql);
		}

		$i = 0;
		while ($i < $num)
		{

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."user_rights WHERE fk_user = $id AND fk_id=$rd[$i]";
			if(!$result=$this->global_db->query($sql)){
				return -1;
			}

			$sql = "INSERT INTO ".MAIN_DB_PREFIX."user_rights (fk_user, fk_id) VALUES ($id, $rd[$i])";
			$result=$this->global_db->query($sql);
			if(!$result){
				return -1;
			}
			$i++;
		}

		return $i;
	}

}
