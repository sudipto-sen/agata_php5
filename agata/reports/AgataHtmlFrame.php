<?php

class AgataHtmlFrame extends AgataReport {

    var $Query;
    var $Maior;
    var $Columns;
    var $FileName;
    var $ColumnTypes;

    function Process() {
        $ReportName = $this->ReportName;

        $FileName = $this->FileName;

        # DIFFERENCE
        $Path = GetPath($this->FileName);
        $File = GetFileName($this->FileName);

        $frameset = "{$Path}.frameset.{$File}";
        $menuhtml = "{$Path}.menuhtml.{$File}";

        $fd = @fopen($FileName, "w");
        if (!$fd) {
            new Dialog(_a('File Error'));
            return false;
        } else {
            # DIFFERENCE
            $fd_frameset = @fopen($frameset, "w");
            $fd_menuhtml = @fopen($menuhtml, "w");
            $frameperc = $this->XmlArray['Report']['Properties']['FrameSize'];
            $frameperc = $frameperc ? $frameperc : '30%';
            $framesize = substr($frameperc, 0, -1);
            $framerest = 100 - $framesize;

            fputs($fd_frameset, "<html>\n");
            fputs($fd_frameset, "<FRAMESET COLS=\"{$framesize}%,{$framerest}%\">");
            fputs($fd_frameset, "    <FRAME  SRC=\".menuhtml.{$File}\" NAME=\"menuhtml\" MARGINWIDTH=01 MARGINHEIGHT=03 SCROLLING=YES FRAMEBORDER=1>");
            fputs($fd_frameset, "    <FRAME  SRC=\"$File\" NAME=\"conteudo\" MARGINWIDTH=01 MARGINHEIGHT=00 SCROLLING=YES FRAMEBORDER=1 NORESIZE>");
            fputs($fd_frameset, "</FRAMESET>");
            fputs($fd_frameset, "</html>\n");
            fputs($fd_menuhtml, "<style>");
            fputs($fd_menuhtml, "a:link {");
            fputs($fd_menuhtml, "        font-weight: bold;");
            fputs($fd_menuhtml, "        text-decoration: none;");
            fputs($fd_menuhtml, "        color: #000000;");
            fputs($fd_menuhtml, "}");
            fputs($fd_menuhtml, "a:visited {");
            fputs($fd_menuhtml, "        font-weight: bold;");
            fputs($fd_menuhtml, "        text-decoration: none;");
            fputs($fd_menuhtml, "        color: #000000;");
            fputs($fd_menuhtml, "}");
            fputs($fd_menuhtml, "a:hover {");
            fputs($fd_menuhtml, "        font-weight: bold;");
            fputs($fd_menuhtml, "        text-decoration: none;");
            fputs($fd_menuhtml, "        color: #3C4D71;");
            fputs($fd_menuhtml, "}");
            fputs($fd_menuhtml, "</style>");
            fputs($fd_menuhtml, "<html>\n");
            fputs($fd_menuhtml, "<table width=400>\n");
        }

        $this->SetReportLocale();

        if ($this->Breaks) {
            $CountBreaks = count($this->Breaks);
            if ($this->Breaks['0']) {
                $CountBreaks--;
            }

            ksort($this->Breaks);
            reset($this->Breaks);
        }

        $MarginBreaks = $CountBreaks * 5;
        for ($n = 1; $n <= count($this->Columns); $n++) {
            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$n])) { //aquipbreak
                $TdCols++;
            }
        }


        if ($this->ShowIndent) {
            $TdCols += $CountBreaks;
        }

        $Schema = Layout::ReadLayout($this->layout);
        $cellpadding = '2';
        $align = 'center';
        $width = '100%';
        $bgcolor = '#FFFFFF';

        $cellspacing = $Schema['CellSpacing'];
        $border = $Schema['Border'];

        $datafont = $Schema['DataFont'];
        $datacolor = $Schema['DataColor'];
        $databgcolor = $Schema['DataBgColor'];
        $datafontset = TreatFont($datafont, $datacolor);
        $datafont1 = $datafontset[0];
        $datafont2 = $datafontset[1];

        $totalfont = $Schema['TotalFont'];
        $totalcolor = $Schema['TotalColor'];
        $totalbgcolor = $Schema['TotalBgColor'];
        $totalfontset = TreatFont($totalfont, $totalcolor);
        $totalfont1 = $totalfontset[0];
        $totalfont2 = $totalfontset[1];

        $groupfont = $Schema['GroupFont'];
        $groupcolor = $Schema['GroupColor'];
        $groupbgcolor = $Schema['GroupBgColor'];
        $groupfontset = TreatFont($groupfont, $groupcolor);
        $groupfont1 = $groupfontset[0];
        $groupfont2 = $groupfontset[1];

        $columnfont = $Schema['ColumnFont'];
        $columncolor = $Schema['ColumnColor'];
        $columnbgcolor = $Schema['ColumnBgColor'];
        $columnfontset = TreatFont($columnfont, $columncolor);
        $columnfont1 = $columnfontset[0];
        $columnfont2 = $columnfontset[1];

        $headerfont = $Schema['HeaderFont'];
        $headercolor = $Schema['HeaderColor'];
        $headerbgcolor = $Schema['HeaderBgColor'];
        $headerfontset = TreatFont($headerfont, $headercolor);
        $headerfont1 = $headerfontset[0];
        $headerfont2 = $headerfontset[1];

        $footerfont = $Schema['FooterFont'];
        $footercolor = $Schema['FooterColor'];
        $footerbgcolor = $Schema['FooterBgColor'];
        $footerfontset = TreatFont($footerfont, $footercolor);
        $footerfont1 = $footerfontset[0];
        $footerfont2 = $footerfontset[1];

        fputs($fd, "<html>\n");
        fputs($fd, "<body>\n");
        fputs($fd, "<table cellspacing=$cellspacing cellpadding=$cellpadding align=$align width=$width border=$border bgcolor=$bgcolor>\n");
        fputs($fd, "<tbody>\n");

        if ((!$this->Breaks) || ((count($this->Breaks) == 1) && ($this->Breaks['0']))) { //aquipbreak
            fputs($fd, "<tr bgcolor=$columnbgcolor>\n");
            for ($z = 0; $z <= count($this->Columns) - 1; $z++) {
                $Column = $this->Columns[$z];
                fputs($fd, "<td bgcolor=$columnbgcolor> $columnfont1");
                fputs($fd, trim($Column));
                fputs($fd, "$columnfont2</td>");
            }
            fputs($fd, "</tr>\n");
        }

        while ($QueryLine = $this->CurrentQuery->FetchNext()) {
            $this->BreakMatrix = null;
            $this->Headers = null;
            $stringline = '';

            //------------------------------------------------------------
            list($break) = $this->ProcessBreaks($QueryLine);
            //------------------------------------------------------------

            for ($y = 1; $y <= count($QueryLine); $y++) {
                $QueryCell = $QueryLine[$y];

                //------------------------------------------------------------
                //list($break) = $this->ProcessBreaks($QueryCell, $y);
                //------------------------------------------------------------
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);

                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) { //aquipbreak
                    $align = $this->Adjustments[$y]['Align'];
                    $stringline .= "<td align=$align> $datafont1 $QueryCell $datafont2</td>";
                }
            }

            if (($this->BreakMatrix) && ($break != '0')) {
                $chaves = array_reverse(array_keys($this->BreakMatrix));

                foreach ($chaves as $chave) {
                    //-----------------------------------------
                    $FinalBreak = $this->EqualizeBreak($chave);
                    //-----------------------------------------
                    if ($this->HasFormula[$chave]) {
                        foreach ($FinalBreak as $FinalBreakLine) {
                            $w = 0;

                            fputs($fd, "<tr bgcolor=$totalbgcolor>\n");
                            if ($this->ShowTotalLabel) {
                                if ($chave == '0') {
                                    fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 (Grand Total) $datafont2</td>");
                                } else {
                                    fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 ({$this->Summary[$chave]['BeforeLastValue']}) $datafont2</td>");
                                }
                                if ($this->ShowIndent) {
                                    fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks - 1));
                                }
                            } else {
                                if ($this->ShowIndent) {
                                    fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks));
                                }
                            }

                            foreach ($FinalBreakLine as $content) {
                                $w++;
                                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                                    if ($content) {
                                        $align = $this->Adjustments[$w]['Align'];
                                        fputs($fd, "<td align=$align bgcolor=$totalbgcolor> $totalfont1 $content $totalfont2</td>\n");
                                    } else {
                                        fputs($fd, "<td bgcolor=$totalbgcolor>&nbsp;</td>\n");
                                    }
                                }
                            }
                            fputs($fd, "</tr>\n");
                        }
                    }
                }
            }

            if (($this->Headers) && ($break != '0')) {
                $lineodd = 0;
                foreach ($this->Headers as $nCountBreak => $Header) {
                    $MarginHeader = $nCountBreak * 5;
                    $this->Index[$nCountBreak + 1]++;
                    $this->Index[$nCountBreak + 2] = 0;

                    $index = '';
                    for ($n = 1; $n <= $nCountBreak + 1; $n++) {
                        $index .= $this->Index[$n] . '.';
                    }
                    if ($this->ShowNumber) {
                        $Header = "{$index} {$Header}";
                    }

                    fputs($fd, "<tr bgcolor=$groupbgcolor>\n");
                    if ($this->ShowIndent) {
                        fputs($fd, $this->Replicate("<td bgcolor=$groupbgcolor>&nbsp;</td>", $nCountBreak));
                        $resto = $TdCols - $nCountBreak;
                    } else {
                        $resto = $TdCols;
                    }

                    $Header = trim($Header);
                    # DIFERENCE
                    fputs($fd, "<td  bgcolor=$groupbgcolor colspan=$resto> $groupfont1 <a name='$Header'> $Header </a> $groupfont2 </td>");
                    fputs($fd, "</tr>\n");
                    # DIFERENCE
                    fputs($fd_menuhtml, "<tr>");
                    $resto = $TdCols - $nCountBreak;
                    fputs($fd_menuhtml, $this->Replicate("<td>&nbsp;</td>", $nCountBreak));
                    fputs($fd_menuhtml, "<td colspan=$resto> <font face='Arial' size=2 color=black> <a href='$FileName#$Header' target=conteudo>$Header</a> </font> </td>");
                    fputs($fd_menuhtml, "</tr>");
                }

                fputs($fd, "<tr bgcolor=$columnbgcolor>\n");
                if ($this->ShowIndent) {
                    fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp;</td>", $CountBreaks));
                }

                for ($z = 0; $z <= count($this->Columns) - 1; $z++) {
                    $Column = $this->Columns[$z];
                    if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z + 1)])) { //aquipbreak
                        fputs($fd, "<td bgcolor=$columnbgcolor> $columnfont1 $Column $columnfont2</td>\n");
                    }
                }
                fputs($fd, "</tr>\n");
            }

            if ($this->ShowDataColumns) {
                $fill_bg = "bgcolor=$databgcolor";
                if ((($lineodd % 2) == 0) and $ColorLines) {
                    $fill_bg = '';
                }
                if (trim($stringline)) {
                    fputs($fd, "<tr $fill_bg>\n");
                    if ($this->ShowIndent) {
                        fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp;</td>", $CountBreaks));
                    }
                    fputs($fd, $stringline);
                    fputs($fd, "</tr>\n");
                }
                $lineodd++;
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
                    foreach ($FinalBreak as $FinalBreakLine) {
                        $w = 0;

                        fputs($fd, "<tr bgcolor=$totalbgcolor>\n");
                        if ($this->ShowTotalLabel) {
                            if ($chave == '0') {
                                fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 (Grand Total) $datafont2</td>");
                            } else {
                                fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 ({$this->Summary[$chave]['LastValue']}) $datafont2</td>");
                            }
                            if ($this->ShowIndent) {
                                fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks - 1));
                            }
                        } else {
                            if ($this->ShowIndent) {
                                fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks));
                            }
                        }

                        foreach ($FinalBreakLine as $content) {
                            $w++;
                            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) {
                                if ($content) {
                                    $align = $this->Adjustments[$w]['Align'];
                                    fputs($fd, "<td align=$align bgcolor=$totalbgcolor aligh=left> $totalfont1 $content $totalfont2 </td>\n");
                                } else {
                                    fputs($fd, "<td bgcolor=$totalbgcolor aligh=right>&nbsp;</td>\n");
                                }
                            }
                        }
                        fputs($fd, "</tr>\n");
                    }
                }
            }
        }


        /*         * ****************
          END OF LAST PROCESS
         * ***************** */


        fputs($fd, "</tbody>\n");
        fputs($fd, "</table>\n");
        fputs($fd, "</body>\n");
        fputs($fd, "</html>\n");
        fclose($fd);
        # DIFFERENCE
        fputs($fd_menuhtml, "</table>");
        fputs($fd_menuhtml, "</html>");
        fclose($fd_frameset);
        fclose($fd_menuhtml);

        if ($this->posAction) {
            $this->ExecPosAction();
            Project::OpenReport($frameset, $this->agataConfig);
        }

        $this->UnSetReportLocale();


        return true;
    }

}

?>