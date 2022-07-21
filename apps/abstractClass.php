<?php
/**
 * Holds abstract class for MHRise SkillSimulator
 *
 * @package MHRSS
 * @since   1.0.0
 */
namespace MHRise\SkillSimulator;

if ( !class_exists( 'abstractClass' ) ) :

abstract class abstractClass {
    const VERSION   = '1.0.1';
    const DEBUG_LOG = true;

    /**
     * Holds application's directory path
     *
     * @access protected
     * @var    string
     */
    protected $app_dir;

    /**
     * Holds full path of configures file
     *
     * @access private
     * @var    string
     */
    private $conf_path;

    /**
     * Holds singleton objects (= instance)
     *
     * @access private
     * @var    array
     */
    private static $objects = [];

    /**
     * Holds option key
     *
     * @access protected
     * @var    string
     */
    //protected $option_key;

    /**
     * Holds options array
     *
     * @access protected
     * @var    array
     */
    protected $options = [];

    
    /**
     * Holds database handler object
     *
     * @access public
     * @var    object
     */
    public $dbh;

    /**
     * Constructor
     *
     * @access protected
     */
    protected function __construct(
        // private string $env       = 'production',
        private string $env       = 'development',
        private string $conf_file = 'database.json',
    ) {
        $this->app_dir   = str_replace( DIRECTORY_SEPARATOR, '/', dirname( __DIR__ ) );
        $this->conf_path = "{$this->app_dir}/.config/{$this->conf_file}";

        $this->load_config();
        $this->env_filter();
        $this->connect_database();
        $this->init();
    }

    /**
     * Returns singleton instance as object
     *
     * @access public
     */
    public static function get_object( ?string $class = null ): object {
        if ( !class_exists( $class ) ) {
            $class = get_called_class();
        }
        if ( !isset( self::$objects[$class] ) ) {
            self::$objects[$class] = new $class;
        }
        return self::$objects[$class];
    }

    /**
     * Loads configures for application from setting file
     *
     * @access private
     */
    private function load_config(): void {
        if ( file_exists( $this->conf_path ) ) {
            $_conf   = json_decode( @file_get_contents( $this->conf_path ), true );
            $_loaded = match ( json_last_error() ) {
                JSON_ERROR_NONE => true,
                JSON_ERROR_DEPTH => 'Config file is maximum stack depth exceeded.',
                JSON_ERROR_STATE_MISMATCH => 'Config file is underflow or the modes mismatch.',
                JSON_ERROR_CTRL_CHAR => 'Config file is unexpected control character found.',
                JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON of config file.',
                JSON_ERROR_UTF8 => 'Config file is malformed UTF-8 characters, possibly incorrectly encoded.',
                default => 'Unknown error for loading config file.'
            };
            if ( is_bool( $_loaded ) && $_loaded ) {
                $this->options = $_conf;
            } else {
                die( $_loaded . PHP_EOL );
            }
        } else {
            die( 'Config file could not be loaded.' );
        }
    }

    /**
     * Called method when object is constructed
     *
     * @access protected
     */
    abstract protected function init();

    /**
     * Connect database
     *
     * @access private
     */
    private function connect_database(): void {
        $_opts = $this->get_option( $this->env );
        $_dsn  = sprintf( '%s:host=%s;port=%d;dbname=%s;charset=%s', $_opts['db_driver'], $_opts['db_host'], $_opts['db_port'], $_opts['db_name'], $_opts['db_charset'] );
        try {
            $this->dbh = new \PDO(
                $_dsn,
                $_opts['db_user'],
                $_opts['db_password'],
                [
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
            if ( !empty( $_opts['db_collate'] ) ) {
                // Check database collation
                $stm = $this->dbh->query( 'SELECT @@collation_database' );
                $current_collate = $stm->fetchColumn();
                if ( $_opts['db_collate'] !== $current_collate ) {
                    die( "Database Connection Error: Specified database collation is different from defining." . PHP_EOL );
                }
            }
            // After connecting completely, modify the db_user and db_password in the option property.
            unset( $this->options[$this->env]['db_user'], $this->options[$this->env]['db_password'] );
        } catch ( \PDOException $e ) {
            die( "Database Connection Error: {$e->getMessage()}" . PHP_EOL );
        }
    }

    /**
     * Retrives an option
     *
     * @access protected
     */
    protected function get_option( array|string $option, mixed $default = false ): mixed {
        if ( !is_array( $option ) ) {
            $option = [ $option ];
        }
        return self::_get_option( $option, $default, $this->options );
    }

    /**
     * Recursively retrieves a multidimensional option
     *
     * @access private
     */
    private function _get_option( array $option, mixed $default, &$options ): mixed {
        $_key = array_shift( $option );
        if ( !isset( $options[$_key] ) ) {
            return $default;
        }
        if ( !empty( $option ) ) {
            return self::_get_option( $option, $default, $options[$_key] );
        }
        return $options[$_key];
    }

    /**
     * Sets an option
     *
     * @access protected
     */
    protected function set_option( array|string $option, mixed $value ): void {
        if ( !is_array( $option ) ) {
            $option = [ $option ];
        }
        self::_set_option( $option, $value, $this->options );
    }

    /**
     * Recursively sets a multidimensional option
     *
     * @access private
     */
    private function _set_option( array $option, mixed $value, &$options ) {
        $_key = array_shift( $option );
        if ( !empty( $option ) ) {
            if ( !isset( $options[$_key] ) ) {
                $options[$_key] = [];
            }
            return self::_set_option( $option, $value, $options[$_key] );
        }
        $options[$_key] = $value;
    }

    /**
     * Catch Requests
     *
     * @access protected
     */
    abstract protected function catch_request();

    /**
     * Filters environment
     *
     * @access protected
     */
    protected function env_filter() {
        $ref_path  = isset( $_SERVER['HTTP_REFERER'] ) ? trim( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_PATH ), DIRECTORY_SEPARATOR ) : DIRECTORY_SEPARATOR;
        $path_elms = explode( DIRECTORY_SEPARATOR, $ref_path );
        if ( isset( $path_elms[1] ) && version_compare( trim( $path_elms[1], 'v' ), '1.0.0' ) < 0 ) {
            $this->env = 'development';
            $res = 'アプリの稼働環境がバージョン 1.0.0 未満（'. $path_elms[1] .'）のため「'. $this->env .'」DBに接続します。';
        } else {
            $this->env = 'production';
            $res = 'アプリの稼働環境がバージョン 1.0.0 以上のため「'. $this->env .'」DBに接続します。';
        }
        self::logger( $res );
    }

    /**
     * Logger
     *
     * @access protected
     */
    protected function logger( mixed $content, ?string $log_dest = null ): void {
        $log_content = match( gettype( $content ) ) {
            'array'   => '(Array) ' . json_encode( $content ),
            'object'  => '(Object) ' . json_encode( $content ),
            'boolean' => '(bool) ' . $content ? 'true': 'false',
            'integer' => '(int) ' . (int)$content,
            'double'  => '(float) ' . (float)$content,
            'NULL'    => '(null)',
            default   => '(string) ' . (string)$content,
        };
        $nowDateTime = new \DateTime( 'NOW', new \DateTimeZone( $this->get_option( 'timezone' ) ) );
        $now_date = '['. $nowDateTime->format( 'Y-m-d H:i:s' ) .'] ';
        if ( ! empty( $log_dest ) && is_writable( $log_dest ) ) {
            $dest = $log_dest;
        } else {
            $dest = dirname( __FILE__, 2 ) . '/debug.log';
        }
        if ( self::DEBUG_LOG ) {
            error_log( $now_date . $log_content . PHP_EOL, 3, $dest );
        }
    }

}

endif;