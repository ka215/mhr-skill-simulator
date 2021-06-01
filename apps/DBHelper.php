<?php
/**
 * DBHelper is trait for handling database helper methods
 *
 * @package MHRSS
 * @since   1.0.0
 */
namespace MHRise\SkillSimulator;

trait DBHelper {
    /**
     * Holds last insertion primary key
     *
     * @access protected
     * @var    int
     */
    protected $last_insert_id;

    /**
     * Check if the table exists in the database
     *
     * @access private
     */
    private function table_exists( string $table_name ): bool {
        try {
            $sth = $this->dbh->query( "SHOW TABLES" );
            $tables = $sth->fetchAll( \PDO::FETCH_COLUMN );
            return in_array( $table_name, $tables, true );
        } catch ( \PDOException $e ) {
            throw $e;
            return false;
        }
    }

    /**
     * Truncate the data in the table
     *
     * @access protected
     */
    protected function truncate_table( string $table_name ): bool {
        try {
            $sth = $this->dbh->query( "TRUNCATE TABLE $table_name" );
            return true;
        } catch ( \PDOException $e ) {
            throw $e;
            return false;
        }
        
    }

    /**
     * Inserting data into a table
     *
     * @access protected
     *
     * @param  string $table_name           Target table name (required)
     * @param  array  $data                 Data array with column names as keys for insertion (required) e.g. `[ 'name' => 'something',... ]`
     * @param  bool   $use_named_parameters Whether to use named bindings for PDO prepared statements (optional; defaults to `true`)
     * @return bool
     */
    protected function insert_data( string $table_name, array $data, bool $use_named_parameters = true ): bool {
        $columns = array_keys( $data );
        if ( $use_named_parameters ) {
            $parameters = ':' . implode( ', :', $columns );
        } else {
            $parameters = rtrim( str_repeat( '?, ', count( $columns ) ), " ," );
        }
        $base_sql = sprintf(
            "INSERT INTO $table_name (`%s`) VALUES (%s)",
            implode( '`, `', $columns ),
            $parameters
        );
        // Ready for binding values
        $bind_values = $this->get_binding_values( $data, $use_named_parameters );
        //$this->logger( $base_sql );
        //$this->logger( $bind_values );
        // Execute data insertion
        try {
            $sth = $this->dbh->prepare( $base_sql );
            if ( ! empty( $bind_values ) ) {
                foreach ( $bind_values as $_key => $_value ) {
                    $sth->bindValue( $_key, $_value[0], $_value[1] );
                }
            }
            $result = $sth->execute();
            if ( $result ) {
                $this->last_insert_id = $this->dbh->lastInsertId();
            }
            return $result;
        } catch ( \PDOException $e ) {
            throw $e;
            return false;
        }
    }

    /**
     * Retrieving data from specified table
     *
     * @access protected
     *
     * @param  string $table_name           Target table name (required)
     * @param  array  $conditions           Conditions for narrowing down data (required) e.g. `[ [ 'id', '>=', 10 ], [ 'valid', '=', 1 ],... ]`
     * @param  string $operator             Operator of multiple conditions (optional; defaults to `and`)
     * @param  bool   $use_named_parameters Whether to use named bindings for PDO prepared statements (optional; defaults to `true`)
     * @return array
     */
    protected function retrieve_data( string $table_name, array $conditions, string $operator = 'and', bool $use_named_parameters = true ): array {
        $operator = preg_match( '/^(and|or)$/i', $operator ) ? strtoupper( $operator ) : 'AND';
        $where = [];
        $parameters = [];
        if ( ! empty( $conditions ) ) {
            foreach ( $conditions as $_cond ) {
                if ( $use_named_parameters ) {
                    if ( preg_match( '/^in$/i', $_cond[1] ) && is_array( $_cond[2] ) ) {
                        $where[":{$_cond[0]}"] = "find_in_set(cast(`{$_cond[0]}` as char), :{$_cond[0]})";
                        $parameters[":{$_cond[0]}"] = implode(',', $_cond[2]);
                    } else {
                        $where[":{$_cond[0]}"] = "`{$_cond[0]}` {$_cond[1]} :{$_cond[0]}";
                        $parameters[":{$_cond[0]}"] = $_cond[2];
                    }
                } else {
                    if ( preg_match( '/^in$/i', $_cond[1] ) && is_array( $_cond[2] ) ) {
                        $where_clauses[$_cond[0]] = "find_in_set(cast(`{$_cond[0]}` as char), ?)";
                        $parameters[$_cond[0]] = implode(',', $_cond[2]);
                    } else {
                        $where[$_cond[0]] = "`{$_cond[0]}` {$_cond[1]} ?";
                        $parameters[$_cond[0]] = $_cond[2];
                    }
                }
            }
        }
        $base_sql = "SELECT * FROM $table_name";
        if ( ! empty( $where ) ) {
            $base_sql .= ' WHERE %s';
            $base_sql = sprintf( $base_sql, implode( " $operator ", $where ) );
        }
        // Ready for binding values
        $bind_values = $this->get_binding_values( $parameters, $use_named_parameters );
        // Execute data insertion
        try {
            $sth = $this->dbh->prepare( $base_sql );
            if ( ! empty( $bind_values ) ) {
                foreach ( $bind_values as $_key => $_value ) {
                    $sth->bindValue( $_key, $_value[0], $_value[1] );
                }
            }
            $sth->execute();
            return $sth->fetchAll( \PDO::FETCH_ASSOC );
        } catch ( \PDOException $e ) {
            throw $e;
            return [];
        }
    }

    /**
     * Updating data in specified table
     *
     * @access protected
     *
     * @param  string $table_name           Target table name (required)
     * @param  array  $data                 Data array with column names as keys for updating (required) e.g. `[ 'name' => 'something',... ]`
     * @param  array  $conditions           Conditions for narrowing down data (required) e.g. `[ [ 'id', '>=', 10 ], [ 'valid', '=', 1 ],... ]`
     * @param  string $operator             Operator of multiple conditions (optional; defaults to `and`)
     * @param  bool   $use_named_parameters Whether to use named bindings for PDO prepared statements (optional; defaults to `true`)
     * @return bool
     */
    protected function update_data( string $table_name, array $data, array $conditions, string $operator = 'and', bool $use_named_parameters = true ): bool {
        $operator = preg_match( '/^(and|or)$/i', $operator ) ? strtoupper( $operator ) : 'AND';
        $set_clauses = [];
        $parameters = [];
        foreach ( $data as $column => $value ) {
            if ( $use_named_parameters ) {
                $set_clauses[":$column"] = "`$column` = :$column";
                $parameters[":$column"] = $value;
            } else {
                $set_clauses[$column] = "`$column` = ?";
                $parameters[$column] = $value;
            }
        }
        $where_clauses = [];
        if ( ! empty( $conditions ) ) {
            foreach ( $conditions as $_cond ) {
                if ( $use_named_parameters ) {
                    if ( preg_match( '/^in$/i', $_cond[1] ) && is_array( $_cond[2] ) ) {
                        $where_clauses[":cond_{$_cond[0]}"] = "find_in_set(cast(`{$_cond[0]}` as char), :cond_{$_cond[0]})";
                        $parameters[":cond_{$_cond[0]}"] = implode(',', $_cond[2]);
                    } else {
                        $where_clauses[":cond_{$_cond[0]}"] = "`{$_cond[0]}` {$_cond[1]} :cond_{$_cond[0]}";
                        $parameters[":cond_{$_cond[0]}"] = $_cond[2];
                    }
                } else {
                    if ( preg_match( '/^in$/i', $_cond[1] ) && is_array( $_cond[2] ) ) {
                        $where_clauses["cond_{$_cond[0]}"] = "find_in_set(cast(`{$_cond[0]}` as char), ?)";
                        $parameters["cond_{$_cond[0]}"] = implode(',', $_cond[2]);
                    } else {
                        $where_clauses["cond_{$_cond[0]}"] = "`{$_cond[0]}` {$_cond[1]} ?";
                        $parameters["cond_{$_cond[0]}"] = $_cond[2];
                    }
                }
            }
        }
        $base_sql = "UPDATE $table_name SET %s";
        $base_sql = sprintf( $base_sql, implode( ', ', $set_clauses ) );
        if ( ! empty( $where_clauses ) ) {
            $base_sql .= " WHERE %s";
            $base_sql = sprintf( $base_sql, implode( " $operator ", $where_clauses ) );
        }
        // Ready for binding values
        $bind_values = $this->get_binding_values( $parameters, $use_named_parameters );
        $this->logger( $base_sql );
        $this->logger( $bind_values );
        // Execute data insertion
        try {
            $sth = $this->dbh->prepare( $base_sql );
            if ( ! empty( $bind_values ) ) {
                foreach ( $bind_values as $_key => $_value ) {
                    $sth->bindValue( $_key, $_value[0], $_value[1] );
                }
            }
            $result = $sth->execute();
            return $result && $sth->rowCount() != 0;
        } catch ( \PDOException $e ) {
            throw $e;
            return false;
        }
    }

    /**
     * Deleting data of specified table
     *
     * @access protected
     *
     * @param  string $table_name           Target table name (required)
     * @param  array  $conditions           Conditions for narrowing down data (required) e.g. `[ [ 'id', '>=', 10 ], [ 'valid', '=', 1 ],... ]`
     * @param  string $operator             Operator of multiple conditions (optional; defaults to `and`)
     * @param  bool   $use_named_parameters Whether to use named bindings for PDO prepared statements (optional; defaults to `true`)
     * @return bool
     */
    protected function delete_data( string $table_name, array $conditions, string $operator = 'and', bool $use_named_parameters = true ): bool {
        $operator = preg_match( '/^(and|or)$/i', $operator ) ? strtoupper( $operator ) : 'AND';
        $parameters = [];
        $where_clauses = [];
        if ( ! empty( $conditions ) ) {
            foreach ( $conditions as $_cond ) {
                if ( $use_named_parameters ) {
                    if ( preg_match( '/^in$/i', $_cond[1] ) && is_array( $_cond[2] ) ) {
                        $where_clauses[":cond_{$_cond[0]}"] = "find_in_set(cast(`{$_cond[0]}` as char), :cond_{$_cond[0]})";
                        $parameters[":cond_{$_cond[0]}"] = implode(',', $_cond[2]);
                    } else {
                        $where_clauses[":cond_{$_cond[0]}"] = "`{$_cond[0]}` {$_cond[1]} :cond_{$_cond[0]}";
                        $parameters[":cond_{$_cond[0]}"] = $_cond[2];
                    }
                } else {
                    if ( preg_match( '/^in$/i', $_cond[1] ) && is_array( $_cond[2] ) ) {
                        $where_clauses["cond_{$_cond[0]}"] = "find_in_set(cast(`{$_cond[0]}` as char), ?)";
                        $parameters["cond_{$_cond[0]}"] = implode(',', $_cond[2]);
                    } else {
                        $where_clauses["cond_{$_cond[0]}"] = "`{$_cond[0]}` {$_cond[1]} ?";
                        $parameters["cond_{$_cond[0]}"] = $_cond[2];
                    }
                }
            }
        }
        $base_sql = "DELETE FROM $table_name";
        if ( ! empty( $where_clauses ) ) {
            $base_sql .= " WHERE %s";
            $base_sql = sprintf( $base_sql, implode( " $operator ", $where_clauses ) );
        }
        // Ready for binding values
        $bind_values = $this->get_binding_values( $parameters, $use_named_parameters );
        // Execute data insertion
        try {
            $sth = $this->dbh->prepare( $base_sql );
            if ( ! empty( $bind_values ) ) {
                foreach ( $bind_values as $_key => $_value ) {
                    $sth->bindValue( $_key, $_value[0], $_value[1] );
                }
            }
            $result = $sth->execute();
            return $result;
        } catch ( \PDOException $e ) {
            throw $e;
            return false;
        }
    }

    /**
     * Get an optimized value that can be bound to a prepared statement
     *
     * @access private
     *
     * @param  array  $data                 Data array for binding (required)
     * @param  bool   $use_named_parameters Whether to use named bindings for PDO prepared statements (optional; defaults to `true`)
     * @return array
     */
    private function get_binding_values( array $data, bool $use_named_parameters = true ): array {
        if ( empty( $data ) ) {
            return [];
        }
        $bind_values = [];
        $bind_index  = 0;
        foreach ( $data as $_key => $_val ) {
            switch ( gettype( $_val ) ) {
                case 'integer':
                    $value = intval( $_val );
                    $data_type = \PDO::PARAM_INT;
                    break;
                case 'array':
                case 'object':
                    $value = json_encode( $_val );
                    $data_type = \PDO::PARAM_STR;
                    break;
                case 'boolean':
                    $value = boolval( $_val );
                    $data_type = \PDO::PARAM_BOOL;
                    //$value = boolval( $_val ) ? 0b01 : 0b00;
                    //$data_type = \PDO::PARAM_INT;
                    break;
                case 'NULL':
                    $value = null;
                    $data_type = \PDO::PARAM_NULL;
                    break;
                case 'double':
                case 'string':
                default:
                    $value = strval( $_val );
                    $data_type = \PDO::PARAM_STR;
                    break;
            }
            $bind_index++;
            $bind_key = $use_named_parameters ? $_key : $bind_index;
            $bind_values[$bind_key] = [ $value, $data_type ];
        }
        return $bind_values;
    }

    /**
     * Wrapper for determining upsert of data
     *
     * @access protected
     *
     * @param  array  $data                 Data array for binding (required)
     * @param  bool   $use_named_parameters Whether to use named bindings for PDO prepared statements (optional; defaults to `true`)
     * @return array
     */

}