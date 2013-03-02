<?php
/*
Plugin Name: P2 Email Notifications
Description: Basic email notification for mentions on P2
Version: 0.2
Author: Jeremy Felt, 10up
Author URI: http://10up.com
License: GPL2
*/

/*  Copyright 2012-2013 Jeremy Felt (email: jeremy.felt@gmail.com)

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

add_action( 'set_object_terms', 'p2_10up_send_mentions', 10, 4 );
/**
 * P2 handles it's mention magic by matching users with terms in a custom taxonomy. Whenever an update
 * is modified, whether it be a post or comment, P2 will search for any possible matching terms in the
 * content and then update the terms on the post accordingly.
 *
 * This allows us to hook into `set_object_terms` and perform our own actions when users are added to
 * the thread. Emails are sent once per user per thread. It would be nice one day to send an email for
 * each mention in a thread, as a conversation could occur for a while.
 *
 * @param $post_id int current post ID
 * @param $users array of terms, in this case users
 * @param $tt_ids array of taxonomy/term ids, not used
 * @param $taxonomy_label string taxonomy label
 */
function p2_10up_send_mentions( $post_id, $users, $tt_ids, $taxonomy_label ) {

	if ( 'mentions' !== $taxonomy_label )
		return;

	if ( ! $notifications_sent = get_post_meta( $post_id, '_p2_notifications_sent', true ) )
		$notifications_sent = array();

	/*
	 * We only send mentions to users that have not already been mentioned on the post. Because
	 * things are handled at the post level, it seems difficult so far to determine if this was
	 * just fired due to a comment or initial update.
	 */
	$new_user_mentions = array_diff( $users, $notifications_sent );

	if ( empty( $new_user_mentions ) )
		return;

	$current_post = get_post( $post_id );

	if ( ! $current_post || 'publish' !== $current_post->post_status )
		return;

	$post_link = get_permalink( $post_id );
	$p2_name = get_bloginfo( 'name' );
	$post_author = get_the_author_meta( 'display_name', $current_post->post_author );

	$email_subject = apply_filters( 'p2_10up_notification_subject', "You've been Mentioned by " . $post_author . "! [" . $p2_name . "]", $post_id, $post_author, $p2_name );
	$email_content = apply_filters( 'p2_10up_notification_body', "You've been mentioned by " . $post_author . " in " . $post_link . "\n\n" . $current_post->post_content, $post_id, $post_author, $post_link );

	$user_emails = array();

	foreach ( $new_user_mentions as $user ) {
		$user_full = get_user_by( 'login', $user );
		$user_emails[] = $user_full->user_email;
	}

	wp_mail( $user_emails, $email_subject, $email_content );

	update_post_meta( $post_id, '_p2_notifications_sent', $users );
}
