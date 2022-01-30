<?php
/* Copyright (C) 2004-2014  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin   <regis.houssin@capnetworks.com>
 * Copyright (C) 2008   Raphael Bertrand  <raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2014  Juanjo Menent   <jmenent@2byte.es>
 * Copyright (C) 2012       Christophe Battarel <christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cédric Salvador     <csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014  Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2015       Marcos García       <marcosgdf@gmail.com>
 * Copyright (C) 2017       Ferran Marcet       <fmarcet@2byte.es>
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
 * or see http://www.gnu.org/
 */

/**
 *  \file       htdocs/core/modules/facture/doc/pdf_factura_exenta.modules.php
 *  \ingroup    facture
 *  \brief      File of class to generate customers invoices from crabe model
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/doc/NumberToLetterConverter.php';

/**
 *  Class to manage PDF invoice template
 */
class pdf_factura_exenta extends ModelePDFFactures
{
  var $db;
  var $name;
  var $description;
  var $type;
  var $phpmin = array(4,3,0); // Minimum version of PHP required by module
  var $version = 'dolibarr';
  var $page_largeur;
  var $page_hauteur;
  var $format;
  var $marge_gauche;
  var $marge_droite;
  var $marge_haute;
  var $marge_basse;
  var $emetteur;  // Objet societe qui emet

  /**
   * @var bool Situation invoice type
   */
  public $situationinvoice;

  /**
   * @var float X position for the situation progress column
   */
  public $posxprogress;


  /**
   *  Constructor
   *
   *  @param    DoliDB    $db      Database handler
   */
  function __construct($db)
  {
    global $conf,$langs,$mysoc;

    $langs->load("main");
    $langs->load("bills");

    $this->db = $db;
    $this->name = "factura_exenta";
    $this->description = "Factura exenta de IVA";

    $this->type = 'pdf';
    $formatarray=pdf_getFormat();
    $this->page_largeur = $formatarray['width'];
    $this->page_hauteur = $formatarray['height'];
    $this->format = array($this->page_largeur,$this->page_hauteur);
    $this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
    $this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
    $this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
    $this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

    $this->option_logo = 1;                    // Affiche logo
    $this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
    $this->option_modereg = 1;                 // Affiche mode reglement
    $this->option_condreg = 1;                 // Affiche conditions reglement
    $this->option_codeproduitservice = 1;      // Affiche code produit-service
    $this->option_multilang = 1;               // Dispo en plusieurs langues
    $this->option_escompte = 1;                // Affiche si il y a eu escompte
    $this->option_credit_note = 1;             // Support credit notes
    $this->option_freetext = 1;          // Support add of a personalised text
    $this->option_draft_watermark = 1;       // Support add of a watermark on drafts

    $this->franchise=!$mysoc->tva_assuj;

    // Get source company
    $this->emetteur=$mysoc;
    if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if was not defined

    // Define position of columns
    $this->posxdesc=$this->marge_gauche+1;
    if($conf->global->PRODUCT_USE_UNITS)
    {
      $this->posxtva=99;
      $this->posxup=114;
      $this->posxqty=130;
      $this->posxunit=147;
    }
    else
    {
      $this->posxtva=112;
      $this->posxup=126;
      $this->posxqty=145;
    }
    $this->posxdiscount=162;
    $this->posxprogress=126; // Only displayed for situation invoices
    $this->postotalht=174;
    if (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT)) $this->posxtva=$this->posxup;
    $this->posxpicture=$this->posxtva - (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)?20:$conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH);  // width of images
    if ($this->page_largeur < 210) // To work with US executive format
    {
        $this->posxpicture-=20;
        $this->posxtva-=20;
        $this->posxup-=20;
        $this->posxqty-=20;
        $this->posxunit-=20;
        $this->posxdiscount-=20;
        $this->posxprogress-=20;
        $this->postotalht-=20;
    }

    $this->tva=array();
    $this->localtax1=array();
    $this->localtax2=array();
    $this->atleastoneratenotnull=0;
    $this->atleastonediscount=0;
    $this->situationinvoice=False;
  }


  /**
     *  Function to build pdf onto disk
     *
     *  @param    Object    $object       Object to generate
     *  @param    Translate $outputlangs    Lang output object
     *  @param    string    $srctemplatepath  Full path of source filename for generator using a template file
     *  @param    int     $hidedetails    Do not show line details
     *  @param    int     $hidedesc     Do not show desc
     *  @param    int     $hideref      Do not show ref
     *  @return     int                     1=OK, 0=KO
   */
  function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
  {
    global $user,$langs,$conf,$mysoc,$db,$hookmanager;

    if (! is_object($outputlangs)) $outputlangs=$langs;
    // For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
    if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

    $outputlangs->load("main");
    $outputlangs->load("dict");
    $outputlangs->load("companies");
    $outputlangs->load("bills");
    $outputlangs->load("products");

    $nblignes = count($object->lines);

    // Loop on each lines to detect if there is at least one image to show
    $realpatharray=array();

    if (count($realpatharray) == 0) $this->posxpicture=$this->posxtva;

    if ($conf->facture->dir_output) {
      $object->fetch_thirdparty();

      $deja_regle = $object->getSommePaiement();
      $amount_credit_notes_included = $object->getSumCreditNotesUsed();
      $amount_deposits_included = $object->getSumDepositsUsed();

      // Definition of $dir and $file
      if ($object->specimen){
        $dir = $conf->facture->dir_output;
        $file = $dir . "/SPECIMEN.pdf";
      } else {
        $objectref = dol_sanitizeFileName($object->ref);
        $dir = $conf->facture->dir_output . "/" . $objectref;
        $file = $dir . "/" . $objectref . ".pdf";
      }

      if (! file_exists($dir)) {
        if (dol_mkdir($dir) < 0) {
          $this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
          return 0;
        }
      }

      if (file_exists($dir)) {
        // Add pdfgeneration hook
        if (! is_object($hookmanager)) {
          include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
          $hookmanager=new HookManager($this->db);
        }

        $hookmanager->initHooks(array('pdfgeneration'));
        $parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
        global $action;

        $reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);
        // Note that $action and $object may have been modified by some hooks

        // Set nblignes with the new facture lines content after hook
        $nblignes = count($object->lines);
        $nbpayments = count($object->getListOfPayments());

        // Create pdf instance
        $pdf=pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs);  // Must be after pdf_getInstance
                $pdf->SetAutoPageBreak(1,0);

                $heightforinfotot = 50+(4*$nbpayments); // Height reserved to output the info and total part and payment part
            $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5); // Height reserved to output the free text on last page
              $heightforfooter = $this->marge_basse + 8;  // Height reserved to output the footer (value include bottom margin)

                if (class_exists('TCPDF')) {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }

                $pdf->SetFont(pdf_getPDFFont($outputlangs));



        $pdf->Open();
        $pagenb=0;
        $pdf->SetDrawColor(128,128,128);

        $pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
        $pdf->SetSubject($outputlangs->transnoentities("Invoice"));
        $pdf->SetCreator("Dolibarr ".DOL_VERSION);
        $pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
        $pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Invoice")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
        if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

        $pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right



        // New page
        $pdf->AddPage();
        if (! empty($tplidx)) $pdf->useTemplate($tplidx);
        $pagenb++;




        // IMPRIMIR LA IMFORMACIÓN EN LA CABECERA
        $this->_pagehead($pdf, $object, 1, $outputlangs);



        $tab_top = 80;
        $tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?42:10);
        $tab_height = 130;
        $tab_height_newpage = 150;

        $iniY = $tab_top + 7;
        $curY = $tab_top + 7;
        $nexY = $tab_top + 7;
        $total_ttc = 0;

        // Loop on each lines
        for ($i = 0; $i < $nblignes; $i++){
          $curY = $nexY;
          $pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
          $pdf->SetTextColor(0,0,0);


          $pdf->setTopMargin($tab_top_newpage);
          $pdf->setPageOrientation('', 1, $heightforfooter+$heightforfreetext+$heightforinfotot); // The only function to edit the bottom margin of current page to set it.
          $pageposbefore=$pdf->getPage();

          $showpricebeforepagebreak=1;
          $posYAfterImage=0;
          $posYAfterDescription=0;

          // We start with Photo of product line
          if (isset($imglinesize['width']) && isset($imglinesize['height']) && ($curY + $imglinesize['height']) > ($this->page_hauteur-($heightforfooter+$heightforfreetext+$heightforinfotot)))  // If photo too high, we moved completely on new page
          {
            $pdf->AddPage('','',true);
            if (! empty($tplidx)) $pdf->useTemplate($tplidx);
            //if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
            $pdf->setPage($pageposbefore+1);

            $curY = $tab_top_newpage;
            $showpricebeforepagebreak=0;
          }

          if (isset($imglinesize['width']) && isset($imglinesize['height']))
          {
            $curX = $this->posxpicture-1;
            $pdf->Image($realpatharray[$i], $curX + (($this->posxtva-$this->posxpicture-$imglinesize['width'])/2), $curY, $imglinesize['width'], $imglinesize['height'], '', '', '', 2, 300); // Use 300 dpi
            // $pdf->Image does not increase value return by getY, so we save it manually
            $posYAfterImage=$curY+$imglinesize['height'];
          }

          // Description of product line
          $curX = $this->posxdesc-1;
          $curX = 30;


          $pdf->startTransaction();
          pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->posxpicture-$curX-$progress_width,3,$curX,$curY,$hideref,$hidedesc);
          $pageposafter=$pdf->getPage();
          if ($pageposafter > $pageposbefore) // There is a pagebreak
          {
            $pdf->rollbackTransaction(true);
            $pageposafter=$pageposbefore;

            $pdf->setPageOrientation('', 1, $heightforfooter);  // The only function to edit the bottom margin of current page to set it.
            pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->posxpicture-$curX,3,$curX,$curY,$hideref,$hidedesc);
            $pageposafter=$pdf->getPage();
            $posyafter=$pdf->GetY();

            if ($posyafter > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforinfotot))) // There is no space left for total+free text
            {
              if ($i == ($nblignes-1))  // No more lines, and no space left to show total, so we create a new page
              {
                $pdf->AddPage('','',true);
                if (! empty($tplidx)) $pdf->useTemplate($tplidx);
                //if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
                $pdf->setPage($pageposafter+1);
              }
            }
            else
            {
              // We found a page break
              $showpricebeforepagebreak=0;
            }
          }
          else  // No pagebreak
          {
            $pdf->commitTransaction();
          }


          $posYAfterDescription=$pdf->GetY();

          $nexY = $pdf->GetY();
          $pageposafter=$pdf->getPage();
          $pdf->setPage($pageposbefore);
          $pdf->setTopMargin($this->marge_haute);
          $pdf->setPageOrientation('', 1, 0); // The only function to edit the bottom margin of current page to set it.

          // We suppose that a too long description or photo were moved completely on next page
          if ($pageposafter > $pageposbefore && empty($showpricebeforepagebreak)) {
            $pdf->setPage($pageposafter); $curY = $tab_top_newpage;
          }

          $pdf->SetFont('','', $default_font_size - 1);   // On repositionne la police par defaut



          // Precio unitario antes del descuento
          $up_excl_tax = pdf_getlineupexcltax($object, $i, $outputlangs, $hidedetails);



          // CANTIDAD DE IVA
          $vatrate=(string) $object->lines[$i]->tva_tx;

          //$total_ttc = ($conf->multicurrency->enabled && $object->multiccurency_tx != 1) ? $object->multicurrency_total_ttc : $object->total_ttc;
          $total_ttc += $object->lines[$i]->multicurrency_subprice;


          // COLUMNA "CANTIDAD" /////////////////////////////////////////////////////////////////////////////////////////
          $qty = pdf_getlineqty($object, $i, $outputlangs, $hidedetails);
          $pdf->SetXY(7, $curY);

          if ($this->situationinvoice)
          {
            $pdf->MultiCell($this->posxprogress-$this->posxqty-0.8, 4, $qty, 0, 'C');
          }
          else if($conf->global->PRODUCT_USE_UNITS)
          {
            $pdf->MultiCell($this->posxunit-$this->posxqty-0.8, 4, $qty, 0, 'C');
          }
          else
          {
            $pdf->MultiCell($this->posxdiscount-$this->posxqty-0.8, 4, $qty, 0, 'C');
          }


          // COLUMNA "PRECIO UNITARIO" ////////////////////////////////////////////////////////////////////////////////////
          $pdf->SetXY($this->posxtva + 12, $curY);
          //$pdf->MultiCell(30, 3, "$ ".price($object->lines[$i]->multicurrency_total_ttc), false, 'R', 0); ////// --> Total with tax
          $pdf->MultiCell(30, 3, "$ ".price($object->lines[$i]->multicurrency_subprice), false, 'R', 0); ////// --> Total with tax


          // COLUMNA "VENTAS AFECTAS" /////////////////////////////////////////////////////////////////////////////////////
          //$total_excl_tax = pdf_getlinetotalexcltax($object, $i, $outputlangs, $hidedetails);
          $pdf->SetXY($this->postotalht - 21, $curY);
          //$pdf->MultiCell(30, 3, "$ ".price($object->lines[$i]->multicurrency_total_ttc), 0, 'R', 0); ////// --> Total with tax
          $pdf->MultiCell(30, 3, "$ ".price($object->lines[$i]->multicurrency_subprice), 0, 'R', 0); ////// --> Total with tax



          $sign=1;
          if (isset($object->type) && $object->type == 2 && ! empty($conf->global->INVOICE_POSITIVE_CREDIT_NOTE)) $sign=-1;
          // Collecte des totaux par valeur de tva dans $this->tva["taux"]=total_tva
          $prev_progress = $object->lines[$i]->get_prev_progress($object->id);
          if ($prev_progress > 0) // Compute progress from previous situation
          {
            if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) $tvaligne = $sign * $object->lines[$i]->multicurrency_total_tva * ($object->lines[$i]->situation_percent - $prev_progress) / $object->lines[$i]->situation_percent;
            else $tvaligne = $sign * $object->lines[$i]->total_tva * ($object->lines[$i]->situation_percent - $prev_progress) / $object->lines[$i]->situation_percent;
          } else {
            if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) $tvaligne= $sign * $object->lines[$i]->multicurrency_total_tva;
            else $tvaligne= $sign * $object->lines[$i]->total_tva;
          }



            // retrieve global local tax
          if ($localtax1_type && $localtax1ligne != 0)
            $this->localtax1[$localtax1_type][$localtax1_rate]+=$localtax1ligne;
          if ($localtax2_type && $localtax2ligne != 0)
            $this->localtax2[$localtax2_type][$localtax2_rate]+=$localtax2ligne;

          if (($object->lines[$i]->info_bits & 0x01) == 0x01) $vatrate.='*';
          if (! isset($this->tva[$vatrate]))        $this->tva[$vatrate]=0;
          $this->tva[$vatrate] += $tvaligne;

          if ($posYAfterImage > $posYAfterDescription) $nexY=$posYAfterImage;


          $nexY+=4;    // Passe espace entre les lignes

          $tab_top = 60;
          //$heightforfooter = 10;



          if (isset($object->lines[$i+1]->pagebreak) && $object->lines[$i+1]->pagebreak)
          {
            if ($pagenb == 1)
            {
              $this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
            }
            else
            {
              $this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
            }
            //$this->_pagefoot($pdf,$object,$outputlangs,1); /////////////////////////////////// Footer
            // New page
            $pdf->AddPage();
            if (! empty($tplidx)) $pdf->useTemplate($tplidx);
            $pagenb++;
            //if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
          }
        }


        $tab_top = 60;
        $height = 200;


        $pdf->SetFillColor(255,255,255);

        if ($vatrate != '0.000') {
          // Mostrar el porcentaje de IVA junto a (-) IVA retenido
          $totalvat;

          foreach($this->tva as $tvakey => $tvaval)
          {
            if ($tvakey != 0)    // On affiche pas taux 0
            {
              $this->atleastoneratenotnull++;

              $index++;
              $pdf->SetXY($this->postotalht - 10, 239);

              $tvacompl='';
              if (preg_match('/\*/',$tvakey))
              {
                $tvakey=str_replace('*','',$tvakey);

              }

              $totalvat.=vatrate($tvakey,1).$tvacompl;
              //$pdf->MultiCell(25, 4, $totalvat, 0, 'L', 1);
            }
          }
        }



        $thetotal = price($total_ttc,0, $outputlangs);



        // Total en letras
        $convertedToLetter = new NumberToLetterConverter();
        $thevalueinletters = $convertedToLetter->to_word((string)$thetotal, "USD");
        $totalinletters = ucfirst(strtolower($thevalueinletters));
        $pdf->SetXY(30, 233);
        $pdf->MultiCell(100, 4, $totalinletters, 0, 'L', 1);


        // Calcular el la cantidad que se va a descontar por el IVA
        if ($vatrate != '0.000') {
          //$pdf->SetXY($this->postotalht, 238);
          //$pdf->MultiCell(25, 4, "$ ".price($total_ttc - $total_ht), $useborder, 'R', 1);
        }


        // Sumas
        $pdf->SetXY($this->postotalht - 21, 214);
        $pdf->MultiCell(30, 4, "$ ".price($sign * $total_ttc, 0, $outputlangs), $useborder, 'R', 1);


        // Total
        $index++;
        $pdf->SetXY($this->postotalht + 3, 256);
        $pdf->MultiCell(30, 4, "$ ".price($sign * $total_ttc, 0, $outputlangs), $useborder, 'R', 1);


        //$this->_pagefoot($pdf,$object,$outputlangs); /////////////////////////////////// Footer

        if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

        $pdf->Close();

        //$pdf->Output('temporal.pdf', 'I');

        $pdf->Output($file,'F');

        // Add pdfgeneration hook
        $hookmanager->initHooks(array('pdfgeneration'));
        $parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
        global $action;
        $reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

        if (! empty($conf->global->MAIN_UMASK))
        @chmod($file, octdec($conf->global->MAIN_UMASK));

        return 1;   // No error
      }
      else
      {
        $this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
        return 0;
      }
    }
    else
    {
      $this->error=$langs->transnoentities("ErrorConstantNotDefined","FAC_OUTPUTDIR");
      return 0;
    }
  }

  /**
   *  Show top header of page.
   *
   *  @param  PDF     $pdf        Object PDF
   *  @param  Object    $object       Object to show
   *  @param  int       $showaddress    0=no, 1=yes
   *  @param  Translate $outputlangs  Object lang for output
   *  @return void
   */
  function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
  {
    global $conf,$langs;

    $outputlangs->load("main");
    $outputlangs->load("bills");
    $outputlangs->load("propal");
    $outputlangs->load("companies");

    $default_font_size = pdf_getPDFFontSize($outputlangs);

    pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

    $w = 110;

    $posy=$this->marge_haute;
      $posx=$this->page_largeur-$this->marge_droite-$w;

      // Imprimir número de factura en el erp
    $pdf->SetFont('','',$default_font_size);
    $pdf->SetXY(50,38);
    $pdf->SetTextColor(0,0,0);
    $pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($object->ref), '', 'C');

    // Imprimir fecha en que se facturó
    $pdf->SetFont('','',$default_font_size);
    $pdf->SetXY(117,47);
    $pdf->SetTextColor(0,0,0);
    $pdf->MultiCell($w, 4, dol_print_date($object->date,"day",false,$outputlangs), '', 'C');

    // Obtener la empresa a la que se factura
    if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) {
      $thirdparty = $object->contact;
    } else {
      $thirdparty = $object->thirdparty;
    }

    // Imprimir información de la empresa a la que se factura
    // Nombre
    $pdf->SetFont('','',$default_font_size);
    $pdf->SetXY(36,47);
    $pdf->SetTextColor(0,0,0);
    $pdf->MultiCell(200, 4, (string)$thirdparty->nom, '', 'L');

    // Dirección
    $pdf->SetFont('','',$default_font_size);
    $pdf->SetXY(36,55);
    $pdf->SetTextColor(0,0,0);
    $pdf->MultiCell(200, 4, (string)$thirdparty->address, '', 'L');

    // DUI/NIT
    $pdf->SetFont('','',$default_font_size);
    $pdf->SetXY(60,63);
    $pdf->SetTextColor(0,0,0);
    $pdf->MultiCell($w, 4, (string)$thirdparty->idprof1, '', 'L');

  }

  /**
   *    Show footer of page. Need this->emetteur object
     *
   *    @param  PDF     $pdf          PDF
   *    @param  Object    $object       Object to show
   *      @param  Translate $outputlangs    Object lang for output
   *      @param  int     $hidefreetext   1=Hide free text
   *      @return int               Return height of bottom margin including footer text
   */
  function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
  {
    global $conf;
    $showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
    return pdf_pagefoot($pdf,$outputlangs,'INVOICE_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object,$showdetails,$hidefreetext);
  }

}



