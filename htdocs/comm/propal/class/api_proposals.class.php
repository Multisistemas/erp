<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2016   Laurent Destailleur     <eldy@users.sourceforge.net>
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

 require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';

/**
 * API class for orders
 *
 * @access protected 
 * @class  DolibarrApiAccess {@requires user,external}
 */
class Proposals extends DolibarrApi
{

    /**
     * @var array   $FIELDS     Mandatory fields, checked when create and update object 
     */
    static $FIELDS = array(
        'socid'
    );

    /**
     * @var propal $propal {@type propal}
     */
    public $propal;

    /**
     * Constructor
     */
    function __construct()
    {
		global $db, $conf;
		$this->db = $db;
        $this->propal = new Propal($this->db);
    }

    /**
     * Get properties of a commercial proposal object
     *
     * Return an array with commercial proposal informations
     * 
     * @param       int         $id         ID of commercial proposal
     * @return 	array|mixed data without useless information
	 *
     * @throws 	RestException
     */
    function get($id)
    {		
		if(! DolibarrApiAccess::$user->rights->propal->lire) {
			throw new RestException(401);
		}
			
        $result = $this->propal->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'Commercial Proposal not found');
        }
		
		if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}
        
        $this->propal->fetchObjectLinked();
		return $this->_cleanObjectDatas($this->propal);
    }

    /**
     * List commercial proposals
     * 
     * Get a list of commercial proposals
     * 
     * @param string	$sortfield	        Sort field
     * @param string	$sortorder	        Sort order
     * @param int		$limit		        Limit for list
     * @param int		$page		        Page number
     * @param string   	$thirdparty_ids	    Thirdparty ids to filter commercial proposal of. Example: '1' or '1,2,3'          {@pattern /^[0-9,]*$/i}
     * @param string    $sqlfilters         Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.datec:<:'20160101')"
     * @return  array                       Array of order objects
     */
    function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 0, $page = 0, $thirdparty_ids = '', $sqlfilters = '') {
        global $db, $conf;
        
        $obj_ret = array();

        // case of external user, $thirdparty_ids param is ignored and replaced by user's socid
        $socids = DolibarrApiAccess::$user->societe_id ? DolibarrApiAccess::$user->societe_id : $thirdparty_ids;
            
        // If the internal user must only see his customers, force searching by him
        $search_sale = 0;
        if (! DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) $search_sale = DolibarrApiAccess::$user->id;

        $sql = "SELECT t.rowid";
        if ((!DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0) $sql .= ", sc.fk_soc, sc.fk_user"; // We need these fields in order to filter by sale (including the case where the user can only see his prospects)
        $sql.= " FROM ".MAIN_DB_PREFIX."propal as t";
        
        if ((!DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc"; // We need this table joined to the select in order to filter by sale

        $sql.= ' WHERE t.entity IN ('.getEntity('propal', 1).')';
        if ((!DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0) $sql.= " AND t.fk_soc = sc.fk_soc";
        if ($socids) $sql.= " AND t.fk_soc IN (".$socids.")";
        if ($search_sale > 0) $sql.= " AND t.rowid = sc.fk_soc";		// Join for the needed table to filter by sale
        // Insert sale filter
        if ($search_sale > 0)
        {
            $sql .= " AND sc.fk_user = ".$search_sale;
        }
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
                $propal_static = new Propal($db);
                if($propal_static->fetch($obj->rowid)) {
                    $obj_ret[] = $this->_cleanObjectDatas($propal_static);
                }
                $i++;
            }
        }
        else {
            throw new RestException(503, 'Error when retrieve propal list : '.$db->lasterror());
        }
        if( ! count($obj_ret)) {
            throw new RestException(404, 'No order found');
        }
		return $obj_ret;
    }

    /**
     * Create commercial proposal object
     *
     * @param   array   $request_data   Request data
     * @return  int     ID of propal
     */
    function post($request_data = NULL)
    {
      if(! DolibarrApiAccess::$user->rights->propal->creer) {
			  throw new RestException(401, "Insuffisant rights");
		  }
        // Check mandatory fields
        $result = $this->_validate($request_data);

        foreach($request_data as $field => $value) {
            $this->propal->$field = $value;
        }
        /*if (isset($request_data["lines"])) {
          $lines = array();
          foreach ($request_data["lines"] as $line) {
            array_push($lines, (object) $line);
          }
          $this->propal->lines = $lines;
        }*/
        if ($this->propal->create(DolibarrApiAccess::$user) < 0) {
            throw new RestException(500, "Error creating order", array_merge(array($this->propal->error), $this->propal->errors));
        }
        
        return $this->propal->id;
    }

    /**
     * Get lines of a commercial proposal
     *
     * @param int   $id             Id of commercial proposal
     * 
     * @url	GET {id}/lines
     * 
     * @return int 
     */
    function getLines($id) {
      if(! DolibarrApiAccess::$user->rights->propal->lire) {
		  	throw new RestException(401);
		  }
        
      $result = $this->propal->fetch($id);
      if( ! $result ) {
         throw new RestException(404, 'Commercial Proposal not found');
      }
		
		  if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			  throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
      }
      $this->propal->getLinesArray();
      $result = array();
      foreach ($this->propal->lines as $line) {
        array_push($result,$this->_cleanObjectDatas($line));
      }
      return $result;
    }

    /**
     * Add a line to given commercial proposal
     *
     * @param int   $id             Id of commercial proposal to update
     * @param array $request_data   Commercial proposal line data   
     * 
     * @url	POST {id}/lines
     * 
     * @return int 
     */
    function postLine($id, $request_data = NULL) {
      if(! DolibarrApiAccess::$user->rights->propal->creer) {
		  	throw new RestException(401);
		  }
        
      $result = $this->propal->fetch($id);
      if( ! $result ) {
         throw new RestException(404, 'Commercial Proposal not found');
      }
		
		  if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			  throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
      }
			$request_data = (object) $request_data;
      $updateRes = $this->propal->addline(
                        $request_data->desc,
                        $request_data->subprice,
                        $request_data->qty,
                        $request_data->tva_tx,
                        $request_data->localtax1_tx,
                        $request_data->localtax2_tx,
                        $request_data->fk_product,
                        $request_data->remise_percent,
                        $request_data->info_bits,
                        $request_data->fk_remise_except,
                        'HT',
                        0,
                        $request_data->date_start,
                        $request_data->date_end,
                        $request_data->product_type,
                        $request_data->rang,
                        $request_data->special_code,
                        $fk_parent_line,
                        $request_data->fk_fournprice,
                        $request_data->pa_ht,
                        $request_data->label,
                        $request_data->array_options,
                        $request_data->fk_unit,
                        $this->element,
                        $request_data->id
      );

      if ($updateRes > 0) {
        return $this->get($id)->line->rowid;

      }
      return false;
    }

    /**
     * Update a line of given commercial proposal
     *
     * @param int   $id             Id of commercial proposal to update
     * @param int   $lineid         Id of line to update
     * @param array $request_data   Commercial proposal line data   
     * 
     * @url	PUT {id}/lines/{lineid}
     * 
     * @return object 
     */
    function putLine($id, $lineid, $request_data = NULL) {
      if(! DolibarrApiAccess::$user->rights->propal->creer) {
		  	throw new RestException(401);
		  }
        
      $result = $this->propal->fetch($id);
      if( ! $result ) {
         throw new RestException(404, 'Proposal not found');
      }
		
		  if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			  throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
      }
			$request_data = (object) $request_data;
      $updateRes = $this->propal->updateline(
                        $lineid,
                        $request_data->desc,
                        $request_data->subprice,
                        $request_data->qty,
                        $request_data->remise_percent,
                        $request_data->tva_tx,
                        $request_data->localtax1_tx,
                        $request_data->localtax2_tx,
                        'HT',
                        $request_data->info_bits,
                        $request_data->date_start,
                        $request_data->date_end,
                        $request_data->product_type,
                        $request_data->fk_parent_line,
                        0,
                        $request_data->fk_fournprice,
                        $request_data->pa_ht,
                        $request_data->label,
                        $request_data->special_code,
                        $request_data->array_options,
                        $request_data->fk_unit
      );

      if ($updateRes > 0) {
        $result = $this->get($id);
        unset($result->line);
        return $this->_cleanObjectDatas($result);
      }
      return false;
    }

    /**
     * Delete a line of given commercial proposal
     *
     *
     * @param int   $id             Id of commercial proposal to update
     * @param int   $lineid         Id of line to delete
     * 
     * @url	DELETE {id}/lines/{lineid}
     * 
     * @return int 
     */
    function delLine($id, $lineid) {
      if(! DolibarrApiAccess::$user->rights->propal->creer) {
		  	throw new RestException(401);
		  }
        
      $result = $this->propal->fetch($id);
      if( ! $result ) {
         throw new RestException(404, 'Proposal not found');
      }
		
		  if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			  throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
      }
			$request_data = (object) $request_data;
      $updateRes = $this->propal->deleteline($lineid);
      if ($updateRes == 1) {
        return $this->get($id);
      }
      return false;
    }

    /**
     * Update commercial proposal general fields (won't touch lines of commercial proposal)
     *
     * @param int   $id             Id of commercial proposal to update
     * @param array $request_data   Datas   
     * 
     * @return int 
     */
    function put($id, $request_data = NULL) {
      if(! DolibarrApiAccess::$user->rights->propal->creer) {
		  	throw new RestException(401);
		  }
        
        $result = $this->propal->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'Proposal not found');
        }
		
		if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}
        foreach($request_data as $field => $value) {
            if ($field == 'id') continue;
            $this->propal->$field = $value;
        }
        
        if($this->propal->update($id, DolibarrApiAccess::$user,1,'','','update'))
            return $this->get($id);
        
        return false;
    }
    
    /**
     * Delete commercial proposal
     *
     * @param   int     $id         Commercial proposal ID
     * 
     * @return  array
     */
    function delete($id)
    {
        if(! DolibarrApiAccess::$user->rights->propal->supprimer) {
			throw new RestException(401);
		}
        $result = $this->propal->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'Commercial Proposal not found');
        }
		
		if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}
        
        if( ! $this->propal->delete(DolibarrApiAccess::$user)) {
            throw new RestException(500, 'Error when delete Commercial Proposal : '.$this->propal->error);
        }
        
        return array(
            'success' => array(
                'code' => 200,
                'message' => 'Commercial Proposal deleted'
            )
        );
        
    }
    
    /**
     * Validate a commercial proposal
     * 
     * @param   int     $id             Commercial proposal ID
     * @param   int     $notrigger      Use {}
     * 
     * @url POST    {id}/validate
     *  
     * @return  array
     * FIXME An error 403 is returned if the request has an empty body.
     * Error message: "Forbidden: Content type `text/plain` is not supported."
     * Workaround: send this in the body
     * {
     * "notrigger": 0
     * }
     */
    function validate($id, $notrigger=0)
    {
        if(! DolibarrApiAccess::$user->rights->propal->creer) {
			throw new RestException(401);
		}
        $result = $this->propal->fetch($id);
        if( ! $result ) {
            throw new RestException(404, 'Commercial Proposal not found');
        }
		
		if( ! DolibarrApi::_checkAccessToResource('propal',$this->propal->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}
        
        $result = $this->propal->valid(DolibarrApiAccess::$user, $notrigger);
        if ($result == 0) {
            throw new RestException(500, 'Error nothing done. May be object is already validated');
        }
        if ($result < 0) {
            throw new RestException(500, 'Error when validating Commercial Proposal: '.$this->propal->error);
        }
        
        return array(
            'success' => array(
                'code' => 200,
                'message' => 'Commercial Proposal validated'
            )
        );
    }
    
    /**
     * Validate fields before create or update object
     * 
     * @param   array           $data   Array with data to verify
     * @return  array           
     * @throws  RestException
     */
    function _validate($data)
    {
        $propal = array();
        foreach (Orders::$FIELDS as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
            $propal[$field] = $data[$field];
            
        }
        return $propal;
    }
}
