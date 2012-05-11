<?php
#################################################################
#  Copyright notice
#
#  (c) 2012 Jérôme Schneider <mail@jeromeschneider.fr>
#  All rights reserved
#
#  http://flake.codr.fr
#
#  This script is part of the Flake project. The Flake
#  project is free software; you can redistribute it
#  and/or modify it under the terms of the GNU General Public
#  License as published by the Free Software Foundation; either
#  version 2 of the License, or (at your option) any later version.
#
#  The GNU General Public License can be found at
#  http://www.gnu.org/copyleft/gpl.html.
#
#  This script is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  This copyright notice MUST APPEAR in all copies of the script!
#################################################################

namespace Flake\Core;

abstract class Database extends \Flake\Core\FLObject {

	/* common stuff */

	function messageAndDie($sMessage) {
		$sError = "<h2>" . get_class($this) . ": " . $sMessage . "</h2>";
		die($sError);
	}

	function exec_INSERTquery($table,$fields_values,$no_quote_fields=FALSE)	{
		return $this->query($this->INSERTquery($table,$fields_values,$no_quote_fields));
	}

	function INSERTquery($table,$fields_values,$no_quote_fields=FALSE)	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function (contrary to values in the arrays which may be insecure).
		if (is_array($fields_values) && count($fields_values))	{

				// quote and escape values
			$fields_values = $this->fullQuoteArray($fields_values,$table,$no_quote_fields);

				// Build query:
			$query = 'INSERT INTO '.$table.'
				(
					'.implode(',
					',array_keys($fields_values)).'
				) VALUES (
					'.implode(',
					',$fields_values).'
				)';

				// Return query:
			if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
			return $query;
		}
	}

	function exec_UPDATEquery($table,$where,$fields_values,$no_quote_fields=FALSE)	{
		return $this->query($this->UPDATEquery($table,$where,$fields_values,$no_quote_fields));
	}

	function UPDATEquery($table,$where,$fields_values,$no_quote_fields=FALSE)	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function (contrary to values in the arrays which may be insecure).
		if (is_string($where))	{
			if (is_array($fields_values) && count($fields_values))	{

					// quote and escape values
				$nArr = $this->fullQuoteArray($fields_values,$table,$no_quote_fields);

				$fields = array();
				foreach ($nArr as $k => $v) {
					$fields[] = $k.'='.$v;
				}

					// Build query:
				$query = 'UPDATE '.$table.'
					SET
						'.implode(',
						',$fields).
					(strlen($where)>0 ? '
					WHERE
						'.$where : '');

					// Return query:
				if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
				return $query;
			}
		} else {
			die('<strong>Fatal Error:</strong> "Where" clause argument for UPDATE query was not a string in $this->UPDATEquery() !');
		}
	}

	function exec_DELETEquery($table,$where)	{
		return $this->query($this->DELETEquery($table,$where));
	}

	function DELETEquery($table,$where)	{
		if (is_string($where))	{

				// Table and fieldnames should be "SQL-injection-safe" when supplied to this function
			$query = 'DELETE FROM '.$table.
				(strlen($where)>0 ? '
				WHERE
					'.$where : '');

			if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
			return $query;
		} else {
			die('<strong>Fatal Error:</strong> "Where" clause argument for DELETE query was not a string in $this->DELETEquery() !');
		}
	}

	function exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')	{
		return $this->query($this->SELECTquery($select_fields,$from_table,$where_clause,$groupBy,$orderBy,$limit));
	}

	function SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function
			// Build basic query:
		$query = 'SELECT '.$select_fields.'
			FROM '.$from_table.
			(strlen($where_clause)>0 ? '
			WHERE
				'.$where_clause : '');

			// Group by:
		if (strlen($groupBy)>0)	{
			$query.= '
			GROUP BY '.$groupBy;
		}
			// Order by:
		if (strlen($orderBy)>0)	{
			$query.= '
			ORDER BY '.$orderBy;
		}
			// Group by:
		if (strlen($limit)>0)	{
			$query.= '
			LIMIT '.$limit;
		}

			// Return query:
		if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
		return $query;
	}
	
	function fullQuote($str, $table)	{
		return '\''.$this->quote($str, $table).'\'';
	}

	function fullQuoteArray($arr, $table, $noQuote=FALSE)	{
		if (is_string($noQuote))	{
			$noQuote = explode(',',$noQuote);
		} elseif (!is_array($noQuote))	{	// sanity check
			$noQuote = FALSE;
		}

		foreach($arr as $k => $v)	{
			if ($noQuote===FALSE || !in_array($k,$noQuote))     {
				$arr[$k] = $this->fullQuote($v, $table);
			}
		}
		return $arr;
	}
	
	/* fonctions abstraites */
	
	abstract function query($sSql);
	
	abstract function lastInsertId();

	abstract function quote($str);
}