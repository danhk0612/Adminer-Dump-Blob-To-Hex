<?php

/** Dump Blob Data To Hex
* @link https://www.adminer.org/plugins/#use
* @author GoodSea0612, https://blog.mydepot.kr/
* @license https://opensource.org/licenses/MIT The MIT License
*/

class AdminerDumpBlobToHex {
      function dumpData($table, $style, $query) {
		global $jush;
            $connection = connection();
		$max_packet = ($jush == "sqlite" ? 0 : 1048576); // default, minimum is 1024
		if ($style) {
			if ($_POST["format"] == "sql") {
				if ($style == "TRUNCATE+INSERT") {
					echo truncate_sql($table) . ";\n";
				}
				$fields = fields($table);
			}
			$result = $connection->query($query, 1); // 1 - MYSQLI_USE_RESULT //! enum and set as numbers
			if ($result) {
				$insert = "";
				$buffer = "";
				$keys = array();
				$suffix = "";
				$fetch_function = ($table != '' ? 'fetch_assoc' : 'fetch_row');
				while ($row = $result->$fetch_function()) {
					if (!$keys) {
						$values = array();
						foreach ($row as $val) {
							$field = $result->fetch_field();
							$keys[] = $field->name;
							$key = idf_escape($field->name);
							$values[] = "$key = VALUES($key)";
						}
						$suffix = ($style == "INSERT+UPDATE" ? "\nON DUPLICATE KEY UPDATE " . implode(", ", $values) : "") . ";\n";
					}
					if ($_POST["format"] != "sql") {
						if ($style == "table") {
							dump_csv($keys);
							$style = "INSERT";
						}
						dump_csv($row);
					} else {
						if (!$insert) {
							$insert = "INSERT INTO " . table($table) . " (" . implode(", ", array_map('idf_escape', $keys)) . ") VALUES";
						}
						foreach ($row as $key => $val) {
							$field = $fields[$key];

							$row[$key] = ($val !== null
								? unconvert_field($field, preg_match(number_type(), $field["type"]) && $val != '' && !preg_match('~\[~', $field["full_type"]) ? $val : q(($val === false ? 0 : $val)))
								: "NULL"
							);
                                          
                                          // check blob type and convert to hex 20220113
                                          if(strpos($field["type"], "blob") !== false){
                                                $row[$key] = "0x".bin2hex($row[$key]);
                                          }
						}
						$s = ($max_packet ? "\n" : " ") . "(" . implode(",\t", $row) . ")";
						if (!$buffer) {
							$buffer = $insert . $s;
						} elseif (strlen($buffer) + 4 + strlen($s) + strlen($suffix) < $max_packet) { // 4 - length specification
							$buffer .= ",$s";
						} else {
							echo $buffer . $suffix;
							$buffer = $insert . $s;
						}
					}
				}
				if ($buffer) {
					echo $buffer . $suffix;
				}
			} elseif ($_POST["format"] == "sql") {
				echo "-- " . str_replace("\n", " ", $connection->error) . "\n";
			}
		}
	}
}