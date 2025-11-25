<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Shuriken_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() { return 'shuriken_table_widget'; }
	public function get_title() { return 'Shuriken Table'; }
	public function get_icon() { return 'eicon-table'; }
	
	// UPDATED: Now uses the custom "Shuriken Addons" category
	public function get_categories() { return [ 'shuriken_addons' ]; }

	protected function register_controls() {

		/* ----------------------------------------------------------------
		   CONTENT TAB
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_content',
			[ 'label' => 'Data Source', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]
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
				'label' => 'Select Saved Table',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
				'default' => array_key_first($options),
			]
		);

		$this->add_control(
			'override_desc',
			[ 'type' => \Elementor\Controls_Manager::RAW_HTML, 'raw' => '<small>Design is controlled in the <strong>Style</strong> tab.</small>', 'separator' => 'before' ]
		);

		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Table Wrapper
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_table',
			[ 'label' => 'Table Container', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

		$this->add_control(
			'table_border_style',
			[
				'label' => 'Border Type',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [ 'none' => 'None', 'solid' => 'Solid', 'double' => 'Double', 'dotted' => 'Dotted', 'dashed' => 'Dashed' ],
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
		$this->add_control(
			'table_radius',
			[ 'label' => 'Border Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .shuriken-table, {{WRAPPER}} .sh-table-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;' ] ]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[ 'name' => 'table_box_shadow', 'selector' => '{{WRAPPER}} .sh-table-wrapper' ]
		);

		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Header
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_head',
			[ 'label' => 'Table Header', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

		$this->add_control(
			'head_bg_color',
			[ 'label' => 'Background Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} th' => 'background-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'head_text_color',
			[ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} th' => 'color: {{VALUE}};' ] ]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'head_typography', 'selector' => '{{WRAPPER}} th' ]
		);
		$this->add_control(
			'head_padding',
			[ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ]
		);
		$this->add_control(
			'head_align',
			[ 'label' => 'Alignment', 'type' => \Elementor\Controls_Manager::CHOOSE, 'options' => [ 'left' => [ 'title' => 'Left', 'icon' => 'eicon-text-align-left' ], 'center' => [ 'title' => 'Center', 'icon' => 'eicon-text-align-center' ], 'right' => [ 'title' => 'Right', 'icon' => 'eicon-text-align-right' ] ], 'selectors' => [ '{{WRAPPER}} th' => 'text-align: {{VALUE}};' ] ]
		);

		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Body
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_body',
			[ 'label' => 'Table Body (Rows)', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

		$this->add_control(
			'row_odd_bg',
			[ 'label' => 'Odd Row Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} tr:nth-child(odd) td' => 'background-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'row_even_bg',
			[ 'label' => 'Even Row Background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} tr:nth-child(even) td' => 'background-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'row_hover_bg',
			[ 'label' => 'Row Hover Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} tr:hover td' => 'background-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'cell_text_color',
			[ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} td' => 'color: {{VALUE}};' ] ]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'body_typography', 'selector' => '{{WRAPPER}} td' ]
		);
		$this->add_control(
			'cell_border_color',
			[ 'label' => 'Cell Border Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} td, {{WRAPPER}} th' => 'border-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'cell_padding',
			[ 'label' => 'Cell Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ]
		);

		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Search
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_search',
			[ 'label' => 'Search Bar', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

		$this->add_control(
			'search_text_color',
			[ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-search-input' => 'color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'search_bg_color',
			[ 'label' => 'Background Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-search-input' => 'background-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'search_border_color',
			[ 'label' => 'Border Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-search-input' => 'border-color: {{VALUE}}; border-width: 1px; border-style: solid;' ] ]
		);
		$this->add_control(
			'search_radius',
			[ 'label' => 'Border Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .sh-search-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ]
		);
		$this->add_control(
			'search_padding',
			[ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .sh-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ]
		);

		$this->end_controls_section();


		/* ----------------------------------------------------------------
		   STYLE TAB: Buttons
		   ---------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_buttons',
			[ 'label' => 'Buttons', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab( 'tab_button_normal', [ 'label' => 'Normal' ] );
		$this->add_control(
			'button_text_color',
			[ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-btn' => 'color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'button_bg_color',
			[ 'label' => 'Background Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-btn' => 'background-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'button_border_color',
			[ 'label' => 'Border Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-btn' => 'border-color: {{VALUE}}; border-style: solid; border-width: 1px;' ] ]
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_button_hover', [ 'label' => 'Hover' ] );
		$this->add_control(
			'button_hover_text_color',
			[ 'label' => 'Text Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-btn:hover' => 'color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'button_hover_bg_color',
			[ 'label' => 'Background Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-btn:hover' => 'background-color: {{VALUE}};' ] ]
		);
		$this->add_control(
			'button_hover_border_color',
			[ 'label' => 'Border Color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .sh-btn:hover' => 'border-color: {{VALUE}};' ] ]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[ 'name' => 'button_typography', 'selector' => '{{WRAPPER}} .sh-btn' ]
		);
		$this->add_control(
			'button_radius',
			[ 'label' => 'Border Radius', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .sh-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ]
		);
		$this->add_control(
			'button_padding',
			[ 'label' => 'Padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'selectors' => [ '{{WRAPPER}} .sh-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$table_id = $settings['table_id'];

		if ( empty( $table_id ) ) { echo 'Please select a table.'; return; }

		$tables = get_option( 'shuriken_tables_db', [] );
		if ( ! isset( $tables[ $table_id ] ) ) { echo 'Table ID not found.'; return; }

		echo \Shuriken_Table_Manager::generate_table_html( $table_id, $tables[ $table_id ], true );
	}
}