<?php
/***********************************************************/
/* Class to deal with Dictionaries
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class Dictionary
{
    function ListDictionaries()
    {
        $aDict = getSimpleDirArray(AGATA_PATH . '/dictionary');
        foreach ($aDict as $dict)
        {
            $result[] = substr($dict, 0, -4);
        }
        
        $result = array_merge(array(''), $result);
        return $result;
    }

    /***********************************************************/
    /* Read the Project Definitions
    /***********************************************************/
    function ReadDictionary($Dictionary)
    {
        if (!$Dictionary)
        {
            return array(null);
        }
        // for Microsiga files it increasis memory up to 214Mb
        // using this stupid script...
        // it will use simpleXml when PHP-GTK2...

        //$array = Xml2Array("dictionary/{$Dictionary}.agd");

        $handler = fopen(AGATA_PATH . "/dictionary/{$Dictionary}.agd", 'r');
        while($line = fgets($handler, 500))
        {
            if (substr($line, 0, 9) == '    <dic:')
            {
                $table = substr($line, 9, -2);
            }
            if (substr($line, 0, 22) == '            <dic:name>')
            {
                if (substr($line, 22, -12))
                {
                    $array[$table]['groups']['name'][] = utf8_decode(utf8_encode(substr($line, 22, -12)));
                }
            }
            if (substr($line, 0, 19) == '        <dic:label>')
            {
                $array[$table]['label'] = utf8_decode(utf8_encode(substr($line, 19, -13)));
            }
            if (substr($line, 0, 17) == '            <dic:')
            {
                $field = substr($line, 17, -2);
            }
            if (substr($line, 0, 27) == '                <dic:label>')
            {
                $array[$table]['fields'][$field]['label'] = utf8_decode(utf8_encode(substr($line, 27, -13)));
            }
            if (substr($line, 0, 26) == '                <dic:link>')
            {
                $array[$table]['fields'][$field]['link'] = utf8_decode(utf8_encode(substr($line, 26, -12)));
            }
            
            
        }

        if ($array)
        {
            foreach ($array as $table => $content)
            {
                $Description["table:$table"] = $content['label'];
                
                if ($content['groups'])
                {
                    if (is_array($content['groups']['name']))
                    {
                        foreach ($content['groups']['name'] as $group)
                        {
                            $TableFamilies[$group][] = $table;
                            $TableGroups[]           = $group;
                        }
                    }
                    else
                    {
                        $group = $content['groups']['name'];
                        $TableFamilies[$group][] = $table;
                        $TableGroups[]           = $group;
                    }
                }
                if ($content['fields'])
                {
                    foreach ($content['fields'] as $field => $contents)
                    {
                        foreach ($contents as $key => $content)
                        {
                            if ($key == 'label')
                            {
                                $Description["table:$table:field:$field"] = $content;
                            }
                            if ($key == 'link')
                            {
                                $pieces = explode('.', $content);
                                $Links[$table][$field] = $pieces;
                            }
                        }
                    }
                }
            }
        }
        
        if ($TableGroups)
        {
            $TableGroups = array_unique($TableGroups);
        }
        unset($array);
        return array($TableFamilies, $TableGroups, $Links,  $Description);
    }

    /***********************************************************/
    /* Convert definitions into a plain format
    /***********************************************************/
    function Planification($agataTbFamilies, $agataTbLinks, $agataDataDescription)
    {
        $PlainTbFamilies = null;
        $PlainTbLinks = null;
        $PlainDataDescription = null;
        
        if ($agataTbFamilies)
        {
            foreach ($agataTbFamilies as $TbFamily => $tables)
            {
                if ($tables)
                {
                    foreach ($tables as $table)
                    {
                        $PlainTbFamilies[] = array($TbFamily, $table);
                    }
                }
            }
            sort($PlainTbFamilies);
        }
        
        if ($agataTbLinks)
        {
            foreach ($agataTbLinks as $table1 => $TbLinks)
            {
                if ($TbLinks)
                {
                    foreach ($TbLinks as $field1 => $TbLink)
                    {
                        $PlainTbLinks[] = array("{$table1}.{$field1}", "{$TbLink[0]}.{$TbLink[1]}");
                    }
                }
            }
            sort($PlainTbLinks);
        }
        
        if ($agataDataDescription)
        {
            foreach ($agataDataDescription as $datastructure => $datadescription)
            {
                if (($datastructure) && ($datadescription))
                $PlainDataDescription[] = array("$datastructure", "$datadescription");
            }
            sort($PlainDataDescription);
        }
        
        return array($PlainTbFamilies, $PlainTbLinks, $PlainDataDescription);
    }

    /***********************************************************/
    /* Write Table families definition to a file
    /***********************************************************/
    function WriteDictionary($file, $array)
    {
        if ($array)
        {
            $handler = fopen($file, 'w');
            fwrite($handler, trim( XMLHEADER . Array2Xml($array, null, " xmlns:dic='http://www.agata.org.br'")));
            fclose($handler);
            return true;
        }
        return false;
    }
}
?>
