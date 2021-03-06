<?php
/***********************************************************/
/* Label Adress tool                                       */
/* Autor: Pablo Dall'Oglio                                 */
/* �ltima atera��o em 15 Agosto 2003 por Pablo             */
/***********************************************************/
class AgataLabel extends AgataMerge
{
    function Generate()
    {
        $this->SetReportLocale();
        
        $this->Pages['A3']        = array(841, 1190);
        $this->Pages['A4']        = array(595, 841);
        $this->Pages['A5']        = array(419, 595);
        $this->Pages['Letter']    = array(612, 790);
        $this->Pages['Legal']     = array(612, 1009);
        
        $this->HSpacing    = $this->XmlArray['Report']['Label']['Config']['HorizontalSpacing'] ? $this->XmlArray['Report']['Label']['Config']['HorizontalSpacing'] : 0;
        $this->VSpacing    = $this->XmlArray['Report']['Label']['Config']['VerticalSpacing']   ? $this->XmlArray['Report']['Label']['Config']['VerticalSpacing']   : 0;
        $this->LabelWidth  = $this->XmlArray['Report']['Label']['Config']['LabelWidth']  ? $this->XmlArray['Report']['Label']['Config']['LabelWidth']  : 200;
        $this->LabelHeight = $this->XmlArray['Report']['Label']['Config']['LabelHeight'] ? $this->XmlArray['Report']['Label']['Config']['LabelHeight'] : 100;
        $this->LeftMargin  = $this->XmlArray['Report']['Label']['Config']['LeftMargin'] ? $this->XmlArray['Report']['Label']['Config']['LeftMargin'] : 0;
        $this->TopMargin   = $this->XmlArray['Report']['Label']['Config']['TopMargin']  ? $this->XmlArray['Report']['Label']['Config']['TopMargin'] : 0;
        $this->RightMargin = $this->LeftMargin;
        $this->BottomMargin= $this->TopMargin;
        
        $ColCount    = $this->XmlArray['Report']['Label']['Config']['Columns'] ? $this->XmlArray['Report']['Label']['Config']['Columns'] : 2;
        $RowCount    = $this->XmlArray['Report']['Label']['Config']['Rows'] ? $this->XmlArray['Report']['Label']['Config']['Rows'] : 6;
        $this->Orientation = 'Portrait';
        $this->PageFormat  = $this->XmlArray['Report']['Label']['Config']['PageFormat'] ? $this->XmlArray['Report']['Label']['Config']['PageFormat'] : 'Automatic';
        $this->LineSpacing = $this->XmlArray['Report']['Label']['Config']['LineSpacing'] ? (int) $this->XmlArray['Report']['Label']['Config']['LineSpacing']: 14;
        
        if ($this->PageFormat == 'Automatic')
        {
            $page_width  = $this->LeftMargin + $this->RightMargin  + ($ColCount * ($this->HSpacing + $this->LabelWidth));
            $page_height = $this->TopMargin  + $this->BottomMargin + ($RowCount * ($this->VSpacing + $this->LabelHeight));
            $this->PageFormat  = array($page_width, $page_height);
        }
        
        $textLabel = $this->XmlArray['Report']['Label']['Body'];
        $textLabel = $this->fillParameters($textLabel);
        
        //$Column   = $Column1;
        $Lines = explode("\n", $textLabel);
        
        define('FPDF_FONTPATH','vendor' . bar . 'fpdf151' . bar . 'font' . bar);
        include_once('vendor/barcode128/fpdf.php');
        include_once('vendor/barcode128/barcode128.inc');
        include_once('vendor/barcode128/pdfbarcode128.inc');
        
        include 'agata/include/report_vars.inc';
        
        $FileName = $this->FileName;
        $this->PDF = new FPDF($this->Orientation, 'pt', $this->PageFormat);
        $this->PDF->SetAutoPageBreak(false);
        $this->PDF->SetMargins($this->LeftMargin, $this->TopMargin);
        $this->PDF->SetMargins($this->LeftMargin,$this->TopMargin,$this->RightMargin);
        $this->PDF->SetCreator('Agata Report');
        $this->PDF->SetTitle('Label');
        $this->PDF->SetKeywords('www.agata.org.br');
        $this->PDF->Open();
        $this->PDF->AliasNbPages();
        $this->SetFont('an10');

        $this->pagina = 1;
        $this->page = 0;
        $this->page ++;
        $this->PDF->AddPage($this->Orientation);
        $this->PDF->SetY(0);
        $this->PDF->SetX(0);
        $LabelNumber = 0;
        $Column = 1;
        $X      = 0;
        
        while ($this->QueryLine = $this->CurrentQuery->FetchNext())
        {
            for ($y=1; $y<=count($this->QueryLine); $y++)
            {
                $QueryCell = $this->QueryLine[$y];
                $QueryCell = FormatMask($this->Adjustments[$y]['Mask'], $QueryCell);
                
                $MyVar = 'var' . $y;
                $$MyVar = $QueryCell;
            }
            
            $LabelNumber ++;
            // In�cio de P�gina
            if($LabelNumber == ($RowCount+1))
            {
                $Column ++;
                
                if ($Column > $ColCount)
                {
                    $this->page ++;
                    $this->PDF->AddPage($this->Orientation);
                    $this->PDF->SetLeftMargin($this->LeftMargin);
                    $this->PDF->SetTopMargin($this->TopMargin);
                    $Column = 1;
                    $X      = $this->LeftMargin;
                }
                else
                {
                    $X = (($this->LabelWidth + $this->HSpacing) * ($Column -1)) + $this->LeftMargin;
                    $this->PDF->SetLeftMargin($X);
                    $this->PDF->SetTopMargin($this->TopMargin);
                }
                $LabelNumber = 1;
            }
            $this->PDF->SetY((($LabelNumber -1)* ($this->LabelHeight + $this->VSpacing)) + $this->TopMargin);
            $lineN = 0;
            
            $X = (($this->LabelWidth + $this->HSpacing) * ($Column -1)) + $this->LeftMargin;
            
            foreach ($Lines as $Line)
            {
                $this->PDF->SetX($X);
                if (strlen($Lines)>0)
                {
                    $lineN ++;
                    eval ("\$Line = \"$Line\";");
                }
                
                if (!trim($Line))
                {
                    $this->PDF->Ln($this->LineSpacing);
                    $this->PDF->SetX($X);
                }
                else
                {
                    $had_content = $this->ParseStringPdf($Line);
                    if ($had_content)
                    {
                        $this->PDF->Ln($this->LineSpacing);
                        $this->PDF->SetX($X);
                    }
                }
            }
        }
        Wait::Off();
        $this->PDF->Output($this->FileName);

        if ($this->agataConfig)
        {
            Project::OpenReport($this->FileName, $this->agataConfig);
        }
        
        if ($this->posAction)
        {
            $obj = &$this->posAction[0];
            $att = &$this->posAction[1];
            
            $obj->{$att}();
        }
        $this->UnsetReportLocale();
        return true;
        
        ################## at� aqui.
    }
}
?>
