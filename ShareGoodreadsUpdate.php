<?php
/*
Plugin Name: Share Goodreads Update
Plugin URI: 
Description: Widget to give an overview of the books you are currently reading on Goodreads. Please note that your profile has to be set to public. Kindly inform me if you encounter any problems. 
Version: 1.1
Author: Stephan Elst
Author URI: https://stephanelst.nl
License: GPL2 or later
*/

/**
 *  Stylesheet.
 */
 
function SGRU_register_plugin_styles() {
	$SGRU_plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'style', $SGRU_plugin_url . '/style.css' );
}
// Register style sheet.
add_action( 'wp_enqueue_scripts', 'SGRU_register_plugin_styles' );


// The widget class
class SGRU_ReadingUpdate extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'SGRU_ReadingUpdate',
			__( 'Share Goodreads Update', 'share-goodreads-update' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$SGRU_defaults = array(
			'title' => 'Currently Reading',
			'text'  => 'https://www.goodreads.com/user/show/00000000-user',
			'links'	=> 'off',
		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $SGRU_defaults ) ); ?>

		<?php // Widget Title ?>
		<p>
			Title of the widget.<br>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'share-goodreads-update' ); ?></label>
			<input class="widefat"  id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		
		<?php // Text Field ?>
		<p>
			Enter your profile url below. <strong>Please note:</strong> your profile has to be set to public. <br>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php _e( 'Profile:', 'share-goodreads-update' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" placeholder="<?php echo esc_url( $SGRU_defaults['text'] ); ?>" value="<?php echo esc_url( $instance['text'] ); ?>" />
		</p>
		
		<?php // Checkbox ?>
		<p>
			The widget can link the books to it's profile on Goodreads.<br>
			<input class="checkbox" type="checkbox" <?php checked( $instance[ 'links' ], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'links' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'links' ) ); ?>" /> 
			<label for="<?php echo esc_attr( $this->get_field_id( 'links' ) ); ?>">Enable Goodread links</label>
		</p>
		
		<?php // Dropdown ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'select' )); ?>"><?php _e( 'Layout', 'share-goodreads-update' ); ?></label>
			<select name="<?php echo esc_attr($this->get_field_name( 'select' )); ?>" id="<?php echo esc_attr($this->get_field_id( 'select' )); ?>" class="widefat">
			<?php
			// Your options array
			$SGRU_options = array(
				'compact' => __( 'Compact', 'share-goodreads-update' ),
				'long' => __( 'Long', 'share-goodreads-update' ),
			);

			// Loop through options and add each one to the select dropdown
			foreach ( $SGRU_options as $key => $name ) {
				echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $select, $key, false ) . '>'. $name . '</option>';

			} ?>
			</select>
		</p>


	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['text'] = isset( $new_instance['text'] ) ? wp_strip_all_tags( $new_instance['text'] ) : '';
		$instance[ 'links' ] = $new_instance[ 'links' ];
		$instance['select']   = isset( $new_instance['select'] ) ? wp_strip_all_tags( $new_instance['select'] ) : '';
		return $instance;
	}
	
	
	// Display the widget
	public function widget( $args, $instance ) {

		extract( $args );
		// Check the widget options
		$SGRU_title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$SGRU_text = isset( $instance['text'] ) ? $instance['text'] : '';
		$SGRU_links = $instance[ 'links' ] ? 'true' : 'false';
		$SGRU_select   = isset( $instance['select'] ) ? $instance['select'] : '';
		

		// Before_widget hook 
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';
			
			if ( $SGRU_title ) {
				echo $before_title . esc_attr( $SGRU_title ) . $after_title;
			}
			
			if ( $SGRU_text ) {
				if ( !function_exists( 'file_get_html' ) ) { 
					require_once 'simple_html_dom.php'; 
				} 
				
				if(!empty($SGRU_text)){

					//get profile
					$SGRU_html = file_get_html(esc_url($SGRU_text));
					if(!empty($SGRU_html)){

						//get info from profile
						foreach($SGRU_html->find('div.Updates') as $SGRU_Book) {
							$SGRU_item['Cover'] = $SGRU_Book->find('img',0)->src;
							$SGRU_item['title'] = $SGRU_Book->find('a.bookTitle', 0)->plaintext;
							$SGRU_item['link'] = $SGRU_Book->find('a.bookTitle', 0)->href;
							$SGRU_item['Author'] = $SGRU_Book->find('a.authorName', 0)->plaintext;
							$SGRU_item['author_link'] = $SGRU_Book->find('a.authorName',0)->href;
							$SGRU_item['Progres'] = $SGRU_Book->find('a.greyText', 0)->plaintext;
							$SGRU_Books[] = $SGRU_item;
						}

						//print the info in a layout
						?>
						<div class="SGRU_block">
						<?php
						if( !empty($SGRU_Books )) {
							foreach( $SGRU_Books as $SGRU_item ) { 
								?>
								<div class='book <?php echo esc_attr($SGRU_select)?>'>
									<?php
									if( $SGRU_links == 'true' ){
									?>
									<div class='img-cover'><a href='https://goodreads.com/<?php echo esc_url($SGRU_item['link'])?>' target='_blank'><img class='cover <?php echo esc_attr($SGRU_select)?>' src='<?php echo esc_url($SGRU_item['Cover']) ?>'/></a></div>
									<p class='title <?php echo esc_attr($SGRU_select)?>'><a href='https://goodreads.com/<?php echo esc_url($SGRU_item['link'])?>' target='_blank'><?php echo esc_attr($SGRU_item['title'])?></a></p>
									<p class='author <?php echo esc_attr($SGRU_select)?>'><a href='https://goodreads.com/<?php echo esc_url($SGRU_item['author_link'])?>' target='_blank'><?php echo esc_attr($SGRU_item['Author'])?></a></p> 
									<?php
									}
									else {
									?>
									<div class='img-cover'><img class='cover <?php echo esc_attr($SGRU_select)?>' src='<?php echo esc_url($SGRU_item['Cover']) ?>'/></div>
									<p class='title <?php echo esc_attr($SGRU_select)?>'><?php echo esc_attr(esc_attr( $SGRU_item['title']))?></p>
									<p class='author <?php echo esc_attr($SGRU_select)?>'><?php echo esc_attr($SGRU_item['Author'])?></p>
									<?php
									}

									$SGRU_progress = trim($SGRU_item['Progres'], '(%)');
									$SGRU_progresstext = trim($SGRU_item['Progres'], '()'); 
									
									if ( $SGRU_progress == NULL ) { 
										$SGRU_progress = '0'; 
										$SGRU_progresstext = '0%'; 
									}
									//check if the string returned is in % or in pages. 
									
									if (strpos($SGRU_progresstext, 'page') !== false) {
											preg_match_all('!\d+!', $SGRU_progresstext, $matches);
											$SGRU_progress = round($matches[0][0]*100/$matches[0][1]);
											$SGRU_progresstext = $SGRU_progress . '%';
									}

									?>
									<div class='progress-wrapper'>
										<div class='progress'>
													<div class= 'progress-color' style='width:<?php echo esc_attr( $SGRU_progresstext ); ?>'>.</div>
													
										</div>
										<div class='progress-text'>
											<?php echo esc_attr( $SGRU_progresstext ); ?> 
										</div>
									</div>
								</div>
								<?php
							}  
						} else { 
							?>
							<p>Currently not reading anything </p>
							<?php
						}
					}				
				}	
						?>
						</div>
						<?php
			}

		// After widget
		echo $after_widget;

	}

}

// Register widget
function SGRU_register_ReadingUpdate() {
	register_widget( 'SGRU_ReadingUpdate' );
}
add_action( 'widgets_init', 'SGRU_register_ReadingUpdate' );