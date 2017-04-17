<?php
/*
 * Multisistemas Gconnect - A Google authentication module for Dolibarr
 * Copyright (C) 2017 Herson Cruz <herson@multisistemas.com.sv>
 * Copyright (C) 2017 Luis Medrano <lmedrano@multisistemas.com.sv>
 *
 */

/**
 * Loads scripts for copy to clipboard
 * @return string HTML scripts
 */
function InitCopyToClipboardButton()
{
    $zeroclipboard_path = dol_buildpath('/gconnect/vendor/zeroclipboard/zeroclipboard/dist/', 2);

    return '
        <script type="text/javascript" src="' . $zeroclipboard_path . 'ZeroClipboard.js"></script>
        <script type="text/javascript">
            ZeroClipboard.config( {
                swfPath: "' . $zeroclipboard_path . 'ZeroClipboard.swf"
            } );
        </script>';
}

/**
 * Button to copy text to clipboard
 *
 * @param string $text The text to copy
 * @param string $id Id of the element
 * @param string $title Title of the element
 *
 * @return string HTML for the button
 */
function CopyToClipboardButton($text, $id = 'copy-button', $title = 'CopyToClipboard')
{
    global $langs;

    return '
        <button
            type="button"
            class="gc_btn gc_zc_btn"
            id="' . $id . '"
            data-clipboard-text="' . $text . '">
        <img src="' . dol_buildpath('/gconnect/img/', 2) . 'copy.png">'
        . '&nbsp;' . $langs->trans($title)
        . '</button>
        <script type="text/javascript">
            var client'.$id.' = new ZeroClipboard( document.getElementById("' . $id . '") );

            client'.$id.'.on( "ready", function( event ) {
                client'.$id.'.on( "aftercopy", function( event ) {
                    $.jnotify(
                        \'' . $langs->trans('CopiedToClipboard') . '\',
                        \'3000\',
                        \'true\'
                    );
                } );
            } );
        </script>';
}
