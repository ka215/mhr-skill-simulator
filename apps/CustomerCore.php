<?php
/**
 * Holds final class for customer
 *
 * @package MHRSS
 * @since   1.0.0
 */

namespace MHRise\SkillSimulator;

if ( !class_exists( 'CustomerCore' ) ) :

final class CustomerCore extends abstractClass {
    /**
     * Holds the request method from the frontend
     *
     * @access private
     * @var    string
     */
    private $request_method;

    /**
     * Holds the retrieved requests as key-value store
     *
     * @access protected
     * @var    array
     */
    protected $the_request;

    /**
     * Load a trait that defines common methods for database operations
     */
    use DBHelper;

    /**
     * Initialization
     *
     * @access public
     */
    public function init() {
        $this->catch_request();
        $this->handle_request();
    }

    /**
     * Catch Requests
     *
     * @access protected
     */
    protected function catch_request(): void {
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $options = [
            // tbl=:table_name
            'tbl' => FILTER_SANITIZE_STRING,
            // id=:id
            'id'  => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => [ 'min_range' => 1, ],
            ],
            // filters[key]=value
            'filters' => [
                'filter'  => FILTER_SANITIZE_STRING,
                'flags'   => FILTER_REQUIRE_ARRAY,
            ],
        ];
        $this->the_request = match( $this->request_method ) {
            'GET'    => filter_input_array( INPUT_GET,    $options ),
            'POST'   => filter_input_array( INPUT_POST,   $options ),
            'COOKIE' => filter_input_array( INPUT_COOKIE, $options ),
        };
        if ( ! $this->the_request ) {
            $this->the_request = json_decode( file_get_contents( 'php://input' ), true );
        }
        if ( ! $this->the_request ) {
            die( 'Request not found.' );
        }
    }

    /**
     * Requests handler
     *
     * @access protected
     */
    protected function handle_request() {
        $use_named_parameters = true;
        $response = null;
        switch ( $this->request_method ) {
            case 'POST':
                break;
            case 'GET':
                if ( array_key_exists( 'tbl', $this->the_request ) ) {
                    if ( ! $this->table_exists( $this->the_request['tbl'] ) ) {
                        die( 'That table does not exist.' );
                    }
                } else {
                    die( 'Table not specified.' );
                }
                $conditions = [];
                if ( array_key_exists( 'id', $this->the_request ) && $this->the_request['id'] ) {
                    $conditions[] = [ 'id', '=', $this->the_request['id'] ];
                } elseif ( array_key_exists( 'filters', $this->the_request ) && $this->the_request['filters'] ) {
                    foreach ( $this->the_request['filters'] as $_key => $_value ) {
                        $conditions[] = [ $_key, '=', $_value ];
                    }
                } else {
                    //
                }
                $response = $this->retrieve_data( $this->the_request['tbl'], $conditions, $use_named_parameters );
                $response = $this->check_to_cast( $response );
                break;
            case 'COOKIE':
            default:
                break;
        }
        if ( $response ) {
            $this->return_response( $response );
        } else {
            $this->return_response( [] );
        }
    }

    /**
     * Returen the response
     *
     * @access protected
     */
    protected function return_response( array $data ): void {
        if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {
            $allow_origin_regex = '@^https?://(localhost:8080|127\.0\.0\.1|(.*)?ka2.org)@';
            $origin = preg_match( $allow_origin_regex, $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : '*';
            $allow_methods = 'GET, POST, OPTIONS';
            $allow_credentials = 'true';
        } else {
            $origin = '*';
            $allow_methods = 'GET';
            $allow_credentials = 'false';
        }
        header( 'Access-Control-Allow-Origin: '. $origin );
        header( 'Access-Control-Allow-Methods: '. $allow_methods );
        header( 'Access-Control-Allow-Credentials: '. $allow_credentials );
        header( 'Access-Control-Allow-Headers: Origin, Content-Type, Accept' );
        header( 'Access-Control-Expose-Headers: X-Custom-header', false );
        header( 'Content-Type: application/json; charset=utf-8' );
        
        die( json_encode( $data ) );
    }

    /**
     * Check the value of the response and cast it to the most appropriate type.
     *
     * @access protected
     */
    protected function check_to_cast( mixed $data ): mixed {
        if ( is_array( $data ) ) {
            foreach ( $data as $i => $row ) {
                if ( is_array( $row ) ) {
                    foreach ( $row as $_key => $_val ) {
                        $data[$i][$_key] = $this->_cast_value( $_val );
                    }
                } elseif ( is_object( $row ) ) {
                    foreach ( $row as $_prop => $_val ) {
                        $data[$i]->$_prop = $this->_cast_value( $_val );
                    }
                } else {
                    $data[$i] = $this->_cast_value( $row );
                }
            }
        } else {
            $data = $this->_cast_value( $_val );
        }
        return $data;
    }

    /**
     * Cast the given value
     *
     * @access protected
     */
    protected function _cast_value( mixed $value ): mixed {
        switch ( true ) {
            case is_bool( $value ):
                return (bool)$value;
            case is_numeric( $value ):
                if ( is_float( $value ) ) {
                    return (float)$value;
                } else {
                    return (int)$value;
                }
            case is_null( $value ):
                return '';
            case is_string( $value ):
                if ( $this->isJson( $value ) ) {
                    return json_decode( $value );
                } else {
                    return (string)$value;
                }
            default:
                return $value;
        }
    }

    /**
     * Checks if the string is in JSON format
     *
     * @access public
     */
    public static function isJson( string $string ): bool {
        $check = json_decode( $string );
        return (json_last_error() == JSON_ERROR_NONE && (is_array( $check ) || is_object( $check )) );
    }

}

endif;