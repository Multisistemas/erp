<?php
/* Copyright (C) 2010-2012 Regis Houssin <regis@dolibarr.fr>
 * Copyright (C) 2013      Jean-François FERRY <jfefe@aternatik.fr>
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
 *
 */
?>

<!-- BEGIN PHP TEMPLATE -->

<?php

$langs = $GLOBALS['langs'];
$langs->load('ticketsup@ticketsup');
$linkedObjectBlock = $GLOBALS['linkedObjectBlock'];
echo '<br />';
print_titre($langs->trans('RelatedTickets'));
?>
<table class="noborder" width="100%">
<tr class="liste_titre">
    <td><?php echo $langs->trans("Subject"); ?></td>
    <td align="center"><?php echo $langs->trans("DateCreation"); ?></td>
    <td align="center"><?php echo $langs->trans("Customer"); ?></td>
    <td align="center"><?php echo $langs->trans("Status"); ?></td>
</tr>
<?php
$var=true;
foreach ($linkedObjectBlock as $object) {
    $var=!$var;
?>
<tr <?php echo $bc[$var]; ?>>
    <td>
        <a href="<?php echo dol_buildpath("/ticketsup/card.php", 1).'?track_id='.$object->track_id; ?>">
    <?php echo img_object($langs->trans("ShowTicket"), "ticketsup@ticketsup") . ' ' . (! empty($object->subject) ? ' '.$object->subject : ''); ?>
        </a>
    </td>
    <td align="center"><?php echo dol_print_date($object->datec, 'day'); ?></td>
    <?php
    $object->socid = $object->fk_soc;
    $object->fetch_thirdparty();
    ?>
    <td align="center"><?php echo $object->thirdparty->getNomUrl(1); ?></td>
    <td align="center"><?php echo $object->getLibstatut(2); ?></td>
</tr>
<?php } ?>
</table>

<!-- END PHP TEMPLATE -->