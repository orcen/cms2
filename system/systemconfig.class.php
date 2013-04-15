<?
class systemConfig
{
	private $variables = array();	# config array, that contains all settings from the config file
	private $variables_comments = array();

	function __construct( $_configFile )
	{
		if( file_exists( $_configFile ) )
		{
			if( $config_file = file( $_configFile ) )
			{
				foreach( $config_file as  $line )
				{
					(string) $param = "";
					(string) $value = "";
					(string) $comment = "";
					if( !empty( $line ) )
					{
						if( substr( $line, 0, 1 ) != "#" AND strpos( $line, "=" ) )
						{							
							list($param, $value) = explode("=",$line);
							
							if( strpos($value,"#",0) ){ list( $value , $comment ) = explode( "#", $value ); }
  
							$value = $this->_processValue( $value );
  
							$this->_set($param,$value);
  
							if( !empty( $comment ) )
							{
								$this->variables_comments[ trim( $param ) ] = str_replace("\n","",$comment);
							}
						}
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	private function _processValue($value)
	{
		$value = trim( $value );

		if( strpos( $value, "|" ) )
		{
			$value = (array)explode("|", $value );
			return $value;
			exit;
		}

		if( in_array( trim( strtolower($value) ), array("true","yes","1") ) )
		{
			$value = (bool)true;
		}
		elseif( in_array( trim( strtolower($value) ), array("false","no","0") ) )
		{
			$value = (bool)false;
		}

		if( strpos( $value, "+" ) )
		{
			list($arg1,$arg2) = explode("+",$value,2);

			$arg1 = trim($arg1);
			$arg2 = trim($arg2);

			if(false != $arg1Value = $this->_get($arg1) )
			{
				$value = $arg1Value;
			}
			else
			{
				$value = $arg1;
			}

			if(false != $arg2Value = $this->_get($arg2) )
			{
				$value .= $arg2Value;
			}
			else
			{
				$value .= $arg2;
			}

		}
		
		return $value;
	}

	function _set($variable,$value)
	{
		$variable = trim($variable);
		if(false == isset($this->variables[$variable]) )
		{
			$this->variables[$variable] = $value;
			return true;
		}
		else
		{
			return false;
		}
	}

	function _get($variable)
	{
		if( isset( $this->variables[$variable] ) )
		{
			return $this->variables[$variable];
		}
		else
		{
			return false;
		}
	}

	function showVariables($withValues = FALSE)
	{
		(string) $result = "";
		foreach( $this->variables as $varName=>$varValue )
		{
			
			if( $withValues === TRUE )
			{
				if( stripos($varName, "pass", 0) )
				{
					$varValue = "*****";
				}

				if( is_array( $varValue ) )
				{
					$varValue = "Array(".implode(",",$varValue).")";
				}

				if( !empty( $this->variables_comments[$varName] ) )
				{
					$varName = "<abr title='". $this->variables_comments[$varName] ."'>$varName</abr>";
				}

				$result .= "\$config[ <strong>$varName</strong> ] = <strong>$varValue</strong>\n";
			}
			else
			{
				$result .= "\$config[ $varName ] \n";
			}
		}

		return "<p>". $result ."</p>";
	}
}
?>
