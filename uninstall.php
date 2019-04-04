<?php
// 如果 uninstall 不是从 WordPress 调用，则退出
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

// 从 options 表删除选项
// delete_option( 'inner_links_options' );

// 删除其他额外的选项和自定义表
include plugin_dir_path(__FILE__) . 'includes/inner_links_db.php';
$inner_links_db = new inner_links_db();
$inner_links_db->dropTable();

?>
