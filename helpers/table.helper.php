<?
//========= Table Helper

class table
{
	var $result;
	var $row_template;
	var $rows=array();
	var $tfoot, $thead;

	function table($cellspacing=1,$params=array())
	{
		$this->result = "<table cellspacing=\"$cellspacing\"";
		if(!empty($params))
		{
			foreach($params as $param=>$value)
			{
				$this->result .= " $param=\"$value\"";
			}
		}
		$this->result .= ">";
	}
	
	//	===== Creates a table header
	//	* $data = [array|string]
	function table_head($data=array(),$params=array())
	{
		$thead = "<thead";

		if(!empty($params))
		{
			foreach($params as $param=>$value)
			{
				$thead .= " $param=\"$value\"";
			}
		}
		$thead .= ">\n";


		$thead .= "<tr>\n";
		foreach($data as $head_col)
		{			
			$thead .= $this->_make_col($head_col);
		}
		$thead .= "</tr>\n";
		$thead .= "</thead>\n";

		$this->thead = $thead;

		//$this->result .= $thead;
	}
	
	//====== Add a single Row to the table
	//	* $data = [array|string]
	//	*	$params = array - optional params for this row as class, etc.
	function add_row($data,$params=null)
	{
		$row_class = ((count($this->rows)%2)?"odd":"even");
		$row_class = (isset($params["class"]))?$params["class"]:$row_class;
		$row_style = (isset($params["style"]))?" style=\"{$params["style"]}\"":"";
		$row = "<tr class=\"$row_class\"$row_style>\n";
		foreach($data as $col)
		{
			$row .= $this->_make_col($col);
			
		}
		$row .= "</tr>\n";

		$this->rows[count($this->rows)] = $row;
	}
	
	//====== Add multiple rows to the table
	//* calls function add_row foreach entry
	function add_rows($data,$params=array())
	{
		for($d=0;$d<count($data);$d++)
		{
			$this->add_row($data[$d]);
		}
	}
	

	//====== Creates a Table from array
	//* from first entry it takes the 
	//*	values for the table head
	//* next it takes every entry and
	//* ads it to the table body
	//* in the end it calls the function
	//* create_output and return $this->result
	function auto_table($data)
	{
		$this->table_head(array_keys($data[0]));

		for($item=0;$item<count($data);$item++)
		{
			$this->add_row($data[$item]);
		}

		$this->create_output();

		return $this->result;
	}
	function table_foot($data,$params=array())
	{
		$tfoot = "<tfoot>\n";
		$tfoot .= "<tr>\n";
		foreach($data as $col)
		{			
			$tfoot .= $this->_make_col($col);
		}
		$tfoot .= "</tr>\n";
		$tfoot .= "</tfoot>\n";

		$this->tfoot = $tfoot;		
	}
	
	//	===== Creates a table column
	//	*$data = [array|string]
	//	*$col => result column
	private function _make_col($data)
	{
		$col = "";
		$col = "\t<td";
		if(is_array($data))
		{
			$value = $data["value"];
			unset($data["value"]);
			foreach($data as $param=>$par_value)
			{
				$col .= " $param=\"$par_value\"";
			}
		}
		else
		{
			$value = $data;
		}			
		$col .= ">$value";			
		$col .= "</td>\n";
		return $col;
	}

	function create_output()
	{
		$this->result .= $this->thead;
		$this->result .= $this->tfoot;
		$this->result .= "<tbody>\n";
		for($row=0;$row<count($this->rows);$row++)
		{
			$this->result .= $this->rows[$row];
		}
		$this->result .= "</tbody>\n";
		$this->result .= "</table>";
	}

	function clear()
	{
		$this->rows = array();
		$this->row_template = NULL;
		$this->result = NULL;
	}

	function __destruct()
	{
		return true;
	}
}
?>
