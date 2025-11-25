<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Base Class to share common controls (Table Source & Link ID)
 */
abstract class Shuriken_Base_Widget extends \Elementor\Widget_Base {
    public function get_categories() { return [ 'shuriken_addons' ]; }

    protected function register_common_controls() {
        $this->start_controls_section( 'section_config', [ 'label' => 'Configuration', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ] );

        // 1. Get Tables
        $saved_tables = get_option( 'shuriken_tables_db', [] );
        $options = [];
        if ( ! empty( $saved_tables ) ) {
            foreach ( $saved_tables as $id => $table ) {
                $options[ $id ] = '#' . $id . ' - ' . $table['name'];
            }
        }
        $this->add_control( 'table_id', [
            'label' => 'Select Data Source', 'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $options, 'default' => array_key_first($options)
        ]);

        // 2. Link ID
        $this->add_control( 'custom_link_id', [
            'label' => 'Table Link ID', 'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'my-table',
            'description' => '<strong>IMPORTANT:</strong> Set the SAME ID here for the Table, Search, and Pagination widgets to connect them.',
            'default' => 'main-table'
        ]);

        $this->end_controls_section();
    }
}

/**
 * 1. TABLE WIDGET
 */
class Shuriken_Widget_Table extends Shuriken_Base_Widget {
    public function get_name() { return 'shuriken_table_data'; }
    public function get_title() { return 'Shuriken Data Table'; }
    public function get_icon() { return 'eicon-table'; }

    protected function register_controls() {
        $this->register_common_controls();
        
        $this->start_controls_section( 'section_style', [ 'label' => 'Table Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_control( 'head_bg', [ 'label' => 'Header Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} th' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'head_text', [ 'label' => 'Header Text', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} th' => 'color: {{VALUE}};'] ] );
        $this->add_control( 'row_even', [ 'label' => 'Striped Rows', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} tr:nth-child(even) td' => 'background-color: {{VALUE}};'] ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $tables = get_option( 'shuriken_tables_db', [] );
        if(empty($tables[$s['table_id']])) return;
        echo \Shuriken_Table_Manager::generate_table_html($s['table_id'], $tables[$s['table_id']], true, 'table', $s['custom_link_id']);
    }
}

/**
 * 2. SEARCH WIDGET
 */
class Shuriken_Widget_Search extends Shuriken_Base_Widget {
    public function get_name() { return 'shuriken_table_search'; }
    public function get_title() { return 'Shuriken Search Bar'; }
    public function get_icon() { return 'eicon-search'; }

    protected function register_controls() {
        $this->register_common_controls();
        
        $this->start_controls_section( 'section_search_settings', [ 'label' => 'Search Settings', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ] );
        $this->add_control( 'target_col', [ 'label' => 'Target Column Key', 'type' => \Elementor\Controls_Manager::TEXT, 'description' => 'Optional: Limit search to one column (e.g. email).' ] );
        $this->end_controls_section();

        $this->start_controls_section( 'section_style', [ 'label' => 'Input Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_control( 'input_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} input' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'input_color', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} input' => 'color: {{VALUE}};'] ] );
        $this->add_control( 'input_padding', [ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $tables = get_option( 'shuriken_tables_db', [] );
        if(empty($tables[$s['table_id']])) return;
        echo \Shuriken_Table_Manager::generate_table_html($s['table_id'], $tables[$s['table_id']], true, 'search', $s['custom_link_id'], $s['target_col']);
    }
}

/**
 * 3. PAGINATION WIDGET
 */
class Shuriken_Widget_Pagination extends Shuriken_Base_Widget {
    public function get_name() { return 'shuriken_table_pagination'; }
    public function get_title() { return 'Shuriken Pagination'; }
    public function get_icon() { return 'eicon-post-navigation'; }

    protected function register_controls() {
        $this->register_common_controls();
        $this->start_controls_section( 'section_style', [ 'label' => 'Button Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_control( 'btn_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} button' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'btn_color', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} button' => 'color: {{VALUE}};'] ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $tables = get_option( 'shuriken_tables_db', [] );
        if(empty($tables[$s['table_id']])) return;
        echo \Shuriken_Table_Manager::generate_table_html($s['table_id'], $tables[$s['table_id']], true, 'pagination', $s['custom_link_id']);
    }
}

/**
 * 4. BUTTONS WIDGET (Export/Print)
 */
class Shuriken_Widget_Buttons extends Shuriken_Base_Widget {
    public function get_name() { return 'shuriken_table_buttons'; }
    public function get_title() { return 'Shuriken Actions'; }
    public function get_icon() { return 'eicon-download-button'; }

    protected function register_controls() {
        $this->register_common_controls();
        $this->start_controls_section( 'section_style', [ 'label' => 'Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_control( 'btn_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'background-color: {{VALUE}};'] ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $tables = get_option( 'shuriken_tables_db', [] );
        if(empty($tables[$s['table_id']])) return;
        echo \Shuriken_Table_Manager::generate_table_html($s['table_id'], $tables[$s['table_id']], true, 'buttons', $s['custom_link_id']);
    }
}

/**
 * 5. INFO WIDGET (Status Text)
 */
class Shuriken_Widget_Info extends Shuriken_Base_Widget {
    public function get_name() { return 'shuriken_table_info'; }
    public function get_title() { return 'Shuriken Status Info'; }
    public function get_icon() { return 'eicon-info-circle'; }

    protected function register_controls() {
        $this->register_common_controls();
        $this->start_controls_section( 'section_style', [ 'label' => 'Typography', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_control( 'text_color', [ 'label' => 'Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-info-text' => 'color: {{VALUE}};'] ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $tables = get_option( 'shuriken_tables_db', [] );
        if(empty($tables[$s['table_id']])) return;
        echo \Shuriken_Table_Manager::generate_table_html($s['table_id'], $tables[$s['table_id']], true, 'info', $s['custom_link_id']);
    }
}