<?php

/**
 * \file lib/admin.lib.php
 * \ingroup zenfusionoauth
 * Module functions library
 */

/**
 * \function zfPrepareHead
 * Display tabs in module admin page
 *
 * @return array
 */
function PrepareHead()
{
    global $langs, $conf;
    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/gconnect/admin/conf.php", 1);
    $head[$h][1] = $langs->trans("Config");
    $head[$h][2] = 'conf';
    $h++;

    if ($conf->global->ZF_SUPPORT) {
        $head[$h][0] = dol_buildpath(
            "/gconnect/admin/support.php",
            1
        );
        $head[$h][1] = $langs->trans("HelpCenter");
        $head[$h][2] = 'help';
        $h++;
    }

    $head[$h][0] = dol_buildpath("/gconnect/admin/about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';

    return $head;
}
