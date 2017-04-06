<?php

/**
 * Multisistemas Gconnect - A Google authentication module for Dolibarr
 * Copyright (C) 2017 Herson Cruz <herson@multisistemas.com.sv>
 * Copyright (C) 2017 Luis Medrano <lmedrano@multisistemas.com.sv>
 *
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
$callback_url = dol_buildpath('/index.php/google/oauth2callback', 2);

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
        ! in_array($callback_url, $params['web']['redirect_uris']) ||
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
// Import configuration from google's api console json file
echo '<p>',
$langs->trans("Instructions1");
// TODO: derive table from installed modules
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
echo '<form>',
    '<fieldset>',
    '<legend>', $langs->trans('RedirectURL'), '</legend>',
    '<input type="text" disabled="disabled" name="callback_url" size="80" value="' . $callback_url . '">',
    CopyToClipboardButton($callback_url, 'callback_url'),
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
    '<input type="file" name = "jsonConfig" required="required">',
    '<input type="submit" class="button" value ="',
    $langs->trans("Upload"), '">',
    '</fieldset>',
    '</form>',
    '<br>';
echo $langs->trans("Instructions4"),
    '</p>';
echo '<br>',
		'<form method="POST" action="', $_SERVER['PHP_SELF'], '">',
    '<fieldset>',
    '<legend>', $langs->trans('EmailDomain'), '</legend>',
    '<input type="hidden" name="token" value="', $_SESSION['newtoken'], '">',
    '<input type="hidden" name="action" value="domain">',
    '<input type="text" name="thedomain" size="80" value="', $conf->global->GC_EMAIL_DOMAIN,'" placeholder="mydomain.com">',
    '<input type="submit" class="button" value ="', $langs->trans("Save"), '">',
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
    '<input type="submit" class="button" value ="', $langs->trans("Save"), '">',
    '</td>',
    '</table>',
    '</form>';

llxFooter();


