<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

$html = '<h5>%1$s</h5><p><strong>Bro</strong> - %2$s Wish<s>cash</s>. %3$s<br><form action="%4$s" method="post" target="_top"><input type="hidden" name="cmd" value="_donations"><input type="hidden" name="business" value="%5$s"><input type="hidden" name="lc" value="US"><input type="hidden" name="item_name" value="STT2EXTAT WordPress Plugin"><input type="hidden" name="item_number" value="1"><input type="hidden" name="currency_code" value="USD"><input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted"><input type="image" src="%6$s" border="0" name="submit" alt="%7$s"><img alt="" border="0" src="%8$s" width="1" height="1"></form></p><p>%9$s <a alt="#TB_inline?height=400&amp;width=533&amp;inlineId=brocatPopup" class="dashicons dashicons-format-image thickbox" href="%10$s" title="%11$s"></a></p>';

$paypal_url   = 'https://www.paypal.com/cgi-bin/webscr';
$email_donate = 'contact@jevuska.com';
$paypal_btn   = STT2EXTAT_PLUGIN_URL . 'lib/assets/img/btn_donateCC_LG.gif';
$paypal_pixel = STT2EXTAT_PLUGIN_URL . 'lib/assets/img/pixel.gif';
$image_url    = STT2EXTAT_PLUGIN_URL . 'lib/assets/img/bro-cat.jpg';

printf( $html,
	__( 'Don&#39;t buy me a coffee.', 'stt2extat' ),
	__( 'It&#39;s my female cat name, and please don&#39;t bothering me about why "Bro"? why not "Sist"?, it was my fault and it was happened. She get pregnant right now and need more', 'stt2extat' ),
	__( 'Would you mind...?', 'stt2extat' ),
	esc_url( $paypal_url ),
	sanitize_email( $email_donate ),
	esc_url( $paypal_btn ),
	esc_attr__( 'PayPal - The safer, easier way to pay online!', 'stt2extat' ),
	esc_url( $paypal_pixel ),
	__( 'Oops... sorry, here my Bro', 'stt2extat' ),
	esc_url( $image_url ),
	wp_kses( __('Bro: <i>&quot;I need more Wish<s>cash</s>&quot;</i>', 'stt2extat'), array( 'i'=> array(), 's'=> array() ) )
);
wp_die();