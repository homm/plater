<?php defined('SYSPATH') or die('No direct script access.');

class Plater_Core extends Kohana_View
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
	
	/*
	 * Captures the output that is generated when a view is included.
	 * The view data will be extracted to make local variables. 
	 * This method repeat View::capture(), but not static.
	 */
	protected function plater_capture($kohana_view_filename, array $kohana_view_data)
	{
		// Import the view variables to local namespace
		extract($kohana_view_data, EXTR_SKIP);

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include $kohana_view_filename;
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}
	
	public function block($name, $content = NULL)
	{
		// Opening block with same name not allowed
		if (array_search($name, $this->_blocks_stack) !== FALSE)
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

			// Render previous block content
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

		// First render
		$result = $this->plater_capture($this->_file, $data);

		$processed = array($this->_file => TRUE);

		while ($this->_extends)
		{
			// Find extendable view
			$this->set_filename($this->_extends);

			$this->_extends = NULL;

			if (isset($processed[$this->_file]))
			{
				throw new Kohana_View_Exception('View recursion extend');
			}

			$result = $this->plater_capture($this->_file, $data);

			$processed[$this->_file] = TRUE;
		}

		return $result;
	}
}
