<?php
/* Copyright (C) 2006-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/**
 *		\file       htdocs/includes/modules/export/export_csv.modules.php
 *		\ingroup    export
 *		\brief      File of class to build exports with CSV format
 *		\author	    Laurent Destailleur
 *		\version    $Id: export_csv.modules.php,v 1.31 2011/08/03 01:38:53 eldy Exp $
 */

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/export/modules_export.php");


/**
 *	    \class      ExportCsv
 *		\brief      Class to build export files with format CSV
 */
class ExportCsv extends ModeleExports
{
	var $id;
	var $label;
	var $extension;
	var $version;

	var $label_lib;
	var $version_lib;

	var $separator;

	var $handle;    // Handle fichier


	/**
	 *		\brief      Constructeur
	 *		\param	    db      Handler acces base de donnee
	 */
	function ExportCsv($db)
	{
		global $conf,$langs;
		$this->db = $db;

		$this->separator=',';
		if (! empty($conf->global->EXPORT_CSV_SEPARATOR_TO_USE)) $this->separator=$conf->global->EXPORT_CSV_SEPARATOR_TO_USE;
		$this->escape='"';
		$this->enclosure='"';

		$this->id='csv';                // Same value then xxx in file name export_xxx.modules.php
		$this->label='Csv';             // Label of driver
		$this->desc=$langs->trans("CSVFormatDesc",$this->separator,$this->enclosure,$this->escape);
		$this->extension='csv';         // Extension for generated file by this driver
		$this->picto='mime/other';		// Picto
		$ver=explode(' ','$Revision: 1.31 $');
		$this->version=$ver[2];         // Driver version

		// If driver use an external library, put its name here
		$this->label_lib='Dolibarr';
		$this->version_lib=DOL_VERSION;

	}

	function getDriverId()
	{
		return $this->id;
	}

	function getDriverLabel()
	{
		return $this->label;
	}

	function getDriverDesc()
	{
		return $this->desc;
	}

	function getDriverExtension()
	{
		return $this->extension;
	}

	function getDriverVersion()
	{
		return $this->version;
	}

	function getLibLabel()
	{
		return $this->label_lib;
	}

	function getLibVersion()
	{
		return $this->version_lib;
	}


	/**
	 *	\brief		Open output file
	 *	\param		file		Path of filename
	 *	\return		int			<0 if KO, >=0 if OK
	 */
	function open_file($file,$outputlangs)
	{
		global $langs;

		dol_syslog("ExportCsv::open_file file=".$file);

		$ret=1;

		$outputlangs->load("exports");
		$this->handle = fopen($file, "wt");
		if (! $this->handle)
		{
			$langs->load("errors");
			$this->error=$langs->trans("ErrorFailToCreateFile",$file);
			$ret=-1;
		}

		return $ret;
	}

	/**
	 * 	\brief		Output header into file
	 * 	\param		langs		Output language
	 */
	function write_header($outputlangs)
	{
		return 0;
	}


	/**
	 * 	   Output title line into file
     *     @param      array_export_fields_label   Array with list of label of fields
     *     @param      array_selected_sorted       Array with list of field to export
     *     @param      outputlangs                 Object lang to translate values
	 */
	function write_title($array_export_fields_label,$array_selected_sorted,$outputlangs)
	{
		global $conf;

		if (! empty($conf->global->EXPORT_CSV_FORCE_CHARSET))
		{
			$outputlangs->charset_output = $conf->global->EXPORT_CSV_FORCE_CHARSET;
		}
		else
		{
			$outputlangs->charset_output = 'ISO-8859-1';
		}

		foreach($array_selected_sorted as $code => $value)
		{
			$newvalue=$outputlangs->transnoentities($array_export_fields_label[$code]);
			$newvalue=$this->csv_clean($newvalue,$outputlangs->charset_output);

			fwrite($this->handle,$newvalue.$this->separator);
		}
		fwrite($this->handle,"\n");
		return 0;
	}


	/**
     *     Output record line into file
     *     @param      array_selected_sorted       Array with list of field to export
     *     @param      objp                        A record from a fetch with all fields from select
     *     @param      outputlangs                 Object lang to translate values
	 */
	function write_record($array_selected_sorted,$objp,$outputlangs)
	{
		global $conf;

		if (! empty($conf->global->EXPORT_CSV_FORCE_CHARSET))
		{
			$outputlangs->charset_output = $conf->global->EXPORT_CSV_FORCE_CHARSET;
		}
		else
		{
			$outputlangs->charset_output = 'ISO-8859-1';
		}

		$this->col=0;
		foreach($array_selected_sorted as $code => $value)
		{
			$alias=str_replace(array('.','-'),'_',$code);
			if (empty($alias)) dol_print_error('','Bad value for field with key='.$code.'. Try to redefine export.');
			$newvalue=$outputlangs->convToOutputCharset($objp->$alias);

			// Translation newvalue
			if (preg_match('/^\((.*)\)$/i',$newvalue,$reg))
			{
				$newvalue=$outputlangs->transnoentities($reg[1]);
			}

			$newvalue=$this->csv_clean($newvalue,$outputlangs->charset_output);

			fwrite($this->handle,$newvalue.$this->separator);
			$this->col++;
		}

		fwrite($this->handle,"\n");
		return 0;
	}

	/**
	 * 	\brief		Output footer into file
	 * 	\param		outputlangs		Output language
	 */
	function write_footer($outputlangs)
	{
		return 0;
	}

	/**
	 * 	\brief		Close file handle
	 */
	function close_file()
	{
		fclose($this->handle);
		return 0;
	}

	/**
	 * Clean a cell to respect rules of CSV file cells
	 *
	 * @param 	string	$newvalue	String to clean
	 * @param	string	$charset	Output character set
	 * @return 	string				Value cleaned
	 */
	function csv_clean($newvalue, $charset)
	{
		$addquote=0;

		// Rule Dolibarr: No HTML
		$newvalue=dol_string_nohtmltag($newvalue,1,$charset);

		// Rule 1 CSV: No CR, LF in cells
		$newvalue=str_replace("\r",'',$newvalue);
		$newvalue=str_replace("\n",'\n',$newvalue);

		// Rule 2 CSV: If value contains ", we must escape with ", and add "
		if (preg_match('/"/',$newvalue))
		{
			$addquote=1;
			$newvalue=str_replace('"','""',$newvalue);
		}

		// Rule 3 CSV: If value contains separator, we must add "
		if (preg_match('/'.$this->separator.'/',$newvalue))
		{
			$addquote=1;
		}

		return ($addquote?'"':'').$newvalue.($addquote?'"':'');
	}

}

?>
