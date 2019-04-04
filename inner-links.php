<?php
/*
Plugin Name: inner-links-manager
Plugin URI: http://example.com/wordpress-plugins/my-plugin
Description: 添加内链
Version: 1.0
Author: yagni
Author URI: http://yagni.cc
License: GPLv2
*/

// include plugin_dir_path(__FILE__) . 'includes/inner_links_widget.php';
include plugin_dir_path(__FILE__) . 'includes/inner_links_db.php';

define('YAGNI', 'inner_links_manager');

register_activation_hook( __FILE__, 'inner_links_install' );
register_deactivation_hook( __FILE__, 'inner_links_uninstall' );

// 启用时要做的事情
function inner_links_install() {
    if( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
        deactivate_plugins( basename( __FILE__ )); //禁用插件
    }

    // 保存插件配置
    // $inner_links_options = array(
    //     'color' => '',
    // );
    // update_option( 'inner_links_options', $inner_links_options );

    // 创建表
    $inner_links_db = new inner_links_db();
    $inner_links_db->createTable();
}

// 禁用时执行的内容
function inner_links_uninstall() {
    // delete_option( 'inner_links_options' );
}

add_action( 'admin_menu', 'inner_links_create_menu' );
function inner_links_create_menu() {
    // 创建顶级菜单
    add_menu_page(
        '文章内链管理',
        '文章内链管理',
        'manage_options',
        YAGNI,
        'inner_links_list'
    );
    // 创建子菜单
    add_submenu_page(
        YAGNI,
        '添加内链',
        '添加内链',
        'manage_options',
        YAGNI.'_op',
        'inner_links_op'
    );
    // OR 添加的设置
    // add_options_page(
    //     '文章内链管理',
    //     '文章内链管理',
    //     'manage_options',
    //     YAGNI,
    //     'inner_links_list'
    // );
}

function inner_links_list() {
    $inner_links_db = new inner_links_db();
    $count = $inner_links_db->count();

    $comments_per_page = 10;
    $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
    $s = isset( $_GET['s'] ) ? $_GET['s']: '';
    $nowPage = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
    $offset = ($nowPage-1)*$comments_per_page;
    $total = ceil($count / $comments_per_page);
    $where = 'where 1=1';
    if (trim($s)) {
        $where .= " and title like '%$s%'";
    }
    $order = 'order by id desc';
    $limit = 'limit '.$offset.','.$comments_per_page;
    $list = $inner_links_db->getList($where, $order, $limit);
    ?>
    <div class="wrap">
    <?php screen_icon( 'plugins' ); ?>
    <h1>内链列表 <a href="?page=inner_links_manager_op" class="page-title-action">新建内链</a></h1>

    <form id="posts-filter" method="get">
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input">搜索自动链接:</label>
            <input type="hidden" name="page" value="inner_links_manager">
            <input type="search" name="s" value="<?php echo $s; ?>">
            <input type="submit" id="search-submit" class="button" value="搜索标题">
        </p>

        <h2 class="screen-reader-text">文章列表</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>标题</th>
                    <th>跳转内链URL</th>
                    <th>是否新窗口</th>
                    <th>添加时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($list as $key => $value): ?>
                <tr>
                    <td><?php echo $value->id; ?></td>
                    <td><?php echo $value->title; ?></td>
                    <td><?php echo $value->url; ?></td>
                    <td><?php if($value->target == 1){echo '是';}else{echo '否';} ; ?></td>
                    <td><?php echo date('Y-m-d H:i:s', $value->addtime); ?></td>
                    <td><a href="?page=inner_links_manager_op&op=edit&id=<?php echo $value->id; ?>">修改</a> | <a href="?page=inner_links_manager_op&op=del&id=<?php echo $value->id; ?>">删除</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">Displaying <?php echo $page; ?>-<?php echo $total; ?> of <?php echo $count; ?></span>
                <?php
                    echo paginate_links( array(
                        'base' => add_query_arg( 'cpage', '%#%' ),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total,
                        'current' => $page
                    ));
                ?>
            </div>
        </div>
    </form>

    <div id="ajax-response"></div>
    <br class="clear">
    </div>
    <?php
}

function inner_links_op() {
    // TODO:
    $op = isset($_GET['op']) ? $_GET['op'] : 'add';
    $id = intval($_GET['id']);
    inner_links_modify($op, $id);
}

function inner_links_modify($op, $id = 0) {
    $inner_links_db = new inner_links_db();
    switch ($op) {
        case 'edit':
            $name = '修改';
            $url = '?page=inner_links_manager_op&op=edit&id='.$id;
            $info = $inner_links_db->info($id);
            break;
        case 'del':
            $rs = $inner_links_db->del($id);
            if ($rs) {
                ?>
                    <div id="message" class="error">删除成功</div>
                    <a href="?page=inner_links_manager">内链列表</a>
                <?php
            } else {
                ?>
                    <div id="message" class="error">删除出现错误</div>
                    <a href="?page=inner_links_manager">内链列表</a>
                <?php
            }
            exit;
            break;
        default:
            $name = '添加';
            $url = '?page=inner_links_manager_op&op=add';
            break;
    }
    ?>
    <div class="wrap">
    <?php screen_icon( 'plugins' ); ?>
    <h1><?php echo $name; ?>内链 <a href="?page=inner_links_manager" class="page-title-action">内链列表</a></h1>

    <?php
        if($_POST && $_POST['save']) {
            $data = [
                'title' => strip_tags($_POST['title']),
                'url' => strip_tags($_POST['url']),
                'target' => intval($_POST['target']),
                'css_style' => strip_tags($_POST['css_style']),
                'addtime' => time()
            ];
            if (!trim($data['title'])) {
                ?>
                    <div id="message" class="error">标题不能为空</div>
                    <a href="javascript:history.go(-1);">返回上一层</a>
                <?php
                exit;
            }
            if (!trim($data['url'])) {
                ?>
                    <div id="message" class="error">内链URL不能为空</div>
                    <a href="javascript:history.go(-1);">返回上一层</a>
                <?php
                exit;
            }
            if ($op == 'edit') {
                $rs = $inner_links_db->update($data, $id);
            } else {
                $rs = $inner_links_db->add($data);
            }
            if ($rs) {
                ?>
                    <div id="message" class="error">保存成功</div>
                    <a href="?page=inner_links_manager">内链列表</a>
                <?php
            } else {
                ?>
                    <div id="message" class="error">保存出现错误</div>
                    <a href="javascript:history.go(-1);">返回上一层</a>
                <?php
            }
            exit;
        }
    ?>

    <form method="POST" action="<?php echo $url; ?>">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="title">标题</label></th>
                <td><input type="text" id="title" name="title" value="<?php echo $info->title; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="url">内链URL</label></th>
                <td><input type="text" id="url" size="80" name="url" value="<?php echo $info->title; ?>" /><label>* eg:http://www.baidu.com/</label></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="css_style">CSS样式</label></th>
                <td><input type="text" id="css_style" size="80" name="css_style" value="<?php if($info->css_style): ?><?php echo $info->css_style; ?><?php else: ?>color:#000000;<?php endif; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="target">是否新窗口</label></th>
                <td>
                    <input type="radio" name="target" value="1" <?php if($info->target==1): ?>checked<?php endif; ?> /> 是
                    <input type="radio" name="target" value="0" <?php if($info->target==0): ?>checked<?php endif; ?> /> 否
                </td>
            </tr>
            <tr valign="top">
                <td>
                    <input type="submit" name="save" value="提 交" class="button-primary" />
                </td>
            </tr>
        </table>
    </form>

    <div id="ajax-response"></div>
    <br class="clear">
    </div>
    <?php
}

// TODO: 前台文章 the_content 钩子没反应
// 特定的页面选择 theme template 之前执行(在只在网站的前端触发，并不在管理员页面触发。)
add_action( 'template_redirect', 'content_modify' );
function content_modify() {
    if( is_singular( 'post' ) ) {
        $post = get_post();
        $content = $post->post_content;
        // 内链
        $inner_links_db = new inner_links_db();
        $list = $inner_links_db->getList();
        foreach ($list as $key => $value) {
            $target = ($value->target == 1) ? 'target="_blank"' : '';
            $css_style = (empty($value->css_style)) ? '' : $value->css_style;
            // 进行替换的最大次数， -1 默认无限匹配
            $content = preg_replace('#(?=[^>]*(?=<(?!/a>)|$))'.$value->title.'#', '<a style="'.$css_style.'" '.$target.' href="'.$value->url.'">'.$value->title.'</a>', $content, -1);
        }
        $post->post_content = $content;
        // return $content;
    }
}

?>
