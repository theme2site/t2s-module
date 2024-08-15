<?php

/**
 * PART 1. Defining Custom Database Table
 * ============================================================================
 *
 * In this part you are going to define custom database table,
 * create it, update, and fill with some dummy data
 *
 * http://codex.wordpress.org/Creating_Tables_with_Plugins
 *
 * In case your are developing and want to check plugin use:
 *
 * DROP TABLE IF EXISTS wp_modules;
 * DELETE FROM wp_options WHERE option_name = 't2s_module_db_version';
 *
 * to drop table and option
 */


/**
 * PART 2. Defining Custom Table List
 * ============================================================================
 *
 * In this part you are going to define custom table list class,
 * that will display your database records in nice looking table
 *
 * https://developer.wordpress.org/reference/classes/wp_list_table/
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class T2S_Module_List_Table extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    public function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'person',
            'plural' => 'persons',
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_age($item)
    {
        return '<em>' . $item['age'] . '</em>';
    }
    public function column_gender($item)
    {
        return _e(T2S_MODULE_GLOBAL_DATA['gender'][$item['gender']], 't2s_module');
    }
    public function column_role($item)
    {
        return _e(T2S_MODULE_GLOBAL_DATA['role'][$item['role']], 't2s_module');
    }
    public function column_status($item)
    {
        return $item['status'] == 1 ? _e('Active', 't2s_module') : _e('Inactive', 't2s_module');
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_name($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=persons_form&id=%s">%s</a>', $item['id'], __('Edit', 't2s_module')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 't2s_module')),
        );

        return sprintf(
            '%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'id' => __('ID#', 't2s_module'),
            'name' => __('Name', 't2s_module'),
            'email' => __('E-Mail', 't2s_module'),
            'age' => __('Age', 't2s_module'),
            'gender' => __('Gender', 't2s_module'),
            'role' => __('Role', 't2s_module'),
            'status' => __('Status', 't2s_module'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'name' => array('name', true),
            'email' => array('email', false),
            'age' => array('age', false),
            'gender' => array('gender', false),
            'role' => array('role', false),
            'status' => array('status', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => '删除'
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    public function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 't2s_modules'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) {
                $ids = implode(',', $ids);
            }

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 't2s_modules'; // do not forget about tables prefix

        $per_page = 20; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = $this->get_pagenum();

        // Set the offset criteria
        $offset = ($paged - 1) * $per_page;

        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}

/**
 * PART 3. Admin page
 * ============================================================================
 *
 * In this part you are going to add admin page for custom table
 *
 * http://codex.wordpress.org/Administration_Menus
 */

/**
 * admin_menu hook implementation, will add pages to list persons and to add new one
 */
function t2s_module_admin_menu()
{
    add_menu_page(__('定制模块', 't2s_module'), __('定制模块', 't2s_module'), 'activate_plugins', 'persons', 't2s_module_persons_page_handler');
    add_submenu_page('persons', __('数据列表', 't2s_module'), __('数据列表', 't2s_module'), 'activate_plugins', 'persons', 't2s_module_persons_page_handler');
    add_submenu_page('persons', __('新增数据', 't2s_module'), __('新增数据', 't2s_module'), 'activate_plugins', 'persons_form', 't2s_module_persons_form_page_handler');
    add_submenu_page('persons', __('导入数据', 't2s_module'), __('导入数据', 't2s_module'), 'activate_plugins', 'persons_import', 't2s_module_persons_import_page_handler');
}

add_action('admin_menu', 't2s_module_admin_menu');

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function t2s_module_persons_page_handler()
{
    global $wpdb;

    $table = new T2S_Module_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 't2s_module'), count($_REQUEST['id'])) . '</p></div>';
    } ?>
    <div class="wrap">

        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Persons', 't2s_module') ?>

            <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=persons_import'); ?>">导入数据</a>
            <a class="add-new-h2" href="<?php echo admin_url('admin-ajax.php?action=persons_export'); ?>">导出数据</a>
            <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons_form'); ?>"><?php _e('新增数据', 't2s_module') ?></a>
        </h2>
        <?php echo $message; ?>

        <form id="persons-table" method="GET">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $table->display() ?>
        </form>

    </div>
<?php
}

/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 *
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/selected
 */

/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
function t2s_module_persons_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 't2s_modules'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => '',
        'email' => '',
        'age' => null,
        'gender' => 0,
        'role' => 0,
        'hobbies' => null,
        'introduction' => null,
        'image_url' => null,
        'file_url' => null,
        'status' => 0,
    );

    // here we are verifying does this request is post back and have correct nonce
    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $_REQUEST['hobbies'] = $_REQUEST['hobbies'] ? json_encode($_REQUEST['hobbies'], JSON_UNESCAPED_UNICODE) : null;
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = t2s_module_validate_data($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 't2s_module');
                } else {
                    $notice = __('There was an error while saving item', 't2s_module');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 't2s_module');
                } else {
                    $notice = __('There was an error while updating item', 't2s_module');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    } else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 't2s_module');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('persons_form_meta_box', 'Person data', 't2s_module_persons_form_meta_box_handler', 'person', 'normal', 'default'); ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Person', 't2s_module') ?>
            <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons'); ?>"><?php _e('返回列表', 't2s_module') ?></a>
        </h2>

        <?php if (!empty($notice)) : ?>
            <div id="notice" class="error">
                <p><?php echo $notice ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($message)) : ?>
            <div id="message" class="updated">
                <p><?php echo $message ?></p>
            </div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>" />
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>" />

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php /* And here we call our custom meta box */ ?>
                        <?php do_meta_boxes('person', 'normal', $item); ?>
                        <input type="submit" value="<?php _e('提交', 't2s_module') ?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function t2s_module_persons_form_meta_box_handler($item)
{
?>
    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Name', 't2s_module') ?></label>
                </th>
                <td>
                    <input id="name" name="name" type="text" style="width: 95%" value="<?php echo esc_attr($item['name']) ?>" size="50" class="code" placeholder="<?php _e('Your name', 't2s_module') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('E-Mail', 't2s_module') ?></label>
                </th>
                <td>
                    <input id="email" name="email" type="email" style="width: 95%" value="<?php echo esc_attr($item['email']) ?>" size="50" class="code" placeholder="<?php _e('Your E-Mail', 't2s_module') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Age', 't2s_module') ?></label>
                </th>
                <td>
                    <input id="age" name="age" type="number" style="width: 95%" value="<?php echo esc_attr($item['age']) ?>" size="50" class="code" placeholder="<?php _e('Your age', 't2s_module') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Gender', 't2s_module') ?></label>
                </th>
                <td>
                    <select name="gender" id="gender" style="width: 95%" class="code">
                        <?php foreach (T2S_MODULE_GLOBAL_DATA['gender'] as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo $key==esc_attr($item['gender']) ? 'selected' :'';?>><?php _e($value, 't2s_module') ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Role', 't2s_module') ?></label>
                </th>
                <td>
                    <fieldset>
                    <?php foreach (T2S_MODULE_GLOBAL_DATA['role'] as $key => $value) { ?>
                        <label for="role<?php echo $key; ?>" style="padding-right: 10px">
                            <input id="role<?php echo $key; ?>" name="role" type="radio" value="<?php echo $key; ?>" class="code" <?php echo $key==esc_attr($item['role']) ? 'checked' :'';?>>
                            <?php _e($value, 't2s_module') ?>
                        </label>
                    <?php } ?>
                    </fieldset>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Hobbies', 't2s_module') ?></label>
                </th>
                <td>
                    <fieldset>
                    <?php foreach (T2S_MODULE_GLOBAL_DATA['hobbies'] as $key => $value) { ?>
                        <label for="hobbies<?php echo $key; ?>" style="padding-right: 10px">
                            <input id="hobbies<?php echo $key; ?>" name="hobbies[]" type="checkbox" value="<?php echo $key; ?>" class="code" <?php echo in_array($key, json_decode($item['hobbies'])) ? 'checked' :'';?>>
                            <?php _e($value, 't2s_module') ?>
                        </label>
                    <?php } ?>
                    </fieldset>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Introduction', 't2s_module') ?></label>
                </th>
                <td>
                    <textarea id="introduction" name="introduction" style="width: 95%" rows="6" class="code" placeholder="<?php _e('Your introduction', 't2s_module') ?>"><?php echo esc_attr($item['introduction']) ?></textarea>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Upload image', 't2s_module') ?></label>
                </th>
                <td>
                    <input type="button" class="button upload-image-or-file-button upload-image-button" value="<?php _e('Upload image', 't2s_module') ?>" />
                    <input type="button" class="button remove-image-or-file-button remove-image-button" value="<?php _e('Remove image', 't2s_module') ?>" />
                    <div class="upload-image-or-file-wrap" style="margin-top:10px">
                        <img id="image-url" src="<?php echo esc_attr($item['image_url']) ?>" alt="" style="max-width: 210px; height: auto;">
                        <input type="hidden" name="image_url" value="<?php echo esc_attr($item['image_url']) ?>"/>
                    </div>
                </td>

            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Upload file', 't2s_module') ?></label>
                </th>
                <td>
                    <input type="button" class="button upload-image-or-file-button upload-file-button" value="<?php _e('Upload file', 't2s_module') ?>" />
                    <input type="button" class="button remove-image-or-file-button remove-file-button" value="<?php _e('Remove File', 't2s_module') ?>" />
                    <div class="upload-image-or-file-wrap" style="margin-top:10px">
                        <div id="file-url"><?php echo esc_attr($item['file_url']) ?></div>
                        <input type="hidden" name="file_url" value="<?php echo esc_attr($item['file_url']) ?>" />
                    </div>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label><?php _e('Status', 't2s_module') ?></label>
                </th>
                <td>
                    <fieldset>
                        <label>
                            <input id="status" name="status" type="checkbox" value="1" class="code" <?php echo esc_attr($item['status']) == 1 ? 'checked' :'';?>> <?php _e('Active', 't2s_module') ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
    <script>
        jQuery(document).ready(function($){
            var mediaUploader1;
            var mediaUploader2;
            $('.upload-image-button').click(function(e) {
                e.preventDefault();
                // If the uploader object has already been created, reopen the dialog
                if (mediaUploader1) {
                    mediaUploader1.open();
                    return;
                }
                // Extend the wp.media object
                mediaUploader1 = wp.media.frames.file_frame = wp.media({
                    title: '<?php _e('Choose Image', 't2s_module') ?>',
                    button: {
                    text: '<?php _e('Choose Image', 't2s_module') ?>'
                }, multiple: false });
                // When a file is selected, grab the URL and set it as the text field's value
                mediaUploader1.on('select', function() {
                    attachment = mediaUploader1.state().get('selection').first().toJSON();
                    $('#image-url').attr('src', attachment.url);
                    $('input[name=image_url]').val(attachment.url);
                });

                // Open the uploader dialog
                mediaUploader1.open();
            });
            $('.upload-file-button').click(function(e) {
                e.preventDefault();
                // If the uploader object has already been created, reopen the dialog
                if (mediaUploader2) {
                    mediaUploader2.open();
                    return;
                }
                // Extend the wp.media object
                mediaUploader2 = wp.media.frames.file_frame = wp.media({
                    title: '<?php _e('Choose File', 't2s_module') ?>',
                    button: {
                    text: '<?php _e('Choose File', 't2s_module') ?>'
                }, multiple: false });
                // When a file is selected, grab the URL and set it as the text field's value
                mediaUploader2.on('select', function() {
                    attachment = mediaUploader2.state().get('selection').first().toJSON();
                    $('#file-url').html(attachment.url);
                    $('input[name=file_url]').val(attachment.url);
                });
                // Open the uploader dialog
                mediaUploader2.open();
            });
            $('.remove-image-or-file-button').click(function(e) {
                // e.preventDefault();
                var answer = confirm("<?php _e('Are you sure?', 't2s_module') ?>");
                if (answer == true) {
                    $show_wrap = $(this).siblings('.upload-image-or-file-wrap');
                    if($show_wrap.children('#image-url')){
                        $show_wrap.children('#image-url').attr('src', '');
                    }
                    if($show_wrap.children('#file-url')){
                        $show_wrap.children('#file-url').html('');
                    }
                    $show_wrap.children('input').val('');
                }
                return;
            });

        });
    </script>
<?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function t2s_module_validate_data($item)
{
    $messages = array();

    if (empty($item['name'])) {
        $messages[] = __('Name is required', 't2s_module');
    }
    if (!empty($item['email']) && !is_email($item['email'])) {
        $messages[] = __('E-Mail is in wrong format', 't2s_module');
    }
    if (!ctype_digit($item['age'])) {
        $messages[] = __('Age in wrong format', 't2s_module');
    }
    if (empty($item['role'])) {
        $messages[] = __('Please select role', 't2s_module');
    }
    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages)) {
        return true;
    }
    return implode('<br />', $messages);
}

/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == t2s_module
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function t2s_module_languages()
{
    load_plugin_textdomain('t2s_module', false, dirname(plugin_basename(__FILE__)));
}
add_action('init', 't2s_module_languages');


function csv_pull_export()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 't2s_modules'; // do not forget about tables prefix

    $file = 'export';
    $results = $wpdb->get_results("SELECT * FROM {$table_name};", ARRAY_A);

    if (empty($results)) {
        return;
    }

    // Create a new Spreadsheet object
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

    // Add data to the spreadsheet
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'name');
    $sheet->setCellValue('B1', 'email');
    $sheet->setCellValue('C1', 'age');
    $sheet->setCellValue('D1', 'gender');
    $sheet->setCellValue('E1', 'role');
    $sheet->setCellValue('F1', 'hobbies');
    $sheet->setCellValue('G1', 'introduction');
    $sheet->setCellValue('H1', 'status');

    $i = 2;
    foreach ($results as $row) {
        $sheet->setCellValue('A' . $i, $row['name']);
        $sheet->setCellValue('B' . $i, $row['email']);
        $sheet->setCellValue('C' . $i, $row['age']);
        $sheet->setCellValue('D' . $i, $row['gender']);
        $sheet->setCellValue('E' . $i, $row['role']);
        $sheet->setCellValue('F' . $i, $row['hobbies']);
        $sheet->setCellValue('G' . $i, $row['introduction']);
        $sheet->setCellValue('H' . $i, $row['status']);
        $i++;
    }

    // Create a writer object
    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    // Set the file headers for Excel
    $filename = "exported_" . date("Y-m-d_His", time());
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Save the Excel file to php://output
    $writer->save('php://output');
    exit;
}
add_action('wp_ajax_persons_export', 'csv_pull_export');

/**
 * 数据导入页面
 *
 * @return void
 */
function t2s_module_persons_import_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 't2s_modules'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // here we are verifying does this request is post back and have correct nonce
    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        switch ($_FILES['filename']['error']) {
            case '4':
                $notice = __('文件上传失败', 't2s_module');
                break;
        }

        $price_excel_start = 0;
        $arUploadDir = wp_upload_dir();
        $path = $arUploadDir['basedir'] . '/import_excel/';
        $uploadfile = $_FILES['filename']['name'];
        $fileparse = explode(".", $uploadfile);
        $extension = end($fileparse); // 获取文件后缀名

        // 允许上传的后缀
        $allowedExts = array("csv", "xlsx", "xls");

        if (isset($_FILES['filename']) && $_FILES['filename']['error'] == 0) {
            if ($_FILES["filename"]["size"] > 1024 * 1024 * 10) {
                $notice = __('file size over 10Mb', 'atcm');
            } elseif (!in_array($extension, $allowedExts)) {
                $notice = __('Incorrect format', 'atcm');
            } else {

                // Create a new Spreadsheet object
                $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['filename']['tmp_name']);

                // Get the first worksheet
                $worksheet = $spreadsheet->getActiveSheet();

                // Initialize an array to map column letters to column names
                $column_map = [];

                // Iterate through the first row to build the column map
                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    foreach ($cellIterator as $cell) {
                        // Assuming that the first row contains the column names
                        $column_map[$cell->getColumn()] = $cell->getValue();
                    }
                    break; // Stop after the first row
                }

                // Iterate through the rows and import data using column names
                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $data = [];

                    foreach ($cellIterator as $cell) {
                        // Use the column map to get the column name
                        $column_name = $column_map[$cell->getColumn()];
                        $data[$column_name] = $cell->getValue();
                    }

                    if ($data['email'] == 'email') {
                        continue;
                    }
                    if ($data['email'] == '') {
                        continue;
                    }

                    $email  = $data['email'];
                    $item_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE email = %s",
                        $email), ARRAY_A);
                    if ($item_data) {
                        $result = $wpdb->update($table_name, array(
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'age' => $data['age'],
                            'gender' => $data['gender'],
                            'role' => $data['role'],
                            'hobbies' => $data['hobbies'],
                            'introduction' => $data['introduction'],
                            'status' => $data['status'],
                            'updated_at'  => date('Y-m-d H:i:s'),
                        ), array('id' => $item_data['id']));
                    } else {
                        $filed = array(
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'age' => $data['age'],
                            'gender' => $data['gender'],
                            'role' => $data['role'],
                            'hobbies' => $data['hobbies'],
                            'introduction' => $data['introduction'],
                            'status' => $data['status'],
                            'created_at'  => date('Y-m-d H:i:s'),
                            'updated_at'  => date('Y-m-d H:i:s'),
                            );
                        $result = $wpdb->insert($table_name, $filed);
                    }

                    if ($result) {
                        $price_excel_start += 1;
                        $message = __('Imported: ' . $price_excel_start, 'atcm');
                    } else {
                        $notice = __('There was an error while saving item', 'atcm');
                    }
                }

            }
        }

    } ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Person', 't2s_module') ?>
            <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons'); ?>"><?php _e('返回列表', 't2s_module') ?></a>
        </h2>

        <?php if (!empty($notice)) : ?>
            <div id="notice" class="error">
                <p><?php echo $notice ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($message)) : ?>
            <div id="message" class="updated">
                <p><?php echo $message ?></p>
            </div>
        <?php endif; ?>

        <form id="form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>" />
            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <h1>导入数据</h1>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="home">选择文件</label></th>
                                <td><input type="file" name="filename" class="">
                                </td>
                            </tr>
                        </table>
                        <input type="submit" value="<?php _e('提交', 't2s_module') ?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
    </div>

<?php
}
