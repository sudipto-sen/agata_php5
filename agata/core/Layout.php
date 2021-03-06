<?php
/***********************************************************/
/* Class to deal with Layouts
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class Layout
{
    function ListLayouts()
    {
        $aDict = getSimpleDirArray(AGATA_PATH . '/layout');
        foreach ($aDict as $dict)
        {
            $result[] = substr($dict, 0, -4);
        }
        
        return $result;
    }

    /***********************************************************/
    /* Read the Layout Template
    /***********************************************************/
    function ReadLayout($Layout)
    {
        if (!$Layout)
        {
            return array(null);
        }
        $array['layout'] = Xml2Array(AGATA_PATH . "assets/layout/{$Layout}.lay");
        $Schema['DataFont']       = $array['layout']['data']['font'];
        $Schema['TotalFont']      = $array['layout']['total']['font'];
        $Schema['GroupFont']      = $array['layout']['group']['font'];
        $Schema['ColumnFont']     = $array['layout']['column']['font'];
        $Schema['HeaderFont']     = $array['layout']['header']['font'];
        $Schema['FooterFont']     = $array['layout']['footer']['font'];
        $Schema['DataColor']      = $array['layout']['data']['color'];
        $Schema['TotalColor']     = $array['layout']['total']['color'];
        $Schema['GroupColor']     = $array['layout']['group']['color'];
        $Schema['ColumnColor']    = $array['layout']['column']['color'];
        $Schema['HeaderColor']    = $array['layout']['header']['color'];
        $Schema['FooterColor']    = $array['layout']['footer']['color'];
        $Schema['DataBgColor']    = $array['layout']['data']['bgcolor'];
        $Schema['TotalBgColor']   = $array['layout']['total']['bgcolor'];
        $Schema['GroupBgColor']   = $array['layout']['group']['bgcolor'];
        $Schema['ColumnBgColor']  = $array['layout']['column']['bgcolor'];
        $Schema['HeaderBgColor']  = $array['layout']['header']['bgcolor'];
        $Schema['FooterBgColor']  = $array['layout']['footer']['bgcolor'];
        $Schema['Orientation']    = $array['layout']['config']['orientation'];
        $Schema['ColorLines']     = $array['layout']['config']['color_lines'];
        $Schema['LeftMargin']     = $array['layout']['config']['left_margin'];
        $Schema['Border']         = $array['layout']['config']['border'];
        $Schema['CellSpacing']    = $array['layout']['config']['cell_spacing'];

        return $Schema;
    }

    /***********************************************************/
    /* Write Layout
    /***********************************************************/
    function WriteLayout($file, $array)
    {
        if ($array)
        {
            $handler = fopen($file, 'w');
            fwrite($handler, trim( XMLHEADER . Array2Xml($array, null)));
            fclose($handler);
            return true;
        }
        return false;
    }
}
?>
