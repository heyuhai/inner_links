<?php
class inner_links_db {

    private $_table = 'wp_inner_links';

    public function createTable() {
        global $wpdb;
        $isExist = $wpdb->query("SHOW TABLES LIKE '".$this->_table."'");
        if (!$isExist) {
            // 安装数据表
            $createTableSql = "CREATE TABLE `".$this->_table."` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(100) NOT NULL DEFAULT '',
                `url` varchar(255) NOT NULL DEFAULT '',
                `target` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否新窗口',
                `css_style` varchar(255) NOT NULL DEFAULT '',
                `addtime` int(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
            $wpdb->query($createTableSql);
        }
    }

    public function dropTable() {
        global $wpdb;
        $isExist = $wpdb->query("SHOW TABLES LIKE '".$this->_table."'");
        if ($isExist) {
            // 安装数据表
            $dropTableSql = "DROP TABLE IF EXISTS `".$this->_table."`;";
            $wpdb->query($dropTableSql);
        }
    }

    public function getList($where = '', $order = '', $limit = '') {
        global $wpdb;
        $list = $wpdb->get_results("SELECT * FROM ".$this->_table." $where $order $limit ");
        return $list;
    }

    public function count() {
        global $wpdb;
        $count = $wpdb->get_var("SELECT count(*) FROM ".$this->_table);
        return intval($count);
    }

    public function add($data) {
        global $wpdb;
        return $wpdb->insert($this->_table, $data);
    }

    public function update($data, $id) {
        global $wpdb;
        return $wpdb->update($this->_table, $data, ['id' => $id]);
    }

    public function del($id) {
        global $wpdb;
        return $wpdb->query("DELETE FROM ".$this->_table." WHERE id = $id");
    }

    public function info($id) {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM ".$this->_table." WHERE id = $id");
    }

}
?>
