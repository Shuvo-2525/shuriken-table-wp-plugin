<?php
/**
 * Plugin Name: Shuriken Table Pro
 * Description: Professional Elementor Form Manager. Save tables, manage columns, and display data with advanced privacy controls.
 * Version: 3.5.0
 * Author: Shuriken Dev
 * Text Domain: shuriken-table
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Shuriken_Table_Manager {

    const OPTION_KEY = 'shuriken_tables_db';

    public function __construct() {
        add_shortcode( 'shuriken_table', array( $this, 'render_frontend_table' ) );
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_post_shuriken_save_table', array( $this, 'handle_save_table' ) );
        add_action( 'admin_post_shuriken_delete_table', array( $this, 'handle_delete_table' ) );
        add_action( 'admin_post_shuriken_export_csv', array( $this, 'handle_csv_export' ) );
        
        add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widgets' ) );
        add_action( 'elementor/elements/categories_registered', array( $this, 'register_elementor_categories' ) );
        
        add_action( 'wp_footer', array( $this, 'print_global_scripts' ) );
    }

    // 1. REGISTER MULTIPLE WIDGETS
    public function register_elementor_widgets( $widgets_manager ) {
        $widget_file = plugin_dir_path( __FILE__ ) . 'widgets/shuriken-widget.php';
        if ( file_exists( $widget_file ) ) {
            require_once( $widget_file );
            // We now register separate widgets for a true "Builder" experience
            $widgets_manager->register( new \Shuriken_Widget_Table() );
            $widgets_manager->register( new \Shuriken_Widget_Search() );
            $widgets_manager->register( new \Shuriken_Widget_Pagination() );
            $widgets_manager->register( new \Shuriken_Widget_Buttons() );
            $widgets_manager->register( new \Shuriken_Widget_Info() );
        }
    }

    public function register_elementor_categories( $elements_manager ) {
        $elements_manager->add_category( 'shuriken_addons', [ 'title' => 'Shuriken Addons', 'icon'  => 'eicon-code' ] );
    }

    /* ==========================================================================
       ADMIN DASHBOARD
       ========================================================================== */
    public function register_admin_menu() {
        add_menu_page( 'Shuriken Tables', 'Shuriken Tables', 'manage_options', 'shuriken-table', array( $this, 'router' ), 'dashicons-editor-table', 50 );
    }

    public function router() {
        $view = isset( $_GET['view'] ) ? $_GET['view'] : 'list';
        if ( $view === 'edit' ) { $this->render_edit_view(); } else { $this->render_list_view(); }
    }

    private function render_list_view() {
        $tables = get_option( self::OPTION_KEY, array() );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Shuriken Table Manager</h1>
            <a href="<?php echo admin_url('admin.php?page=shuriken-table&view=edit'); ?>" class="page-title-action">Create New Table</a>
            <hr class="wp-header-end">
            <?php if ( empty( $tables ) ) : ?>
                <div style="background: #fff; padding: 40px; text-align: center; border: 1px solid #ccd0d4; margin-top: 20px;">
                    <h2>No Tables Created Yet</h2>
                    <p>Create your first table to display Elementor Form data.</p>
                    <a href="<?php echo admin_url('admin.php?page=shuriken-table&view=edit'); ?>" class="button button-primary">Create Table</a>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                    <thead><tr><th style="width: 50px;">ID</th><th>Table Name</th><th>Shortcode</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ( $tables as $id => $table ) : ?>
                            <tr>
                                <td>#<?php echo esc_html( $id ); ?></td>
                                <td><strong><?php echo esc_html( $table['name'] ); ?></strong></td>
                                <td><code>[shuriken_table id="<?php echo esc_attr( $id ); ?>"]</code></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=shuriken-table&view=edit&id=' . $id); ?>" class="button button-small">Edit</a>
                                    <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=shuriken_delete_table&id=' . $id), 'shuriken_delete_' . $id ); ?>" class="button button-small button-link-delete" onclick="return confirm('Delete this table?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_edit_view() {
        global $wpdb;
        $tables = get_option( self::OPTION_KEY, array() );
        $id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
        
        $data = array(
            'name' => '', 'form_id' => '', 'style' => 'modern', 'limit' => 100, 
            'role' => '', 'date_format' => 'default', 'no_data_msg' => 'No submissions found.',
            'search' => '1', 'pagination' => '1', 'export' => '0', 'print' => '0', 'columns' => array()
        );

        if ( $id && isset( $tables[$id] ) ) { $data = array_merge( $data, $tables[$id] ); }

        $table_subs = $wpdb->prefix . 'e_submissions';
        $forms_list = $wpdb->get_col("SELECT DISTINCT form_name FROM {$table_subs}");

        $detected_columns = array();
        if ( ! empty( $data['form_id'] ) ) {
            $table_vals = $wpdb->prefix . 'e_submissions_values';
            $keys = $wpdb->get_col( $wpdb->prepare("SELECT DISTINCT v.key FROM {$table_vals} v INNER JOIN {$table_subs} s ON s.id = v.submission_id WHERE s.form_name = %s LIMIT 100", $data['form_id']) );
            $detected_columns = $keys ? $keys : array();
        }
        ?>
        <div class="wrap">
            <h1><?php echo $id ? 'Edit Table' : 'Create New Table'; ?></h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="shuriken_save_table">
                <?php wp_nonce_field( 'shuriken_save' ); ?>
                <?php if ($id): ?><input type="hidden" name="table_id" value="<?php echo esc_attr($id); ?>"><?php endif; ?>

                <div id="poststuff"><div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="postbox"><h2 class="hndle"><span>Settings</span></h2><div class="inside"><table class="form-table">
                            <tr><th>Table Name</th><td><input type="text" name="name" value="<?php echo esc_attr($data['name']); ?>" class="regular-text" required></td></tr>
                            <tr><th>Source Form</th><td>
                                <select name="form_id" onchange="this.form.submit()">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($forms_list as $f) echo '<option value="'.esc_attr($f).'" '.selected($data['form_id'], $f, false).'>'.esc_html($f).'</option>'; ?>
                                </select>
                            </td></tr>
                        </table></div></div>

                        <?php if ( ! empty($data['form_id']) ): ?>
                        <div class="postbox"><h2 class="hndle"><span>Columns</span></h2><div class="inside"><table class="widefat striped">
                            <thead><tr><th>Show</th><th>Original Key</th><th>Custom Label</th></tr></thead>
                            <tbody>
                                <?php $all_cols = array_unique( array_merge( $detected_columns, array_keys($data['columns']) ) );
                                foreach ( $all_cols as $col_key ): 
                                    $saved_col = isset($data['columns'][$col_key]) ? $data['columns'][$col_key] : array();
                                    $is_checked = isset($saved_col['hidden']) && $saved_col['hidden'] ? '' : 'checked';
                                    $label = isset($saved_col['label']) ? $saved_col['label'] : ucwords(str_replace(['_','-'], ' ', $col_key));
                                ?>
                                <tr>
                                    <td><input type="checkbox" name="columns[<?php echo esc_attr($col_key); ?>][active]" value="1" <?php echo $is_checked; ?>></td>
                                    <td><code><?php echo esc_html($col_key); ?></code></td>
                                    <td><input type="text" name="columns[<?php echo esc_attr($col_key); ?>][label]" value="<?php echo esc_attr($label); ?>" style="width: 100%;"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div></div>
                        <?php endif; ?>
                    </div>
                    <div class="postbox-container"><div class="postbox"><h2 class="hndle"><span>Actions</span></h2><div class="inside">
                        <button type="submit" class="button button-primary button-large" style="width:100%;">Save</button>
                    </div></div></div>
                </div></div>
            </form>
        </div>
        <?php
    }

    public function handle_save_table() {
        check_admin_referer( 'shuriken_save' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die('Unauthorized');
        $tables = get_option( self::OPTION_KEY, array() );
        $id = ! empty( $_POST['table_id'] ) ? intval( $_POST['table_id'] ) : ( !empty($tables) ? max(array_keys($tables)) + 1 : 1 );

        $columns_data = array();
        if ( isset( $_POST['columns'] ) ) {
            foreach ( $_POST['columns'] as $key => $vals ) {
                $columns_data[$key] = array( 'label' => sanitize_text_field( $vals['label'] ), 'hidden' => ! isset( $vals['active'] ) );
            }
        }

        $tables[$id] = array(
            'name' => sanitize_text_field( $_POST['name'] ),
            'form_id' => sanitize_text_field( $_POST['form_id'] ),
            'style' => 'default', 
            'limit' => 100,
            'role' => '',
            'date_format' => 'default',
            'no_data_msg' => 'No submissions found.',
            'search' => '1', 'pagination' => '1', 'export' => '0', 'print' => '0',
            'columns' => $columns_data
        );
        update_option( self::OPTION_KEY, $tables );
        wp_redirect( admin_url('admin.php?page=shuriken-table&view=edit&id=' . $id . '&msg=saved') ); exit;
    }

    public function handle_delete_table() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die('Unauthorized');
        $id = intval( $_GET['id'] );
        check_admin_referer( 'shuriken_delete_' . $id );
        $tables = get_option( self::OPTION_KEY, array() );
        if ( isset( $tables[$id] ) ) { unset( $tables[$id] ); update_option( self::OPTION_KEY, $tables ); }
        wp_redirect( admin_url('admin.php?page=shuriken-table') ); exit;
    }

    public function handle_csv_export() { 
        $id = intval($_GET['table_id']);
        $tables = get_option(self::OPTION_KEY);
        if(!isset($tables[$id])) wp_die('Table not found');
        $config = $tables[$id];
        $data = self::fetch_data($config['form_id'], 9999);
        header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename=export-table-'.$id.'.csv');
        $out = fopen('php://output', 'w');
        $headers = ['Date'];
        foreach($data['headers'] as $k) { if(empty($config['columns'][$k]['hidden'])) $headers[] = $k; }
        fputcsv($out, $headers);
        foreach($data['rows'] as $r) {
            $row = [$r['created_at']];
            foreach($data['headers'] as $k) { if(empty($config['columns'][$k]['hidden'])) $row[] = isset($r['fields'][$k]) ? $r['fields'][$k] : ''; }
            fputcsv($out, $row);
        }
        fclose($out); exit;
    }

    /* ==========================================================================
       FRONTEND RENDERERS
       ========================================================================== */
    
    public function render_frontend_table( $atts ) {
        $atts = shortcode_atts( array( 'id' => '' ), $atts );
        $tables = get_option( self::OPTION_KEY, array() );
        if ( empty( $atts['id'] ) || ! isset( $tables[ $atts['id'] ] ) ) return '<p>Table ID not found.</p>';
        return self::generate_table_html( $atts['id'], $tables[ $atts['id'] ], false, 'all' );
    }

    public static function fetch_data( $form_id, $limit ) {
        global $wpdb;
        $t_sub = $wpdb->prefix . 'e_submissions';
        $t_val = $wpdb->prefix . 'e_submissions_values';
        $limit_raw = intval($limit) * 20; 
        if ( $wpdb->get_var("SHOW TABLES LIKE '$t_sub'") != $t_sub ) return array('headers' => [], 'rows' => []);
        $rows_data = $wpdb->get_results( $wpdb->prepare("SELECT s.id, s.created_at, v.key, v.value FROM {$t_sub} s JOIN {$t_val} v ON s.id = v.submission_id WHERE s.form_name = %s ORDER BY s.created_at DESC LIMIT %d", $form_id, $limit_raw) );
        $pivoted = array(); $headers = array();
        foreach ( $rows_data as $r ) {
            if ( !isset($pivoted[$r->id]) ) {
                if ( count($pivoted) >= $limit ) continue;
                $pivoted[$r->id] = array('id'=>$r->id, 'created_at'=>$r->created_at, 'fields'=>array());
            }
            if ( isset($pivoted[$r->id]) && !empty($r->key) ) {
                $pivoted[$r->id]['fields'][$r->key] = $r->value;
                if (!in_array($r->key, $headers)) $headers[] = $r->key;
            }
        }
        return array('headers' => $headers, 'rows' => $pivoted);
    }

    // 4. MODULAR HTML GENERATOR (UPDATED FOR ROBUST LINKING)
    public static function generate_table_html( $table_id, $config, $is_elementor = false, $component = 'all', $link_id = '', $search_col = '' ) {
        $data = self::fetch_data( $config['form_id'], $config['limit'] );
        
        // Robust ID generation
        $unique_id = !empty($link_id) ? $link_id : 'sh_' . $table_id . '_' . uniqid();
        
        ob_start();
        
        if ( ! $is_elementor ) {
            echo '<style>
            .shuriken-container { width: 100%; overflow-x: auto; font-family: sans-serif; }
            .shuriken-table { width: 100%; border-collapse: collapse; }
            .shuriken-table th, .shuriken-table td { padding: 10px; border: 1px solid #ddd; }
            .sh-controls { display: flex; gap: 10px; margin-bottom: 10px; }
            .sh-search-input { padding: 8px; border: 1px solid #ccc; }
            .sh-btn { padding: 8px 12px; background: #0073aa; color: #fff; text-decoration: none; border:none; cursor: pointer; }
            </style>';
        }
        
        echo '<div class="shuriken-container sh-comp-' . esc_attr($component) . '">';
        
        // --- COMPONENT: SEARCH ---
        // Renders just the input. Does NOT need data.
        if ( $component === 'all' || $component === 'search' ) {
            $col_attr = !empty($search_col) ? 'data-col="'.esc_attr($search_col).'"' : '';
            // Data-target-id tells JS which table to look for
            echo '<input type="text" class="sh-search-input" placeholder="Search..." data-target-id="'.esc_attr($unique_id).'" '.$col_attr.'>';
        }

        // --- COMPONENT: BUTTONS ---
        if ( $component === 'all' || $component === 'buttons' ) {
            echo '<div class="sh-buttons-group">';
            echo '<button onclick="window.print()" class="sh-btn sh-btn-print">Print</button> ';
            echo '<a href="' . wp_nonce_url( admin_url('admin-post.php?action=shuriken_export_csv&table_id='.$table_id), 'shuriken_csv' ) . '" class="sh-btn sh-btn-csv">Export CSV</a>';
            echo '</div>';
        }

        // --- COMPONENT: INFO (NEW) ---
        if ( $component === 'info' ) {
            $count = count($data['rows']);
            // The span needs the ID so JS can update it
            echo '<div class="sh-info-text" data-target-id="'.esc_attr($unique_id).'">Showing <span class="sh-showing-count">'.$count.'</span> results</div>';
        }

        // --- COMPONENT: TABLE ---
        if ( $component === 'all' || $component === 'table' ) {
            if ( empty( $data['rows'] ) ) {
                echo '<p>' . esc_html( $config['no_data_msg'] ) . '</p>';
            } else {
                echo '<div class="sh-table-wrapper">';
                // The Table gets the data-shuriken-id attribute. This is the beacon for other widgets.
                echo '<table data-shuriken-id="'.esc_attr($unique_id).'" class="shuriken-table"><thead><tr>';
                echo '<th>Date</th>';
                $visible_cols = array();
                foreach ( $data['headers'] as $key ) {
                    if ( empty($config['columns'][$key]['hidden']) ) {
                        $visible_cols[] = $key;
                        $lbl = !empty($config['columns'][$key]['label']) ? $config['columns'][$key]['label'] : $key;
                        echo '<th>' . esc_html( $lbl ) . '</th>';
                    }
                }
                echo '</tr></thead><tbody>';

                foreach ( $data['rows'] as $row ) {
                    echo '<tr><td>' . esc_html( $row['created_at'] ) . '</td>';
                    foreach ( $visible_cols as $col ) {
                        echo '<td data-key="'.esc_attr($col).'">' . esc_html( isset($row['fields'][$col]) ? $row['fields'][$col] : '' ) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table>';
                echo '</div>';
            }
        }

        // --- COMPONENT: PAGINATION ---
        if ( $component === 'all' || $component === 'pagination' ) {
             echo '<div class="sh-pagination">';
             // Buttons target the ID
             echo '<button class="sh-btn sh-pagination-btn sh-prev" data-target-id="'.esc_attr($unique_id).'" data-dir="-1">Prev</button>';
             echo '<button class="sh-btn sh-pagination-btn sh-next" data-target-id="'.esc_attr($unique_id).'" data-dir="1">Next</button>';
             echo '</div>';
        }
        
        echo '</div>';
        return ob_get_clean();
    }
    
    // 5. GLOBAL JS (Fixed Search & Connection)
    public function print_global_scripts() {
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function(){ 

            // 1. Search Logic (Event Delegation)
            document.body.addEventListener('input', function(e) {
                if(e.target.classList.contains('sh-search-input')) {
                    let input = e.target;
                    let targetId = input.getAttribute('data-target-id');
                    let filter = input.value.trim().toUpperCase();
                    let targetCol = input.getAttribute('data-col');
                    
                    // Find the table by the data attribute, NOT getElementById (robust!)
                    let table = document.querySelector('table[data-shuriken-id="'+targetId+'"]');
                    if(!table) return;

                    let rows = table.querySelectorAll("tbody tr");
                    let visibleCount = 0;
                    
                    rows.forEach(r => { 
                        let text = "";
                        if(targetCol) {
                            let cell = r.querySelector('td[data-key="'+targetCol+'"]');
                            text = cell ? cell.innerText : "";
                        } else {
                            text = r.innerText;
                        }
                        
                        let match = text.toUpperCase().includes(filter);
                        r.style.display = match ? "" : "none"; 
                        if(match) visibleCount++;
                    });
                    
                    // Reset pagination for that table
                    shurikenUpdatePagination(table, 0);
                    shurikenUpdateInfo(targetId, visibleCount);
                }
            });

            // 2. Pagination Click Logic
            document.body.addEventListener('click', function(e) {
                if(e.target.classList.contains('sh-pagination-btn')) {
                    let btn = e.target;
                    let targetId = btn.getAttribute('data-target-id');
                    let dir = parseInt(btn.getAttribute('data-dir'));
                    let table = document.querySelector('table[data-shuriken-id="'+targetId+'"]');
                    if(table) shurikenUpdatePagination(table, dir);
                }
            });
            
            // Init Pagination on Load
            document.querySelectorAll('table[data-shuriken-id]').forEach(t => {
                shurikenUpdatePagination(t, 0);
            });
        });

        function shurikenUpdatePagination(table, dir) {
            let rows = Array.from(table.querySelectorAll("tbody tr")).filter(r => r.style.display !== 'none' || r.dataset.pageHidden);
            
            // Filter out rows hidden by SEARCH (they don't have pageHidden, they have display:none directly)
            let visibleRows = rows.filter(r => !r.style.display || r.style.display !== 'none' || r.dataset.pageHidden === 'true');
            // Actually, we just want rows that match the search query.
            // Simplified: Iterate ALL rows. If row matches search (based on logic or if we assume search resets pagination), include it.
            // For robustness: We assume search clears pagination first.
            
            if(!table.dataset.currPage) table.dataset.currPage = 1;
            let curr = parseInt(table.dataset.currPage);
            
            // Reset page if direction is 0 (Search trigger)
            if(dir === 0) curr = 1; 
            else curr += dir;
            
            if(curr < 1) curr = 1;
            
            let per = 10; 
            let start = (curr-1)*per; 
            let end = start+per;
            
            // Apply logic to currently "search-visible" rows
            let count = 0;
            // First pass: identify valid rows (not filtered by search)
            let validRows = [];
            table.querySelectorAll("tbody tr").forEach(r => {
                // If it was hidden by pagination, unhide it to check search
                if(r.dataset.pageHidden) {
                     r.style.display = ''; 
                     delete r.dataset.pageHidden;
                }
                // Now check if it's visible (search didn't hide it)
                if(r.style.display !== 'none') {
                    validRows.push(r);
                }
            });
            
            let max = Math.ceil(validRows.length / per);
            if(curr > max && max > 0) curr = max;
            table.dataset.currPage = curr;
            
            // Second pass: Apply pagination
            validRows.forEach((r, i) => {
                if (i >= start && i < end) {
                    r.style.display = ""; 
                } else {
                    r.style.display = "none";
                    r.dataset.pageHidden = "true";
                }
            });
        }

        function shurikenUpdateInfo(targetId, count) {
            let info = document.querySelector('.sh-info-text[data-target-id="'+targetId+'"] .sh-showing-count');
            if(info) info.innerText = count;
        }
        </script>
        <?php
    }
}
new Shuriken_Table_Manager();