<?php
interface iDatabase
{
	public function insert($cols=array(),$table);
	public function select($cols,$table,$where=NULL,$order=NULL,$group=NULL,$start=0,$limit=0);
	public function select_row($cols,$table,$where=NULL,$order=NULL,$group=NULL);
	public function update($cols,$table,$where,$limit=NULL);
	public function delete($table, $where, $limit=NULL);
}

class db implements iDatabase
{	private static $instance = false;

	protected static $connection_id;

  public $source;

  public $error = NULL;
	public $errno;
	public $row_count;

	public $last_ID;

	public $table_prefix;

	private $server;
	private $port;
	private $user;
	private $password;
	private $database;
	private $coding;
  
  private function __constructor($server,$user,$password,$database,$codepage,$port){}
  
	function getInstance()
	{
		if( self::$instance == false )
		{
			self::$instance = new db();
		}

		return self::$instance;
	}

/*	function getInstance($server,$user,$password,$database,$codepage='utf8',$port='3306')
	{
		if( self::$instance == false )
		{
			self::$instance = new db($server,$user,$password,$database,$codepage,$port);
		}

		return self::$instance;
	}

  function db($server,$user,$password,$database,$codepage,$port)
  { //Establishing connection to the database
		
		$this->server = $server = $server.":".$port;
    $this->connection_id = mysql_connect($server, $user, $password);
    	
		if( $database != false )
		{
    	$db = @mysql_select_db($database);
		}
		else
		{
			$db=true;
		}
    
		$res1 = mysql_query("SET CHARACTER SET '$codepage'",$this->connection_id);
		$res2 = mysql_query("SET NAMES '$codepage'",$this->connection_id);

    if(!$this->connection_id || !$db || !$res1 || !$res2)
		{
			print mysql_error();
		}
  }*/

	function db()
	{
		//return true;
	}

	function _set($variable, $value)
  {		 
	  if( array_key_exists( $variable, self::_getClassVars() ) )
    {
	    $this->{$variable} = $value;
      return true;
    }
    else
    {
      return false;
    }
  }

  function _get($variable)
  {		 
     if( array_key_exists( $variable, self::_getClassVars() ) )
     {
        return $this->{$variable};
     }
  }

	function _getClassVars()
	{
		return get_class_vars( get_class( $this ) );
	}

	function connect()
	{
		if( !empty( $this->server ) )
		{
			$server = $this->server.":".($this->port?$this->port:3306);

			if( $con = mysql_connect($server,$this->user,$this->password) )
			{
				$this->connection_id = $con;
			}
			else
			{
				$this->errno = mysql_errno($con);
			}
		}
		else
		{
			
			return false;
		}

		if( $this->connection_id && !empty( $this->database ) )
		{
			mysql_select_db($this->database);
		}

		if( $this->connection_id && !empty( $this->coding ) )
		{
			$this->db_query("SET CHARACTER SET '".$this->coding."'");
			$this->db_query("SET NAMES '".$this->coding."';");
		}

		return true;
	}
  
  function db_query($sql=NULL)
  { //start query function
		$this->source = $sql;
		$this->row_count = 0;
    if(!empty($sql))
    {   // if $sql task is not empty, then proceed

      $resource = mysql_query($sql,$this->connection_id);
			

			if(!empty($resource))
      { // if resource is filled then return it        
				
        return $resource; // return resource of the task
      }
      else
      {
        $this->error = "SQL Failure - >".mysql_error($this->connection_id)."<br />Source:{$this->source}<hr />";
				$this->errno = mysql_errno($this->connection_id);
				return false;
      }
    }
    else
    {   // else make an error and return false
      $this->error = "Task was empty";
      return false;
    }
  } // end function
  
  function select($cols,$table,$where=NULL,$order=NULL,$group=NULL,$start=0,$limit=0)
  { // create select question

    $cols = explode(",",$cols);
    $sql = "SELECT ";
    
    for($i=0;$i<count($cols);$i++)
      { // filing the question with col names
        $sql .= $cols[$i].", ";
      }
    $sql = substr($sql,0,-2);
		if(substr_count($table,">")>0)
		{
			$table = $this->_make_table_joins($table);
		}
		/*elseif(substr_count($table,"join")==0 && !strpos($table,"`"))
		{
			$table = "`".$this->table_prefix.str_replace("`","",$table)."`";
		}*/
    
    $sql .= " FROM $table ";
    

   	$sql .= (!empty($where)?"WHERE $where ":""); // when a WHERE is, then set it, else make free space
  	$sql .= (!empty($group)?"GROUP BY $group ":"");   // when a GROUP is set, then set it, else close the question
  	$sql .= (!empty($order)?"ORDER BY $order ":"");   // when ORDER is set, then set it, else close the question
  	$sql .= (!empty($limit)?"LIMIT $start,$limit":";");   // when a LIMIT is set, then set it, else close the question

		$this->source = $sql;
		$resource = $this->db_query($sql); // get the resource handler		

		if(!empty($resource))
		{
			$this->row_count = @mysql_num_rows($resource); // count of affected rows
			$result = $this->fetch_assoc($resource); // get the values
			@mysql_free_result($resource);
			if(!empty($result))
			{
			  return($result); // return values
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
  }

	function select_row($cols,$table,$where=NULL,$order=NULL,$group=NULL)
  { // create select question

    $cols = explode(",",$cols);
    $sql = "SELECT ";
    
    for($i=0;$i<count($cols);$i++)
      { // filing the question with col names
        $sql .= $cols[$i].", ";
      }
    $sql = substr($sql,0,-2);
		if(substr_count($table,">")>0)
		{
			$table = $this->_make_table_joins($table);
		}
		elseif(substr_count($table," ")==0)
		{
			$table = "`".$this->table_prefix.str_replace("`","",$table)."`";
		}
    
    $sql .= " FROM $table ";
    

   	$sql .= (!empty($where)?"WHERE $where ":""); // when a WHERE is, then set it, else make free space
		$sql .= (!empty($order)?"ORDER BY $order ":"");   // when ORDER is set, then set it, else close the question

		$sql .= "LIMIT 1";

		$this->source = $sql;
		$resource = $this->db_query($sql); // get the resource handler

		if(!empty($resource))
		{
			$this->row_count = @mysql_num_rows($resource); // count of affected rows
			$result = $this->fetch_assoc($resource); // get the values
			$result = $result[0];
			@mysql_free_result($resource);
			if(!empty($result))
			{						
			  return($result); // return values
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
  }
  
  function insert($cols=array(),$table)
  {
		$table = "`".$this->table_prefix.str_replace("`","",$table)."`";

    $sql = "INSERT INTO $table ";
    foreach($cols as $col_name => $col_value)
      {
        $col_names[] = mysql_real_escape_string($col_name);
        $col_values[] = mysql_real_escape_string($col_value);
      }
      
    if(count($col_names) == count($col_values))
      {
        $sql .= "(";
        for($i=0;$i<count($col_names);$i++)
          {
            $sql .= "`".$col_names[$i]."`,";
          }
        $sql = substr($sql,0,-1);                    // Erase the last ','
        
        $sql .= ") values(";
        
        for($i=0;$i<count($col_values);$i++)
          {
            $sql .= "'".$col_values[$i]."',";
          }
          
        $sql = substr($sql,0,-1);                   // Erase the last ','
      }
      
    $sql .= ")";
    
    $this->source = $sql;
    $result = $this->db_query($sql);
		
  	if($result === true)
		{
			$this->last_ID = @mysql_insert_id($this->connection_id);
			$this->row_count = @mysql_affected_rows($result); // count of affected rows
			return true;
		}
		else
		{
			return false;
		}
	}

  function update($cols,$table,$where,$limit=NULL)
  {
    if(!empty($where))
    {
		
			$table = "`".$this->table_prefix.str_replace("`","",$table)."`";

      $sql = "UPDATE $table SET ";
      
			if(is_array($cols))
			{
        foreach($cols as $col_name => $col_value)
	      {
  	      //$sql .= "`$col_name`='".htmlentities($col_value,ENT_QUOTES,"UTF-8")."', ";
					/*if(is_numeric($col_value))
					{
						if(preg_match("/[\+\-]{1}[0-9]*//*",$col_value))
						{
							$sql .= "`$col_name`=$col_name$col_value, ";
						}
						else
						{
							$sql .= "`$col_name`=$col_value, ";
						}
					}
					else
					{
						$sql .= "`$col_name`='".mysql_real_escape_string($col_value)."', ";
					}*/
					 
					$sql .= "`$col_name`='".mysql_real_escape_string($col_value)."', ";
    	  }
				
				$sql = substr($sql,0,-2);
			}
			else
			{
				$sql .= $cols;
			}
      
      $sql .= " WHERE $where";
			if($limit != NULL) { $sql .= " LIMIT $limit;"; }

			$this->source = $sql;
      $result = $this->db_query($sql);
			

      if($result!==false)
      {
				$this->row_count = @mysql_affected_rows($result); // count of affected rows
			  return true;
      }
      else
      {
        return false;
      }
    }
    else
    {
      $this->error = "No where was set, too many rows would be affected";
      return false;
    }
  }

  function delete($table, $where, $limit=NULL)
  {
    if(!empty($where))
    {
			$table = "`".$this->table_prefix.str_replace("`","",$table)."`";

      $sql = "DELETE FROM $table WHERE $where";
      $sql .= (($limit!==NULL)?" LIMIT $limit;":";");

      $this->source = $sql;
      $result = $this->db_query($sql);
			$this->row_count = @mysql_affected_rows($result); // count of affected rows
        
      if($result===true)
      {
      	return true;
      }
      else
      {
            return false;
      }
    }
    else
    {
      $this->error = "No Search parameter was set, too many rows would be affected";
        
      return false;
    }        
  }

   function fetch_assoc($resource)
  {
		if($this->row_count>0)
		{
    	while($result = @mysql_fetch_assoc($resource))
      {
       	$source[] = array_map("stripslashes",$result); 
      }

	    return $source;
		}
		else
		{
			return false;
		}
  }
	

	protected function db_get_cols($table)
	{
		$sql = "SHOW columns from $table";
		$resource = $this->db_query($sql);
		$result = $this->fetch_assoc($resource);
		return $result;
	}

	protected function _make_table_joins($table_list)
	{
		$tables = explode(">",$table_list);

		for((int)$i=0;$i<count($tables);$i++)
		{
			$tab = array("table_name","join_col","next_join");

			list($tab["table_name"],$tab["join_col"],$tab["next_join"]) = explode("|",$tables[$i],3);

			$tab=str_replace(array("(",")"),"",$tab);

			$tab["shortcut"] = "`".strtoupper(substr($tab["table_name"],0,2))."`";
			if($tab["shortcut"] == $table[$i-1]["shortcut"])
			{
				$tab["shortcut"] = "`".strtoupper(substr($tab["table_name"],0,4))."`";
			}

			$table[$i] = $tab;
		}
		
		$result="(`".$this->table_prefix.$table[0]["table_name"]."` as ".$table[0]["shortcut"]." left join ".$this->table_prefix.$table[1]["table_name"]." as ".$table[1]["shortcut"]." on ".$table[0]["shortcut"].".`".$table[0]["join_col"]."`=".$table[1]["shortcut"].".`".$table[1]["join_col"]."`)";

		if(count($table)>2)
		{
			for($tab=2;$tab<count($table);$tab++)
			{				
				$result = "($result left join `".$this->table_prefix.$table[$tab]["table_name"]."` as ".$table[$tab]["shortcut"]." on ".$table[$tab-1]["next_join"]."=".$table[$tab]["shortcut"].".`".$table[$tab]["join_col"]."`)";
			}
		}

		return $result;
	}
	


  public function close()
  {
    mysql_close($this->connection_id);
  }
}
?>
