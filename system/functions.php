<?

function imgTag($src,$params = array() )
{
	global $_sC;

	if( file_exists( $_sC->_get( 'path_images' )  . trim($src) ) )
	{
		$fileSrc = $_sC->_get( 'domain' ) . '/uploads/images/' . trim($src);
		
		$imgParams = "";
		
		if(count($params) > 0)
		{			
			foreach($params as $key=>$value)
			{
				$imgParams .= $key.'="'.$value.'" ';
			}
		}
		return '<img src="'.$fileSrc.'" '.$imgParams.'/>';
	}
}

function getSubpart($content, $marker) {
	
	$start = strpos($content, $marker);
	
	if ($start === FALSE) {
			return '';
	}
	
	$start += strlen($marker);
	$stop = strpos($content, $marker, $start);
	
			// Q: What shall get returned if no stop marker is given
			// /*everything till the end*/ or nothing?
	if ($stop === FALSE) {
			return ''; /*substr($content, $start)*/
	}
	
	$content = substr($content, $start, $stop - $start);
	
	$matches = array();
	if (preg_match('/^([^<]*\-\->)(.*)(<\!\-\-[^>]*)$/s', $content, $matches) === 1) {
			return $matches[2];
	}
	
	$matches = array(); // resetting $matches
	if (preg_match('/(.*)(<\!\-\-[^>]*)$/s', $content, $matches) === 1) {
			return $matches[1];
	}
	
	
	$matches = array(); // resetting $matches
	if (preg_match('/^([^<]*\-\->)(.*)$/s', $content, $matches) === 1) {
			return $matches[2];
	}
	
	return $content;
}

function substituteMarkerArray($template,$markers,$submarkers = array())
{
	$result = $template;

	foreach($markers as $mark=>$cont)
	{
    //echo $mark . ' = ' . $cont .' <br />';
		$result = preg_replace("/$mark/",$cont,$result);
	}

	return $result;
}

function xml2array($contents) 
{ 
    $xml_values = array(); 
    $parser = xml_parser_create(''); 
    if(!$parser) 
        return false; 

    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8'); 
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
    xml_parse_into_struct($parser, trim($contents), $xml_values); 
    xml_parser_free($parser); 
    if (!$xml_values) 
        return array(); 
    
    $xml_array = array(); 
    $last_tag_ar =& $xml_array; 
    $parents = array(); 
    $last_counter_in_tag = array(1=>0); 
    foreach ($xml_values as $data) 
    { 
        switch($data['type']) 
        { 
            case 'open': 
                $last_counter_in_tag[$data['level']+1] = 0; 
                $new_tag = array('name' => $data['tag']); 
                if(isset($data['attributes'])) 
                    $new_tag['attributes'] = $data['attributes']; 
                if(isset($data['value']) && trim($data['value'])) 
                    $new_tag['value'] = trim($data['value']); 
                $last_tag_ar[$last_counter_in_tag[$data['level']]] = $new_tag; 
                $parents[$data['level']] =& $last_tag_ar; 
                $last_tag_ar =& $last_tag_ar[$last_counter_in_tag[$data['level']]++]; 
                break; 
            case 'complete': 
                $new_tag = array('name' => $data['tag']); 
                if(isset($data['attributes'])) 
                    $new_tag['attributes'] = $data['attributes']; 
                if(isset($data['value']) && trim($data['value'])) 
                    $new_tag['value'] = trim($data['value']); 

                $last_count = count($last_tag_ar)-1; 
                $last_tag_ar[$last_counter_in_tag[$data['level']]++] = $new_tag; 
                break; 
            case 'close': 
                $last_tag_ar =& $parents[$data['level']]; 
                break; 
            default: 
                break; 
        }; 
    } 
    return $xml_array; 
}
?>
