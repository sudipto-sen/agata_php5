<?php

class AgataPdf extends AgataReport {

    var $Query;
    var $Maior;
    var $Columns;
    var $FileName;
    var $ColumnTypes;

    /*     * ******************************************************** */
    /* Set Text Properties
      /********************************************************** */

    function SetTextConfig($font, $color, $bgcolor) {
        $fonts = explode('-', $font);
        $this->PDF->SetFont($fonts[0], $fonts[1], $fonts[2]);

        $colorR = hexdec(substr($color, 1, 2));
        $colorG = hexdec(substr($color, 3, 2));
        $colorB = hexdec(substr($color, 5, 2));
        $this->PDF->SetTextColor($colorR, $colorG, $colorB);

        $bgcolorR = hexdec(substr($bgcolor, 1, 2));
        $bgcolorG = hexdec(substr($bgcolor, 3, 2));
        $bgcolorB = hexdec(substr($bgcolor, 5, 2));
        $this->PDF->SetFillColor($bgcolorR, $bgcolorG, $bgcolorB);
    }

    /*     * ******************************************************** */
    /* Process the Document output
      /********************************************************** */

    function Process() {
        $this->SetReportLocale();

        define('FPDF_FONTPATH', 'vendor' . bar . 'fpdf151' . bar . 'font' . bar);
        include_once('vendor' . bar . 'fpdf151' . bar . 'fpdf.php');
        include_once('vendor' . bar . 'reports' . bar . 'MyPdf.php');

        $ReportName = $this->ReportName;

        $Schema = Layout::ReadLayout($this->layout);
        /* $titlefont     = $Schema['TitleFont'];
          $titlecolor    = $Schema['TitleColor'];
          $titlebgcolor  = $Schema['TitleBgColor']; */
        $datafont = $Schema['DataFont'];
        $datacolor = $Schema['DataColor'];
        $databgcolor = $Schema['DataBgColor'];
        $totalfont = $Schema['TotalFont'];
        $totalcolor = $Schema['TotalColor'];
        $totalbgcolor = $Schema['TotalBgColor'];
        $groupfont = $Schema['GroupFont'];
        $groupcolor = $Schema['GroupColor'];
        $groupbgcolor = $Schema['GroupBgColor'];
        $columnfont = $Schema['ColumnFont'];
        $columncolor = $Schema['ColumnColor'];
        $columnbgcolor = $Schema['ColumnBgColor'];
        $headerfont = $Schema['HeaderFont'];
        $headercolor = $Schema['HeaderColor'];
        $headerbgcolor = $Schema['HeaderBgColor'];
        $footerfont = $Schema['FooterFont'];
        $footercolor = $Schema['FooterColor'];
        $footerbgcolor = $Schema['FooterBgColor'];

        $cellspacing = $Schema['CellSpacing'];
        $border = $Schema['Border'];
        if ($border > 1) {
            $border = 1;
        };

        $ColorLines = $Schema['ColorLines'];


        $this->Orientation = $this->XmlArray['Report']['PageSetup']['Orientation'] ? $this->XmlArray['Report']['PageSetup']['Orientation'] : 'portrait';
        $this->LeftMargin = $this->XmlArray['Report']['PageSetup']['LeftMargin'] ? $this->XmlArray['Report']['PageSetup']['LeftMargin'] : 5;
        $this->TopMargin = $this->XmlArray['Report']['PageSetup']['TopMargin'] ? $this->XmlArray['Report']['PageSetup']['TopMargin'] : 5;
        $this->RightMargin = $this->XmlArray['Report']['PageSetup']['RightMargin'] ? $this->XmlArray['Report']['PageSetup']['RightMargin'] : 5;
        $this->FooterMargin = $this->XmlArray['Report']['PageSetup']['BottomMargin'] ? $this->XmlArray['Report']['PageSetup']['BottomMargin'] : 10;
        $this->PageFormat = $this->XmlArray['Report']['PageSetup']['Format'] ? $this->XmlArray['Report']['PageSetup']['Format'] : 'A4';

        $FileName = $this->FileName;
        $this->PDF = new MyPdf($this->Orientation, 'mm', $this->PageFormat);
        $this->PDF->SetMargins($this->LeftMargin, $this->TopMargin, $this->RightMargin);
        $this->PDF->SetCreator('Agata Report');
        $this->PDF->SetTitle('Report');
        $this->PDF->SetKeywords('www.agata.org.br');
        $this->PDF->AliasNbPages();
        $this->PDF->Parameters = $this->Parameters;
        $this->PDF->textHeader = $this->textHeader;
        $this->PDF->textFooter = $this->textFooter;
        $this->PDF->alignHeader = $this->alignHeader;
        $this->PDF->alignFooter = $this->alignFooter;
        $this->PDF->headerfont = $headerfont;
        $this->PDF->headercolor = $headercolor;
        $this->PDF->headerbgcolor = $headerbgcolor;
        $this->PDF->footerfont = $footerfont;
        $this->PDF->footercolor = $footercolor;
        $this->PDF->footerbgcolor = $footerbgcolor;
        $this->PDF->FileName = $FileName;
        $this->PDF->ReportName = $this->ReportName;

        $footer = explode("\n", $this->textFooter);
        if (count($footer) > 0) {
            $this->PDF->SetAutoPageBreak(true, (count($footer) * 6) + 10);
        } else {
            $this->PDF->SetAutoPageBreak(true, 10);
        }
        //$this->PDF->SetMargins(5, 5, 5);
        $this->PDF->Open();
        $this->PDF->AliasNbPages();
        $this->PDF->AddPage($this->Orientation);
        $extend = 2.2;
        $lineheight = 6;

        $this->pagina = 1;

        if ($this->Orientation == 'Landscape') {
            $this->lin = -34;
            $this->limite = -565;
            $this->central_col = 420;
        } else {
            $this->lin = 820;
            $this->limite = 20;
            $this->central_col = 297;
        }


        $Left = 6;

        $OffSet = 1;
        if ($this->Breaks) {
            $CountBreaks = count($this->Breaks);
            if ($this->Breaks['0']) {
                $CountBreaks--;
            }
            if ($this->ShowTotalLabel) {
                $OffSet = 30;
            }
            ksort($this->Breaks);
            reset($this->Breaks);
        }

        $MarginBreaks = $CountBreaks * 5;
        if (!$this->ShowIndent) {
            $MarginBreaks = 3;
            $OffSet = 1;
            $Left = 0;
        }

        for ($n = 1; $n <= count($this->Columns); $n++) {
            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$n])) { //aquipbreak
                $Cols += $this->Adjustments[$n]['Points'];
            }
        }
        $this->LineSize = $Cols + $MarginBreaks;

        $this->SetTextConfig($columnfont, $columncolor, $columnbgcolor);
        if ((!$this->Breaks) or ((count($this->Breaks) == 1) && ($this->Breaks['0']))) {
            for ($z = 0; $z < count($this->Columns); $z++) {
                $Column = $this->Columns[$z];

                $width = $this->Adjustments[$z + 1]['Points'];
                $align = strtoupper(substr($this->Adjustments[$z + 1]['Align'], 0, 1));
                $this->PDF->Cell($width, $lineheight, $Column, $border, 0, $align, 1);
            }
        }
        $this->PDF->Ln($lineheight);

        while ($QueryLine = $this->CurrentQuery->FetchNext()) {
            $this->col = $Left;

            $this->BreakMatrix = null;
            $this->Headers = null;
            $stringline = null;

            //------------------------------------------------------------
            list($break) = $this->ProcessBreaks($QueryLine);
            //------------------------------------------------------------

            for ($y = 1; $y <= count($QueryLine); $y++) {
                $QueryCell = $UnformattedCell = htmlspecialchars($QueryLine[$y]);

                $aligns['left'] = 'L';
                $aligns['center'] = 'C';
                $aligns['right'] = 'R';
                $align = $aligns[$this->Adjustments[$y]['Align']];

                //------------------------------------------------------------
                //list($break) = $this->ProcessBreaks($QueryCell, $y);
                //------------------------------------------------------------
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);

                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) {
                    $conditionalResult = false;
                    # AVALIAR A EXPR. CONDICIONAL AQUI !!
                    if ($this->Adjustments[$y]['Conditional']) {
                        $cond_high = parent::EvalConditional($this->Adjustments[$y]['Conditional'], $UnformattedCell, $QueryLine);
                        if ($cond_high) {
                            $stringline[] = array($y, $QueryCell, $align, $this->Adjustments[$y]['Points'], $cond_high);
                        } else {
                            $stringline[] = array($y, $QueryCell, $align, $this->Adjustments[$y]['Points'], null);
                        }
                    } else {
                        $stringline[] = array($y, $QueryCell, $align, $this->Adjustments[$y]['Points'], null);
                    }
                }
            }

            $this->SetTextConfig($totalfont, $totalcolor, $totalbgcolor);
            if (($this->BreakMatrix) && ($break != '0')) {
                $chaves = array_reverse(array_keys($this->BreakMatrix));

                foreach ($chaves as $chave) {
                    //-----------------------------------------
                    $FinalBreak = $this->EqualizeBreak($chave);
                    //-----------------------------------------

                    if ($this->HasFormula[$chave]) {
                        $this->PDF->Ln(4);

                        foreach ($FinalBreak as $FinalBreakLine) {
                            if ($this->ShowTotalLabel) {
                                $label_width = ($MarginHeader * $extend) + $OffSet;
                                if ($chave == '0') {
                                    $this->PDF->Cell($label_width, $lineheight, 0, '(Grand Total)', 0, 0, '', 0);
                                } else {
                                    $this->PDF->Cell($label_width, $lineheight, '(' . substr($this->Summary[$chave]['BeforeLastValue'], 0, 11) . ')', 0, 0, '', 0);
                                }
                            } else {
                                $this->PDF->Cell(($MarginHeader * $extend) + $OffSet, $lineheight, '', 0, 0);
                            }

                            $w = 0;
                            foreach ($FinalBreakLine as $content) {
                                $w++;
                                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                                    if ($content) {
                                        $width = (($this->Adjustments[$w]['Points']));
                                        $align = $aligns[$this->Adjustments[$w]['Align']];
                                        $this->PDF->Cell($width, $lineheight, $content, $border, 0, $align, 1);
                                    } else {
                                        $this->PDF->Cell(($this->Adjustments[$w]['Points']), $lineheight, '', 0, 0);
                                    }
                                }
                            }
                            $this->PDF->Ln($lineheight);
                        }
                    }
                }
            }

            if (($this->Headers) && ($break != '0')) {
                $lineodd = 0;
                $this->PDF->Ln(4);

                $this->SetTextConfig($groupfont, $groupcolor, $groupbgcolor);
                foreach ($this->Headers as $nCountBreak => $Header) {
                    $MarginHeader = ($nCountBreak * $extend);
                    if (!$this->ShowIndent) {
                        $MarginHeader = 0;
                    } else {
                        $complement = ((($CountBreaks - 1) - $nCountBreak) * $extend * $extend);
                    }
                    $this->Index[$nCountBreak + 1]++;
                    $this->Index[$nCountBreak + 2] = 0;
                    $index = '';
                    for ($n = 1; $n <= $nCountBreak + 1; $n++) {
                        $index .= $this->Index[$n] . '.';
                    }
                    if ($this->ShowNumber) {
                        $Header = "{$index} {$Header}";
                    }

                    $this->col = ($MarginHeader * $extend) + $OffSet;
                    $this->PDF->Cell($this->col, $lineheight, '', 0, 0);
                    $this->PDF->Cell($Cols + $complement, $lineheight, $Header, $border, 0, '', 1);
                    $this->PDF->Ln($lineheight);
                }

                $this->SetTextConfig($columnfont, $columncolor, $columnbgcolor);

                $this->PDF->Cell($this->col, $lineheight, '', 0, 0);
                for ($z = 0; $z < count($this->Columns); $z++) {
                    $Column = $this->Columns[$z];
                    if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z + 1)])) {
                        $width = $this->Adjustments[$z + 1]['Points'];
                        $align = strtoupper(substr($this->Adjustments[$z + 1]['Align'], 0, 1));
                        $this->PDF->Cell($width, $lineheight, $Column, $border, 0, $align, 1);
                    }
                }
                $this->PDF->Ln($lineheight);
            }

            if ($this->ShowDataColumns) {
                if ($stringline) {
                    $color = 1;
                    if ((($lineodd % 2) == 0) and $ColorLines) {
                        $color = 0;
                    }

                    $maxline = 0;
                    $new_stringline = null;
                    # Cut the line in litle pieces...
                    foreach ($stringline as $line) {
                        $_y = $line[0];
                        $_QueryCell = $line[1];
                        $_align = $line[2];
                        $_len = $line[3];
                        $_condhigh = $line[4];

                        $_i = 0;
                        $test = null;
                        $result = null;
                        $firstpiece = true;

                        if (($this->PDF->GetStringWidth("$_QueryCell  ")) > $_len) {
                            $pieces = explode(' ', trim($_QueryCell));
                            foreach ($pieces as $piece) {
                                $test .= "$piece ";
                                if ($firstpiece) {
                                    $result[$_i] = "$piece ";
                                    $firstpiece = false;
                                } else if (($this->PDF->GetStringWidth("$test  ")) > $_len) {
                                    $_i++;
                                    $result[$_i] = "$piece ";
                                    $test = "$piece ";
                                } else {
                                    $result[$_i] .= "$piece ";
                                }
                            }
                            $maxline = $_i > $maxline ? $_i : $maxline;
                        } else {
                            $result[$_i] = $_QueryCell;
                        }
                        $new_stringline[] = array($_y, $result, $_align, $_len, $_condhigh);
                    }

                    // shows the slices
                    for ($subline = 0; $subline <= $maxline; $subline++) {
                        if ($this->Breaks) {
                            if (!($this->Breaks['0'] && count($this->Breaks) == 1)) {
                                $this->PDF->Cell(($MarginHeader * $extend) + $OffSet, $lineheight, '', 0, 0);
                            }
                        }
                        foreach ($new_stringline as $subcolumn) {
                            $_y = $subcolumn[0];
                            $results = $subcolumn[1];
                            $_align = $subcolumn[2];
                            $_len = $subcolumn[3];
                            $_condhigh = $subcolumn[4];
                            if ($_condhigh) {
                                $this->SetTextConfig($_condhigh['fontface'], $_condhigh['fontcolor'], $_condhigh['bgcolor']);
                                $color = 1;
                            } else {
                                $this->SetTextConfig($datafont, $datacolor, $databgcolor);
                            }

                            if ($border) { // from schema
                                // verify border positions
                                if ($subline == 0)
                                    $_border = 'LTR';
                                else
                                    $_border = 'LR';

                                if ($subline == $maxline)
                                    $_border .= 'B';
                            }

                            if ($results[$subline]) {
                                $this->PDF->Cell($_len, $lineheight, $results[$subline], $_border, 0, $_align, $color);
                            } else {
                                $this->PDF->Cell($_len, $lineheight, '', $_border, 0, $_align, $color);
                            }
                        }
                        $this->PDF->Ln();
                    }
                    $lineodd++;
                }
            }
        }

        /*         * ************************
          PROCESS TOTALS OF LAST LINE
         * ************************* */

        //------------------------
        $this->ProcessLastBreak();
        //------------------------

        if ($this->BreakMatrix) {
            $chaves = array_reverse(array_keys($this->BreakMatrix));
            foreach ($chaves as $chave) {
                //-----------------------------------------
                $FinalBreak = $this->EqualizeBreak($chave);
                //-----------------------------------------

                if (($this->HasFormula[$chave]) || ($chave == '0')) {
                    $this->PDF->Ln(4);

                    $this->SetTextConfig($totalfont, $totalcolor, $totalbgcolor);
                    foreach ($FinalBreak as $FinalBreakLine) {
                        if ($this->ShowTotalLabel) {
                            if ($chave == '0') {
                                $this->PDF->Cell(($MarginHeader * $extend) + $OffSet, $lineheight, 0, '(Grand Total)', 0, 0, '', 1);
                            } else {
                                $this->PDF->Cell(($MarginHeader * $extend) + $OffSet, 0, '(' . substr($this->Summary[$chave]['LastValue'], 0, 11) . ')', 0, 0, '', 1);
                            }
                        } else {
                            if (!($this->Breaks['0'] && count($this->Breaks) == 1)) {
                                $this->PDF->Cell(($MarginHeader * $extend) + $OffSet, $lineheight, '', 0, 0);
                            }
                        }

                        $w = 0;
                        foreach ($FinalBreakLine as $content) {
                            $w++;
                            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                                if ($content) {
                                    $width = $this->Adjustments[$w]['Points'];
                                    $align = $aligns[$this->Adjustments[$w]['Align']];
                                    $this->PDF->Cell($width, $lineheight, $content, $border, 0, $align, 1);
                                } else {
                                    $this->PDF->Cell(($this->Adjustments[$w]['Points']), $lineheight, '', 0, 0);
                                }
                            }
                        }
                        $this->PDF->Ln($lineheight);
                    }
                }
            }
        }

        /*         * ****************
          END OF LAST PROCESS
         * ***************** */

        $this->PDF->Output($FileName);

        if ($this->posAction) {
            $this->ExecPosAction();
            Project::OpenReport($FileName, $this->agataConfig);
        }
        $this->UnsetReportLocale();
        Wait::Off();

        return true;
    }

}

?>