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
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file admin/conf.php
 * Multisistemas Team
 * Module configuration page
 */

$res = 0;
// from standard dolibarr install
if (!$res && file_exists('../../main.inc.php')) {
    $res = @include '../../main.inc.php';
}
// from custom dolibarr install
if (!$res && file_exists('../../../main.inc.php')) {
    $res = @include '../../../main.inc.php';
}
if (!$res) {
    die("Main include failed");
}

require_once '../lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once '../lib/copybtn.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';

global $conf, $db, $user, $langs;

$mesg = ""; // User message

// Oauth2 params
$client_id = '';
$client_secret = '';

// Build javascript origin URI
$javascript_origin = 'http';
if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on') { // HTTPS?
    $javascript_origin .= 's';
}
$javascript_origin .= '://';
$javascript_origin .= $_SERVER['HTTP_HOST'];
if (
    array_key_exists('SERVER_PORT' ,$_SERVER)
    && $_SERVER['SERVER_PORT'] != 80 // Standard HTTP
    && $_SERVER['SERVER_PORT'] != 443 // Standard HTTPS
) { // Non standard port?
    $javascript_origin .= ':' . $_SERVER['SERVER_PORT'];
}


$langs->load('gconnect@gconnect');
$langs->load('admin');
$langs->load('help');


//////////

if (empty($conf->global->GC_ORIGIN_CALL) || $conf->global->GC_ORIGIN_CALL == NULL){
    $thecallback = $langs->trans("UndefinedCallback");
} else {
    if ($conf->global->GC_ORIGIN_CALL == '/'){
        $thecallback = $javascript_origin.'/index.php/google/oauth2callback';
    } else {
        $thecallback = $javascript_origin.'/'.$conf->global->GC_ORIGIN_CALL.'/index.php/google/oauth2callback';
    }
}

//////////

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$error = 0; // Error counter

/*
 * Actions
 */
if ($action == 'upload') {
    $file = file_get_contents($_FILES['jsonConfig']['tmp_name']);
    $params = json_decode($file, true);
    // TODO: write a file verification function to have better error messages for each case
    if (
        $params === null ||
        ! in_array($thecallback, $params['web']['redirect_uris']) ||
        ! in_array($javascript_origin, $params['web']['javascript_origins'])
    ) {
        $error++;
    } else {
        $client_id = $params['web']['client_id'];
        $client_secret = $params['web']['client_secret'];
    }
    if ($error) {
        $mesg = '<div class="error">' . $langs->trans("BadFile") . '</div>';
    }
}

if ($action == 'update') {
    $client_id = GETPOST('clientId', 'alpha');
    $client_secret = GETPOST('clientSecret', 'alpha');
}

if ($action == 'domain'){

	$finded = strpos(GETPOST('thedomain', 'alpha'), '@');

	if ($finded === false) {
		$thedomain = GETPOST('thedomain', 'alpha');	
	} else {
		$mesg = '<div class="error">' . $langs->trans("BadDomain") . '</div>';
		$error++;
	}
}

if ($action == 'origin') {
    $theorigin = GETPOST('theorigin', 'alpha');
}

// Set constants common to update and upload actions
if (($action == 'upload' || $action == 'update') && !$error) {
    $res = dolibarr_set_const(
        $db,
        'GC_OAUTH_CLIENT_ID',
        $client_id,
        '',
        0,
        '',
        $conf->entity
    );
    if (!$res > 0) {
        $error++;
    }
    $res = dolibarr_set_const(
        $db,
        'GC_OAUTH_CLIENT_SECRET',
        $client_secret,
        '',
        0,
        '',
        $conf->entity
    );
    if (!$res > 0) {
        $error++;
    }
    if (!$error) {
        $db->commit();
        $mesg = '<div class="ok">' . $langs->trans("Saved") . '</div>';
    } else {
        $db->rollback();
        $mesg = '<div class="error">'
            . $langs->trans("UnexpectedError")
            . '</div>';
    }
}

if (($action == 'domain') && !$error){
	$res = dolibarr_set_const(
        $db,
        'GC_EMAIL_DOMAIN',
        $thedomain,
        '',
        0,
        '',
        $conf->entity
  );

  if (!$res > 0) {
    $error++;
  }

  if (!$error) {
        $db->commit();
        $mesg = '<div class="ok">' . $langs->trans("Saved") . '</div>';
    } else {
        $db->rollback();
        $mesg = '<div class="error">'
            . $langs->trans("UnexpectedError")
            . '</div>';
    }

}

if (($action == 'origin') && !$error){
    $res = dolibarr_set_const(
        $db,
        'GC_ORIGIN_CALL',
        $theorigin,
        '',
        0,
        '',
        $conf->entity
  );

  if (!$res > 0) {
    $error++;
  }

  if (!$error) {
        $db->commit();
        $mesg = '<div class="ok">' . $langs->trans("Saved") . '</div>';
    } else {
        $db->rollback();
        $mesg = '<div class="error">'
            . $langs->trans("UnexpectedError")
            . '</div>';
    }

}

/**
 * view
 */
llxHeader();
dol_htmloutput_mesg($msg);
$form = new Form($db);
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
// Folder icon title
print_fiche_titre("GConnect", $linkback, 'setup');

$head = PrepareHead();
dol_fiche_head(
    $head,
    'conf',
    $langs->trans("ModuleName"),
    0,
    'multisistemas@gconnect'
);

// Error / confirmation messages
dol_htmloutput_mesg($mesg);

print_titre($langs->trans("GoogleApiConfig"));

echo '<br>';

echo '<p>',
$langs->trans("Instructions1");

echo '<ul><li>Contacts API</li><li>Google+ API</li></ul>';
echo $langs->trans("Instructions2");
echo InitCopyToClipboardButton();
echo '<form>',
    '<fieldset>',
    '<legend>', $langs->trans('JavascriptOrigin'), '</legend>',
    '<input type="text" disabled="disabled" name="javascript_origin" size="80" value="' . $javascript_origin . '">',
    CopyToClipboardButton($javascript_origin, 'javascript_origin'),
    '</fieldset>',
    '</form>',
    '<br>';

echo '<p>';
echo $langs->trans("Instructions5");
echo '</p>';

echo '<br>',
    '<form method="POST" action="', $_SERVER['PHP_SELF'], '">',
    '<fieldset>',
    '<legend>', $langs->trans('OriginCall'), '</legend>',
    '<input type="hidden" name="token" value="', $_SESSION['newtoken'], '">',
    '<input type="hidden" name="action" value="origin">',
    '<input type="text" name="theorigin" size="80" value="', $conf->global->GC_ORIGIN_CALL,'" placeholder="/" required="required">',
    '<input type="submit" class="gc_btn" value ="', $langs->trans("Save"), '">',
    '</fieldset>',
    '</form>',
    '<br>';

echo '<form>',
    '<fieldset>',
    '<legend>', $langs->trans('RedirectURL'), '</legend>',
    '<input type="text" disabled="disabled" name="thecallback" size="80" value="'.$thecallback. '">',
    CopyToClipboardButton($thecallback, 'thecallback'),
    '</fieldset>',
    '</form>',
    '<br>';

echo $langs->trans("Instructions3");
echo '<form enctype="multipart/form-data" method="POST" action="', $_SERVER['PHP_SELF'], '">',
    '<fieldset>',
    '<legend>', $langs->trans("JSONConfigFile"), '</legend>',
    '<input type="hidden" name="token" value="', $_SESSION['newtoken'], '">',
    '<input type="hidden" name="action" value="upload">',
    '<input type="hidden" name="MAX_FILE_SIZE" value="1000">',
    '<input type="file" class="gc_file_btn" name = "jsonConfig" required="required">',
    '<input type="submit" class="gc_btn" value ="',
    $langs->trans("Upload"), '">',
    '</fieldset>',
    '</form>',
    '<br>';
echo $langs->trans("Instructions4"),
    '</p>';

echo '<hr class="gc_hr">';
echo '<br>';
print_titre($langs->trans("OtherConfig"));

echo '<p>';
echo $langs->trans("Instructions6");
echo '</p>';

echo '<br>',
	'<form method="POST" action="', $_SERVER['PHP_SELF'], '">',
    '<fieldset>',
    '<legend>', $langs->trans('EmailDomain'), '</legend>',
    '<input type="hidden" name="token" value="', $_SESSION['newtoken'], '">',
    '<input type="hidden" name="action" value="domain">',
    '<input type="text" name="thedomain" size="80" value="', $conf->global->GC_EMAIL_DOMAIN,'" placeholder="mydomain.com" required="required">',
    '<input type="submit" class="gc_btn" value ="', $langs->trans("Save"), '">',
    '</fieldset>',
    '</form>',
    '<br>';

print_titre($langs->trans("ManualConfiguration"));

echo '<form method="POST" action="', $_SERVER['PHP_SELF'], '">',
    '<input type="hidden" name="token" value="', $_SESSION['newtoken'], '">',
    '<input type="hidden" name="action" value="update">',
    '<table class="noborder">',
    '<tr class="liste_titre">',
    '<td>', $langs->trans("ClientId"), '</td>',
    '<td>', $langs->trans("ClientSecret"), '</td>',
    '<td></td>',
    '</tr>',
    '<tr>',
    '<td>',
    '<input type="text" name="clientId" value="', $conf->global->GC_OAUTH_CLIENT_ID, '" required="required">',
    '</td>',
    '<td>',
    '<input type="password" name="clientSecret" value="', $conf->global->GC_OAUTH_CLIENT_SECRET, '" required="required">',
    '</td>',
    '<td>',
    '<input type="submit" class="gc_btn" value ="', $langs->trans("Save"), '">',
    '</td>',
    '</table>',
    '</form>';

llxFooter();


