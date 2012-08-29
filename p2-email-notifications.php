<?php
/*
Plugin Name: P2 Email Notifications
Description: Basic email notification for mentions on P2
Version: 0.1
Author: Jeremy Felt, 10up
Author URI: http://10up.com
License: GPL2
*/

/*  Copyright 2012 Jeremy Felt (email: jeremy.felt@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'set_object_terms', 'p2_10up_send_mentions', 10, 6 );
function p2_10up_send_mentions( $post_id, $users, $tt_ids, $taxonomy_label, $append, $old_tt_ids ) {

	if ( 'mentions' != $taxonomy_label )
		return;

	if ( ! $notifications_sent = get_post_meta( $post_id, '_p2_notifications_sent', true ) )
		$notifications_sent = array();

	$new_user_mentions = array_diff( $users, $notifications_sent );

	if ( empty( $new_user_mentions ) )
		return;

	$current_post = get_post( $post_id );

	if ( ! $current_post )
		return;

	$post_link = get_permalink( $post_id );
	$p2_name = get_bloginfo( 'name' );
	$post_author = get_the_author_meta( 'display_name', $current_post->post_author );

	foreach ( $new_user_mentions as $user ) {
		$user_full = get_user_by( 'login', $user );
		wp_mail( $user_full->user_email, "You've been Mentioned by " . $post_author . "! [" . $p2_name . "]", "You've been mentioned by " . $post_author . " in " . $post_link . "\n\n" . $current_post->post_content );
	}

	update_post_meta( $post_id, '_p2_notifications_sent', $users );
}