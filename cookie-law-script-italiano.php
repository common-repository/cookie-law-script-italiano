<?php
/**
 * Plugin Name: Cookie Law Script Italiano
 * Plugin URI: https://github.com/AlbertoOlla/wp_cookie_law_script_italiano
 * Plugin Issues: https://github.com/AlbertoOlla/wp_cookie_law_script_italiano/issues
 * Description:  La soluzione più semplice per adempiere alla normativa italiana sui cookie.
 * Version: 1.0.0
 * Author: Alberto Olla <info@albertoolla.it>
 * Author URI: httpw://www.albertoolla.it
 * Tags: cookie, cookie law, cookie law script, italia, eu cookie, eu cookie law
 * Author e-mail: info@albertoolla.it
 * Text Domain: cookie-law-script-italiano
 */
  
if(!defined('ABSPATH')) exit('No direct script access allowed');

define('ICL_VERSION','1.0.0');

define('ICL_BUILD_DATE','2017-06-09');


// set plugin instance

$it_cookie_law_plugin = new it_Cookie_Law_Plugin();

/**

 * IT_Cookie_Law_Plugin final class.

 *

 * @class IT_Cookie_Law_Plugin

 * @version	1.0.0

 */

class it_Cookie_Law_Plugin {
    
        public $options;
        
	public $defaults = array(

		'acceptButtonText' => 'Chiudi',
                'infoLinkText' => 'Leggi informativa',
                'infoText' => "Questo sito utilizza i cookie, anche di terze parti: cliccando sul bottone, proseguendo nella navigazione, effettuando lo scroll della pagina o altro tipo di interazione col sito, acconsenti all'utilizzo dei cookie. Per maggiori informazioni o per negare il consenso a tutti o ad alcuni cookie, consulta l'informativa.",
                'acceptedCookieLife' => 3000,
                'acceptByScroll' => 0,
                'divEsternoColor' => '#000000',
                'divInfoTextColor' => '#ffffff',
                'buttonColor' => '#ffffff',
                'acceptButtonColor' => '#ff9900',
                'infoLinkColor' => '#ffffff',
            
	);
        
        public $links = [
          'brand' => 'https://corsidia.com/',
          'cookie-law-tutorial' => 'https://corsidia.com/materia/web-design/webmaster-tutorial/privacy-e-cookie-law#quadro-normativo',
          'original-plugin-github' => 'https://github.com/AlbertoOlla/wp_cookie_law_script_italiano',
          'preventive-blocking' => 'https://github.com/AlbertoOlla/wp_cookie_law_script_italiano#blocco-preventivo-come-applicarlo',
          'grante-pricavy' => 'http://www.garanteprivacy.it/cookie',
        ];
   
    
	/**

	 * Class constructor.

	 */

	public function __construct() {

		// settings

		$this->options = array_merge( $this->defaults, (array) get_option( 'icl-plugin-options', $this->defaults ) );


		// actions

		add_action( 'admin_init', array( $this, 'register_options' ) );
        
        add_action( 'admin_menu', array( $this, 'admin_menu_options' ) );
        
        add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'set_links' ) );
        
        add_action( 'admin_notices', array( $this, 'settings_errors' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ));
        
        add_action ( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );
        
        add_action ( 'admin_footer', array( $this, 'admin_footer') );
        
        add_action( 'init', array( $this, 'script_position' ) );
        
        add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );

	}
    
	/**

	 * Setting links.

	 */
     
    public function set_links($links) {
    
        $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=cookie-law-script-italiano') ) .'">'.__( 'Modifica', 'cookie-law-script-italiano' ).'</a>';
        return $links;
    }
    
	/**

	 * Add submenu.

	 */

	public function admin_menu_options() {

		add_menu_page(__('Cookie Law ITA','cookie-law-script-italiano'), __('Cookie Law ITA','cookie-law-script-italiano'), 'manage_options', 'cookie-law-script-italiano', array( $this, 'options_page' ) , 'none');
  
	}
    
    public function hexToRBG($hex){
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        return $r.','.$g.','.$b;
    }
    
	/**

	 * Load admin style inline, for menu icon only.

	 * 

	 * @return mixed

	 */

	public function admin_print_styles() {

		echo '

		<style>

			a.toplevel_page_cookie-law-script-italiano .wp-menu-image {

				background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAlVQTFRFAAAAqyYnOwAAkh8f5jo7oiYmri8xfB0d4Ds7BQEBAAEEAAABcBcZAQED/2NjAAECAQECqCkpzzIzAAAIoioqcRka6zMv1zY2/6GhIgUGhSAg/8/THQUGWhMUBAEB/0VIAQEBAQICAAAJAwMEvjIy7D4+8j4/9UBB8j4+2Dc4wTAx2DU2+T9A+EJC/UND9kFB8z4/9UBA/EJC+EBA2zY2XRQUOhUX7kFB/kND7z4+tS4ufyAgchsbgR8gwzEx8z8/90BAyjMzKAcI0jU2+UND6Dw8fx8fGgYGAAAAAAAAAAAAKwkJpioq9EBA8j4+nSYmAAAA/z8/+0FB+EBAkiQkDgIDAAAALAoKzzQ0+0FByTMzGAME/0BA/0JC3Tg4SRARAAAAoycn9UBA4Tk6TBIS/j8//0JCxzMzKQkJAAAAiiEh9D8/6Tw8ZRcX5zg5+0FB0TU1NwwNwS0u9z8/4jw8TRMTAAAAsSos9D9A5zw8axkZlSYn80FB1Tc3MQwMAAAAPwsL5jo6+kFByDIyNAoL0jQ09kJCtzExFgQFryor8z4+90BA0jU1qSgo90A//kJCoioqAAAAFgECti0u8T4//UJC7j091Dc44Tg5qyoqty4u2TY29UFBhyIiAAAAJAgIlyUl1zY38D9A+UJC+0FC80FC+UJC/EJC/0JD7T8/XxgZAAAAAgAARRARdB0dmyYnpCsrwjMz2zk55j0990FB3jo6TBMTAAAABQEBIQcHNQ0NVhUVfCAgcx4fJAgIAAAAAAAAAAAAAAAAAAAAAAAB9kFB////YjVzCQAAAMV0Uk5TAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAF0+BkYVUGQJBr+z6+vnqtEoHAU/c/ufCp67T+eJkCCvN/cdrMBoeOpHy3UQBA3780VYNGJT6ohUWvPaEETLc3Dwp1eRRAxG28Fss2Nw/CrL0aAYcw+xSGczsWAQHjfyZETXm2D893O1xDmf6vygIcOzxols1IhY1v6MXFHPW+vfl1cjc/PqHDg1FjbvR4vH6/vBoJTlMZ4Sgoz4CBw8bJA4Aw1XfAAAAtElEQVQoz03OvyuEAQCH8e8HScmPkKSkbpHN7q+Q4VLKGSTLlXRZ35lBdoPJwkDdYrOoG2+6TFaDbrgkXUeZuPfZnmd6JFSAzuhPImNr/mkPYnxDiee+ic1y8GgLNH2mCg924XrqI5nexpUDuOkls3bwrY6LmV6SuRo4Red+vpssHIICLt+TRXW0LB3D2cjbshOcW2kMJ9ZfFLJalMcaktwN/elWkhx9Vf/KviRJ5XWyCfa6v1FLJ38mtEveAAAAAElFTkSuQmCC);

				background-position: center center;

				background-repeat: no-repeat;

				background-size: auto auto;

			}

		</style>

		';

	}
    
    
	/**

	 * Register plugin options.

	 */

	public function register_options() {
        
        
		register_setting( 'icl-plugin-options', 'icl-plugin-options', array( $this, 'save_options' ) );


		add_settings_section( 'icl-plugin-options', '', '', 'icl-plugin-options' );
        

        add_settings_field("icl_page", __('La tua pagina Policy', 'cookie-law-script-italiano'), array( $this, 'icl_link' ), "icl-plugin-options", "icl-plugin-options");
        
        
        add_settings_field("icl_info_text", __('Messaggio per richiedere il consenso', 'cookie-law-script-italiano'), array( $this, 'icl_info_text' ), "icl-plugin-options", "icl-plugin-options");
        
        
        
        add_settings_field("icl_info_link_text", __('Testo per maggiori informazioni', 'cookie-law-script-italiano'), array( $this, 'icl_info_link_text' ), "icl-plugin-options", "icl-plugin-options");
        
        add_settings_field("icl_exit", __('Testo del pulsante', 'cookie-law-script-italiano'), array( $this, 'icl_exit' ), "icl-plugin-options", "icl-plugin-options");
        
        add_settings_field("icl_cookie_life", __('Durata del consenso', 'cookie-law-script-italiano'), array( $this, 'icl_cookie_life' ), "icl-plugin-options", "icl-plugin-options");
        
        add_settings_field("icl_scroll", __('Accetta con lo scroll', 'cookie-law-script-italiano'), array( $this, 'icl_scroll' ), "icl-plugin-options", "icl-plugin-options");
        
    	add_settings_field ( 'icl_out_color', __( 'Colore di sfondo', 'cookie-law-script-italiano' ), array( $this, 'icl_out_color' ), 'icl-plugin-options', 'icl-plugin-options' );

    	add_settings_field ( 'icl_info_text_color', __( 'Colore del testo', 'cookie-law-script-italiano' ), array( $this, 'icl_info_text_color' ),'icl-plugin-options', 'icl-plugin-options' );
        
        add_settings_field ( 'icl_info_link_color', __( 'Colore del link informativo', 'cookie-law-script-italiano' ), array( $this, 'icl_info_link_color' ),'icl-plugin-options', 'icl-plugin-options');
        
    	//add_settings_field ( 'icl_button_color', __( 'Colore del pulsante', 'cookie-law-script-italiano' ), array( $this, 'icl_button_color' ),'icl-plugin-options', 'icl-plugin-options');
        
    	add_settings_field ( 'icl_accept_button_color', __( 'Colore del pulsante', 'cookie-law-script-italiano' ), array( $this, 'icl_accept_button_color' ),'icl-plugin-options', 'icl-plugin-options');

    	

	}
        
	/**

	 * Display errors and notices.

	 */

	public function settings_errors() {

		settings_errors( 'icl_settings_errors' );

	}
    

	/**

	 * Save options.

	 * 

	 * @return void

	 */

	public function save_options( $input ) {

		// save options

		if ( isset( $_POST['save_icl_options'] ) ) {
                    
			add_settings_error( 'icl_settings_errors', 'icl_settings_updated', __( 'Modifiche Salvate.', 'cookie-law-script-italiano' ), 'updated' );

			// reset options

		} elseif ( isset( $_POST['reset_icl_options'] ) ) {

			$input = $this->defaults;

			add_settings_error( 'icl_settings_errors', 'icl_settings_restored', __( 'Impostazioni ripristinate correttamente.', 'cookie-law-script-italiano' ), 'updated' );

		}


		return $input;

	}
        
	public function icl_link() {

		echo '
		<div>
			<label>';
                
                $key = 'cookiePolicyURL';
                $link = "icl-plugin-options[".$key."]";

                echo '<select id="icl_link" name="'.$link.'">';	
                    echo '<option value="0">'.__('-- Nessuna --', 'cookie-law-script-italiano').'</option>';
                    $selected_page = @$this->options[$key];
                    $pages = get_pages();
                    foreach ( $pages as $page ) {
                        $option = '<option value="' . get_page_link( $page->ID ) . '" ';
                        $option .= ( get_page_link( $page->ID ) == $selected_page ) ? 'selected="selected"' : '';
                        $option .= '>';
                        $option .= $page->post_title;
                        $option .= '</option>';
                        echo $option;
                    }
                echo '</select>';
                
                echo '</label>';
                
                echo '<p class="description">' . __('Questa pagina deve contenere la Cookie Policy (l\'informativa estesa) del tuo sito.', 'cookie-law-script-italiano') . '</p>';
                
                echo '</div>';

	}
        
        
        public function icl_scroll() {

                        
		echo '
		<div>
			<label>';
                
                $key = 'acceptByScroll';
                $input = "icl-plugin-options[".$key."]";

                echo '<input type="checkbox" name="'.$input.'" value="1" ' . checked( true, (bool) @$this->options[$key], false ) . '/>';
                
               
                
                echo  __('Il visitatore accetta scrollando la pagina.', 'cookie-law-script-italiano') ;
                 echo '</label>';
                echo '</div>';

	}
        
        public function inputText($key,$placeholder = '', $description = '',$type = 'text') {
		echo '
		<div>
			<label>';
                
                $input = "icl-plugin-options[".$key."]";
                if(empty($placeholder))
                    $placeholder = @$this->defaults[$key];
                              
                echo '<input class="regular-text" type="'.$type.'" name="'.$input.'" id="'.$input.'" value="'.@$this->options[$key].'" placeholder="'.$placeholder.'" />';
               
                echo '</label>';
                
                if(!empty($description))
                    echo '<p class="description">' . $description . '</p>';
                
                echo '</div>';
        }
        
	public function icl_exit() {
                $key = 'acceptButtonText';
                              
                $this->inputText($key,'',__('Verrà mostrato nel pulsante per dare il consenso.', 'cookie-law-script-italiano'));
	}
        
	public function icl_info_link_text() {

                $key = 'infoLinkText';
                              
                $this->inputText($key,'',__('Testo cliccabile per la pagina.', 'cookie-law-script-italiano'));
	}
        
	public function icl_info_text() {

		echo '
		<div>
			<label>';
                
                $key = 'infoText';
                $input = "icl-plugin-options[".$key."]";
                $placeholder = "Questo sito utilizza i cookie, anche di terze parti: cliccando su acceptButtonText, proseguendo nella navigazione, effettuando lo scroll della pagina o altro tipo di interazione col sito, acconsenti all'utilizzo dei cookie. Per maggiori informazioni o per negare il consenso a tutti o ad alcuni cookie, consulta l'informativa.";
                              
  
                echo '<textarea name="'.$input.'" id="'.$input.'" class="large-text" cols="50" rows="10" placeholder="'.$placeholder.'">' . html_entity_decode( trim( wp_kses( @$this->options[$key], array( 'script' => array( 'type' => array(), 'src' => array(), 'charset' => array(), 'async' => array() ) ) ) ) ) . '</textarea>';
                echo '</label></div>';
	}
        
	public function icl_cookie_life() {

                $key = 'acceptedCookieLife';
                              
                $this->inputText($key,'',__('Durata del cookie in giorni.', 'cookie-law-script-italiano'),'number');
	}

        public function colorPicker($key,$description = '') {
		echo '
		<div>
			<label>';
                
                $input = "icl-plugin-options[".$key."]";
                
                echo '<input type="text" class="icl-color-field" name="'.$input.'" id="'.$input.'" value="'.esc_attr( @$this->options[$key] ).'">';
                echo '</label>';
                
                if(!empty($description))
                    echo '<p class="description">' . $description . '</p>';
                
                echo '</div>';
        }
        
	public function icl_out_color() {
                $key = 'divEsternoColor';
                        
                $this->colorPicker($key);
        }
       
        
	public function icl_info_text_color() {
                $key = 'divInfoTextColor';
                        
                $this->colorPicker($key);
        }
        
	public function icl_button_color() {
                $key = 'divButtonsColor';
                        
                $this->colorPicker($key);
        }
        
	public function icl_accept_button_color() {
                $key = 'acceptButtonColor';
                          
                $this->colorPicker($key);
        }
        
	public function icl_info_link_color() {
                $key = 'infoLinkColor';
                        
                $this->colorPicker($key);
        }
    
	/**

	 * Includes public script.

	 */

	public function public_scripts() {
        if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
            wp_register_script('icl-js', ''.plugins_url( 'assets/js/it_cookie_law.min.js', __FILE__ ).'', array( 'jquery' ), ICL_VERSION, true);
            wp_enqueue_script('icl-js');
        }
	}
    
	/**

	 * Admin includes script.

	 */

	public function admin_enqueue_scripts() {
    	wp_enqueue_style ( 'wp-color-picker' );
    	wp_enqueue_script ( 'wp-color-picker', false, array ( 'jquery' ) );
        
        wp_enqueue_style( 'icl-admin', plugins_url( 'assets/css/admin.css', __FILE__ ) );
	}
    
	/**

	 * Admin footer includes script.

	 */

	public function admin_footer() {
            
            $screen = get_current_screen();
            if ( $screen->id == 'toplevel_page_cookie-law-script-italiano' ) {
            ?>
                    <script>
                            jQuery(document).ready(function($){
                                    $('.icl-color-field').wpColorPicker();
                            });
                    </script>
            <?php			}		
	}
    
	/**

	 * Enqueue frontend scripts.

	 * 

	 * @return mixed

	 */

	public function script_position() {

		// break on admin side

		if ( is_admin() ) {

			return;

		}

        add_action( 'wp_head', array( $this, 'script_load' ), 99 );


	}
    

	/**

	 * Load scripts.

	 * 

	 * @return string

	 */

	public function script_load() {


		$code = "

		<script>
            window.itCookieLaw = { ";
                
                foreach ($this->options as $key => $value) {
                    if( $value[0] == '#' && strlen($value) === 7 ){
                        switch ($key) {
                            case 'divEsternoColor':
                                $value = "background-color: rgba(".$this->hexToRBG($value).", 0.7);";
                                break;
                            
                            default:
                                $value = "color: rgb(".$this->hexToRBG($value).");";
                                break;
                        }
                    }
                    
                    if($key === 'acceptByScroll')
                        $value = (bool) $value;
                        
                    $code .= ( $this->options[$key] ? '"'.$key.'":"'.esc_js($value).'",' : '' );
                }
               
                
            $code .= "};
		</script>";


		echo apply_filters( 'code', $code );

	}
    

	/**

	 * Load admin options page.

	 * 

	 * @return void

	 */

	public function options_page() {


		?>

		<div class="wrap">

			<div class="icl-container">

			<?php 
            
			echo '

				<a class="icl-logo" href="'.$this->links['brand'].'" target="_blank"><img src="data:image/png;base64,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"/></a>

				<p>

					' . sprintf( __( "Questo plugin, consente di adempiere facilmente alla <a href=\"%s\" rel=\"nofollow\" target=\"_blank\">normativa sui cookie italiana</a> (che dovresti leggere con attenzione), in particolare consente di implementare:", 'cookie-law-script-italiano' ), $this->links['grante-pricavy'] ). '

				</p>
                                
                                <ul>
                                    <li>Il <b>blocco preventivo</b> di tutti gli elementi interni ed esterni che fanno uso di cookie;</li>
                                    <li>La <b>presentazione del banner</b> (l\'Informativa Breve) ai soli utenti che non hanno ancora accettato la cookie policy;</li>
                                    <li>La <b>registrazione del consenso</b> dell\'utente espresso con la continuazione della navigazione mediante scroll della pagina o click sul pulsante di accettazione del banner;</li>
                                    <li>Lo <b>sblocco di tutti gli elementi</b> preventivamente bloccati per i soli utenti che hanno espresso il consenso con le modalità descritte nel banner (basta anche solo uno scroll del mouse, se selezioni l\'opzione).</li>
                                </ul>
                                    
				<p>

					' . sprintf( __( "Maggiori informazioni sulla cookie law e sulle modalità di adempimento <a href=\"%s\" target=\"_blank\">sono disponibili qui.</a>", 'cookie-law-script-italiano' ), $this->links['cookie-law-tutorial'] ).  '
                    
				</p>
                
				<p>

					'. sprintf( __( '<b>Nota bene:</b> il punto di forza di questo plugin è la sua <b>semplicità</b>, se conosci un po\' di Javascript ti invitiamo a dare un\'occhiata a come funziona! Il file principale è <a href=\'%s\' target=\'_blank\'>solo questo</a> ed è ben commentato!', 'cookie-law-script-italiano' ), $this->links['original-plugin-github'] ).'
                    
				</p>
                
                ';
                
		?>

				<form id="icl-tabs" action="options.php" method="post">

				<?php

				settings_fields( 'icl-plugin-options' );

				do_settings_sections( 'icl-plugin-options' );


				echo '	<p class="submit">';

				submit_button( '', 'primary', 'save_icl_options', false );

				echo ' ';

				submit_button( __( 'Ripristina i valori originali', 'cookie-law-script-italiano' ), 'secondary', 'reset_icl_options', false );

				echo '	</p>';

				?>

				</form>

					<?php     
                                        echo '
                                            
<b>'.__( 'Cos\'è il blocco preventivo e come si applica?', 'cookie-law-script-italiano' ).'</b>
				<p>
                                    
                                    
                                    
					' . sprintf( __( "Il modo più sicuro per applicare il blocco preventivo consiste nel modificare l'HTML degli elementi esterni (script e iframe), <a href=\"%s\" target=\"_blank\">ecco come puoi fare.</a>", 'cookie-law-script-italiano' ), $this->links['preventive-blocking'] ).  '

				</p>
                                <br>
                                        <b>'.__( 'Hai ancora dubbi sulla cookie policy?', 'cookie-law-script-italiano' ).'</b>
				<p>
                                    
                                    
                                    
					' . sprintf( __( "Per qualsiasi informazione puoi visitare la <a href=\"%s\" target=\"_blank\">documentazione del plugin ufficile.</a>", 'cookie-law-script-italiano' ), $this->links['original-plugin-github'] ).  '

				</p>';
                

					?>

			</div>

			<div class="clear"></div>

		</div>

		<?php

	}
    
}
