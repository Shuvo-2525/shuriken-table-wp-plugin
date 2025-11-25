<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Shuriken_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() { return 'shuriken_table_widget'; }
	public function get_title() { return 'Shuriken Table'; }
	public function get_icon() { return 'eicon-table'; }
	public function get_categories() { return [ 'shuriken_addons' ]; }

	protected function register_controls() {

		/* ----------------------------------------------------------------
		   CONTENT TAB: Main Configuration
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_content',
			[ 'label' => 'Configuration', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]
		);

		$saved_tables = get_option( 'shuriken_tables_db', [] );
		$options = [];
		if ( ! empty( $saved_tables ) ) {
			foreach ( $saved_tables as $id => $table ) {
				$options[ $id ] = '#' . $id . ' - ' . $table['name'];
			}
		}

		$this->add_control(
			'table_id',
			[
				'label' => 'Select Data Source',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
				'default' => array_key_first($options),
			]
		);

		// NEW: Component Selector
		$this->add_control(
			'component_type',
			[
				'label' => 'Component to Render',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'all' => 'Complete Table (Default)',
					'table' => 'Data Table Only',
					'search' => 'Search Bar Only',
					'buttons' => 'Action Buttons Only',
					'pagination' => 'Pagination Controls Only',
				],
				'default' => 'all',
				'description' => 'Use this to place Search Bar and Table in different columns.',
			]
		);

		// NEW: Link ID
		$this->add_control(
			'custom_link_id',
			[
				'label' => 'Link ID',
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'e.g. my-table-1',
				'description' => '<strong>Required for split components.</strong> Give the Table and Search Bar the SAME ID to connect them.',
				'condition' => [ 'component_type!' => 'all' ],
			]
		);
		
		// NEW: Search Target
		$this->add_control(
			'search_target_col',
			[
				'label' => 'Search Specific Column',
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'e.g. email',
				'description' => 'Enter the exact field key (e.g., name, email) to restrict search. Leave empty to search all.',
				'condition' => [ 'component_type' => ['all', 'search'] ],
			]
		);

		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Table (Conditional)
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_table',
			[ 
				'label' => 'Table Styling', 
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [ 'component_type' => ['all', 'table'] ] 
			]
		);

		$this->add_control(
			'table_border_style',
			[
				'label' => 'Border Type',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [ 'none' => 'None', 'solid' => 'Solid', 'double' => 'Double', 'dotted' => 'Dotted' ],
				'selectors' => [ '{{WRAPPER}} .shuriken-table' => 'border-style: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'table_border_width',
			[ 'label' => 'Border Width', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .shuriken-table' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ]
		);
		$this->add_control(
			'table_border_color',
			[ 'label' => 'Border Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .shuriken-table' => 'border-color: {{VALUE}};' ] ]
		);
		
		$this->add_control('head_heading', ['type' => \Elementor\Controls_Manager::HEADING, 'label' => 'Header Row']);
		$this->add_control('head_bg', ['label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} th' => 'background-color: {{VALUE}};']]);
		$this->add_control('head_text', ['label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} th' => 'color: {{VALUE}};']]);
		
		$this->add_control('body_heading', ['type' => \Elementor\Controls_Manager::HEADING, 'label' => 'Body Rows', 'separator' => 'before']);
		$this->add_control('row_odd', ['label' => 'Odd Rows', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} tr:nth-child(odd) td' => 'background-color: {{VALUE}};']]);
		$this->add_control('row_even', ['label' => 'Even Rows', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} tr:nth-child(even) td' => 'background-color: {{VALUE}};']]);
		
		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Search (Conditional)
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_search',
			[ 
				'label' => 'Search Styling', 
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [ 'component_type' => ['all', 'search'] ]
			]
		);

		$this->add_control('search_text_color', ['label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-search-input' => 'color: {{VALUE}};']]);
		$this->add_control('search_bg_color', ['label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-search-input' => 'background-color: {{VALUE}};']]);
		$this->add_control('search_border_color', ['label' => 'Border Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-search-input' => 'border-color: {{VALUE}}; border-width: 1px; border-style: solid;']]);
		$this->add_control('search_padding', ['label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);

		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Buttons (Conditional)
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_buttons',
			[ 
				'label' => 'Button Styling', 
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [ 'component_type' => ['all', 'buttons', 'pagination'] ]
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );
		$this->start_controls_tab( 'tab_btn_normal', [ 'label' => 'Normal' ] );
		$this->add_control('btn_text', ['label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'color: {{VALUE}};']]);
		$this->add_control('btn_bg', ['label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'background-color: {{VALUE}};']]);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_btn_hover', [ 'label' => 'Hover' ] );
		$this->add_control('btn_text_h', ['label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-btn:hover' => 'color: {{VALUE}};']]);
		$this->add_control('btn_bg_h', ['label' => 'Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .sh-btn:hover' => 'background-color: {{VALUE}};']]);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		
		$this->add_control('btn_radius', ['label' => 'Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
		$this->add_control('btn_padding', ['label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => ['{{WRAPPER}} .sh-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$table_id = $settings['table_id'];

		if ( empty( $table_id ) ) { echo 'Please select a table.'; return; }

		$tables = get_option( 'shuriken_tables_db', [] );
		if ( ! isset( $tables[ $table_id ] ) ) { echo 'Table ID not found.'; return; }
        
        // Pass the new params to the core engine
		echo \Shuriken_Table_Manager::generate_table_html( 
		    $table_id, 
		    $tables[ $table_id ], 
		    true, 
		    $settings['component_type'],
		    $settings['custom_link_id'],
		    $settings['search_target_col']
		);
	}
}