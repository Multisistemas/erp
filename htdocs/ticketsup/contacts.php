<?php
/* Copyright (C) 2011-2016    Jean-François Ferry    <jfefe@aternatik.fr>
 * Copyright (C) 2011       Regis Houssin        <regis@dolibarr.fr>
 *                  2016        Christophe Battarel <christophe@altairis.fr>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *       \file       ticketsup/contacts.php
 *        \ingroup    ticketsup
 *        \brief      Contacts des tickets
 */
$res = 0;
if (file_exists("../main.inc.php")) {
    $res = include "../main.inc.php"; // From htdocs directory
} elseif (!$res && file_exists("../../main.inc.php")) {
    $res = include "../../main.inc.php"; // From "custom" directory
} else {
    die("Include of main fails");
}

require_once 'class/ticketsup.class.php';
dol_include_once('/ticketsup/lib/ticketsup.lib.php');

require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . "/core/lib/company.lib.php";
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("ticketsup@ticketsup");

// Get parameters
$socid = GETPOST("socid", 'int');
$action = GETPOST("action", 'alpha');
$track_id = GETPOST("track_id", 'alpha');
$id = GETPOST("id", 'int');
$ref = GETPOST('ref', 'alpha');

$type = GETPOST('type', 'alpha');
$source = GETPOST('source', 'alpha');

$ligne = GETPOST('ligne', 'int');
$lineid = GETPOST('lineid', 'int');




// Protection if external user
if ($user->societe_id > 0) {
    $socid = $user->societe_id;
    accessforbidden();
}

// Store current page url
$url_page_current = dol_buildpath('/ticketsup/contacts.php', 1);

$object = new Ticketsup($db);

/*
 * Ajout d'un nouveau contact
 */

if ($action == 'addcontact' && $user->rights->ticketsup->write) {
    $result = $object->fetch($id, $track_id);

    if ($result > 0 && ($id > 0 || (!empty($track_id)))) {
        $contactid = (GETPOST('userid', 'int') ? GETPOST('userid', 'int') : GETPOST('contactid', 'int'));
        $result = $object->add_contact($contactid, $type, $source);
    }

    if ($result >= 0) {
        Header("Location: " . $url_page_current . "?id=" . $object->id);
        exit;
    } else {
        if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
            $langs->load("errors");
            setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
        } else {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    }
}

// bascule du statut d'un contact
if ($action == 'swapstatut' && $user->rights->ticketsup->write) {
    if ($object->fetch($id, $track_id)) {
        $result = $object->swapContactStatus($ligne);
    } else {
        dol_print_error($db, $object->error);
    }
}

// Efface un contact
if ($action == 'deletecontact' && $user->rights->ticketsup->write) {
    if ($object->fetch($id, $track_id)) {
        $result = $object->delete_contact($lineid);

        if ($result >= 0) {
            Header("Location: " . $url_page_current . "?id=" . $object->id);
            exit;
        }
    }
}

/*
 * View
 */
$help_url = 'FR:DocumentationModuleTicket';
llxHeader('', $langs->trans("TicketContacts"), $help_url);

$form = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic = new Contact($db);
$userstatic = new User($db);

/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */

if ($id > 0 || !empty($track_id) || !empty($ref)) {
    if ($object->fetch($id, $track_id, $ref) > 0) {
        if ($object->fk_soc > 0) {
            $object->fetch_thirdparty();
            $head = societe_prepare_head($object->thirdparty);
            dol_fiche_head($head, 'ticketsup', $langs->trans("ThirdParty"), 0, 'company');
            dol_banner_tab($object->thirdparty, 'socid', '', ($user->societe_id ? 0 : 1), 'rowid', 'nom');
            dol_fiche_end();
        }

        if (!$user->societe_id && $conf->global->TICKETS_LIMIT_VIEW_ASSIGNED_ONLY) {
            $object->next_prev_filter = "te.fk_user_assign = '" . $user->id . "'";
        } elseif ($user->societe_id > 0) {
            $object->next_prev_filter = "te.fk_soc = '" . $user->societe_id . "'";
        }
        $head = ticketsup_prepare_head($object);
        dol_fiche_head($head, 'tabTicketContacts', $langs->trans("Ticket"), 0, 'ticketsup@ticketsup');
        $object->label = $object->ref;
        // Author
        if ($object->fk_user_create > 0) {
            $object->label .= ' - ' . $langs->trans("CreatedBy") . '  ';
            $langs->load("users");
            $fuser = new User($db);
            $fuser->fetch($object->fk_user_create);
            $object->label .= $fuser->getNomUrl(0);
        }
        $linkback = '<a href="' . dol_buildpath('/ticketsup/list.php', 1) . '"><strong>' . $langs->trans("BackToList") . '</strong></a> ';
        $object->ticketsup_banner_tab('ref', '', ($user->societe_id ? 0 : 1), 'ref', 'subject', '', '', '', $morehtmlleft, $linkback);

        dol_fiche_end();
        print '<br>';

        $permission = $user->rights->ticketsup->write;

        // Contacts lines (modules that overwrite templates must declare this into descriptor)
        $dirtpls=array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
        foreach ($dirtpls as $reldir) {
            $res=@include dol_buildpath($reldir.'/contacts.tpl.php');
            if ($res) {
            	break;
            }
        }
    } else {
        print "ErrorRecordNotFound";
    }
}

// End of page
llxFooter();
$db->close();
