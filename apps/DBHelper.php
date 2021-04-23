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
        $bind_values = [];
        $bind_index  = 0;
        foreach ( $data as $_col => $_val ) {
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
            $bind_key = $use_named_parameters ? ':' . $_col : $bind_index;
            $bind_values[$bind_key] = [ $value, $data_type ];
        }
        // Execute data insertion
        try {
            $sth = $this->dbh->prepare( $base_sql );
            foreach ( $bind_values as $_key => $_value ) {
                $sth->bindValue( $_key, $_value[0], $_value[1] );
            }
            return $sth->execute();
        } catch ( \PDOException $e ) {
            throw $e;
            return false;
        }
    }
}