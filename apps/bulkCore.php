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
     * Catch Requests
     *
     * @access protected
     */
    public function catch_request() {
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
        $file_path = $this->optimize_file( $csv_file );
        $csv_data = new \SplFileObject( $file_path, 'r' );
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
                    case preg_match( '@^(float|double)@', $_type ):
                        $line[$_idx] = (float)$_val;
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
        // Discard file that have been read
        if ( $csv_file !== $file_path ) {
            $this->discard_file( $file_path );
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
            if ( $table_name === 'talismans' ) {
                $this->evaluate_talisman_worth();
            }
            $message = sprintf( '%d/%d data has been completed insertion into the "%s" table.', $counter, count( $data ), $table_name );
        } else {
            $message = 'Failed to insert data.';
        }
        die( $message . PHP_EOL );
    }

    /**
     * Unify character encoding of import files to UTF-8
     *
     * @access private
     */
    private function optimize_file( string $file_path ): string {
        setlocale( LC_ALL, 'ja_JP.UTF-8' );
        $encodings = [ 'ASCII', 'ISO-2022-JP', 'UTF-8', 'eucjp-win', 'Windows-31J', 'sjis-win', 'SJIS' ];
        $_data = @file_get_contents( $file_path );
        $before_encoding = mb_detect_encoding( $_data, $encodings );
        if ( $before_encoding === 'UTF-8' ) {
            return $file_path;
        }
        if ( $before_encoding === false ) {
            $before_encoding = 'SJIS';
        }
        $_data = mb_convert_encoding( $_data, 'UTF-8', $before_encoding );
        $put_file_path = __DIR__ .'/'. md5( microtime( true ) ) .'.csv';
        if ( file_put_contents( $put_file_path, $_data, LOCK_EX ) ) {
            return $put_file_path;
        } else {
            die( 'Oops, this CSV does not contain any valid data to import.' );
        }
    }

    /**
     * Discard specific file
     *
     * @access private
     */
    private function discard_file( string $file_path ): void {
        @unlink( $file_path );
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
                'ruby_name'         => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '武器名ルビ' ],
                'type'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '武器種' ],
                'tree'              => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '派生名' ],
                'rarity'            => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'レア度' ],
                'rank'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'ランク' ],
                'attack'            => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '攻撃力' ],
                'affinity'          => [ 'type' => 'tinyint(4)',          'pattern' => '^-?\d{1,4}$', 'label' => '会心率' ],
                'defense_bonus'     => [ 'type' => 'int(11)',             'pattern' => '^[0-9]+$',    'label' => '防御力ボーナス' ],
                'element1'          => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '属性1' ],
                'element2'          => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '属性2' ],
                'elem1_value'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '属性値1' ],
                'elem2_value'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '属性値2' ],
                'slot1'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット1' ],
                'slot2'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット2' ],
                'slot3'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'スロット3' ],
            ],
            'weapon_meta' => [
                'id'                  => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '武器メタID' ],
                'weapon_id'           => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '武器ID' ],
                'sharpness'           => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '斬れ味' ],
                'shelling_type'       => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '砲撃タイプ' ],
                'shelling_level'      => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '砲撃レベル' ],
                'melody_effects'      => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '旋律効果' ],
                'phial_type'          => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '装着ビン' ],
                'phial_element'       => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'ビン属性' ],
                'phial_element_value' => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => 'ビン属性値' ],
                'kinsect_level'       => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '猟虫レベル' ],
                'deviation'           => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'ブレ' ],
                'recoil'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '反動' ],
                'reload'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'リロード' ],
                'mods'                => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'パーツ' ],
                'cluster_bomb_type'   => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '拡散弾タイプ' ],
                'special_ammo'        => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '特殊弾' ],
                'arc_shot'            => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '曲射' ],
                'charge_shot'         => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '溜め攻撃' ],
                'forging_materials'   => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '生産素材' ],
                'upgrade_materials'   => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '強化素材' ],
                'forge_funds'         => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '生産費用' ],
                'forge_with_money'    => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '購入費用' ],
                'upgrade_funds'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '強化費用' ],
                'rampage_skills'      => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '百竜スキル' ],
                'rollbackable'        => [ 'type' => 'bit(1)',              'pattern' => '^(TRUE|true|True|FALSE|false|False|0|1)?$', 'label' => 'ロールバック可否' ],
            ],
            'ammo' => [
                'id'                      => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '弾薬管理ID' ],
                'weapon_id'               => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '武器ID' ],
                'capacity'                => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '弾数（初期値）' ],
                'capacity_lv1'            => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '弾数（装填拡張Lv1）' ],
                'capacity_lv2'            => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '弾数（装填拡張Lv2）' ],
                'capacity_lv3'            => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '弾数（装填拡張Lv3）' ],
                'recoil'                  => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '反動（初期値）' ],
                'recoil_lv1'              => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '反動（反動軽減Lv1）' ],
                'recoil_lv2'              => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '反動（反動軽減Lv2）' ],
                'recoil_lv3'              => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '反動（反動軽減Lv3）' ],
                'reload'                  => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => 'リロード（初期値）' ],
                'reload_lv1'              => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => 'リロード（装填速度Lv1）' ],
                'reload_lv2'              => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => 'リロード（装填速度Lv2）' ],
                'reload_lv3'              => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => 'リロード（装填速度Lv3）' ],
                'moving_shot'             => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '移動射撃可' ],
                'moving_reload'           => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '移動リロード可' ],
                'single_fire_auto_reload' => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '単発自動装填' ],
                'for_rapid_fire'          => [ 'type' => 'json',                'pattern' => '^.*?$',       'label' => '速射対応' ],
            ],
            'armors' => [
                'id'                 => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '防具ID' ],
                'name_male'          => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '防具名（男性用）' ],
                'name_female'        => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '防具名（女性用）' ],
                'ruby_name_male'     => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '防具名ルビ（男性用）' ],
                'ruby_name_female'   => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => '防具名ルビ（女性用）' ],
                'series'             => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',       'label' => 'シリーズ名' ],
                'part'               => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => '部位' ],
                'rarity'             => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'レア度' ],
                'rank'               => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$',   'label' => 'ランク' ],
                'defense'            => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',    'label' => '防御力' ],
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
                'id'            => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '護石ID' ],
                'name'          => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => '護石名' ],
                'rarity'        => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'レア度' ],
                'slot1'         => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット1' ],
                'slot2'         => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット2' ],
                'slot3'         => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット3' ],
                'skills'        => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => 'スキル' ],
                'worth'         => [ 'type' => 'float(5,2) unsigned', 'pattern' => '^\d{1,3}\.?\d{,2}$', 'label' => '評価値' ],
                'emission_type' => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => '排出タイプ' ],
                'emissions'     => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '排出数' ],
                'disabled'      => [ 'type' => 'bit(0)',              'pattern' => '^(TRUE|true|True|FALSE|false|False|0|1)?$', 'label' => '無効フラグ' ],
            ],
            'decorations' => [
                'id'                => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '装飾品ID' ],
                'name'              => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => '装飾品名' ],
                'ruby_name'         => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => '装飾品名ルビ' ],
                'rarity'            => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'レア度' ],
                'slot'              => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット' ],
                'skills'            => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => 'スキル' ],
                'forging_materials' => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => '生産素材' ],
                'forge_funds'       => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '生産費用' ],
            ],
            'skills' => [
                'id'          => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => 'スキルID' ],
                'name'        => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => 'スキル名' ],
                'ruby_name'   => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => 'スキル名ルビ' ],
                'description' => [ 'type' => 'text',                'pattern' => '^.*+$',     'label' => 'スキル概要' ],
                'max_lv'      => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => '最大レベル' ],
                'status'      => [ 'type' => 'json',                'pattern' => '^.*?$',     'label' => 'ステータス' ],
            ],
            'skill_evaluation' => [
                'id'         => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => 'スキル評価ID' ],
                'name'       => [ 'type' => 'varchar(255)',        'pattern' => '^.*+$',     'label' => 'スキル名' ],
                'max_lv'     => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => '最大レベル' ],
                'rarity'     => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'レア度' ],
                'slot'       => [ 'type' => 'tinyint(4) unsigned', 'pattern' => '^\d{1,4}$', 'label' => 'スロット' ],
                'score'      => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => 'スコア' ],
                'evaluation' => [ 'type' => 'int(11) unsigned',    'pattern' => '^[0-9]+$',  'label' => '評価' ],
            ],
            default => [],
        };
    }


    public function evaluate_talisman_worth(): void {
        if ( ! $this->table_exists( 'skill_evaluation' ) ) {
            return;
        }
        $talismans = $this->retrieve_data( 'talismans', [[ 'worth', '=', '0' ]] );
        $skill_evaluation = $this->retrieve_data( 'skill_evaluation', [] );
        $slot_base_worth = 8.33 * 1.6491;
        foreach ( $talismans as $item ) {
            $evaluations = [ 1 ];
            $slots = [(int)$item['slot1'], (int)$item['slot2'], (int)$item['slot3']];
            foreach( $slots as $slot ) {
                $evaluations[] = match( $slot ) {
                    3 => $slot_base_worth * 1.37,
                    2 => $slot_base_worth * 1.21,
                    1 => $slot_base_worth * 1.06,
                    default => 0,
                };
            }
            $skills = json_decode( $item['skills'], true );
            foreach ( $skills as $skill => $lv ) {
                $_se = array_filter( $skill_evaluation, function( $elm ) use ( $skill ) { return $elm['name'] === $skill; } );
                if ( $_se ) {
                    $_se = array_shift( $_se );
                    $_bonus = (int)$lv == (int)$_se['max_lv'] ? 1.1 : 1;
                    //$evaluations[] = ((int)$_se['score'] / 100) * (1 + ((int)$_se['slot'] * (int)$lv / 10)) * $_bonus;
                    $evaluations[] = ((int)$_se['evaluation'] / 100) * (1 + ((int)$_se['slot'] - 1) / 10) * (int)$lv * $_bonus;
                }
            }
            $worth = array_sum( $evaluations );
            $stmh = $this->dbh->prepare( 'UPDATE talismans SET worth = :worth WHERE id = :id' );
            $stmh->execute( [ ':worth' => (float)$worth, ':id' => (int)$item['id'] ] );
        }
    }

}

endif;