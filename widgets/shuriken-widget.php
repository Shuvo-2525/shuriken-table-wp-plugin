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
 * 1. TABLE WIDGET (With Typography, Modal & Styles)
 */
class Shuriken_Widget_Table extends Shuriken_Base_Widget {
    public function get_name() { return 'shuriken_table_data'; }
    public function get_title() { return 'Shuriken Data Table'; }
    public function get_icon() { return 'eicon-table'; }

    protected function register_controls() {
        $this->register_common_controls();
        
        $this->start_controls_section( 'section_features', [ 'label' => 'Features', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ] );
        $this->add_control( 'view_modal', [
            'label' => 'Enable Detail Modal',
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Yes', 'label_off' => 'No',
            'return_value' => 'yes', 'default' => 'yes',
        ]);
        $this->add_control( 'view_btn_text', [
            'label' => 'View Button Text',
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'View Details',
            'condition' => ['view_modal' => 'yes']
        ]);
        $this->end_controls_section();

        // --- STYLE: HEADER ---
        $this->start_controls_section( 'section_style_head', [ 'label' => 'Header Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'head_typo', 'selector' => '{{WRAPPER}} th' ] );
        $this->add_control( 'head_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} th' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'head_text', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} th' => 'color: {{VALUE}};'] ] );
        $this->add_control( 'head_padding', [ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->end_controls_section();

        // --- STYLE: BODY ROWS ---
        $this->start_controls_section( 'section_style_body', [ 'label' => 'Rows Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'body_typo', 'selector' => '{{WRAPPER}} td' ] );
        $this->add_control( 'cell_padding', [ 'label' => 'Cell Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        
        $this->start_controls_tabs( 'tabs_rows' );
        // Normal Rows
        $this->start_controls_tab( 'tab_row_normal', [ 'label' => 'Normal' ] );
        $this->add_control( 'row_bg_odd', [ 'label' => 'Odd Row BG', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} tr:nth-child(odd) td' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'row_text_odd', [ 'label' => 'Odd Row Text', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} tr:nth-child(odd) td' => 'color: {{VALUE}};'] ] );
        $this->end_controls_tab();
        // Even (Striped) Rows
        $this->start_controls_tab( 'tab_row_even', [ 'label' => 'Even (Striped)' ] );
        $this->add_control( 'row_bg_even', [ 'label' => 'Even Row BG', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} tr:nth-child(even) td' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'row_text_even', [ 'label' => 'Even Row Text', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} tr:nth-child(even) td' => 'color: {{VALUE}};'] ] );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        
        // Border
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'table_border', 'selector' => '{{WRAPPER}} th, {{WRAPPER}} td' ] );
        $this->end_controls_section();

        // --- STYLE: MODAL BUTTON ---
        $this->start_controls_section( 'section_style_modal_btn', [ 'label' => 'Action Button', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['view_modal' => 'yes'] ] );
        $this->add_control( 'mbtn_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-view-btn' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'mbtn_color', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-view-btn' => 'color: {{VALUE}};'] ] );
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'mbtn_typo', 'selector' => '{{WRAPPER}} .sh-view-btn' ] );
        $this->add_control( 'mbtn_padding', [ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-view-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_control( 'mbtn_radius', [ 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-view-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $tables = get_option( 'shuriken_tables_db', [] );
        if(empty($tables[$s['table_id']])) return;
        
        $extras = [
            'view_modal' => $s['view_modal'],
            'view_text' => $s['view_btn_text']
        ];
        
        echo \Shuriken_Table_Manager::generate_table_html($s['table_id'], $tables[$s['table_id']], true, 'table', $s['custom_link_id'], $extras);
    }
}

/**
 * 2. SEARCH WIDGET (Updated with Button & Dropdown)
 */
class Shuriken_Widget_Search extends Shuriken_Base_Widget {
    public function get_name() { return 'shuriken_table_search'; }
    public function get_title() { return 'Shuriken Search Bar'; }
    public function get_icon() { return 'eicon-search'; }

    protected function register_controls() {
        $this->register_common_controls();
        
        $this->start_controls_section( 'section_search_settings', [ 'label' => 'Search Settings', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ] );
        
        // Dynamic Dropdown for Columns
        $tables = get_option( 'shuriken_tables_db', [] );
        $col_options = ['' => 'All Columns'];
        foreach($tables as $tid => $tdata) {
             if(!empty($tdata['columns'])) {
                 foreach($tdata['columns'] as $k => $v) {
                     $col_options[$k] = $v['label'] . " (Table #$tid)";
                 }
             }
        }
        
        $this->add_control( 'target_col', [ 
            'label' => 'Search Specific Column', 
            'type' => \Elementor\Controls_Manager::SELECT, 
            'options' => $col_options,
            'default' => '',
            'description' => 'Select a column to restrict search. If empty, searches all columns.' 
        ]);

        $this->add_control( 'show_search_btn', [ 'label' => 'Show Button', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'no' ] );
        $this->add_control( 'search_icon', [ 'label' => 'Button Icon', 'type' => \Elementor\Controls_Manager::ICON, 'condition' => ['show_search_btn'=>'yes'] ] );
        $this->end_controls_section();

        // --- STYLE: INPUT ---
        $this->start_controls_section( 'section_style_input', [ 'label' => 'Input Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ] );
        $this->add_control( 'input_width', [ 'label' => 'Width', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ '%', 'px' ], 'range' => [ '%' => [ 'min' => 10, 'max' => 100 ] ], 'selectors' => [ '{{WRAPPER}} .sh-search-input' => 'width: {{SIZE}}{{UNIT}};' ] ] );
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'input_typo', 'selector' => '{{WRAPPER}} .sh-search-input' ] );
        $this->add_control( 'input_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} input' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'input_color', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} input' => 'color: {{VALUE}};'] ] );
        $this->add_control( 'input_padding', [ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'input_border', 'selector' => '{{WRAPPER}} input' ] );
        $this->add_control( 'input_radius', [ 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'input_shadow', 'selector' => '{{WRAPPER}} input' ] );
        $this->end_controls_section();

        // --- STYLE: BUTTON ---
        $this->start_controls_section( 'section_style_btn', [ 'label' => 'Button Style', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => ['show_search_btn'=>'yes'] ] );
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'btn_typo', 'selector' => '{{WRAPPER}} .sh-search-btn' ] );
        $this->add_control( 'btn_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-search-btn' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'btn_color', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-search-btn' => 'color: {{VALUE}};'] ] );
        $this->add_control( 'btn_padding', [ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-search-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_control( 'btn_margin', [ 'label' => 'Margin', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-search-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_control( 'btn_radius', [ 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-search-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $tables = get_option( 'shuriken_tables_db', [] );
        if(empty($tables[$s['table_id']])) return;
        
        $extras = [
            'search_col' => $s['target_col'],
            'search_btn' => $s['show_search_btn'],
            'search_icon'=> isset($s['search_icon']['value']) ? $s['search_icon']['value'] : ''
        ];

        echo \Shuriken_Table_Manager::generate_table_html($s['table_id'], $tables[$s['table_id']], true, 'search', $s['custom_link_id'], $extras);
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
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'btn_typo', 'selector' => '{{WRAPPER}} button' ] );
        $this->add_control( 'btn_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} button' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'btn_color', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} button' => 'color: {{VALUE}};'] ] );
        $this->add_control( 'btn_padding', [ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_control( 'btn_gap', [ 'label' => 'Gap', 'type' => \Elementor\Controls_Manager::SLIDER, 'selectors' => ['{{WRAPPER}} .sh-pagination' => 'gap: {{SIZE}}px; display:flex;'] ] );
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
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'btn_typo', 'selector' => '{{WRAPPER}} .sh-btn' ] );
        $this->add_control( 'btn_bg', [ 'label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'background-color: {{VALUE}};'] ] );
        $this->add_control( 'btn_color', [ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'color: {{VALUE}};'] ] );
        $this->add_control( 'btn_padding', [ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_control( 'btn_radius', [ 'label' => 'Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ] );
        $this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => 'btn_shadow', 'selector' => '{{WRAPPER}} .sh-btn' ] );
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
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => 'text_typo', 'selector' => '{{WRAPPER}} .sh-info-text' ] );
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