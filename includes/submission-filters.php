<?php

/**
 * Add BP Groups filter to submission list filters
 *
 * @since  alpha
 *
 * @param  string $output HTML Markup.
 * @param  array $atts    Shortcode Attributes.
 * @return string         HTML Markup.
 */
function badgeos_bp_group_submission_filters( $output, $atts ) {

	if ( 'false' !== $atts['show_filter'] && badgeos_user_can_manage_submissions() ) {
		$bp_public_groups = groups_get_groups(
			array(
				'orderby' => 'name',
				'order'   => 'ASC'
			)
		);

		$selected_id = isset( $atts['group_id'] ) ? absint( $atts['group_id'] ) : 0;

		if ( $bp_public_groups['total'] > 0 ) {
			$output .= '<div class="badgeos-feedback-filter badgeos-feedback-bp-groups">';
				$output .= '<label for="group_id">' . __( 'Group:', 'badgeos' ) . '</label>';
				$output .= ' <select name="group_id" id="group_id">';
					$output .= '<option value="0">' . __( 'All', 'badgeos-community' ) . '</option>';
				foreach( $bp_public_groups['groups'] as $group ) {
					$output .= '<option value="' . absint( $group->id ) . '" ' . selected( $selected_id, $group->id, false ) . '>' . esc_attr( $group->name ) . '</option>';
				}
				$output .= '</select>';
			$output .= '</div>';
		}
	}

	return $output;
}
add_filter( 'badgeos_render_feedback_filters', 'badgeos_bp_group_submission_filters', 10, 2 );

/**
 * Limit feedback query to specific BP group members.
 *
 * @since  alpha
 *
 * @param  array $args Feedback args.
 * @return array       Feedback args.
 */
function badgeos_bp_filter_feedback_args( $args ) {

	if ( isset( $_REQUEST['group_id'] ) ) {
		$bp_member_ids = badgeos_bp_get_group_member_ids_from_group( $_REQUEST['group_id'] );
		if ( ! empty( $bp_member_ids ) ) {
			$args['author__in'] = $bp_member_ids;
		}
	}

	return $args;
}
add_filter( 'badgeos_get_feedback_args', 'badgeos_bp_filter_feedback_args' );

/**
 * Get user IDs for a given BP group.
 *
 * @since  alpha
 *
 * @param  integer $group_id BuddyPress Group ID.
 * @return array             User IDs, or empty array.
 */
function badgeos_bp_get_group_member_ids_from_group( $group_id = 0 ) {
	$group_members = groups_get_group_members( array( 'group_id' => absint( $group_id ) ) );
	return ( ! empty( $group_members['members'] ) ) ? wp_list_pluck( $group_members['members'], 'ID' ) : array();
}

/**
 * Register group_id filter selector for submission list shortcode.
 *
 * @since  alpha
 *
 * @param  array $atts     Available attributes.
 * @param  array $defaults Default attributes.
 * @param  array $passed   Passed attributes.
 * @return array           Available attributes.
 */
function badgeos_bp_submissions_atts( $atts, $defaults, $passed ) {
	$atts['group_id'] = isset( $passed['group_id'] ) ? absint( $passed['group_id'] ) : 0;
	$atts['filters']['group_id'] = '.badgeos-feedback-bp-groups select';
	return $atts;
}
add_filter( 'shortcode_atts_badgeos_submissions', 'badgeos_bp_submissions_atts', 10, 3 );
