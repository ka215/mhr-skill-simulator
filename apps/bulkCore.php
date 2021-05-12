<?php
/**
 * Holds final class for bulk user
 *
 * @package MHRSS
 * @since   1.0.0
 */

namespace MHRise\SkillSimulator;

if ( !class_exists( 'bulkCore' ) ) :

final class bulkCore extends abstractClass {

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
        // Nothing to do
    }

    /**
     * Importing data from a CSV file and registering it in a database
     *
     * @access public
     */
    public function import_csv( string $table_name, string $csv_file ): void {
        if ( !file_exists( $csv_file ) ) {
            die( 'Could not find the CSV file to import.' . PHP_EOL );
        }
        if ( !$this->table_exists( $table_name ) ) {
            die( 'The table specified as the import destination does not exist.' . PHP_EOL );
        }
        $csv_data = new \SplFileObject( $csv_file, 'r' );
        $csv_data->setFlags( \SplFileObject::READ_CSV );
        $base_csv_format = $this->get_csv_format( $table_name );
        $csv_format = array_values( $base_csv_format );
        $columns = array_keys( $base_csv_format );
        $data = [];
        foreach ( $csv_data as $line ) {
            $line = array_filter( $line, function( $item, $index ) use ( &$csv_format ) {
                if ( $index <= count( $csv_format ) ) {
                    return $csv_format[$index]['label'] !== $item && (bool)preg_match( "@{$csv_format[$index]['pattern']}@", $item );
                } else {
                    return false;
                }
            }, ARRAY_FILTER_USE_BOTH );
            if ( empty( $line ) ) {
                continue;
            }
            foreach ( $line as $_idx => $_val ) {
                $_type = $csv_format[$_idx]['type'];
                switch ( true ) {
                    case preg_match( '@^(|tiny|medium|big)int@', $_type ):
                        $line[$_idx] = (int)$_val;
                        break;
                    case $_type === 'json':
                        $line[$_idx] = json_decode( str_replace( "'", '"', $_val ), true );
                        break;
                    case $_type === 'bit(1)':
                        $line[$_idx] = (bool)preg_match( '/^(true|1)$/i', $_val);
                        break;
                    case preg_match( '@^(varchar|text)@', $_type ):
                    default:
                        $line[$_idx] = (string)$_val;
                        break;
                }
            }
            $data[] = $line;
        }
        if ( empty( $data ) ) {
            die( 'Oops, this CSV does not contain any valid data to import.' . PHP_EOL );
        }
        // Truncate table before insertion
        $this->truncate_table( $table_name );
        // Start insertion with transaction
        $counter = 0;
        try {
            $this->dbh->beginTransaction();
            foreach ( $data as $_record ) {
                $target_cols = array_filter( $columns, function( $_k ) use ( &$_record ) {
                    return array_key_exists( $_k, $_record );
                }, ARRAY_FILTER_USE_KEY );
                $one_row_data = array_combine( $target_cols, $_record );
                if ( $this->insert_data( $table_name, $one_row_data ) ) {
                    $counter++;
                }
            }
            $this->dbh->commit();
        } catch ( \PDOException $e ) {
            $this->dbh->rollBack();
            die( 'Error: ' . $e->getMessage() );
        }
        if ( $counter > 0 ) {
            $message = sprintf( '%d/%d data has been completed insertion into the "%s" table.', $counter, count( $data ), $table_name );
        } else {
            $message = 'Failed to insert data.';
        }
        die( $message . PHP_EOL );
    }

    /**
     * Obtain the data validation schema for the table to be handled
     *
     * @access private
     */
    private function get_csv_format( string $table_name ): array {
        return match ( $table_name ) {
            'weapons' => [
                'id'                => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '武器ID' ],
                'name'              => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '武器名' ],
                'type'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '武器種' ],
                'tree'              => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '派生名' ],
                'rarity'            => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'レア度' ],
                'rank'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'ランク' ],
                'attack'            => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '攻撃力' ],
                //'sharpness'         => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '切れ味' ],
                'affinity'          => [ 'type' => 'tinyint(4)',          'pattern' => '^-?\d{1,4}$', 'label' => '会心率' ],
                'defense_bonus'     => [ 'type' => 'int(11)',             'pattern' => '^[0-9]+$',    'label' => '防御力ボーナス' ],
                'element1'          => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '属性1' ],
                'element2'          => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '属性2' ],
                'elem1_value'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '属性値1' ],
                'elem2_value'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '属性値2' ],
                'slot1'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット1' ],
                'slot2'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット2' ],
                'slot3'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット3' ],
                //'rampage_skills'    => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '百竜スキル' ],
                //'forging_materials' => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '生産素材' ],
                //'upgrade_materials' => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '強化素材' ],
                //'forge_funds'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '生産費用' ],
                //'forge_with_money'  => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '購入費用' ],
                //'upgrade_funds'     => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '強化費用' ],
                //'rollbackable'      => [ 'type' => 'bit(1)',              'pattern' => '^(TRUE|true|True|FALSE|false|False|0|1)?$', 'label' => 'ロールバック可否' ],
            ],
            'armors' => [
                'id'                 => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '防具ID' ],
                'name_male'          => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '防具名（男性用）' ],
                'name_female'        => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '防具名（女性用）' ],
                'series'             => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => 'シリーズ名' ],
                'type'               => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '部位' ],
                'rarity'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'レア度' ],
                'rank'               => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'ランク' ],
                'defense'            => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '防御力' ],
                'level'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'レベル' ],
                'max_level'          => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '最大レベル' ],
                'fire_resistance'    => [ 'type' => 'tinyint(4)',          'pattern' => '^-?\d{1,4}$', 'label' => '火耐性' ],
                'water_resistance'   => [ 'type' => 'tinyint(4)',          'pattern' => '^-?\d{1,4}$', 'label' => '水耐性' ],
                'thunder_resistance' => [ 'type' => 'tinyint(4)',          'pattern' => '^-?\d{1,4}$', 'label' => '雷耐性' ],
                'ice_resistance'     => [ 'type' => 'tinyint(4)',          'pattern' => '^-?\d{1,4}$', 'label' => '氷耐性' ],
                'dragon_resistance'  => [ 'type' => 'tinyint(4)',          'pattern' => '^-?\d{1,4}$', 'label' => '龍耐性' ],
                'slot1'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット1' ],
                'slot2'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット2' ],
                'slot3'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット3' ],
                'skills'             => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => 'スキル' ],
                'forging_materials'  => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '生産素材' ],
                'forge_funds'        => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '生産費用' ],
            ],
            'talismans' => [
                'id'     => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '護石ID' ],
                'name'   => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => '護石名' ],
                'rarity' => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'レア度' ],
                'slot1'  => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット1' ],
                'slot2'  => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット2' ],
                'slot3'  => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット3' ],
                'skills' => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => 'スキル' ],
            ],
            'decorations' => [
                'id'                => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '装飾品ID' ],
                'name'              => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => '装飾品名' ],
                'rarity'            => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'レア度' ],
                'slot'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット' ],
                'skills'            => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => 'スキル' ],
                'forging_materials' => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => '生産素材' ],
                'forge_funds'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '生産費用' ],
            ],
            'skills' => [
                'id'          => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => 'スキルID' ],
                'name'        => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => 'スキル名' ],
                'description' => [ 'type' => 'text',                'pattern' => '^.*+$',     'label' => 'スキル概要' ],
                'max_lv'      => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => '最大レベル' ],
                'status'      => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => 'ステータス' ],
            ],
            default => [],
        };
    }

}

endif;