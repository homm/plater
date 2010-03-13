<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Plater extends Kohana_View
{
	// Array of blocks in current View
	protected $_blocks = array();

	// Order of blocks
	protected $_blocks_stack = array();
	
	protected $_extends = NULL;
	
	public function extend($file)
	{
		$this->_extends = $file;
	}
	
	public function block($name, $content = NULL)
	{
		if (array_search($name, $this->_blocks_stack))
		{
			throw new Kohana_Exception('View :file already collect block :block', array(
					':file' => $this->_file,
					':block' => $name,
				));
		}
		
		$this->_blocks_stack[] = $name;
		
		ob_start();
		
		return empty($this->_blocks[$name]);
	}
	
	public function endblock($name = NULL)
	{
		// Get name of last block in stack
		$last = array_pop($this->_blocks_stack);
		
		// If user pas $name, we can check block's order 
		if ($name AND $name !== $last)
		{
			throw new Kohana_Exception('Wrong blocks order. :expected expected, but :given given.', array(
					':expected' => $last,
					':given' => $name,
				));
		}
		
		if (empty($this->_blocks[$name]))
		{
			// If block not yet present, render current and save it
			$this->_blocks[$name] = ob_get_flush();
		}
		else
		{
			// This output overlapped
			ob_end_clean();
			
			// Render previous block_content
			echo $this->_blocks[$name];
		}
	}
	
	public function blocks()
	{
		return $this->_blocks;
	}
	
	public function render($file = NULL)
	{
		if ($file !== NULL)
		{
			$this->set_filename($file);
		}
		
		if ($file === NULL AND empty($this->_file))
		{
			throw new Kohana_View_Exception('You must set the file to use within your view before rendering');
		}

		// Combine local and global data and capture the output
		$data = $this->_data + View::$_global_data;
		
		// Adds pointer to this object, to allow blocking
		$data['self'] = $this;

		// First render
		$result = View::capture($this->_file, $data);
		
		while ($this->_extends)
		{
			// Find extendable view
			$this->set_filename($this->_extends);
			
			$this->_extends = NULL;
			
			$result = View::capture($this->_file, $data);
		}
		
		return $result;
	}
}
