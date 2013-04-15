<?
class xml_reader
{
	protected $tag;
	protected $inside_data;
	protected $artNr;
	public $userdata;
	protected $state;
	protected $usercount;
	protected $parser;
	protected $searched_tags;

	public $system_path;
	protected $source_encoding;

	function read_file($file)
	{
		$this->source_file = $file;

		if ( !( $fp=@fopen($file, "r") ) )
        die ("Couldn't open XML.");

		if (!($this->parser = xml_parser_create()))
        die("Couldn't create parser.");
		
#	Detects the Encoding of the source file
		$lines = file($file);
		preg_match("/encoding=\"(.*)\"/",$lines[0],$match);
		$this->source_encoding = strtoupper($match[1]);

		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);

		xml_set_element_handler($this->parser,array ( &$this, "startElementHandler"),array ( &$this, "endElementHandler"));
		xml_set_character_data_handler( $this->parser, array ( &$this, "characterDataHandler"));

		while( $data = fread($fp,8134))
		{
			if(false == xml_parse($this->parser, $data, feof($fp)))
			{
				$error = "Error: ".xml_error_string(xml_get_error_code($this->parser))." At Line: ".xml_get_current_line_number ( $this->parser );
			}
		}

		xml_parser_free($this->parser);
	}

	function startElementHandler ($parser,$name,array $attribs)
	{
		if( !empty($name))
		{
			$this->tag = $name;
			
		}
		else
		{
			$this->tag = NULL;
		}
		
		if( !empty($attribs) )
		{
			$keys = array_keys($attribs);
			$this->userdata[$this->tag]["atributes"] = $attribs;
		}
		
		$this->inside_data = false;
	}

	function endElementHandler ($parser,$name)
	{
		$this->tag = null;
		$this->inside_data = false;
  }
	
	function characterDataHandler ($parser, $data) 
	{
		if($this->tag != NULL )
		{
				$this->userdata[$this->tag]["value"] = $data;
		}
		$this->inside_data = true;
	}


	function utf8_code($text,$decode=false)
	{
		if( $decode == false )
		{
			if( $this->source_encoding != "UTF-8" )
			{
				$text = iconv($this->source_encoding,"UTF-8",$text);
			}

			$text = str_replace(
				array("ä","ë","ü","ö","ß","Ä","Ë","Ü","Ö","ß","©"),
				array("#ae#","#ea#","#ue#","#oe#","#ss#","#AE#","#EA#","#UE#","#OE#","#SS#","#copy#"),
				$text
			);

		}
		else
		{
			$text = str_replace(
				array("#ae#","#ea#","#ue#","#oe#","#ss#","#AE#","#EA#","#UE#","#OE#","#SS#","#copy#"),
				array("ä","ë","ü","ö","ß","Ä","Ë","Ü","Ö","ß","©"),
				$text
			);
			//$text = utf8_decode($text);
		}

		return $text;
	}

	protected function write_file($filename,$section,$text)
	{
		$filename = str_replace("/","-",$filename);
		if( !file_exists($this->target_dir."{$filename}") )
		{
			$_file = fopen($this->target_dir."{$filename}","w+");
			fwrite( $_file, $text);
			fclose($_file);

			return true;
		}
		else
		{
			return false;
		}
	}

	
}
?>
