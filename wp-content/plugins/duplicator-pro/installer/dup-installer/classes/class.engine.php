<?php
defined("DUPXABSPATH") or die("");

/**
 * Walks every table in db that then walks every row and column replacing searches with replaces
 * large tables are split into 50k row blocks to save on memory.
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\UpdateEngine
 *
 */
class DUPX_UpdateEngine
{

    /**
     *  Used to report on all log errors into the installer-txt.log
     *
     * @param string $report The report error array of all error types
     *
     * @return string Writes the results of the update engine tables to the log
     */
    public static function logErrors($report)
    {
        if (!empty($report['errsql'])) {
            DUPX_Log::info("--------------------------------------");
            DUPX_Log::info("DATA-REPLACE ERRORS (MySQL)");
            foreach ($report['errsql'] as $error) {
                DUPX_Log::info($error);
            }
            DUPX_Log::info("");
        }
        if (!empty($report['errser'])) {
            DUPX_Log::info("--------------------------------------");
            DUPX_Log::info("DATA-REPLACE ERRORS (Serialization):");
            foreach ($report['errser'] as $error) {
                DUPX_Log::info($error);
            }
            DUPX_Log::info("");
        }
        if (!empty($report['errkey'])) {
            DUPX_Log::info("--------------------------------------");
            DUPX_Log::info("DATA-REPLACE ERRORS (Key):");
            DUPX_Log::info('Use SQL: SELECT @row := @row + 1 as row, t.* FROM some_table t, (SELECT @row := 0) r');
            foreach ($report['errkey'] as $error) {
                DUPX_Log::info($error);
            }
        }
    }

    /**
     *  Used to report on all log stats into the installer-txt.log
     *
     * @param string $report The report stats array of all error types
     *
     * @return string Writes the results of the update engine tables to the log
     */
    public static function logStats($report)
    {
        if (!empty($report) && is_array($report)) {
            $stats = "--------------------------------------\n";
            $srchnum = 0;
            foreach ($GLOBALS['REPLACE_LIST'] as $item) {
                $srchnum++;
                $stats .= sprintf("Search{$srchnum}:\t'%s' \nChange{$srchnum}:\t'%s' \n", $item['search'], $item['replace']);
            }
            $stats .= sprintf("SCANNED:\tTables:%d \t|\t Rows:%d \t|\t Cells:%d \n", $report['scan_tables'], $report['scan_rows'], $report['scan_cells']);
            $stats .= sprintf("UPDATED:\tTables:%d \t|\t Rows:%d \t|\t Cells:%d \n", $report['updt_tables'], $report['updt_rows'], $report['updt_cells']);
            $stats .= sprintf("ERRORS:\t\t%d \nRUNTIME:\t%f sec", $report['err_all'], $report['time']);
            DUPX_Log::info($stats);
        }
    }

    /**
     * Returns only the text type columns of a table ignoring all numeric types
     *
     * @param obj $conn A valid database link handle
     * @param string $table A valid table name
     *
     * @return array All the column names of a table
     */
    public static function getTextColumns($conn, $table)
    {
        $type_where = "type NOT LIKE 'tinyint%' AND ";
        $type_where .= "type NOT LIKE 'smallint%' AND ";
        $type_where .= "type NOT LIKE 'mediumint%' AND ";
        $type_where .= "type NOT LIKE 'int%' AND ";
        $type_where .= "type NOT LIKE 'bigint%' AND ";
        $type_where .= "type NOT LIKE 'float%' AND ";
        $type_where .= "type NOT LIKE 'double%' AND ";
        $type_where .= "type NOT LIKE 'decimal%' AND ";
        $type_where .= "type NOT LIKE 'numeric%' AND ";
        $type_where .= "type NOT LIKE 'date%' AND ";
        $type_where .= "type NOT LIKE 'time%' AND ";
        $type_where .= "type NOT LIKE 'year%' ";

        $result = mysqli_query($conn, "SHOW COLUMNS FROM `".mysqli_real_escape_string($conn, $table)."` WHERE {$type_where}");
        if (!$result) {
            return null;
        }
        $fields = array();
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row['Field'];
            }
        }

        //Return Primary which is needed for index lookup.  LIKE '%PRIMARY%' is less accurate with lookup
        //$result = mysqli_query($conn, "SHOW INDEX FROM `{$table}` WHERE KEY_NAME LIKE '%PRIMARY%'");
        $result = mysqli_query($conn, "SHOW INDEX FROM `".mysqli_real_escape_string($conn, $table)."`");
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row['Column_name'];
            }
        }

        return (count($fields) > 0) ? $fields : null;
    }

    /**
     * Begins the processing for replace logic
     *
     * @param mysql $conn The db connection object
     * @param array $tables The tables we want to look at
     * @param array $fullsearch Search every column regardless of its data type
     *
     * @return array Collection of information gathered during the run.
     */
    public static function load($conn, $tables = array(), $fullsearch = false)
    {

        @mysqli_autocommit($conn, false);

        $report = array(
            'scan_tables' => 0,
            'scan_rows' => 0,
            'scan_cells' => 0,
            'updt_tables' => 0,
            'updt_rows' => 0,
            'updt_cells' => 0,
            'errsql' => array(),
            'errser' => array(),
            'errkey' => array(),
            'errsql_sum' => 0,
            'errser_sum' => 0,
            'errkey_sum' => 0,
            'time' => '',
            'err_all' => 0
        );

        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        $srManager = DUPX_S_R_MANAGER::getInstance();

		function set_sql_column_safe(&$str) {
			$str = "`$str`";
		}

    	// init search and replace array
    	$searchList = array();
    	$replaceList = array();

        $profile_start = DUPX_U::getMicrotime();
        if (is_array($tables) && !empty($tables)) {

            foreach ($tables as $table) {
                $report['scan_tables']++;
                $columns = array();

                // Count the number of rows we have in the table if large we'll split into blocks
                $row_count = mysqli_query($conn, "SELECT COUNT(*) FROM `".mysqli_real_escape_string($conn, $table)."`");
                if (!$row_count)  continue;
                $rows_result = mysqli_fetch_array($row_count);
                @mysqli_free_result($row_count);
                $row_count = $rows_result[0];
                if ($row_count == 0) {
                    DUPX_Log::info("{$table}^ ({$row_count})");
                    continue;
                }

                // search replace by table
                $searchList = array();
                $replaceList = array();
                $list = $srManager->getSearchReplaceList($table);
                foreach ($list as $item) {
                    $searchList[] = $item['search'];
                    $replaceList[] = $item['replace'];
                }

                // Get a list of columns in this table
                $sql = 'DESCRIBE ' . mysqli_real_escape_string($conn, $table);
                $fields = mysqli_query($conn, $sql);
                if (!$fields)  continue;
                while ($column = mysqli_fetch_array($fields)) {
                    $columns[$column['Field']] = $column['Key'] == 'PRI' ? true : false;
                }

                // $page_size = 25000;
                $page_size = 3500;
                $offset = ($page_size + 1);
                $pages = ceil($row_count / $page_size);

                // Grab the columns of the table.  Only grab text based columns because
                // they are the only data types that should allow any type of search/replace logic
                $colList = '*';
                $colMsg = '*';
                if (!$fullsearch) {
                    $colList = self::getTextColumns($conn, $table);
                    if ($colList != null && is_array($colList)) {
                        array_walk($colList, 'set_sql_column_safe');
                        $colList = implode(',', $colList);
                    }
                    $colMsg = (empty($colList)) ? '*' : '~';
                }

                if (empty($colList)) {
                    DUPX_Log::info("{$table}^ ({$row_count})");
                    continue;
                } else {
                    DUPX_Log::info("{$table}{$colMsg} ({$row_count})");
                }

                $columnsSRList = array();
                foreach ($columns as $column => $primary_key) {
                    if (($cScope = self::getSearchReplaceCustomScope($table, $column)) === false) {
                        // if don't have custom scope get normal search and reaplce table list
                        $columnsSRList[$column] = array(
                            'list' => &$list,
                            'sList' => &$searchList,
                            'rList' => &$replaceList,
                            'exactMatch' => false
                        );
                    } else {
                        // if column have custom scope overvrite default table search/replace list
                        $columnsSRList[$column] = array(
                            'list' => $srManager->getSearchReplaceList($cScope, true, false),
                            'sList' => array(),
                            'rList' => array(),
                            'exactMatch' => self::isExactMatch($table, $column)
                        );
                        foreach ($columnsSRList[$column]['list'] as $item) {
                            $columnsSRList[$column]['sList'][]  = $item['search'];
                            $columnsSRList[$column]['rList'][]  = $item['replace'];
                        }
                    }
                }
                //Paged Records
                for ($page = 0; $page < $pages; $page++) {
                    $current_row = 0;
                    $start = $page * $page_size;
                    $end = $start + $page_size;
                    $sql = sprintf("SELECT {$colList} FROM `%s` LIMIT %d, %d", $table, $start, $offset);
                    $data = mysqli_query($conn, $sql);

                    if (!$data) {
                        $errMsg = mysqli_error($conn);
                        $report['errsql'][] = $errMsg;
                        $nManager->addFinalReportNotice(array(
                            'shortMsg' => 'DATA-REPLACE ERRORS: MySQL',
                            'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                            'longMsg' => $errMsg,
                            'sections' => 'search_replace'
                        ));
                    }

                    $scan_count = ($row_count < $end) ? $row_count : $end;
                    DUPX_Log::info("\tScan => {$start} of {$scan_count}", 2);

                    //Loops every row
                    while ($row = mysqli_fetch_assoc($data)) {
                        $report['scan_rows']++;
                        $current_row++;
                        $upd_col = array();
                        $upd_sql = array();
                        $where_sql = array();
                        $upd = false;
                        $serial_err = 0;
                        $is_unkeyed = !in_array(true, $columns);

                        //Loops every cell
                        foreach ($columns as $column => $primary_key) {
                            $report['scan_cells']++;
                            if (!isset($row[$column]))  {
                                continue;
                            }

                            $safe_column = '`'.mysqli_real_escape_string($conn, $column).'`';
                            $edited_data = $data_to_fix = $row[$column];
                            $base64converted = false;
                            $txt_found = false;

                            //Unkeyed table code
                            //Added this here to add all columns to $where_sql
                            //The if statement with $txt_found would skip additional columns -TG
                            if ($is_unkeyed && !empty($data_to_fix)) {
                                $where_sql[] = $safe_column . ' = "' . mysqli_real_escape_string($conn, $data_to_fix) . '"';
                            }

                            //Only replacing string values
                            if (!empty($row[$column]) && !is_numeric($row[$column]) && $primary_key != 1) {
                                // get search and reaplace list for column
                                $tColList        = &$columnsSRList[$column]['list'];
                                $tColSearchList  = &$columnsSRList[$column]['sList'];
                                $tColreplaceList = &$columnsSRList[$column]['rList'];
                                $tColExactMatch  = $columnsSRList[$column]['exactMatch'];

                                // skip empty search col
                                if (empty($tColSearchList)) {
                                    continue;
                                }
                               
                                // Search strings in data
                                foreach ($tColList  as $item) {
                                    if (strpos($edited_data, $item['search']) !== false) {
                                        $txt_found = true;
                                        break;
                                    }
                                }

                                if (!$txt_found) {
                                    //if not found decetc Base 64
                                    if (($decoded = DUPX_U::is_base64($row[$column])) !== false) {
                                        $edited_data = $decoded;
                                        $base64converted = true;

                                        // Search strings in data decoded
                                        foreach ($tColList  as $item) {
                                            if (strpos($edited_data, $item['search']) !== false) {
                                                $txt_found = true;
                                                break;
                                            }
                                        }
                                    }

                                    //Skip table cell if match not found
                                    if (!$txt_found) {
                                        continue;
                                    }
                                }

                                if (self::is_serialized_string( $edited_data ) && strlen($edited_data) > MAX_STRLEN_SERIALIZED_CHECK) {
                                    // skip search and replace for too big serialized string
                                    $serial_err++;
                                } else  {
                                    //Replace logic - level 1: simple check on any string or serlized strings
                                    if ($tColExactMatch) {
                                        // if is exact match search and replace the itentical string
                                        if (($rIndex = array_search($edited_data,$tColSearchList)) !== false) {
                                            $edited_data = $tColreplaceList[$rIndex];
                                        }
                                    } else {
                                        // search if column contain search list
                                        $edited_data = self::searchAndReplaceItems($tColSearchList , $tColreplaceList , $edited_data);
                                    }

                                    //Replace logic - level 2: repair serialized strings that have become broken
                                    // check value without unserialize it
                                    if (self::is_serialized_string( $edited_data )) {
                                        $serial_check = self::fixSerialString($edited_data);
                                        if ($serial_check['fixed']) {
                                            $edited_data = $serial_check['data'];
                                        } elseif ($serial_check['tried'] && !$serial_check['fixed']) {
                                            $serial_err++;
                                        }
                                    }
                                }
                            }

                            //Change was made
                            if ($serial_err > 0 || $edited_data != $data_to_fix) {
                                $report['updt_cells']++;
                                //Base 64 encode
                                if ($base64converted) {
                                    $edited_data = base64_encode($edited_data);
                                }
                                $upd_col[] = $safe_column;
                                $upd_sql[] = $safe_column . ' = "' . mysqli_real_escape_string($conn, $edited_data) . '"';
                                $upd = true;
                            }

                            if ($primary_key) {
                                $where_sql[] = $safe_column . ' = "' . mysqli_real_escape_string($conn, $data_to_fix) . '"';
                            }
                        }

                        //PERFORM ROW UPDATE
                        if ($upd && !empty($where_sql)) {
                            $sql	= "UPDATE `{$table}` SET " . implode(', ', $upd_sql) . ' WHERE ' . implode(' AND ', array_filter($where_sql));
							$result	= mysqli_query($conn, $sql);
                            if ($result) {
                                if ($serial_err > 0) {
                                    $errMsg = "SELECT " . implode(', ',
                                            $upd_col) . " FROM `{$table}`  WHERE " . implode(' AND ',
                                            array_filter($where_sql)) . ';';
                                    $report['errser'][] = $errMsg;

                                    $nManager->addFinalReportNotice(array(
                                        'shortMsg' => 'DATA-REPLACE ERROR: Serialization',
                                        'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                                        'longMsg' => $errMsg,
                                        'sections' => 'search_replace'
                                    ));
                                }
                                $report['updt_rows']++;
                            } else {
                                $errMsg = mysqli_error($conn);
								$report['errsql'][]	= ($GLOBALS['LOGGING'] == 1)
									? 'DB ERROR: ' . $errMsg
									: 'DB ERROR: ' . $errMsg . "\nSQL: [{$sql}]\n";

                                $nManager->addFinalReportNotice(array(
                                    'shortMsg' => 'DATA-REPLACE ERRORS: MySQL',
                                    'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                                    'longMsg' => $errMsg,
                                    'sections' => 'search_replace'
                                ));
                            }

							//DEBUG ONLY:
                            DUPX_Log::info("\t{$sql}\n", 3);

                        } elseif ($upd) {
                            $errMsg =  sprintf("Row [%s] on Table [%s] requires a manual update.", $current_row, $table);
                            $report['errkey'][] = $errMsg;

                            $nManager->addFinalReportNotice(array(
                                    'shortMsg' => 'DATA-REPLACE ERROR: Key',
                                    'level' => DUPX_NOTICE_ITEM::SOFT_WARNING,
                                    'longMsg' => $errMsg,
                                    'sections' => 'search_replace'
                                ));
                        }
                    }
                    //DUPX_U::fcgiFlush();
                    @mysqli_free_result($data);
                }

                if ($upd) {
                    $report['updt_tables']++;
                }
            }
        }

        @mysqli_commit($conn);
        @mysqli_autocommit($conn, true);

        $nManager->saveNotices();
        
        $profile_end = DUPX_U::getMicrotime();
        $report['time'] = DUPX_U::elapsedTime($profile_end, $profile_start);
        $report['errsql_sum'] = empty($report['errsql']) ? 0 : count($report['errsql']);
        $report['errser_sum'] = empty($report['errser']) ? 0 : count($report['errser']);
        $report['errkey_sum'] = empty($report['errkey']) ? 0 : count($report['errkey']);
        $report['err_all'] = $report['errsql_sum'] + $report['errser_sum'] + $report['errkey_sum'];

        return $report;
    }

	/**
	 * searches and replaces strings without deserializing
	 * recursion for arrays
	 *
	 * @param array $search
	 * @param array $replace
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	public static function searchAndReplaceItems($search , $replace , $data) {

	    if (empty( $data ) || is_numeric($data) || is_bool($data) || is_callable($data) ) {

		/* do nothing */

	    } else if (is_string($data)) {

		//  Multiple replace string. If the string is serialized will fixed with fixSerialString
		$data = str_replace($search , $replace , $data);

	    } else if (is_array($data)) {

		$_tmp = array();
		foreach ($data as $key => $value) {

		    // prevent recursion overhead
		    if (empty( $value ) || is_numeric($value) || is_bool($value) || is_callable($value) || is_object($data)) {

		        $_tmp[$key] = $value;

		    } else {

		        $_tmp[$key] = self::searchAndReplaceItems($search, $replace , $value, false);

		    }

		}

		$data = $_tmp;
		unset($_tmp);

	    } elseif (is_object($data)) {
            // it can never be an object type
            DUPX_Log::info("OBJECT DATA IMPOSSIBLE\n" );
	    }

	    return $data;

	}

	/**
	 * FROM WORDPRESS
	 * Check value to find if it was serialized.
	 *
	 * If $data is not an string, then returned value will always be false.
	 * Serialized data is always a string.
	 *
	 * @since 2.0.5
	 *
	 * @param string $data   Value to check to see if was serialized.
	 * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
	 * @return bool False if not serialized and true if it was.
	 */
	public static function is_serialized_string( $data, $strict = true ) {
	    // if it isn't a string, it isn't serialized.
	    if ( ! is_string( $data ) ) {
	        return false;
	    }
	    $data = trim( $data );
	    if ( 'N;' == $data ) {
	        return true;
	    }
	    if ( strlen( $data ) < 4 ) {
	        return false;
	    }
	    if ( ':' !== $data[1] ) {
	        return false;
	    }
	    if ( $strict ) {
	        $lastc = substr( $data, -1 );
	        if ( ';' !== $lastc && '}' !== $lastc ) {
	            return false;
	        }
	    } else {
	        $semicolon = strpos( $data, ';' );
	        $brace     = strpos( $data, '}' );
	        // Either ; or } must exist.
	        if ( false === $semicolon && false === $brace )
	            return false;
	            // But neither must be in the first X characters.
	            if ( false !== $semicolon && $semicolon < 3 )
	                return false;
	                if ( false !== $brace && $brace < 4 )
	                    return false;
	    }
	    $token = $data[0];
	    switch ( $token ) {
	        case 's' :
	            if ( $strict ) {
	                if ( '"' !== substr( $data, -2, 1 ) ) {
	                    return false;
	                }
	            } elseif ( false === strpos( $data, '"' ) ) {
	                return false;
	            }
	            // or else fall through
	        case 'a' :
	        case 'O' :
	            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
	        case 'b' :
	        case 'i' :
	        case 'd' :
	            $end = $strict ? '$' : '';
	            return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	    }
	    return false;
	}

    /**
     * Test if a string in properly serialized
     *
     * @param string $data Any string type
     *
     * @return bool Is the string a serialized string
     */
    public static function unserializeTest($data)
    {
        if (!is_string($data)) {
            return false;
        } else if ($data === 'b:0;') {
            return true;
        } else {        
            try {
                DUPX_Handler::$should_log = false;
                $unserialize_ret = @unserialize($data);
                DUPX_Handler::$should_log = true;
                return ($unserialize_ret !== false);
             } catch (Exception $e) {
                DUPX_Log::info("Unserialize exception: ".$e->getMessage());
                //DEBUG ONLY:
                DUPX_Log::info("Serialized data\n".$data, 3);
                return false;
            }
        }
    }

    /**
     * custom columns list
     * if the table / column pair exists in this array then the search scope will be overwritten with that contained in the array
     *
     * @var array
     */
    private static $customScopes = array(
        'blogs' => array(
            'domain' => array(
                'scope' => 'domain_host',
                'exact' => true
            ),
            'path' => array(
                'scope' => 'domain_path',
                'exact' => false
            )
        ),
        'signups' => array(
            'domain' => array(
                'scope' => 'domain_host',
                'exact' => true
            ),
            'path' => array(
                'scope' => 'domain_path',
                'exact' => false
            )
        ),
        'site' => array(
            'domain' => array(
                'scope' => 'domain_host',
                'exact' => true
            ),
            'path' => array(
                'scope' => 'domain_path',
                'exact' => false
            )
        )
    );

    /**
     *
     * @param string $table
     * @param string $column
     * @return boolean|string  false if custom scope not found or return custom scoper for table/column
     */
    private static function getSearchReplaceCustomScope($table, $column)
    {
        if (strpos($table, $GLOBALS['DUPX_AC']->wp_tableprefix) !== 0) {
            return false;
        }

        $table_key = substr($table, strlen($GLOBALS['DUPX_AC']->wp_tableprefix));

        if (!array_key_exists($table_key, self::$customScopes)) {
            return false;
        }

        if (!array_key_exists($column, self::$customScopes[$table_key])) {
            return false;
        }

        return self::$customScopes[$table_key][$column]['scope'];
    }

    /**
     *
     * @param string $table
     * @param string $column
     * @return boolean if true search a exact match in column if false search as LIKE
     */
    private static function isExactMatch($table, $column) {
        if (strpos($table, $GLOBALS['DUPX_AC']->wp_tableprefix) !== 0) {
            return false;
        }

        $table_key = substr($table, strlen($GLOBALS['DUPX_AC']->wp_tableprefix));

        if (!array_key_exists($table_key, self::$customScopes)) {
            return false;
        }

        if (!array_key_exists($column, self::$customScopes[$table_key])) {
            return false;
        }

        return self::$customScopes[$table_key][$column]['exact'];
    }

    /**
     *  Fixes the string length of a string object that has been serialized but the length is broken
     *
     * @param string $data The string object to recalculate the size on.
     *
     * @return string  A serialized string that fixes and string length types
     */
	public static function fixSerialString($data)
	{
		$result = array('data' => $data, 'fixed' => false, 'tried' => false);

        // check if serialized string must be fixed
		if (!self::unserializeTest($data)) {

		    $serialized_fixed = self::recursiveFixSerialString($data);

			if (self::unserializeTest($serialized_fixed)) {

			    $result['data'] = $serialized_fixed;

			    $result['fixed'] = true;
			}

			$result['tried'] = true;
		}

		return $result;
	}

	/**
	 *  Fixes the string length of a string object that has been serialized but the length is broken
	 *  Work on nested serialized string recursively.
	 *
	 *  @param string $data	The string ojbect to recalculate the size on.
	 *
	 *  @return string  A serialized string that fixes and string length types
	 */

	public static function recursiveFixSerialString($data ) {

	    if (!self::is_serialized_string($data)) {
	        return $data;
	    }

	    $result = '';

	    $matches = null;

	    $openLevel = 0;
	    $openContent = '';
	    $openContentL2 = '';

            // parse every char
	    for ($i = 0 ; $i < strlen($data) ; $i++) {

	        $cChar = $data[$i];

	        $addChar = true;

	        if ($cChar  == 's') {

	            // test if is a open string
	            if (preg_match ( '/^(s:\d+:")/', substr($data , $i)  , $matches )) {

	                $addChar = false;

	                $openLevel ++;

	                $i += strlen($matches[0]) - 1;

	            }

	        } else if ($openLevel > 0 && $cChar  == '"') {

                    // test if is a close string
	            if (preg_match ( '/^";(?:}|a:|s:|S:|b:|d:|i:|o:|O:|C:|r:|R:|N;)/', substr($data , $i) ) ) {

	                $addChar = false;

	                switch ($openLevel) {
		            case 1:
		                // level 1
				// flush string content
		                $result .= 's:'.strlen($openContent).':"'.$openContent.'";';

		                $openContent = '';

		                break;
		            case 2;
		               // level 2
			       // fix serial string level2
			       $sublevelstr = self::recursiveFixSerialString($openContentL2);

			       // flush content on level 1
		               $openContent .= 's:'.strlen($sublevelstr).':"'.$sublevelstr.'";';

		               $openContentL2 = '';

		               break;
		            default:
                              // level > 2
			      // keep writing at level 2; it will be corrected with recursion
                              break;

	                }

	                $openLevel --;

	                $closeString = '";';

	                $i += strlen($closeString) -1;

	            }

	        }


	        if ($addChar) {

	            switch ($openLevel) {
		        case 0:
		            // level 0
			    // add char on result
		            $result .= $cChar;

		            break;
		        case 1:
		            // level 1
			    // add char on content level1
		            $openContent .= $cChar;

		            break;
		        default:
		            // level > 1
			    // add char on content level2
		            $openContentL2 .= $cChar;

		            break;
	            }

	        }

	    }

	    return $result;

	}

}
