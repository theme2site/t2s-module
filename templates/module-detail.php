<?php
get_header();

global $wpdb;
$table_name = $wpdb->prefix . 't2s_modules';
$module_id = get_query_var('module_id');
$result = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $module_id");
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main container" role="main">
        <?php
        if ($result) :
        ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title"><?php echo $result->name; ?></h1>
                    </header>

                    <div class="entry-content">
                        <div>邮箱：<?php echo $result->email; ?></div>
                        <div>年龄：<?php echo $result->age; ?></div>
                        <div>性别：<?php echo _e(T2S_MODULE_GLOBAL_DATA['gender'][$result->gender], 't2s_module') ?></div>
                        <div>角色：<?php echo _e(T2S_MODULE_GLOBAL_DATA['role'][$result->role], 't2s_module') ?></div>
                        <div>爱好：<?php foreach (json_decode($result->hobbies) as $key => $value) {
                                    if ($key == count(json_decode($result->hobbies)) - 1) {
                                        echo _e(T2S_MODULE_GLOBAL_DATA['hobbies'][$value], 't2s_module');
                                    }else{
                                        echo _e(T2S_MODULE_GLOBAL_DATA['hobbies'][$value], 't2s_module') . ' - ';
                                    }
                                }?></div>
                        <div>介绍：<?php echo $result->introduction; ?></div>
                        <div>图片：<img src="<?php echo $result->image_url; ?>" alt=""></div>
                        <div>文件：<a href="<?php echo $result->file_url; ?>">下载文件</a></div>
                        <div>状态：<?php echo $result->status == 1 ? _e('Active', 't2s_module') : _e('Inactive', 't2s_module'); ?></div>
                    </div>
                </article>
            <?php
        else :
            ?>
            <p>404</p>
        <?php
        endif;
        ?>
    </main>
</div>

<?php
get_footer(); // 获取网站底部
?>
