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
 * \file admin/about.php
 * Multisistemas Team
 * Module about page
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

require_once '../core/modules/modGconnect.class.php';
require_once '../lib/admin.lib.php';

global $conf, $db, $user, $langs;

$langs->load('gconnect@gconnect');
$langs->load('admin');
$langs->load('help');

// only readable by admin
if (!$user->admin) {
    accessforbidden();
}

$module = new modGconnect($db);

/*
 * View
 */

// Little folder on the html page
llxHeader();
/// Navigation in the modules
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
// Folder icon title
print_fiche_titre("GConnect", $linkback, 'setup');

$head = PrepareHead();

dol_fiche_head(
    $head,
    'about',
    $langs->trans("ModuleName"),
    0,
    'multisistemas@gconnect'
);

echo '<h3>',
$langs->trans("ModuleName"),
' — ',
$langs->trans('ModuleDesc'),
'</h3>';
echo '<em>', $langs->trans("Version"), ' ',
$module->version, '</em><br>';
echo '<em>&copy;2017 Multisistemas Team<br><em><br>';
echo '<a target="_blank" href="http://www.multisistemas.com.sv/">',
'<img src="../img/logo_multisistemas.png" alt="Multisistemas Logo"></a><br>';
echo '<address>Pinares de Suiza Pol. 12, No. 35 Santa Tecla, La Libertad El Salvador<br>',
'Tel.:+503 2207 2444</address>',
'<a href="mailto:info@multisistemas.com.sv">info@multisistemas.com.sv</a>';

echo '<h3>', $langs->trans("Credits"), '</h3>';

echo '<h4>', $langs->trans("Development"), '</h4>';

echo '<ul>';
echo '<li>Herson Cruz, ', $langs->trans('ProjectManager'), '</li>';
echo '<li>Luis Medrano, ', $langs->trans('SoftwareEngineer'), '</li>';
echo '</ul>';

echo '<h4>' . $langs->trans("Libraries") . '</h4>';
echo '<ul>',
'<li>',
'<a href="http://opauth.org/" target="_blank">',
'Opauth - Multi-provider authentication framework for PHP',
'</a>',
'<br>',
'Copyright © 2012 U-Zyn Chua.',
'<br>',
'Opauth is released under the MIT License.',
'<br>',
'</li>',
'<li>',
'<a href="http://zeroclipboard.org" target="_blank">',
'ZeroClipboard',
'</a>',
'<br>',
'Copyright © 2014 Jon Rohan, James M. Greene',
'<br>',
'ZeroClipboard is released under the MIT License.',
'<br>',
'</li>',
'</ul>';

llxFooter();
