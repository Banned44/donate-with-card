<?php
/**
 * Basic database operations class.
 * User: Kambaa
 */

class DbOps
{
    /**
     * A comma seperated table replacement placeholder list.
     */
    const TABLE_REPLACEMENT_PLACEHOLDERS = "TABLENAME,TABLE_NAME";

    public function __construct()
    {
    }

    /**
     * Returns wordpress's wpdb variable.
     * @return mixed
     */
    public static function db()
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * Adds wordpress' prefix to the table name
     * @param $tableName string
     * @return string prefixed version of the given table name
     */
    private function getPrefixedTableName($tableName)
    {
        return $this::db()->prefix . $tableName;
    }

    /**
     * @param $sql String Table generation sql script string. Table name will be prefixed with the wordpress prefix,
     * so replace table name with TABLENAME or TABLE_NAME (uppercase as shown).
     * @param $tableName string Table name this name will be prefixed with the wordpress db prefix defined in
     * installation.
     */
    public function createTable($sql, $tableName)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $tableName = $this->getPrefixedTableName($tableName);
        $replacedSql = str_replace(explode(",", $this::TABLE_REPLACEMENT_PLACEHOLDERS), $tableName, $sql);
        dbDelta($replacedSql);
    }

    /**
     * @param $tableName string Table name to drop.
     */
    public function dropTable($tableName)
    {
        $sql = "DROP TABLE IF EXISTS " . $this->getPrefixedTableName($tableName);
        $this::db()->query($sql);
    }

    /**
     * Gets results of the given sql statement
     * @param $sql string SQL statement to execute
     * @return mixed @link https://developer.wordpress.org/reference/classes/wpdb/get_results/ see for more information.
     */
    private function query($sql)
    {
        return $this::db()->get_results($sql, ARRAY_A);
    }

    /**
     * Fetches every row of the given table.
     * @param $tableName string Table name to fetch. Will be prefixed automatically
     * @return mixed @see query method for return values.
     */
    public function fetchRows($tableName)
    {
        $sql = "SELECT * FROM `" . $this->getPrefixedTableName($tableName) . "`";
        return $this->query($sql);
    }

    /**
     * Fetches rows according to given condition and order.
     * @param $tableName string Table name
     * @param null $condition SQL condition string
     * @param null $order SQL order string
     * @return mixed @see query method for return values.
     */
    public function fetchRow($tableName, $condition = null, $order = null)
    {
        $condition = empty($condition) ? "1" : $condition;
        $condition = "WHERE " . $condition;
        $order = !empty($order) ? "ORDER BY " . $order : null;
        $sql = "SELECT * FROM `" . $this->getPrefixedTableName($tableName) . "` " . $condition . " " . $order;
        return $this->query($sql);
    }

    /**
     * Inserts data to the given table. This method just calls the $wpdb->insert method
     * @link https://developer.wordpress.org/reference/classes/wpdb/insert/ for more info.
     *
     */
    public function insert($table, $data, $format = null)
    {
        return self::db()->insert($table, $data, $format);
    }

    /**
     * Updates data. This method just calls the $wpdb->update method.
     * @link https://developer.wordpress.org/reference/classes/wpdb/update/ for more info
     */
    public function update($table, $data, $where, $format = null, $where_format = null)
    {
        return self::db()->update($table, $data, $where, $format, $where_format);
    }

    /**
     * Deletes row. This method just calls the $wpdb->delete method.
     * @link https://developer.wordpress.org/reference/classes/wpdb/delete/ for more info
     */
    public function delete($table, $where, $where_format = null)
    {
        return self::db()->delete($table, $where, $where_format);
    }
}
