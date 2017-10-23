<?php

	class clsCsv
	{
		public static function toArray($sData, $sSeperator = ',')
		{
			if(is_array($sData))
			{
				// debug_print_backtrace();
				return $sData;
			}
			elseif(!is_string($sData))
			{
				return array();
			}
			elseif(strlen($sData) < 1)
			{
				return array('');
			}
			elseif(@is_file($sData))
			{
				$sData = file_get_contents($sData);
			}

			$sData = str_replace(array("\r\n", "\r"), array("\n", "\n"), $sData);

			// output
			$csv = array();
			$line = array();

			$quoted = false; // Flag: quoted string

			$buffer = ''; // Buffer (quoted values)
			$junk = ''; // Junk buffer (unquoted values)

			for($i = 0; $i < strlen($sData); $i++)
			{
				$char = $sData[$i];

				if($quoted)
				{
					if($char == '"')
					{
						if(isset($sData[$i + 1]) && ($sData[$i + 1] == '"')) // Peak next char
						{
							// Add char to buffer
							$buffer .= $char;
							$i++;
						}
						else
						{
							// Set flags
							$quoted = false;
						}
					}
					else
					{
						// Add char to buffer
						$buffer .= $char;
					}
				}
				else
				{
					if($char == LF) // Start a new line
					{
						if(strlen($buffer) > 0)
						{
							// Add buffer to line
							$line[] = $buffer;

							// Clear buffer
							$buffer = '';
						}
						else
						{
							$junk = trim($junk);

							// Add junk to line (possible unquoted values?)
							$line[] = $junk;
						}

						// Clear junk
						$junk = '';

						// Add line to CSV
						$csv[] = $line;

						// Clear line
						$line = array();
					}
					elseif($char == '"') // Start new quoted value
					{
						// Set flags
						$quoted = true;
					}
					elseif($char == $sSeperator)
					{
						if(strlen($buffer) > 0)
						{
							// Add buffer to line
							$line[] = $buffer;

							// Clear buffer
							$buffer = '';
						}
						else
						{
							$junk = trim($junk);

							// Add junk to line (possible unquoted values?)
							$line[] = $junk;
						}

						// Clear junk
						$junk = '';
					}
					else // Add to junk char
					{
						$junk .= $char;
					}
				}
			}

			// Clean up
			if(strlen($buffer) > 0)
			{
				// Add buffer to line
				$line[] = $buffer;

				// Clear buffer
				$buffer = '';
			}
			else
			{
				$junk = trim($junk);

				// Add junk to line (possible unquoted values?)
				$line[] = $junk;
			}

			$csv[] = $line;

			return $csv;
		}
	}

?>