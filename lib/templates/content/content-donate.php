<?php
/*
 * @package STT2EXTAT
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) || ! defined( 'STT2EXTAT_PLUGIN_FILE' ) )
	exit;

$image_url = 'http://i0.wp.com/www.jevuska.com/wp-content/uploads/2015/05/bro-cat.jpg';

printf( '<p>%s</p><p><strong>%s</strong> - %s Wish<s>cash</s>. %s<br><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - %s!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form></p><p>%s <a alt="#TB_inline?height=400&amp;width=533&amp;inlineId=brocatPopup" class="dashicons dashicons-format-image thickbox" href="%s" title="%s"></a></p>',
	__( 'Don&#39;t buy me a coffee.', 'stt2extat' ),
	__( 'Bro', 'stt2extat' ),
	__( 'It&#39;s my female cat name, and please don&#39;t bothering me about why "Bro"?, why not "Sist"?!. It was my fault and it was happened. She get pregnant right now and need more', 'stt2extat' ),
	__( 'Would you mind...?', 'stt2extat' ),
	__( 'The safer, easier way to pay online', 'stt2extat' ),
	__( 'Oops... sorry, here my Bro', 'stt2extat' ),
	esc_url( $image_url ),
	wp_kses( __('Bro: <i>&quot;I need more Wish<s>cash</s>&quot;</i>', 'stt2extat'), array( 'i'=> array(), 's'=> array() ) )
);
wp_die();