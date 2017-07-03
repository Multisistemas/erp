<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use Luracast\Restler\RestException;

//require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';

/**
 * API class for users
 *
 * @access protected 
 * @class  DolibarrApiAccess {@requires user,external}
 */
class Users extends DolibarrApi
{
	/**
	 *
	 * @var array   $FIELDS     Mandatory fields, checked when create and update object 
	 */
	static $FIELDS = array(
		'login'
	);

	/**
	 * @var User $user {@type User}
	 */
	public $useraccount;

	/**
	 * Constructor
	 */
	function __construct() {
		global $db, $conf;
		$this->db = $db;
		$this->useraccount = new User($this->db);
	}

	
	/**
	 * List Users
	 *
	 * Get a list of Users
	 *
	 * @param string	$sortfield	Sort field
	 * @param string	$sortorder	Sort order
	 * @param int		$limit		Limit for list
	 * @param int		$page		Page number
	 * @param string   	$user_ids   User ids filter field. Example: '1' or '1,2,3'          {@pattern /^[0-9,]*$/i}
     * @param string    $sqlfilters Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @return  array               Array of User objects
	 */
	function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 0, $page = 0, $user_ids = 0, $sqlfilters = '') {
	    global $db, $conf;
	
	    $obj_ret = array();
	
		if(! DolibarrApiAccess::$user->rights->user->user->lire) {
	       throw new RestException(401, "You are not allowed to read list of users");
	    }
	     
	    // case of external user, $societe param is ignored and replaced by user's socid
	    //$socid = DolibarrApiAccess::$user->societe_id ? DolibarrApiAccess::$user->societe_id : $societe;
	
	    $sql = "SELECT t.rowid";
	    $sql.= " FROM ".MAIN_DB_PREFIX."user as t";
	    $sql.= ' WHERE t.entity IN ('.getEntity('user', 1).')';
	    if ($user_ids) $sql.=" AND t.rowid IN (".$user_ids.")";
	    // Add sql filters
        if ($sqlfilters) 
        {
            if (! DolibarrApi::_checkFilters($sqlfilters))
            {
                throw new RestException(503, 'Error when validating parameter sqlfilters '.$sqlfilters);
            }
	        $regexstring='\(([^:\'\(\)]+:[^:\'\(\)]+:[^:\(\)]+)\)';
            $sql.=" AND (".preg_replace_callback('/'.$regexstring.'/', 'DolibarrApi::_forge_criteria_callback', $sqlfilters).")";
        }
	    
	    $sql.= $db->order($sortfield, $sortorder);
	    if ($limit)	{
	        if ($page < 0)
	        {
	            $page = 0;
	        }
	        $offset = $limit * $page;
	
	        $sql.= $db->plimit($limit + 1, $offset);
	    }
	
	    $result = $db->query($sql);
	
	    if ($result)
	    {
	        $num = $db->num_rows($result);
	        while ($i < min($num, ($limit <= 0 ? $num : $limit)))
	        {
	            $obj = $db->fetch_object($result);
	            $user_static = new User($db);
	            if($user_static->fetch($obj->rowid)) {
	                $obj_ret[] = $this->_cleanObjectDatas($user_static);
	            }
	            $i++;
	        }
	    }
	    else {
	        throw new RestException(503, 'Error when retrieve User list : '.$db->lasterror());
	    }
	    if( ! count($obj_ret)) {
	        throw new RestException(404, 'No User found');
	    }
	    return $obj_ret;
	}
	
	/**
	 * Get properties of an user object
	 *
	 * Return an array with user informations
	 *
	 * @param 	int 	$id ID of user
	 * @return 	array|mixed data without useless information
	 * 
	 * @throws 	RestException
	 */
	function get($id) {
		//if (!DolibarrApiAccess::$user->rights->user->user->lire) {
			//throw new RestException(401);
		//}

		$result = $this->useraccount->fetch($id);
		if (!$result)
		{
			throw new RestException(404, 'User not found');
		}

		if (!DolibarrApi::_checkAccessToResource('user', $this->useraccount->id, 'user'))
		{
			throw new RestException(401, 'Access not allowed for login ' . DolibarrApiAccess::$user->login);
		}

		return $this->_cleanObjectDatas($this->useraccount);
	}
	
	
	/**
	 * Create user account
	 *
	 * @param array $request_data New user data
	 * @return int
	 */
	function post($request_data = NULL) {
	    // check user authorization
	    //if(! DolibarrApiAccess::$user->rights->user->creer) {
	    //   throw new RestException(401, "User creation not allowed");
	    //}
	    // check mandatory fields
	    /*if (!isset($request_data["login"]))
	        throw new RestException(400, "login field missing");
	    if (!isset($request_data["password"]))
	        throw new RestException(400, "password field missing");
	    if (!isset($request_data["lastname"]))
	         throw new RestException(400, "lastname field missing");*/
	    //assign field values
        foreach ($request_data as $field => $value)
	    {
	          $this->useraccount->$field = $value;
	    }

	    if ($this->useraccount->create(DolibarrApiAccess::$user) < 0) {
             throw new RestException(500, 'Error creating', array_merge(array($this->useraccount->error), $this->useraccount->errors));
	    }
	    return $this->useraccount->id;
    }
	
    
	/**
	 * Update account
	 *
	 * @param int   $id             Id of account to update
	 * @param array $request_data   Datas   
	 * @return int 
	 */
	function put($id, $request_data = NULL) {
		//if (!DolibarrApiAccess::$user->rights->user->user->creer) {
			//throw new RestException(401);
		//}

		$result = $this->useraccount->fetch($id);
		if (!$result)
		{
			throw new RestException(404, 'Account not found');
		}

		if (!DolibarrApi::_checkAccessToResource('user', $this->useraccount->id, 'user'))
		{
			throw new RestException(401, 'Access not allowed for login ' . DolibarrApiAccess::$user->login);
		}

		foreach ($request_data as $field => $value)
		{
            if ($field == 'id') continue;
		    $this->useraccount->$field = $value;
		}

		if ($this->useraccount->update(DolibarrApiAccess::$user, 1))
			return $this->get($id);

        return false;
    }

    /**
	 * add user to group
	 *
	 * @param   int     $id User ID
	 * @param   int     $group Group ID
	 * @return  int
     * 
	 * @url	GET {id}/setGroup/{group}
	 */
	function setGroup($id, $group) {
		//if (!DolibarrApiAccess::$user->rights->user->user->supprimer) {
			//throw new RestException(401);
		//}
        $result = $this->useraccount->fetch($id);
        if (!$result)
        {
          throw new RestException(404, 'User not found');
        }
    
        if (!DolibarrApi::_checkAccessToResource('user', $this->useraccount->id, 'user'))
        {
          throw new RestException(401, 'Access not allowed for login ' . DolibarrApiAccess::$user->login);
        }
    
        return $this->useraccount->SetInGroup($group,1);
    }

	/**
	 * Delete account
	 *
	 * @param   int     $id Account ID
	 * @return  array
	 */
	function delete($id) {
		//if (!DolibarrApiAccess::$user->rights->user->user->supprimer) {
			//throw new RestException(401);
		//}
		$result = $this->useraccount->fetch($id);
		if (!$result)
		{
			throw new RestException(404, 'User not found');
		}

		if (!DolibarrApi::_checkAccessToResource('user', $this->useraccount->id, 'user'))
		{
			throw new RestException(401, 'Access not allowed for login ' . DolibarrApiAccess::$user->login);
		}

		return $this->useraccount->delete($id);
	}

	/**
	 * Validate fields before create or update object
     * 
	 * @param   array|null     $data   Data to validate
	 * @return  array
	 * @throws RestException
	 */
	function _validate($data) {
		$account = array();
		foreach (Users::$FIELDS as $field)
		{
			if (!isset($data[$field]))
				throw new RestException(400, "$field field missing");
			$account[$field] = $data[$field];
		}
		return $account;
	}
}
