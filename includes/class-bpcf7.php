<?php

/**
 * This file contains a number of (increasingly complex) implementations of
 * BP_Group_Extension integrations of Contact Form 7 into BuddyPress groups.
 *
 * For more details, see:
 * - my BuddyCamp presentation:
 * - the BP_Group_Extension codex page: http://codex.buddypress.org/developer/plugin-development/group-extension-api/
 */

if ( class_exists( 'BP_Group_Extension' ) ) :

	/**
	 * Bare-bones implementation
	 */
	class BPCF7 extends BP_Group_Extension {
		public function __construct() {
			$this->name = __( 'Contact Forms', 'bp-cf7' );
			$this->slug = 'contact-forms';
		}

		public function display() {
			echo '<h2>' . __( 'Contact', 'bp-cf7' ) . '</h2>';
			echo do_shortcode( '[contact-form-7 id="318" title="My Group Form"]' );
		}
	}
	//bp_register_group_extension( 'BPCF7' );

	/**
	 * Group-specific settings, accessible via an Edit screen
	 */
	class BPCF7_2 extends BP_Group_Extension {
		public function __construct() {
			$this->name = __( 'Contact Forms', 'bp-cf7' );
			$this->slug = 'contact-forms';
			$this->enable_create_step = false;
		}

		protected function get_form_id() {
			$group_id = bp_get_current_group_id();
			$form_id = groups_get_groupmeta( $group_id, 'cf7_form_id' );

			if ( ! $form_id )
				$form_id = 318;

			return $form_id;
		}

		public function edit_screen() {
			$form_id = $this->get_form_id();
			?>

			<h2><?php echo esc_html( $this->name ) ?></h2>

			<label for="cf7_form_id"><?php _e( 'Form ID', 'bp-cf7' ) ?></label>
			<input id="cf7_form_id" name="cf7_form_id" value="<?php echo esc_attr( $form_id ) ?>" />
			<input type="submit" value="<?php _e( 'Submit', 'bp-cf7' ) ?>" />
			<?php
			wp_nonce_field( 'groups_edit_save_' . $this->slug );
		}

		public function edit_screen_save() {
			if ( ! isset( $_POST['cf7_form_id'] ) )
				return;

			check_admin_referer( 'groups_edit_save_' . $this->slug );

			$form_id = intval( $_POST['cf7_form_id'] );

			if ( groups_update_groupmeta( bp_get_current_group_id(), 'cf7_form_id', $form_id ) )
				bp_core_add_message( __( 'Yes!', 'bp-cf7' ) );
			else
				bp_core_add_message( __( 'Sadface.', 'bp-cf7' ), 'error' );

			bp_core_redirect( bp_get_group_permalink( groups_get_current_group() ) . '/admin/' . $this->slug . '/' );
		}

		public function display() {
			$form_id = $this->get_form_id();
			echo '<h2>' . __( 'Contact', 'bp-cf7' ) . '</h2>';
			$shortcode = '[contact-form-7 id="' . intval( $form_id ) . '" title="My Group Form"]';
			echo do_shortcode( $shortcode );
		}
	}
//	bp_register_group_extension( 'BPCF7_2' );

	/**
	 * Group-specific settings, with edit/create/admin interfaces
	 */
	class BPCF7_3 extends BP_Group_Extension {
		public function __construct() {
			$this->name = __( 'Contact Forms', 'bp-cf7' );
			$this->slug = 'contact-forms';

			$this->group_id = bp_get_current_group_id();
			if ( ! $this->group_id ) {
				$this->group_id = bp_get_new_group_id();
			}
		}

		protected function get_form_id() {
			$group_id = $this->group_id;
			$form_id = groups_get_groupmeta( $group_id, 'cf7_form_id' );

			if ( ! $form_id )
				$form_id = 318;

			return $form_id;
		}

		protected function form() {
			$form_id = $this->get_form_id();
			?>

			<label for="cf7_form_id"><?php _e( 'Form ID', 'bp-cf7' ) ?></label>
			<input id="cf7_form_id" name="cf7_form_id" value="<?php echo esc_attr( $form_id ) ?>" />
			<?php
		}

		protected function form_save() {
			$form_id = intval( $_POST['cf7_form_id'] );
			return groups_update_groupmeta( $this->group_id, 'cf7_form_id', $form_id );
		}

		public function edit_screen() {
			?><h2><?php echo esc_html( $this->name ) ?></h2><?php
			$this->form();
			echo '<input type="submit" value="' . __( 'Submit', 'bp-cf7' ) . '" />';
			wp_nonce_field( 'groups_edit_save_' . $this->slug );
		}

		public function edit_screen_save() {
			if ( ! isset( $_POST['cf7_form_id'] ) )
				return;

			check_admin_referer( 'groups_edit_save_' . $this->slug );

			if ( $this->form_save() )
				bp_core_add_message( __( 'Yes!', 'bp-cf7' ) );
			else
				bp_core_add_message( __( 'Sadface.', 'bp-cf7' ), 'error' );

			bp_core_redirect( bp_get_group_permalink( groups_get_current_group() ) . '/admin/' . $this->slug . '/' );
		}

		public function create_screen() {
			if ( ! bp_is_group_creation_step( $this->slug ) ) {
				return;
			}

			?><h2><?php echo esc_html( $this->name ) ?></h2><?php

			$this->form();
			wp_nonce_field( 'groups_create_save_' . $this->slug );
		}

		public function create_screen_save() {
			if ( ! isset( $_POST['cf7_form_id'] ) )
				return;

			$this->group_id = intval( $_POST['group_id'] );

			check_admin_referer( 'groups_create_save_' . $this->slug );

			$this->form_save();
		}

		public function admin_screen( $group_id ) {
			$this->group_id = intval( $group_id );
			$this->form();
		}

		public function admin_screen_save( $group_id ) {
			$this->group_id = intval( $group_id );
			$this->form_save();
		}

		public function display() {
			$form_id = $this->get_form_id();
			echo '<h2>' . __( 'Contact', 'bp-cf7' ) . '</h2>';
			$shortcode = '[contact-form-7 id="' . intval( $form_id ) . '" title="My Group Form"]';
			echo do_shortcode( $shortcode );
		}
	}
	bp_register_group_extension( 'BPCF7_3' );



endif; // class_exists( 'BP_Group_Extension' )

