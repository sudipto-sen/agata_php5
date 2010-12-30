<?php
class AgataHtml extends AgataReport
{
    var $Query;
    var $Maior;
    var $Columns;
    var $FileName;
    var $ColumnTypes;

    function Process()
    {
        if (isGui)
        {
            $InputBox = $this->InputBox;
            $ReportName = $InputBox->InputEntry->get_text();
            $InputBox->Close();
        }
        else
        {
            $ReportName = $this->ReportName;
        }

        include 'include/report_vars.inc';
        $FileName = $this->FileName;
        
        $fd = @fopen($FileName, "w");
        if (!$fd)
        {
            if (isGui)
                new Dialog(_a('File Error'));
            return false;
        }
        
        $this->SetReportLocale();
        if ($this->Breaks)
        {
            $CountBreaks=count($this->Breaks);
            if ($this->Breaks['0'])
            {
                $CountBreaks --;
            }
            
            ksort($this->Breaks);
            reset($this->Breaks);
        }
        
        $MarginBreaks = $CountBreaks * 5;

        for ($n=1; $n<=count($this->Columns); $n++)
        {
            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$col])) //aquipbreak
            {
                $TdCols ++;
            }
        }

        if ($this->ShowIndent)
        {
            $TdCols += $CountBreaks;
        }
        
        $Schema = Layout::ReadLayout($this->layout);
        $cellpadding = '2';
        $align = 'center';
        $width = '100%';
        $bgcolor = '#FFFFFF';
        $cellspacing = $Schema['CellSpacing'];
        $border      = $Schema['Border'];
        $ColorLines  = $Schema['ColorLines'];
        
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

        $header = explode("\n", $this->textHeader);
        $footer = explode("\n", $this->textFooter);

        # PRINT THE REPORT HEADER
        if (strlen(trim($this->textHeader)) >0)
        {
            fputs($fd, "<table cellspacing=$cellspacing cellpadding=$cellpadding align=$align width=$width border=$border bgcolor=$headerbgcolor>\n");
            fputs($fd, " <tr>\n");
            fputs($fd, "  <td align=$this->alignHeader bgcolor=$headerbgcolor>\n");
            foreach($header as $headerline)
            {
                eval ("\$tmp = \"$headerline\";");
                if (substr($tmp,0,6) == '#image')
                {
                    fputs($fd, "<img src='" . substr($tmp,7) . "'><br>  ");
                }
                else
                {
                    fputs($fd, "$headerfont1 $tmp $headerfont2<br>");
                }
            }
            fputs($fd, "  </td>\n");
            fputs($fd, " </tr>\n");
            fputs($fd, "</table>\n");
        }

        
        fputs($fd, "<html>\n");
        if ( $this->encoding )
        {
            fputs($fd, "<meta http-equiv=\"Content-Type\" content=\"text/html; charset={$this->encoding}\" />");
        }
        fputs($fd, "<body>\n");
        fputs($fd, "<table cellspacing=$cellspacing cellpadding=$cellpadding align=$align width=$width border=$border bgcolor=$bgcolor>\n");
        fputs($fd, "<tbody>\n");
        
        if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
        {
            fputs($fd, "<tr bgcolor=$columnbgcolor>\n");
            for ($z=0; $z<=count($this->Columns) -1; $z++)
            {
                $Column = $this->Columns[$z];
                $align = $this->Adjustments[$z+1]['Align'];
                fputs($fd, "<td align=$align bgcolor=$columnbgcolor> $columnfont1");
                fputs($fd, trim($Column));
                fputs($fd, "$columnfont2</td>");
            }
            fputs($fd, "</tr>\n");
        }

        while ($QueryLine = $this->CurrentQuery->FetchNext())
        {
            $this->BreakMatrix = null;
            $this->Headers = null;
            $stringline = '';
            //------------------------------------------------------------
            list($break) = $this->ProcessBreaks($QueryLine);
            //------------------------------------------------------------
            
            for ($y=1; $y<=count($QueryLine); $y++)
            {
                $QueryCell = $UnformattedCell = htmlspecialchars($QueryLine[$y]);
                
                //------------------------------------------------------------
                //list($break) = $this->ProcessBreaks($QueryCell, $y);
                //------------------------------------------------------------
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);
                
                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
                {
                    $align = $this->Adjustments[$y]['Align'];
                    if ($this->Adjustments[$y]['Conditional'])
                    {
                        //$cond_high  = splitCondHigh($this->Adjustments[$y]['Conditional']);
                        $cond_high  = parent::EvalConditional($this->Adjustments[$y]['Conditional'], $UnformattedCell, $QueryLine);
                        if ($cond_high)
                        {
                            $fontface = $cond_high['fontface'];
                            $fontcolor= $cond_high['fontcolor'];
                            $cbgcolor = $cond_high['bgcolor'];
                            
                            $condfontset = TreatFont($fontface, $fontcolor);
                            $condfont1 = $condfontset[0];
                            $condfont2 = $condfontset[1];
                            $stringline .= "<td bgcolor=$cbgcolor align=$align $x> $condfont1 $QueryCell $condfont2</td>";
                        }
                        else
                        {
                            $stringline .= "<td align=$align $x> $datafont1 $QueryCell $datadfont2</td>";
                        }
                    }
                    else
                    {
                        $stringline .= "<td align=$align $x> $datafont1 $QueryCell $datafont2</td>";
                    }
                }
            }
            
            if (($this->BreakMatrix) && ($break != '0'))
            {
                $chaves = array_reverse(array_keys($this->BreakMatrix));
                
                foreach ($chaves as $chave)
                {
                    //-----------------------------------------
                    $FinalBreak = $this->EqualizeBreak($chave);
                    //-----------------------------------------
                    if ($this->HasFormula[$chave])
                    {
                        foreach ($FinalBreak as $FinalBreakLine)
                        {
                            $w = 0;
                            
                            fputs($fd, "<tr bgcolor=$totalbgcolor>\n");
                            if ($this->ShowTotalLabel)
                            {
                                if ($chave == '0')
                                {
                                    fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 (Grand Total) $datafont2</td>");
                                }
                                else
                                {
                                    fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 ({$this->Summary[$chave]['BeforeLastValue']}) $datafont2</td>");
                                }
                                if ($this->ShowIndent)
                                {
                                    fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks -1));
                                }
                            }
                            else
                            {
                                if ($this->ShowIndent)
                                {
                                    fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks));
                                }
                            }
                            
                            foreach($FinalBreakLine as $content)
                            {
                                $w ++;
                                if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
                                {
                                    if ($content)
                                    {
                                        $align = $this->Adjustments[$w]['Align'];
                                        fputs($fd, "<td align=$align bgcolor=$totalbgcolor> $totalfont1 $content $totalfont2</td>\n");
                                    }
                                    else
                                    {
                                        fputs($fd, "<td bgcolor=$totalbgcolor>&nbsp;</td>\n");
                                    }
                                }
                            }
                            fputs($fd, "</tr>\n");
                        }
                    }
                }
            }
            
            if (($this->Headers) && ($break != '0'))
            {
                $lineodd = 0;
                foreach ($this->Headers as $nCountBreak => $Header)
                {
                    $MarginHeader = $nCountBreak * 5;
                    $this->Index[$nCountBreak +1] ++;
                    $this->Index[$nCountBreak +2] = 0;

                    $index = '';
                    for ($n=1; $n<=$nCountBreak +1; $n ++)
                    {
                        $index .= $this->Index[$n]. '.';
                    }
                    if ($this->ShowNumber)
                    {
                        $Header = "{$index} {$Header}";
                    }

                    fputs($fd, "<tr bgcolor=$groupbgcolor>\n");
                    if ($this->ShowIndent)
                    {
                        fputs($fd, $this->Replicate("<td bgcolor=$groupbgcolor>&nbsp;</td>", $nCountBreak));
                        $resto = $TdCols - $nCountBreak;
                    }
                    else
                    {
                        $resto = $TdCols;
                    }
                    
                    $Header = trim($Header);
                    fputs($fd, "<td  bgcolor=$groupbgcolor colspan=$resto> $groupfont1 $Header $groupfont2 </td>");
                    fputs($fd, "</tr>\n");
                }
                
                fputs($fd, "<tr bgcolor=$columnbgcolor>\n");
                if ($this->ShowIndent)
                {
                    fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp;</td>", $CountBreaks));
                }
                
                for ($z=0; $z<=count($this->Columns) -1; $z++)
                {
                    $Column = $this->Columns[$z];
                    if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
                    {
                        $align = $this->Adjustments[$z+1]['Align'];
                        fputs($fd, "<td align=$align bgcolor=$columnbgcolor> $columnfont1 $Column $columnfont2</td>\n");
                    }
                }
                fputs($fd, "</tr>\n");
                
            }

            if ($this->ShowDataColumns)
            {
                $fill_bg = "bgcolor=$databgcolor";
                if ((($lineodd % 2) ==0) and $ColorLines)
                {
                    $fill_bg = '';
                }
                if (trim($stringline))
                {
                    fputs($fd, "<tr $fill_bg>\n");
                    if ($this->ShowIndent)
                    {
                        fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp;</td>", $CountBreaks));
                    }
                    fputs($fd, $stringline);
                    fputs($fd, "</tr>\n");
                }
                $lineodd ++;
            }
        }
        
        
        /**************************
        PROCESS TOTALS OF LAST LINE
        ***************************/
        
        //------------------------
        $this->ProcessLastBreak();
        //------------------------
        
        if ($this->BreakMatrix)
        {
            $chaves = array_reverse(array_keys($this->BreakMatrix));
            
            foreach ($chaves as $chave)
            {
                //-----------------------------------------
                $FinalBreak = $this->EqualizeBreak($chave);
                //-----------------------------------------
                if (($this->HasFormula[$chave]) || ($chave =='0'))
                {
                    foreach ($FinalBreak as $FinalBreakLine)
                    {
                        $w = 0;
                        
                        fputs($fd, "<tr bgcolor=$totalbgcolor>\n");
                        if ($this->ShowTotalLabel)
                        {
                            if ($chave == '0')
                            {
                                fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 (Grand Total) $datafont2 </td>");
                            }
                            else
                            {
                                fputs($fd, "<td bgcolor=$bgcolor>&nbsp; $datafont1 ({$this->Summary[$chave]['LastValue']}) $datafont2</td>");
                            }
                            if ($this->ShowIndent)
                            {
                                fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks -1));
                            }
                        }
                        else
                        {
                            if ($this->ShowIndent)
                            {
                                fputs($fd, $this->Replicate("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks));
                            }
                        }
                        
                        foreach($FinalBreakLine as $content)
                        {
                            $w ++;
                            if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
                            {
                                if ($content)
                                {
                                    $align = $this->Adjustments[$w]['Align'];
                                    fputs($fd, "<td align=$align bgcolor=$totalbgcolor>".
                                               "$totalfont1 $content $totalfont2 </td>\n");
                                }
                                else
                                {
                                    fputs($fd, "<td bgcolor=$totalbgcolor align=right>&nbsp;</td>\n");
                                }
                            }
                        }
                        fputs($fd, "</tr>\n");
                    }
                }
            }
        }
        
        
        /******************
        END OF LAST PROCESS
        *******************/


        fputs($fd, "</tbody>\n");
        fputs($fd, "</table>\n");

        # PRINT THE REPORT FOOTER
        if (strlen(trim($this->textFooter)) >0)
        {
            fputs($fd, "<table cellspacing=$cellspacing cellpadding=$cellpadding align=$align width=$width border=$border bgcolor=$footerbgcolor>\n");
            fputs($fd, " <tr>\n");
            fputs($fd, "  <td align=$this->alignFooter bgcolor=$footerbgcolor>\n");
            foreach($footer as $footerline)
            {
                eval ("\$tmp = \"$footerline\";");
                if (substr($tmp,0,6) == '#image')
                {
                    fputs($fd, "<img src='" . substr($tmp,7) . "'><br>");
                }
                else
                {
                    fputs($fd, "$footerfont1 $tmp $footerfont2<br>");
                }
                
            }
            fputs($fd, "  </td>\n");
            fputs($fd, " </tr>\n");
            fputs($fd, "</table>\n");
        }

        fputs($fd, "</body>\n");
        fputs($fd, "</html>\n");
        fclose($fd);
        if ($this->posAction)
        {
            $this->ExecPosAction();
            Project::OpenReport($FileName, $this->agataConfig);
        }

        $this->UnSetReportLocale();

        
        return true;
    }
}
?>
