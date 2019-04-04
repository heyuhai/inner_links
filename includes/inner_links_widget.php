<?php
// 使用 widgets_init 动作钩子来执行自定义的函数
add_action( 'widgets_init', 'inner_links_widgets' );

// 注册小工具
function inner_links_widgets() {
    register_widget( 'inner_links_widget' );
}

class inner_links_widget extends WP_Widget {

    // 构造函数
    function inner_links_widget() {
        $widget_ops = array(
            'classname' => 'inner_links_widget_class',
            'description' => '内链管理小工具'
        );
        $this->WP_Widget( 'inner_links_widget', '内链管理', $widget_ops );
    }

     // 创建小工具的设置表单
    function form($instance) {
        $defaults = array( 'title' => 'My Info', 'movie' => '', 'song' => '' );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = $instance['title'];
        $movie = $instance['movie'];
        $song = $instance['song'];

        ?>
        <p>Title: <input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <p>Favorite Movie: <input class="widefat" name="<?php echo $this-> get_field_name( 'movie' ); ?> "type="text" value="<?php echo esc_attr( $movie ); ?>" /></p>
        <p>Favorite Song: <textarea class="widefat" name="<?php echo $this-> get_field_name( 'song' ); ?>"><?php echo esc_attr( $song ); ?></textarea></p>
        <?php
    }

    function update( $new_instance, $old_instance ) {
        // 小工具选项的保存过程
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['movie'] = strip_tags( $new_instance['movie'] );
        $instance['song'] = strip_tags( $new_instance['song'] );

        return $instance;
    }

    function widget( $args, $instance ) {
        // 显示小工具
        extract( $args );

        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
        $movie = empty( $instance['movie'] ) ? ' ' : $instance['movie'];
        $song = empty( $instance['song'] ) ? ' ' : $instance['song'];

        if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
        echo '<p> Fav Movie: ' . $movie . '</p>';
        echo '<p> Fav Song: ' . $song . '</p>';
        echo $after_widget;
    }
}
?>
