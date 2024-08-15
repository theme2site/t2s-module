<?php
get_header();

global $wpdb;
$table_name = $wpdb->prefix . 't2s_modules';

// 获取搜索关键字
$search_keyword = isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : '';

// 当前页数
$current_page = get_query_var('paged') ? get_query_var('paged') : 1;
// 每页显示的结果数
$results_per_page = 10;

// 计算结果偏移量
$offset = ($current_page - 1) * $results_per_page;

// 构建 SQL 查询语句
$query = $wpdb->prepare(
    "SELECT * FROM {$table_name} WHERE name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR age LIKE '%%%s%%' OR introduction LIKE '%%%s%%' LIMIT %d OFFSET %d",
    '%' . $wpdb->esc_like($search_keyword) . '%',
    '%' . $wpdb->esc_like($search_keyword) . '%',
    '%' . $wpdb->esc_like($search_keyword) . '%',
    '%' . $wpdb->esc_like($search_keyword) . '%',
    $results_per_page,
    $offset
);

// 获取总结果数
$total_results = $wpdb->get_var("
    SELECT COUNT(*) FROM {$table_name}
    WHERE name LIKE '%" . $wpdb->esc_like($search_keyword) . "%'
    OR email LIKE '%" . $wpdb->esc_like($search_keyword) . "%'
    OR age LIKE '%" . $wpdb->esc_like($search_keyword) . "%'
    ");

// 构建分页链接
$pagination = paginate_links(array(
    'base' => add_query_arg('paged', '%#%'),
    'format' => '',
    'prev_text' => __('&laquo;'),
    'next_text' => __('&raquo;'),
    'total' => ceil($total_results / $results_per_page),
    'current' => $current_page
));

// 执行查询
$results = $wpdb->get_results($query);
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main container" role="main">

        <form class="product-inventory-search my-4" action="<?php echo esc_url(site_url('module-list')); ?>" method="GET">
            <div class="d-flex justify-content-end">
                <input class="input-search mr-4" type="text" name="keyword" placeholder="请输入关键词" value="<?php echo esc_attr($search_keyword); ?>" />
                <input type="submit" class="btn btn-primary btn-sm mx-4" value="搜索" />
            </div>
        </form>

        <?php if ($results) : ?>
            <table class="table text-center">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">姓名</th>
                        <th scope="col">邮箱</th>
                        <th scope="col">年龄</th>
                        <th scope="col">性别</th>
                        <th scope="col">角色</th>
                        <th scope="col">爱好</th>
                        <th scope="col">介绍</th>
                        <th scope="col">状态</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result) : ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url(site_url('module-detail/' . $result->id) . '/'); ?>">
                                    <?php echo $result->name; ?>
                                </a>
                            </td>
                            <td><?php echo $result->email; ?></td>
                            <td><?php echo $result->age; ?></td>
                            <td><?php echo _e(T2S_MODULE_GLOBAL_DATA['gender'][$result->gender], 't2s_module') ?></td>
                            <td><?php echo _e(T2S_MODULE_GLOBAL_DATA['role'][$result->role], 't2s_module') ?></td>
                            <td>
                                <?php foreach (json_decode($result->hobbies) as $key => $value) {
                                    if ($key == count(json_decode($result->hobbies)) - 1) {
                                        echo _e(T2S_MODULE_GLOBAL_DATA['hobbies'][$value], 't2s_module');
                                    }else{
                                        echo _e(T2S_MODULE_GLOBAL_DATA['hobbies'][$value], 't2s_module') . ' - ';
                                    }
                                }?>
                            </td>
                            <td><?php echo $result->introduction; ?></td>
                            <td><?php echo $result->status == 1 ? _e('Active', 't2s_module') : _e('Inactive', 't2s_module'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No results found.</p>
        <?php endif; ?>

        <div class="paginate my-5">
            <div class="wp-pagenavi" role="navigation">
                <?php echo $pagination; ?>
            </div>
        </div>

    </main>
</div>

<?php
get_footer(); // 获取网站底部
?>
