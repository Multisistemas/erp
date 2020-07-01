<?php
/* Copyright (C) 2013-2018	Jean-François FERRY	<hello@librethic.io>
 * Copyright (C) 2016		Christophe Battarel	<christophe@altairis.fr>
 * Copyright (C) 2018		Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2019		Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2019		Laurent Destailleur <eldy@users.sourceforge.net>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file     htdocs/ticket/list.php
 *    \ingroup	ticket
 *    \brief    List page for tickets
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/actions_ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
include_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("ticket", "companies", "other", "projects"));


// Get parameters
$action     = GETPOST('action', 'aZ09') ?GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...
$massaction = GETPOST('massaction', 'alpha'); // The bulk action (combo box choice into lists)
$show_files = GETPOST('show_files', 'int'); // Show files area generated by bulk actions ?
$confirm    = GETPOST('confirm', 'alpha'); // Result of a confirmation
$cancel     = GETPOST('cancel', 'alpha'); // We click on a Cancel button
$toselect   = GETPOST('toselect', 'array'); // Array of ids of elements selected into a list
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'ticketlist'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha'); // Go back to a dedicated page
$optioncss  = GETPOST('optioncss', 'aZ'); // Option for the css output (always '' except when 'print')

$id = GETPOST('id', 'int');
$msg_id     = GETPOST('msg_id', 'int');
$socid      = GETPOST('socid', 'int');
$projectid  = GETPOST('projectid', 'int');
$project_ref = GETPOST('project_ref', 'alpha');
$search_societe = GETPOST('search_societe', 'alpha');
$search_fk_project = GETPOST('search_fk_project', 'int') ?GETPOST('search_fk_project', 'int') : GETPOST('projectid', 'int');
$search_fk_status = GETPOST('search_fk_statut', 'array');
$mode = GETPOST('mode', 'alpha');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) { $page = 0; }     // If $page is not defined, or '' or -1 or if we click on clear filters
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects
$object = new Ticket($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->ticket->dir_output.'/temp/massgeneration/'.$user->id;
if ($socid > 0)         $hookmanager->initHooks(array('thirdpartyticket'));
elseif ($projectid > 0) $hookmanager->initHooks(array('projectticket'));
else $hookmanager->initHooks(array('ticketlist'));
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Default sort order (if not yet defined by previous GETPOST)
if (!$sortfield) $sortfield = "t.datec";
if (!$sortorder) $sortorder = "DESC";

if (GETPOST('search_fk_status', 'alpha') == 'non_closed') $_GET['search_fk_statut'][] = 'openall'; // For backward compatibility

// Initialize array of search criterias
$search_all = trim(GETPOSTISSET("search_all") ?GETPOSTISSET("search_all", 'alpha') : GETPOST('sall'));
$search = array();
foreach ($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key, 'alpha')) $search[$key] = GETPOST('search_'.$key, 'alpha');
}

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	's.nom'=>"ThirdParty",
	's.name_alias'=>"AliasNameShort",
	's.zip'=>"Zip",
	's.town'=>"Town",
);
foreach ($object->fields as $key => $val)
{
	if ($val['searchall']) $fieldstosearchall['t.'.$key] = $val['label'];
}

// Definition of fields for list
$arrayfields = array();
foreach ($object->fields as $key => $val)
{
	// If $val['visible']==0, then we never show the field
	if (!empty($val['visible'])) $arrayfields['t.'.$key] = array('label'=>$val['label'], 'checked'=>(($val['visible'] < 0) ? 0 : 1), 'enabled'=>$val['enabled'], 'position'=>$val['position']);
}
// Extra fields
if (is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label']) > 0)
{
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val)
	{
		if (!empty($extrafields->attributes[$object->table_element]['list'][$key]))
			$arrayfields["ef.".$key] = array('label'=>$extrafields->attributes[$object->table_element]['label'][$key], 'checked'=>(($extrafields->attributes[$object->table_element]['list'][$key] < 0) ? 0 : 1), 'position'=>$extrafields->attributes[$object->table_element]['pos'][$key], 'enabled'=>(abs($extrafields->attributes[$object->table_element]['list'][$key]) != 3 && $extrafields->attributes[$object->table_element]['perms'][$key]));
	}
}
$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');
//if ($socid > 0) $arrayfields['t.fk_soc']['enabled']=0;
//if ($projectid > 0) $arrayfields['t.fk_project']['enabled']=0;


// Security check
if (!$user->rights->ticket->read) {
    accessforbidden();
}

// Store current page url
$url_page_current = dol_buildpath('/ticket/list.php', 1);

if ($project_ref)
{
	$tmpproject = new Project($db);
	$tmpproject->fetch(0, $project_ref);
	$projectid = $tmpproject->id;
	$search_fk_project = $projectid;
}


/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) { $action = 'list'; $massaction = ''; }
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction = ''; }

$parameters = array();
if ($socid > 0) $parameters['socid'] = $socid;
if ($projectid > 0) $parameters['projectid'] = $projectid;
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// Selection of new fields
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
	{
		foreach ($object->fields as $key => $val)
		{
			$search[$key] = '';
		}
		$toselect = '';
		$search_array_options = array();
	}
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')
		|| GETPOST('button_search_x', 'alpha') || GETPOST('button_search.x', 'alpha') || GETPOST('button_search', 'alpha'))
	{
		$massaction = ''; // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass = 'Ticket';
	$objectlabel = 'Ticket';
	$permissiontoread = $user->rights->ticket->read;
	$permissiontodelete = $user->rights->ticket->delete;
	$uploaddir = $conf->ticket->dir_output;
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}



/*
 * View
 */

$form = new Form($db);
$formTicket = new FormTicket($db);

$now = dol_now();

$user_assign = new User($db);
$user_create = new User($db);
$socstatic = new Societe($db);

$help_url = '';
$title = $langs->trans('TicketList');


// Build and execute select
// --------------------------------------------------------------------
$sql = 'SELECT ';
foreach ($object->fields as $key => $val)
{
	$sql .= 't.'.$key.', ';
}
// Add fields from extrafields
if (!empty($extrafields->attributes[$object->table_element]['label']))
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) $sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? "ef.".$key.' as options_'.$key.', ' : '');
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
$sql = preg_replace('/, $/', '', $sql);
$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
if (is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) $sql .= " LEFT JOIN ".MAIN_DB_PREFIX.$object->table_element."_extrafields as ef on (t.rowid = ef.fk_object)";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON (t.fk_soc = s.rowid)";
$sql .= " WHERE t.entity IN (".getEntity($object->element).")";
if ($socid > 0)
{
	$sql .= " AND t.fk_soc = ".$socid;
}

foreach ($search as $key => $val)
{
    if ($key == 'fk_statut')
	{
	    $tmpstatus = '';
	    if ($search['fk_statut'] == 'openall' || in_array('openall', $search['fk_statut'])) $tmpstatus .= ($tmpstatus ? ',' : '')."'".Ticket::STATUS_NOT_READ."', '".Ticket::STATUS_READ."', '".Ticket::STATUS_ASSIGNED."', '".Ticket::STATUS_IN_PROGRESS."', '".Ticket::STATUS_NEED_MORE_INFO."', '".Ticket::STATUS_WAITING."'";
	    if ($search['fk_statut'] == 'closeall' || in_array('closeall', $search['fk_statut'])) $tmpstatus .= ($tmpstatus ? ',' : '')."'".Ticket::STATUS_CLOSED."', '".Ticket::STATUS_CANCELED."'";
	    if ($tmpstatus) $sql .= " AND fk_statut IN (".$tmpstatus.")";
	    elseif (is_array($search[$key]) && count($search[$key])) $sql .= natural_search($key, join(',', $search[$key]), 2);
	    continue;
	}
	if ($key == 'fk_user_assign')
	{
	    if ($search[$key] > 0) $sql .= natural_search($key, $search[$key], 2);
	    continue;
	}
	$mode_search = (($object->isInt($object->fields[$key]) || $object->isFloat($object->fields[$key])) ? 1 : 0);
    if ($search[$key] != '') $sql .= natural_search($key, $search[$key], $mode_search);
}
if ($search_all) $sql .= natural_search(array_keys($fieldstosearchall), $search_all);
if ($search_societe)     $sql .= natural_search('s.nom', $search_societe);
if ($search_fk_project) $sql .= natural_search('fk_project', $search_fk_project, 2);
if (!$user->socid && ($mode == "mine" || (!$user->admin && $conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY))) {
    $sql .= " AND (t.fk_user_assign = ".$user->id;
    if (empty($conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY)) $sql .= " OR t.fk_user_create = ".$user->id;
    $sql .= ")";
}

// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= $db->order($sortfield, $sortorder);

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
	if (($page * $limit) > $nbtotalofrecords)	// if total of record found is smaller than page * limit, goto and load page 0
	{
		$page = 0;
		$offset = 0;
	}
}
// if total of record found is smaller than limit, no need to do paging and to restart another select with limits set.
if (is_numeric($nbtotalofrecords) && $limit > $nbtotalofrecords)
{
	$num = $nbtotalofrecords;
}
else
{
	$sql .= $db->plimit($limit + 1, $offset);

	$resql = $db->query($sql);
	if (!$resql)
	{
		dol_print_error($db);
		exit;
	}

	$num = $db->num_rows($resql);
}

// Direct jump if only one record found
if ($num == 1 && !empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $search_all)
{
	$obj = $db->fetch_object($resql);
	$id = $obj->rowid;
	header("Location: ".DOL_URL_ROOT.'/ticket/card.php?id='.$id);
	exit;
}


// Output page
// --------------------------------------------------------------------

llxHeader('', $title, $help_url);


if ($socid && !$projectid && !$project_ref && $user->rights->societe->lire) {
    $socstat = new Societe($db);
    $res = $socstat->fetch($socid);
    if ($res > 0) {
    	$tmpobject = $object;
    	$object = $socstat; // $object must be of type Societe when calling societe_prepare_head
        $head = societe_prepare_head($socstat);
		$object = $tmpobject;

        dol_fiche_head($head, 'ticket', $langs->trans("ThirdParty"), -1, 'company');

        dol_banner_tab($socstat, 'socid', '', ($user->socid ? 0 : 1), 'rowid', 'nom');

        print '<div class="fichecenter">';

        print '<div class="underbanner clearboth"></div>';
        print '<table class="border centpercent tableforfield">';

        // Customer code
        if ($socstat->client && !empty($socstat->code_client)) {
            print '<tr><td class="titlefield">';
            print $langs->trans('CustomerCode').'</td><td>';
            print $socstat->code_client;
            if ($socstat->check_codeclient() != 0) {
                print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
            }

            print '</td>';
            print '</tr>';
        }
        // Supplier code
        if ($socstat->fournisseur && !empty($socstat->code_fournisseur)) {
        	print '<tr><td class="titlefield">';
        	print $langs->trans('SupplierCode').'</td><td>';
        	print $socstat->code_fournisseur;
        	if ($socstat->check_codefournisseur() != 0) {
        		print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
        	}

        	print '</td>';
        	print '</tr>';
        }

        print '</table>';
        print '</div>';
        dol_fiche_end();
    }
}

if ($projectid > 0 || $project_ref) {
    $projectstat = new Project($db);
    if ($projectstat->fetch($projectid, $project_ref) > 0) {
    	$projectid = $projectstat->id;
        $projectstat->fetch_thirdparty();

        $savobject = $object;
        $object = $projectstat;

        // To verify role of users
        //$userAccess = $object->restrictedProjectArea($user,'read');
        $userWrite = $projectstat->restrictedProjectArea($user, 'write');
        //$userDelete = $object->restrictedProjectArea($user,'delete');
        //print "userAccess=".$userAccess." userWrite=".$userWrite." userDelete=".$userDelete;

        $head = project_prepare_head($projectstat);
        dol_fiche_head($head, 'ticket', $langs->trans("Project"), -1, ($projectstat->public ? 'projectpub' : 'project'));

        // Project card

        $linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

        $morehtmlref = '<div class="refidno">';
        // Title
        $morehtmlref .= $object->title;
        // Thirdparty
        if ($object->thirdparty->id > 0)
        {
        	$morehtmlref .= '<br>'.$langs->trans('ThirdParty').' : '.$object->thirdparty->getNomUrl(1, 'project');
        }
        $morehtmlref .= '</div>';

        // Define a complementary filter for search of next/prev ref.
        if (!$user->rights->projet->all->lire)
        {
        	$objectsListId = $object->getProjectsAuthorizedForUser($user, 0, 0);
        	$object->next_prev_filter = " rowid in (".(count($objectsListId) ?join(',', array_keys($objectsListId)) : '0').")";
        }

        dol_banner_tab($object, 'project_ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

        print '<div class="fichecenter">';
        print '<div class="underbanner clearboth"></div>';

        print '<table class="border tableforfield" width="100%">';

        // Visibility
        print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
        if ($projectstat->public) {
            print $langs->trans('SharedProject');
        } else {
            print $langs->trans('PrivateProject');
        }
        print '</td></tr>';

        print "</table>";

        print '</div>';
        dol_fiche_end();

        $object = $savobject;
    } else {
        print "ErrorRecordNotFound";
    }
}

$arrayofselected = is_array($toselect) ? $toselect : array();

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param .= '&contextpage='.urlencode($contextpage);
if ($limit > 0 && $limit != $conf->liste_limit) $param .= '&limit='.urlencode($limit);
foreach ($search as $key => $val)
{
    if (is_array($search[$key]) && count($search[$key])) foreach ($search[$key] as $skey) $param .= '&search_'.$key.'[]='.urlencode($skey);
    else $param .= '&search_'.$key.'='.urlencode($search[$key]);
}
if ($optioncss != '')     $param .= '&optioncss='.urlencode($optioncss);
// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';
if ($socid)     $param .= '&socid='.urlencode($socid);
if ($projectid) $param .= '&projectid='.urlencode($projectid);

// List of mass actions available
$arrayofmassactions = array(
	//'presend'=>$langs->trans("SendByMail"),
	//'builddoc'=>$langs->trans("PDFMerge"),
);
if ($user->rights->ticket->delete) $arrayofmassactions['predelete'] = '<span class="fa fa-trash paddingrightonly"></span>'.$langs->trans("Delete");
if (GETPOST('nomassaction', 'int') || in_array($massaction, array('presend', 'predelete'))) $arrayofmassactions = array();
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">';
if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';
print '<input type="hidden" name="mode" value="'.$mode.'" >';
if ($socid)     print '<input type="hidden" name="socid" value="'.$socid.'" >';
if ($projectid) print '<input type="hidden" name="projectid" value="'.$projectid.'" >';

$newcardbutton = '';
$newcardbutton .= dolGetButtonTitle($langs->trans('NewTicket'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/ticket/card.php?action=create'.($socid ? '&socid='.$socid : '').($projectid ? '&origin=projet_project&originid='.$projectid : ''), '', !empty($user->rights->ticket->write));

$picto = 'ticket';
if ($socid > 0) $picto = '';

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

if ($mode == 'mine') {
    print '<div class="opacitymedium">'.$langs->trans('TicketAssignedToMeInfos').'</div><br>';
}
// Add code for pre mass action (confirmation or email presend form)
$topicmail = "SendTicketRef";
$modelmail = "ticket";
$objecttmp = new Ticket($db);
$trackid = 'tick'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

if ($search_all)
{
	foreach ($fieldstosearchall as $key => $val) $fieldstosearchall[$key] = $langs->trans($val);
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).join(', ', $fieldstosearchall).'</div>';
}

$moreforfilter = '';
/*$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
$moreforfilter.= '</div>';*/

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
else $moreforfilter = $hookmanager->resPrint;

if (!empty($moreforfilter))
{
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";


// Fields title search
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
foreach ($object->fields as $key => $val)
{
	$cssforfield = (empty($val['css']) ? '' : $val['css']);
	if ($key == 'fk_statut') $cssforfield .= ($cssforfield ? ' ' : '').'center';
	elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) $cssforfield .= ($cssforfield ? ' ' : '').'center';
	elseif (in_array($val['type'], array('timestamp'))) $cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
	elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID') $cssforfield .= ($cssforfield ? ' ' : '').'right';
	if (!empty($arrayfields['t.'.$key]['checked'])) {
		if ($key == 'type_code') {
			print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'">';
			$formTicket->selectTypesTickets(dol_escape_htmltag($search[$key]), 'search_'.$key.'', '', 2, 1, 1, 0, ($val['css'] ? $val['css'] : 'maxwidth150'));
			print '</td>';
		} elseif ($key == 'category_code') {
			print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'">';
			$formTicket->selectGroupTickets(dol_escape_htmltag($search[$key]), 'search_'.$key.'', '', 2, 1, 1, 0, ($val['css'] ? $val['css'] : 'maxwidth150'));
			print '</td>';
		} elseif ($key == 'severity_code') {
			print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'">';
			$formTicket->selectSeveritiesTickets(dol_escape_htmltag($search[$key]), 'search_'.$key.'', '', 2, 1, 1, 0, ($val['css'] ? $val['css'] : 'maxwidth150'));
			print '</td>';
		} elseif ($key == 'fk_user_assign') {
		    print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'">';
		    print $form->select_dolusers($search[$key], 'search_'.$key, 1, null, 0, '', '', '0', 0, 0, '', 0, '', ($val['css'] ? $val['css'] : 'maxwidth150'));
		    print '</td>';
		} elseif ($key == 'fk_statut') {
		    $arrayofstatus = array();
		    $arrayofstatus['openall'] = '-- '.$langs->trans('OpenAll').' --';
		    foreach ($object->statuts_short as $key2 => $val2)
		    {
		        $arrayofstatus[$key2] = $val2;
		        if ($key2 == '6') $arrayofstatus['closeall'] = '-- '.$langs->trans('ClosedAll').' --';
		    }
		    print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'">';
		    //var_dump($arrayofstatus);var_dump($search['fk_statut']);var_dump(array_values($search[$key]));
		    $selectedarray = null;
		    if ($search[$key]) $selectedarray = array_values($search[$key]);
			print Form::multiselectarray('search_fk_statut', $arrayofstatus, $selectedarray, 0, 0, 'minwidth150', 1, 0, '', '', '');
			print '</td>';
		}
		elseif ($key == "fk_soc")
		{
			print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'"><input type="text" class="flat maxwidth75" name="search_societe" value="'.dol_escape_htmltag($search_societe).'"></td>';
		}
		else {
			print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'"><input type="text" class="flat maxwidth75" name="search_'.$key.'" value="'.dol_escape_htmltag($search[$key]).'"></td>';
		}
	}
}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

// Fields from hook
$parameters = array('arrayfields'=>$arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $object); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
// Action column
print '<td class="liste_titre maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';
print '</tr>'."\n";


// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
foreach ($object->fields as $key => $val)
{
	$cssforfield = (empty($val['css']) ? '' : $val['css']);
	if ($key == 'fk_statut') $cssforfield .= ($cssforfield ? ' ' : '').'center';
	elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) $cssforfield .= ($cssforfield ? ' ' : '').'center';
	elseif (in_array($val['type'], array('timestamp'))) $cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
	elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID') $cssforfield .= ($cssforfield ? ' ' : '').'right';
	if (!empty($arrayfields['t.'.$key]['checked']))
	{
        print getTitleFieldOfList($arrayfields['t.'.$key]['label'], 0, $_SERVER['PHP_SELF'], 't.'.$key, '', $param, '', $sortfield, $sortorder, ($cssforfield ? $cssforfield.' ' : ''))."\n";
	}
}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
// Hook fields
$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"], '', '', '', '', $sortfield, $sortorder, 'maxwidthsearch center ')."\n";
print '</tr>'."\n";


// Detect if we need a fetch on each output line
$needToFetchEachLine = 0;
if (is_array($extrafields->attributes[$object->table_element]['computed']) && count($extrafields->attributes[$object->table_element]['computed']) > 0)
{
	foreach ($extrafields->attributes[$object->table_element]['computed'] as $key => $val)
	{
		if (preg_match('/\$object/', $val)) $needToFetchEachLine++; // There is at least one compute field that use $object
	}
}


// Loop on record
// --------------------------------------------------------------------
$i = 0;
$totalarray = array();
while ($i < min($num, $limit))
{
	$obj = $db->fetch_object($resql);
	if (empty($obj)) break; // Should not happen

	// Store properties in $object
	$object->id = $obj->rowid;
	foreach ($object->fields as $key => $val)
	{
		if (property_exists($obj, $key)) $object->$key = $obj->$key;
	}
	$langs->load("ticket");

	// Show here line of result
	print '<tr class="oddeven">';
	foreach ($object->fields as $key => $val)
	{
		$cssforfield = '';
		if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) $cssforfield .= ($cssforfield ? ' ' : '').'center';
		if (in_array($val['type'], array('timestamp'))) $cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		if (in_array($key, array('ref', 'fk_project'))) $cssforfield .= ($cssforfield ? ' ' : '').'nowraponall';
		if ($key == 'fk_statut') $cssforfield .= ($cssforfield ? ' ' : '').'center';
		if (!empty($arrayfields['t.'.$key]['checked']))
		{
			print '<td';
			if ($cssforfield || $val['css']) print ' class="';
			print $cssforfield;
			if ($cssforfield && $val['css']) print ' ';
			print $val['css'];
			if ($cssforfield || $val['css']) print '"';
			print '>';
			if ($key == 'fk_statut') print $object->getLibStatut(5);
			elseif ($key == 'category_code') print $langs->getLabelFromKey($db, $object->category_code, 'c_ticket_category', 'code', 'label');
			elseif ($key == 'severity_code') print $langs->getLabelFromKey($db, $object->severity_code, 'c_ticket_severity', 'code', 'label');
			elseif ($key == 'type_code') print $langs->getLabelFromKey($db, $object->type_code, 'c_ticket_type', 'code', 'label');
			elseif ($key == 'tms') print dol_print_date($db->jdate($obj->$key), 'dayhour', 'tzuser');
			elseif ($key == 'fk_user_create') {
				if ($object->fk_user_create > 0) {
					$user_create->fetch($object->fk_user_create);
					print $user_create->getNomUrl(-1);
				}
			}
			elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) print $object->showOutputField($val, $key, $db->jdate($obj->$key), '');
			else print $object->showOutputField($val, $key, $obj->$key, '');
			print '</td>';
			if (!$i) $totalarray['nbfield']++;
			if (!empty($val['isameasure']))
			{
				if (!$i) $totalarray['pos'][$totalarray['nbfield']] = 't.'.$key;
				$totalarray['val']['t.'.$key] += $obj->$key;
			}
		}
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
	// Fields from hook
	$parameters = array('arrayfields'=>$arrayfields, 'obj'=>$obj, 'i'=>$i, 'totalarray'=>&$totalarray);
	$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Action column
	print '<td class="nowrap center">';
	if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
	{
		$selected = 0;
		if (in_array($obj->rowid, $arrayofselected)) $selected = 1;
		print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
	}
	print '</td>';
	if (!$i) $totalarray['nbfield']++;

	print '</tr>';

	$i++;
}

// Show total line
include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';


// If no record found
if ($num == 0)
{
	$colspan = 1;
	foreach ($arrayfields as $key => $val) { if (!empty($val['checked'])) $colspan++; }
	print '<tr><td colspan="'.$colspan.'" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
}


$db->free($resql);

$parameters = array('arrayfields'=>$arrayfields, 'sql'=>$sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '</table>'."\n";
print '</div>'."\n";

print '</form>'."\n";



if (in_array('builddoc', $arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords))
{
	$hidegeneratedfilelistifempty = 1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) $hidegeneratedfilelistifempty = 0;

	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
	$formfile = new FormFile($db);

	// Show list of available documents
	$urlsource = $_SERVER['PHP_SELF'].'?sortfield='.$sortfield.'&sortorder='.$sortorder;
	$urlsource .= str_replace('&amp;', '&', $param);

	$filedir = $diroutputmassaction;
	$genallowed = $user->rights->ticket->read;
	$delallowed = $user->rights->ticket->write;

	print $formfile->showdocuments('massfilesarea_ticket', '', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '', '', '', null, $hidegeneratedfilelistifempty);
}

// End of page
llxFooter();
$db->close();
