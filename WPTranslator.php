<?php

/**
 * @link              http://WPTranslator.com
 * @since             1.0.0
 * @package           WPTranslator
 *
 * @wordpress-plugin
 * Plugin Name:       WPTranslator
 * Plugin URI:        http://WPTranslator.com/
 * Description:       WPTranslator allows visitors to translate your site into any language
 * Version:           1.0.0
 * Author:            Wordpress Translator
 * Author URI:        http://WPTranslator.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       WPTranslator
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}


function activate_WPTranslator() {
	$data = array(
	    'WPTranslator_title' => 'Wordpress Translator',
	);
	$data = get_option('WPTranslator');
	WPTranslator::load_defaults($data);

	add_option('WPTranslator', $data);
}


function deactivate_WPTranslator() {
	// on delete
}

register_activation_hook( __FILE__, 'activate_WPTranslator' );
register_deactivation_hook( __FILE__, 'deactivate_WPTranslator' );


add_action('widgets_init', array('WPTranslator', 'register'));
add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('WPTranslator', 'settings_link'));
add_action('admin_menu', array('WPTranslator', 'admin_menu'));
add_action('init', array('WPTranslator', 'enqueue_scripts'));
add_shortcode('WPTranslator', array('WPTranslator', 'get_widget_code'));

$wpt_languages = array('af','sq','am','ar','hy','az','eu','be','bn','bs','bg','ca','ceb','ny','zh-CN','zh-TW','co','hr','cs','da','nl','en','eo','et','tl','fi','fr','fy','gl','ka','de','el','gu','ht','ha','haw','iw','hi','hmn','hu','is','ig','id','ga','it','ja','jw','kn','kk','km','ko','ku','ky','lo','la','lv','lt','lb','mk','mg','ms','ml','mt','mi','mr','mn','my','ne','no','ps','fa','pl','pt','pa','ro','ru','sm','gd','sr','st','sn','sd','si','sk','sl','so','es','su','sw','sv','tg','ta','te','th','tr','uk','ur','uz','vi','cy','xh','yi','yo','zu');


class WPTranslator extends WP_Widget {

    public static function settings_link($links) {
        $settings_link = array('<a href="' . admin_url('options-general.php?page=WPTranslator_options') . '">WPTranslate Settings</a>');
        return array_merge($links, $settings_link);
    }

    public static function control() {
        $data = get_option('WPTranslator');
        ?>
        <p><label>Title: <input name="WPTranslator_title" type="text" class="widefat" value="<?php echo $data['WPTranslator_title']; ?>"/></label></p>
        <p>For options click Settings > WPTranslator on the main navigation bar</p>
        <?php
        if (isset($_POST['WPTranslator_title'])){
            $data['WPTranslator_title'] = esc_attr($_POST['WPTranslator_title']);
            update_option('WPTranslator', $data);
        }
    }

    public static function enqueue_scripts() {
        $data = get_option('WPTranslator');
        WPTranslator::load_defaults($data);
        $wp_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
        wp_enqueue_script('jquery');
    }

    public function widget($args, $instance) {
        $data = get_option('WPTranslator');
        WPTranslator::load_defaults($data);

        echo $args['before_widget'];
        echo $args['before_title'] . $data['WPTranslator_title'] . $args['after_title'];
        if(empty($data['widget_code']))
            echo 'Setup WPTranslate here: <a href="' . admin_url('options-general.php?page=WPTranslator_options') . '">WPTranslate Settings</a>';
        else
            echo $data['widget_code'];

        if(isset($_SERVER['HTTP_X_GT_LANG']) and in_array($_SERVER['HTTP_X_GT_LANG'], $wpt_languages)) {
	        echo '<script>jQuery(document).ready(function() {jQuery(\'.switcher div.selected a\').html(jQuery(".switcher div.option a[onclick*=\'|'.esc_js($_SERVER['HTTP_X_GT_LANG']).'\']").html())});</script>';
	    }

        echo $args['after_widget'];
    }

    public static function widget2($args) {
        $data = get_option('WPTranslator');
        WPTranslator::load_defaults($data);

        echo $args['before_widget'];
        echo $args['before_title'] . $data['WPTranslator_title'] . $args['after_title'];
        if(empty($data['widget_code']))
            echo 'Setup WPTranslate here: <a href="' . admin_url('options-general.php?page=WPTranslator_options') . '">WPTranslate Settings</a>';
        else
            echo $data['widget_code'];

        if(isset($_SERVER['HTTP_X_GT_LANG']) and in_array($_SERVER['HTTP_X_GT_LANG'], $wpt_languages)) {
	        echo '<script>jQuery(document).ready(function() {jQuery(\'.switcher div.selected a\').html(jQuery(".switcher div.option a[onclick*=\'|'.esc_js($_SERVER['HTTP_X_GT_LANG']).'\']").html())});</script>';
	    }

        echo $args['after_widget'];
    }

    function get_widget_code($atts) {
        $data = get_option('WPTranslator');
        WPTranslator::load_defaults($data);

        if(empty($data['widget_code']))
            return 'Setup WPTranslate here: <a href="' . admin_url('options-general.php?page=WPTranslator_options') . '">WPTranslate Settings</a>';
        else {
	        if(isset($_SERVER['HTTP_X_GT_LANG']) and in_array($_SERVER['HTTP_X_GT_LANG'], $wpt_languages)) {
	            return $data['widget_code'] . '<script>jQuery(document).ready(function() {jQuery(\'.switcher div.selected a\').html(jQuery(".switcher div.option a[onclick*=\'|'.esc_js($_SERVER['HTTP_X_GT_LANG']).'\']").html())});</script>';
	        } else
            	return $data['widget_code'];
        }
    }

    public static function register() {
        wp_register_sidebar_widget('WPTranslator', 'WPTranslator', array('WPTranslator', 'widget2'), array('description' => __('Google Automatic Translations')));
        wp_register_widget_control('WPTranslator', 'WPTranslator', array('WPTranslator', 'control'));
    }

    public static function admin_menu() {
        add_options_page('WPTranslator Options', 'WPTranslator', 'administrator', 'WPTranslator_options', array('WPTranslator', 'options'));
    }

    public static function options() {
        ?>
        <div class="wrap">
        <div id="icon-options-general" class="icon32"><br/></div>
        <h2><img src="<?php echo trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) ); ?>/WPTranslator.png" border="0" title="WPTranslator" alt="WPTranslator"></h2>
        <?php
        if(isset($_POST['save']) and $_POST['save'])
            WPTranslator::control_options();
        $data = get_option('WPTranslator');
        WPTranslator::load_defaults($data);

        $site_url = site_url();
        $wp_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );

        extract($data);


$script = <<<EOF

var languages = ['Afrikaans','Albanian','Amharic','Arabic','Armenian','Azerbaijani','Basque','Belarusian','Bengali','Bosnian','Bulgarian','Catalan','Cebuano','Chichewa','Chinese (Simplified)','Chinese (Traditional)','Corsican','Croatian','Czech','Danish','Dutch','English','Esperanto','Estonian','Filipino','Finnish','French','Frisian','Galician','Georgian','German','Greek','Gujarati','Haitian Creole','Hausa','Hawaiian','Hebrew','Hindi','Hmong','Hungarian','Icelandic','Igbo','Indonesian','Irish','Italian','Japanese','Javanese','Kannada','Kazakh','Khmer','Korean','Kurdish (Kurmanji)','Kyrgyz','Lao','Latin','Latvian','Lithuanian','Luxembourgish','Macedonian','Malagasy','Malay','Malayalam','Maltese','Maori','Marathi','Mongolian','Myanmar (Burmese)','Nepali','Norwegian','Pashto','Persian','Polish','Portuguese','Punjabi','Romanian','Russian','Samoan','Scottish Gaelic','Serbian','Sesotho','Shona','Sindhi','Sinhala','Slovak','Slovenian','Somali','Spanish','Sudanese','Swahili','Swedish','Tajik','Tamil','Telugu','Thai','Turkish','Ukrainian','Urdu','Uzbek','Vietnamese','Welsh','Xhosa','Yiddish','Yoruba','Zulu'];
var language_codes = ['af','sq','am','ar','hy','az','eu','be','bn','bs','bg','ca','ceb','ny','zh-CN','zh-TW','co','hr','cs','da','nl','en','eo','et','tl','fi','fr','fy','gl','ka','de','el','gu','ht','ha','haw','iw','hi','hmn','hu','is','ig','id','ga','it','ja','jw','kn','kk','km','ko','ku','ky','lo','la','lv','lt','lb','mk','mg','ms','ml','mt','mi','mr','mn','my','ne','no','ps','fa','pl','pt','pa','ro','ru','sm','gd','sr','st','sn','sd','si','sk','sl','so','es','su','sw','sv','tg','ta','te','th','tr','uk','ur','uz','vi','cy','xh','yi','yo','zu'];

function RefreshDoWidgetCode() {
    var new_line = "\\n";
    var widget_preview = '';
    var widget_code = '';
    var translation_method = 'onfly'; //jQuery('#translation_method').val();
    var widget_look = jQuery('#widget_look').val();
    var default_language = jQuery('#default_language').val();
    var flag_size = jQuery('#flag_size').val();
    var new_window = jQuery('#new_window:checked').length > 0 ? true : false;
    var analytics = jQuery('#analytics:checked').length > 0 ? true : false;


    jQuery('#new_window_option').hide();


    if(widget_look == 'dropdown' || widget_look == 'flags_dropdown') {
        jQuery('#dropdown_languages_option').show();
    } else {
        jQuery('#dropdown_languages_option').hide();
    }

    if(widget_look == 'flags' || widget_look == 'flags_dropdown') {
        jQuery('#flag_languages_option').show();
    } else {
        jQuery('#flag_languages_option').hide();
    }

    if(widget_look == 'flags' || widget_look == 'dropdown') {
        jQuery('#line_break_option').hide();
    } else {
        jQuery('#line_break_option').show();
    }

    if(widget_look == 'dropdown') {
        jQuery('#flag_size_option').hide();
    } else {
        jQuery('#flag_size_option').show();
    }

    if(translation_method == 'google_default') {
        included_languages = '';
        jQuery.each(languages, function(i, val) {
            lang = language_codes[i];
            if(jQuery('#incl_langs'+lang+':checked').length) {
                lang_name = val;
                included_languages += ','+lang;
            }
        });

        widget_preview += '<div id="google_translate_element"></div>'+new_line;
        widget_preview += '<script type="text/javascript">'+new_line;
        widget_preview += 'function googleTranslateElementInit() {new google.translate.TranslateElement({pageLanguage: \'';
        widget_preview += default_language;
        widget_preview += '\', layout: google.translate.TranslateElement.InlineLayout.SIMPLE';
        widget_preview += ', autoDisplay: false';
        widget_preview += ', includedLanguages: \'';
        widget_preview += included_languages;
        widget_preview += "'}, 'google_translate_element');}"+new_line;
        widget_preview += '<\/script>';
        widget_preview += '<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"><\/script>'+new_line;
    } else if(translation_method == 'on_fly' || translation_method == 'redirect' || translation_method == 'onfly') {
         // Adding dropdown
        if(widget_look == 'dropdown' || widget_look == 'flags_dropdown' /* jQuery('#show_dropdown:checked').length */) {
            widget_preview += '<select style="border-radius: 5px;" onchange="dropdownselector(this);">';
            widget_preview += '<option value="">Select Language</option>';
            jQuery.each(languages, function(i, val) {
                lang = language_codes[i];
                if(jQuery('#incl_langs'+lang+':checked').length) {
                    lang_name = val;
                    widget_preview += '<option value="'+default_language+'|'+lang+'">'+lang_name+'</option>';
                }
            });
            widget_preview += '<option value="WPTranslator"><a href="http://WPTranslator.com">Wordpress Translator</a></option>';
            widget_preview += '</select>';
            widget_preview += '<br />';
        }


        // Adding flags
        if(widget_look == 'flags' || widget_look == 'flags_dropdown' /* jQuery('#show_flags:checked').length */) {
            //console.log('adding flags');
            jQuery.each(languages, function(i, val) {
                lang = language_codes[i];
                if(jQuery('#fincl_langs'+lang+':checked').length) {
                    lang_name = val;
                    var href = '#';
                   
                    widget_preview += '<a href="'+href+'" onclick="dropdownselector(\''+default_language+'|'+lang+'\');return false;" title="'+lang_name+'" class="wptflag nturl"><img src="{$site_url}/wp-content/plugins/wptranslator/flags/'+lang+'.png" height="'+flag_size+'" width="'+flag_size+'" alt="'+lang_name+'" /></a>';
                }
            });


        }



        // Adding onfly html and css
        if(translation_method == 'onfly') {
            //console.log('adding onfly html, css and javascript');

            widget_code += '<style type="text/css">'+new_line;
            widget_code += '<!--'+new_line;
            widget_code += "#goog-gt-tt {display:none !important;}"+new_line;
            widget_code += ".goog-te-banner-frame {display:none !important;}"+new_line;
            widget_code += ".goog-te-menu-value:hover {text-decoration:none !important;}"+new_line;
            widget_code += "body {top:0 !important;}"+new_line;
            widget_code += "#google_translate_element2 {display:none!important;}"+new_line;
            widget_code += '-->'+new_line;
            widget_code += '</style>'+new_line+new_line;
            widget_code += '<div id="google_translate_element2"></div>'+new_line;
            widget_code += '<script type="text/javascript">'+new_line;
            widget_code += 'function googleTranslateElementInit2() {new google.translate.TranslateElement({pageLanguage: \'';
            widget_code += default_language;
            widget_code += '\',autoDisplay: false';
            //if(analytics)
            //	widget_code += ",gaTrack: (typeof ga!='undefined'),gaId: (typeof ga!='undefined' ? ga.getAll()[0].get('trackingId') : '')";
            widget_code += "}, 'google_translate_element2');}"+new_line;
            widget_code += '<\/script>';
            widget_code += '<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"><\/script>'+new_line;
        }

       

        // Adding javascript

        widget_code += new_line+new_line;
        widget_code += '<script type="text/javascript">'+new_line;
       if(translation_method == 'redirect' && new_window) {
            widget_code += 'if(top.location!=self.location)top.location=self.location;'+new_line;
            widget_code += "window['_tipoff']=function(){};window['_tipon']=function(a){};"+new_line;
            if(analytics)
                widget_code += "function dropdownselector(lang_pair) {if(lang_pair.value=='WPTranslator'){window.location.href='http://WPTranslator.com';return;}if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;var lang=lang_pair.split('|')[1];if(typeof _gaq!='undefined'){_gaq.push(['_trackEvent', 'WPTranslator', lang, location.pathname+location.search]);}else {if(typeof ga!='undefined')ga('send', 'event', 'WPTranslator', lang, location.pathname+location.search);}if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')openTab(unescape(gfg('u')));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')openTab('//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href));else openTab('//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u')));}"+new_line;
            else
                widget_code += "function dropdownselector(lang_pair) {if(lang_pair.value=='WPTranslator'){window.location.href='http://WPTranslator.com';return;}if(lang_pair.value)lang_pair=lang_pair.value;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;else if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')openTab(unescape(gfg('u')));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')openTab('//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href));else openTab('//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u')));}"+new_line;
            widget_code += 'function gfg(name) {name=name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");var regexS="[\\?&]"+name+"=([^&#]*)";var regex=new RegExp(regexS);var results=regex.exec(location.href);if(results==null)return "";return results[1];}'+new_line;
            widget_code += "function openTab(url) {var form=document.createElement('form');form.method='post';form.action=url;form.target='_blank';document.body.appendChild(form);form.submit();}"+new_line;
        } else if(translation_method == 'redirect') {
            widget_code += 'if(top.location!=self.location)top.location=self.location;'+new_line;
            widget_code += "window['_tipoff']=function(){};window['_tipon']=function(a){};"+new_line;
            if(analytics)
                widget_code += "function dropdownselector(lang_pair) {if(lang_pair.value=='WPTranslator'){window.location.href='http://WPTranslator.com';return;}if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;var lang=lang_pair.split('|')[1];if(typeof _gaq!='undefined'){_gaq.push(['_trackEvent', 'WPTranslator', lang, location.pathname+location.search]);}else {if(typeof ga!='undefined')ga('send', 'event', 'WPTranslator', lang, location.pathname+location.search);}if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')location.href=unescape(gfg('u'));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')location.href='//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href);else location.href='//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u'));}"+new_line;
            else
                widget_code += "function dropdownselector(lang_pair) {if(lang_pair.value=='WPTranslator'){window.location.href='http://WPTranslator.com';return;}if(lang_pair.value)lang_pair=lang_pair.value;if(location.hostname!='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')return;else if(location.hostname=='translate.googleusercontent.com' && lang_pair=='"+default_language+"|"+default_language+"')location.href=unescape(gfg('u'));else if(location.hostname!='translate.googleusercontent.com' && lang_pair!='"+default_language+"|"+default_language+"')location.href='//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+escape(location.href);else location.href='//translate.google.com/translate?client=tmpg&hl=en&langpair='+lang_pair+'&u='+unescape(gfg('u'));}"+new_line;
            widget_code += 'function gfg(name) {name=name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");var regexS="[\\?&]"+name+"=([^&#]*)";var regex=new RegExp(regexS);var results=regex.exec(location.href);if(results==null)return "";return results[1];}'+new_line;
        } else if(translation_method == 'onfly') {
            widget_code += "function WPTranslatorFireEvent(element,event){try{if(document.createEventObject){var evt=document.createEventObject();element.fireEvent('on'+event,evt)}else{var evt=document.createEvent('HTMLEvents');evt.initEvent(event,true,true);element.dispatchEvent(evt)}}catch(e){}}function dropdownselector(lang_pair){if(lang_pair.value=='WPTranslator'){window.location.href='http://WPTranslator.com';return;}if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];var teCombo;var sel=document.getElementsByTagName('select');for(var i=0;i<sel.length;i++)if(sel[i].className=='goog-te-combo')teCombo=sel[i];if(document.getElementById('google_translate_element2')==null||document.getElementById('google_translate_element2').innerHTML.length==0||teCombo.length==0||teCombo.innerHTML.length==0){setTimeout(function(){dropdownselector(lang_pair)},500)}else{teCombo.value=lang;WPTranslatorFireEvent(teCombo,'change');WPTranslatorFireEvent(teCombo,'change')}}"+new_line;
        }

        widget_code += '<\/script>'+new_line;

    }

    widget_code = widget_preview + widget_code;

    jQuery('#widget_code').val(widget_code);

    ShowWidgetPreview(widget_preview);

}

function ShowWidgetPreview(widget_preview) {
    widget_preview = widget_preview.replace(/javascript:dropdownselector/g, 'javascript:void')
    widget_preview = widget_preview.replace('onchange="dropdownselector(this);"', '');
    widget_preview = widget_preview.replace('if(jQuery.cookie', 'if(false && jQuery.cookie');
    jQuery('#widget_preview').html(widget_preview);
}


jQuery('#new_window').attr('checked', '$new_window'.length > 0);
jQuery('#analytics').attr('checked', '$analytics'.length > 0);
jQuery('#load_jquery').attr('checked', '$load_jquery'.length > 0);

jQuery('#default_language').val('$default_language');
//jQuery('#translation_method').val('$translation_method');
jQuery('#widget_look').val('$widget_look');
jQuery('#flag_size').val('$flag_size');

if('$widget_look' == 'dropdown' || '$widget_look' == 'flags_dropdown') {
    jQuery('#dropdown_languages_option').show();
} else {
    jQuery('#dropdown_languages_option').hide();
}

if('$widget_look' == 'flags' || '$widget_look' == 'flags_dropdown') {
    jQuery('#flag_languages_option').show();
} else {
    jQuery('#flag_languages_option').hide();
}

if('$widget_look' == 'flags' || '$widget_look' == 'dropdown') {
    jQuery('#line_break_option').hide();
} else {
    jQuery('#line_break_option').show();
}

if('$widget_look' == 'dropdown') {
    jQuery('#flag_size_option').hide();
} else {
    jQuery('#flag_size_option').show();
}

if(jQuery('#widget_code').val() == '')
    RefreshDoWidgetCode();
else
    ShowWidgetPreview(jQuery('#widget_code').val());
EOF;

// selected languages
if(count($incl_langs) > 0)
    $script .= "jQuery.each(languages, function(i, val) {jQuery('#incl_langs'+language_codes[i]).attr('checked', false);});\n";
if(count($fincl_langs) > 0)
    $script .= "jQuery.each(languages, function(i, val) {jQuery('#fincl_langs'+language_codes[i]).attr('checked', false);});\n";
foreach($incl_langs as $lang)
    $script .= "jQuery('#incl_langs$lang').attr('checked', true);\n";
foreach($fincl_langs as $lang)
    $script .= "jQuery('#fincl_langs$lang').attr('checked', true);\n";

// alt flags
foreach($alt_flags as $flag)
    $script .= "jQuery('#alt_$flag').attr('checked', true);\n";
?>

        <form id="WPTranslator" name="form1" method="post" action="<?php echo admin_url('options-general.php?page=WPTranslator_options'); ?>">

        <div class="postbox-container og_left_col">

        <div id="poststuff">
            <div class="postbox">
                <h3 id="settings">WPTranslator Options</h3>
                <div class="inside">
                    <table style="width:100%;" cellpadding="4">
											<tr>
                        <td class="option_name">Default language:</td>
                        <td>
                            <select id="default_language" name="default_language" onChange="RefreshDoWidgetCode()">
                            <?php

							$wpt_languages = array('af','sq','am','ar','hy','az','eu','be','bn','bs','bg','ca','ceb','ny','zh-CN','zh-TW','co','hr','cs','da','nl','en','eo','et','tl','fi','fr','fy','gl','ka','de','el','gu','ht','ha','haw','iw','hi','hmn','hu','is','ig','id','ga','it','ja','jw','kn','kk','km','ko','ku','ky','lo','la','lv','lt','lb','mk','mg','ms','ml','mt','mi','mr','mn','my','ne','no','ps','fa','pl','pt','pa','ro','ru','sm','gd','sr','st','sn','sd','si','sk','sl','so','es','su','sw','sv','tg','ta','te','th','tr','uk','ur','uz','vi','cy','xh','yi','yo','zu');
							$wpt_languages_assoc = array('af' => 'Afrikaans','sq' => 'Albanian','am' => 'Amharic','ar' => 'Arabic','hy' => 'Armenian','az' => 'Azerbaijani','eu' => 'Basque','be' => 'Belarusian','bn' => 'Bengali','bs' => 'Bosnian','bg' => 'Bulgarian','ca' => 'Catalan','ceb' => 'Cebuano','ny' => 'Chichewa','zh-CN' => 'Chinese (Simplified)','zh-TW' => 'Chinese (Traditional)','co' => 'Corsican','hr' => 'Croatian','cs' => 'Czech','da' => 'Danish','nl' => 'Dutch','en' => 'English','eo' => 'Esperanto','et' => 'Estonian','tl' => 'Filipino','fi' => 'Finnish','fr' => 'French','fy' => 'Frisian','gl' => 'Galician','ka' => 'Georgian','de' => 'German','el' => 'Greek','gu' => 'Gujarati','ht' => 'Haitian Creole','ha' => 'Hausa','haw' => 'Hawaiian','iw' => 'Hebrew','hi' => 'Hindi','hmn' => 'Hmong','hu' => 'Hungarian','is' => 'Icelandic','ig' => 'Igbo','id' => 'Indonesian','ga' => 'Irish','it' => 'Italian','ja' => 'Japanese','jw' => 'Javanese','kn' => 'Kannada','kk' => 'Kazakh','km' => 'Khmer','ko' => 'Korean','ku' => 'Kurdish (Kurmanji)','ky' => 'Kyrgyz','lo' => 'Lao','la' => 'Latin','lv' => 'Latvian','lt' => 'Lithuanian','lb' => 'Luxembourgish','mk' => 'Macedonian','mg' => 'Malagasy','ms' => 'Malay','ml' => 'Malayalam','mt' => 'Maltese','mi' => 'Maori','mr' => 'Marathi','mn' => 'Mongolian','my' => 'Myanmar (Burmese)','ne' => 'Nepali','no' => 'Norwegian','ps' => 'Pashto','fa' => 'Persian','pl' => 'Polish','pt' => 'Portuguese','pa' => 'Punjabi','ro' => 'Romanian','ru' => 'Russian','sm' => 'Samoan','gd' => 'Scottish Gaelic','sr' => 'Serbian','st' => 'Sesotho','sn' => 'Shona','sd' => 'Sindhi','si' => 'Sinhala','sk' => 'Slovak','sl' => 'Slovenian','so' => 'Somali','es' => 'Spanish','su' => 'Sudanese','sw' => 'Swahili','sv' => 'Swedish','tg' => 'Tajik','ta' => 'Tamil','te' => 'Telugu','th' => 'Thai','tr' => 'Turkish','uk' => 'Ukrainian','ur' => 'Urdu','uz' => 'Uzbek','vi' => 'Vietnamese','cy' => 'Welsh','xh' => 'Xhosa','yi' => 'Yiddish','yo' => 'Yoruba','zu' => 'Zulu');


                        	foreach ($wpt_languages_assoc as $lang => $langtext) {
                        		echo '<option value="'.$lang.'">'.$langtext.'</option>';
                        	}

                            ?>
                            </select>
                        </td>
                    </tr>
                   
                    <tr id="flag_size_option">
                        <td class="option_name">Flag size:</td>
                        <td>
                        <select id="flag_size"  name="flag_size" onchange="RefreshDoWidgetCode()">
                            <option value="16">Small</option>
                            <option value="24" selected>Medium</option>
                            <option value="32">Large</option>
                            <option value="48">Extra Large</option>
                        </select>
                        </td>
                    </tr>
                    <tr id="flag_languages_option" style="display:none;">
                        <td class="option_name">Flag languages:</td>
                        <td><button type="button" onclick="document.getElementById('wptflagcontainer').style.display = 'block';">Select Flags</button><br /><br />

                            <div id="wptflagcontainer" style="overflow:hidden; column-count: 3; -webkit-column-count: 3; -moz-column-count: 3; display: none;">
    						<?php 

    						foreach ($wpt_languages_assoc as $lang => $langtext) {
    							if(preg_match("/(en|es|de|fr|pt|ru)/",$lang)) {
    								echo '<input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langs'.$lang.'" name="fincl_langs[]" value="'.$lang.'" checked><label for="fincl_langs'.$lang.'">'.$langtext.'</label><br />';
    							} else {
                                    echo '<input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="fincl_langs'.$lang.'" name="fincl_langs[]" value="'.$lang.'"><label for="fincl_langs'.$lang.'">'.$langtext.'</label><br />';
    							}
    						}
    						?>
       
                            </div>
                        </td>
                    </tr>
 
                    <tr id="dropdown_languages_option" style="display:none;">
                        <td class="option_name">Dropdown languages:</td>
                        <td><button type="button" onclick="document.getElementById('wptdropcontainer').style.display = 'block';">Select Dropdown Options</button><br /><br />
                            <div id="wptdropcontainer" style="column-count: 3; -webkit-column-count: 3; -moz-column-count: 3; display: none;">
                            <?php 
    						foreach ($wpt_languages_assoc as $lang => $langtext) {
    							echo '<input type="checkbox" onclick="RefreshDoWidgetCode()" onchange="RefreshDoWidgetCode()" id="incl_langs'.$lang.'" name="incl_langs[]" value="'.$lang.'" checked><label for="incl_langs'.$lang.'">'.$langtext.'</label><br />';
    						}
    						?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="option_name">Widget look:</td>
                        <td>
                            <select id="widget_look" name="widget_look" onChange="RefreshDoWidgetCode()">
                                <option value="flags_dropdown">Dropdown &amp Flags</option>
                                <option value="dropdown">Just Dropdown</option>
                                <option value="flags">Just Flags</option>
                            </select>
                        </td>
                    </tr>
                    
                    </table>
                </div>
            </div>
        </div>

        <div id="poststuff" style="display: none;">
            <div class="postbox">
                <h3 id="settings">Widget code</h3>
                <div class="inside">
                    <textarea id="widget_code" name="widget_code" onchange="ShowWidgetPreview(this.value)" style="font-family:Monospace;font-size:11px;height:150px;width:565px;"><?php echo $widget_code; ?></textarea>
                </div>
            </div>
        </div>

        <?php wp_nonce_field('WPTranslator-save'); ?>
        <p class="submit"><input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes'); ?>" /></p>

        </div>

        </form>

        <div class="postbox-container og_right_col">
            <div id="poststuff">
                <div class="postbox">
                    <h3 id="settings">Widget preview</h3>
                    <div class="inside">
                        <div id="widget_preview"></div>
                    </div>
                </div>
            </div>

            <div id="poststuff">
                <div class="postbox">
                    <h3 id="settings">Love WPTranslator?</h3>
                    <div class="inside">
                        <p>Send us a review at <a href="https://wordpress.org/plugins/wptranslator/">https://wordpress.org/plugins/wptranslator/</a>.</p>
                    </div>
                </div>
            </div>

            <div id="poststuff">
                <div class="postbox">
                    <h3 id="settings">How To Setup</h3>
                    <div class="inside">
                    Choose your options on this page and then click "Save Changes" at the bottom<br><br>
                    You can use the shortcode [WPTranslator] anywhere on your site or drop it in as a <a href="widgets.php">widget</a> on to your side bar.
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript"><?php echo $script; ?></script>
        <style type="text/css">
        .postbox #settings {padding-left:12px;}
        .og_left_col {      width: 59%;     }
        .og_right_col {     width: 39%;     float: right;       }
        .og_left_col #poststuff,        .og_right_col #poststuff {      min-width: 0;       }
        table.form-table tr th,     table.form-table tr td {        line-height: 1.5;       }
        table.form-table tr th {        font-weight: bold;      }
        table.form-table tr th[scope=row] { min-width: 300px;       }
        table.form-table tr td hr {     height: 1px;        margin: 0px;        background-color: #DFDFDF;      border: none;       }
        table.form-table .dashicons-before {        margin-right: 10px;     font-size: 12px;        opacity: 0.5;       }
        table.form-table .dashicons-facebook-alt {      color: #3B5998;     }
        table.form-table .dashicons-googleplus {        color: #D34836;     }
        table.form-table .dashicons-twitter {       color: #55ACEE;     }
        table.form-table .dashicons-rss {       color: #FF6600;     }
        table.form-table .dashicons-admin-site,     table.form-table .dashicons-admin-generic {     color: #666;        }
        </style>
        <?php
    }

    public static function control_options() {
        check_admin_referer('WPTranslator-save');

        $data = get_option('WPTranslator');

        $data['new_window'] = isset($_POST['new_window']) ? intval($_POST['new_window']) : '';
        $data['analytics'] = isset($_POST['analytics']) ? intval($_POST['analytics']) : '';
        $data['load_jquery'] = isset($_POST['load_jquery']) ? intval($_POST['load_jquery']) : '';
        $data['default_language'] = isset($_POST['default_language']) ? sanitize_text_field($_POST['default_language']) : 'en';
        $data['translation_method'] = 'onfly';
        $data['widget_look'] = isset($_POST['widget_look']) ? sanitize_text_field($_POST['widget_look']) : 'flags_dropdown';
        $data['flag_size'] = isset($_POST['flag_size']) ? intval($_POST['flag_size']) : '24';
        $data['widget_code'] = isset($_POST['widget_code']) ? stripslashes($_POST['widget_code']) : '';
        $data['incl_langs'] = (isset($_POST['incl_langs']) and is_array($_POST['incl_langs'])) ? $_POST['incl_langs'] : array('en');
        $data['fincl_langs'] = (isset($_POST['fincl_langs']) and is_array($_POST['fincl_langs'])) ? $_POST['fincl_langs'] : array('en');
        $data['alt_flags'] = (isset($_POST['alt_flags']) and is_array($_POST['alt_flags'])) ? $_POST['alt_flags'] : array();

        echo '<p style="color:red;">Changes Saved</p>';
        update_option('WPTranslator', $data);
    }

    public static function load_defaults(& $data) {
        $data['new_window'] = isset($data['new_window']) ? $data['new_window'] : '';
        $data['analytics'] = isset($data['analytics']) ? $data['analytics'] : '';
        $data['load_jquery'] = isset($data['load_jquery']) ? $data['load_jquery'] : '1';
        $data['default_language'] = isset($data['default_language']) ? $data['default_language'] : 'en';
        $data['translation_method'] = isset($data['translation_method']) ? $data['translation_method'] : 'onfly';
        if($data['translation_method'] == 'on_fly') $data['translation_method'] = 'redirect';
        $data['widget_look'] = isset($data['widget_look']) ? $data['widget_look'] : 'flags_dropdown';
        $data['flag_size'] = isset($data['flag_size']) ? $data['flag_size'] : '24';
        $data['widget_code'] = isset($data['widget_code']) ? $data['widget_code'] : '';
        $data['incl_langs'] = isset($data['incl_langs']) ? $data['incl_langs'] : array();
        $data['fincl_langs'] = isset($data['fincl_langs']) ? $data['fincl_langs'] : array();
        $data['alt_flags'] = isset($data['alt_flags']) ? $data['alt_flags'] : array();
    }
}

class WPTranslator_Notices {
    protected $prefix = 'WPTranslator';
	public $notice_spam = 0;
	public $notice_spam_max = 1;

	// Basic actions to run
	public function __construct() {
		// Runs the admin notice ignore function incase a dismiss button has been clicked
		add_action('admin_init', array($this, 'admin_notice_ignore'));
		// Runs the admin notice temp ignore function incase a temp dismiss link has been clicked
		add_action('admin_init', array($this, 'admin_notice_temp_ignore'));

		// Adding notices
		add_action('admin_notices', array($this, 'gt_admin_notices'));
	}

	// Checks to ensure notices aren't disabled and the user has the correct permissions.
	public function gt_admin_notice() {

		$gt_settings = get_option($this->prefix . '_admin_notice');
		if (!isset($gt_settings['disable_admin_notices']) || (isset($gt_settings['disable_admin_notices']) && $gt_settings['disable_admin_notices'] == 0)) {
			if (current_user_can('manage_options')) {
				return true;
			}
		}
		return false;
	}

	// Primary notice function that can be called from an outside function sending necessary variables
	public function admin_notice($admin_notices) {

		// Check options
		if (!$this->gt_admin_notice()) {
			return false;
		}

		foreach ($admin_notices as $slug => $admin_notice) {
			// Call for spam protection

			if ($this->anti_notice_spam()) {
				return false;
			}

			// Check for proper page to display on
			if (isset( $admin_notices[$slug]['pages']) and is_array( $admin_notices[$slug]['pages'])) {

				if (!$this->admin_notice_pages($admin_notices[$slug]['pages'])) {
					return false;
				}

			}

			// Check for required fields
			if (!$this->required_fields($admin_notices[$slug])) {

				// Get the current date then set start date to either passed value or current date value and add interval
				$current_date = current_time("n/j/Y");
				$start = (isset($admin_notices[$slug]['start']) ? $admin_notices[$slug]['start'] : $current_date);
				$start = date("n/j/Y", strtotime($start));
				$end = ( isset( $admin_notices[ $slug ]['end'] ) ? $admin_notices[ $slug ]['end'] : $start );
 	            $end = date( "n/j/Y", strtotime( $end ) );
				$date_array = explode('/', $start);
				$interval = (isset($admin_notices[$slug]['int']) ? $admin_notices[$slug]['int'] : 0);
				$date_array[1] += $interval;
				$start = date("n/j/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));
				// This is the main notices storage option
				$admin_notices_option = get_option($this->prefix . '_admin_notice', array());
				// Check if the message is already stored and if so just grab the key otherwise store the message and its associated date information
				if (!array_key_exists( $slug, $admin_notices_option)) {
					$admin_notices_option[$slug]['start'] = $start;
					$admin_notices_option[$slug]['int'] = $interval;
					update_option($this->prefix . '_admin_notice', $admin_notices_option);
				}

				// Sanity check to ensure we have accurate information
				// New date information will not overwrite old date information
				$admin_display_check = (isset($admin_notices_option[$slug]['dismissed']) ? $admin_notices_option[$slug]['dismissed'] : 0);
				$admin_display_start = (isset($admin_notices_option[$slug]['start']) ? $admin_notices_option[$slug]['start'] : $start);
				$admin_display_interval = (isset($admin_notices_option[$slug]['int']) ? $admin_notices_option[$slug]['int'] : $interval);
				$admin_display_msg = (isset($admin_notices[$slug]['msg']) ? $admin_notices[$slug]['msg'] : '');
				$admin_display_title = (isset($admin_notices[$slug]['title']) ? $admin_notices[$slug]['title'] : '');
				$admin_display_link = (isset($admin_notices[$slug]['link']) ? $admin_notices[$slug]['link'] : '');
				$output_css = false;

				// Ensure the notice hasn't been hidden and that the current date is after the start date
                if ($admin_display_check == 0 and strtotime($admin_display_start) <= strtotime($current_date)) {
					// Get remaining query string
					$query_str = esc_url(add_query_arg($this->prefix . '_admin_notice_ignore', $slug));

					// Admin notice display output
                    echo '<div class="update-nag gt-admin-notice">';
                    echo '<div class="gt-notice-logo"></div>';
                    echo ' <p class="gt-notice-title">';
                    echo $admin_display_title;
                    echo ' </p>';
                    echo ' <p class="gt-notice-body">';
                    echo $admin_display_msg;
                    echo ' </p>';
                    echo '<ul class="gt-notice-body gt-red">
                          ' . $admin_display_link . '
                        </ul>';
                    echo '<a href="' . $query_str . '" class="dashicons dashicons-dismiss"></a>';
                    echo '</div>';

					$this->notice_spam += 1;
				}
			}

		}
	}

	// Spam protection check
	public function anti_notice_spam() {
		if ($this->notice_spam >= $this->notice_spam_max) {
			return true;
		}
		return false;
	}

	// Ignore function that gets ran at admin init to ensure any messages that were dismissed get marked
	public function admin_notice_ignore() {
		// If user clicks to ignore the notice, update the option to not show it again
		if (isset($_GET[$this->prefix . '_admin_notice_ignore'])) {
			$admin_notices_option = get_option($this->prefix . '_admin_notice', array());
			$admin_notices_option[$_GET[$this->prefix . '_admin_notice_ignore']]['dismissed'] = 1;
			update_option($this->prefix . '_admin_notice', $admin_notices_option);
			$query_str = remove_query_arg($this->prefix . '_admin_notice_ignore');
			wp_redirect($query_str);
			exit;
		}
	}

	// Temp Ignore function that gets ran at admin init to ensure any messages that were temp dismissed get their start date changed
	public function admin_notice_temp_ignore() {
		// If user clicks to temp ignore the notice, update the option to change the start date - default interval of 14 days
		if (isset($_GET[$this->prefix . '_admin_notice_temp_ignore'])) {
			$admin_notices_option = get_option($this->prefix . '_admin_notice', array());
			$current_date = current_time("n/j/Y");
			$date_array   = explode('/', $current_date);
			$interval     = (isset($_GET['gt_int']) ? $_GET['gt_int'] : 14);
			$date_array[1] += $interval;
			$new_start = date("n/j/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));

			$admin_notices_option[$_GET[$this->prefix . '_admin_notice_temp_ignore']]['start'] = $new_start;
			$admin_notices_option[$_GET[$this->prefix . '_admin_notice_temp_ignore']]['dismissed'] = 0;
			update_option($this->prefix . '_admin_notice', $admin_notices_option);
			$query_str = remove_query_arg(array($this->prefix . '_admin_notice_temp_ignore', 'gt_int'));
			wp_redirect( $query_str );
			exit;
		}
	}

	public function admin_notice_pages($pages) {
		foreach ($pages as $key => $page) {
			if (is_array($page)) {
				if (isset($_GET['page']) and $_GET['page'] == $page[0] and isset($_GET['tab']) and $_GET['tab'] == $page[1]) {
					return true;
				}
			} else {
				if ($page == 'all') {
					return true;
				}
				if (get_current_screen()->id === $page) {
					return true;
				}

				if (isset($_GET['page']) and $_GET['page'] == $page) {
					return true;
				}
			}
		}

		return false;
	}

	// Required fields check
	public function required_fields( $fields ) {
		if (!isset( $fields['msg']) or (isset($fields['msg']) and empty($fields['msg']))) {
			return true;
		}
		if (!isset( $fields['title']) or (isset($fields['title']) and empty($fields['title']))) {
			return true;
		}
		return false;
	}

	// Special parameters function that is to be used in any extension of this class
	public function special_parameters($admin_notices) {
		// Intentionally left blank
	}

    public function gt_admin_notices() {
	  	$two_week_review_ignore = esc_url(add_query_arg(array($this->prefix . '_admin_notice_ignore' => 'two_week_review')));
	    $two_week_review_temp = esc_url(add_query_arg(array($this->prefix . '_admin_notice_temp_ignore' => 'two_week_review', 'gt_int' => 14)));

	    $notices['two_week_review'] = array(
	        'title' => 'Leave a review?',
	        'msg' => 'We hope you have enjoyed using WPTranslator! Would you consider leaving us a review on WordPress.org?',
	        'link' => '<li><span class="dashicons dashicons-external"></span><a href="https://wordpress.org/support/view/plugin-reviews/WPTranslator?filter=5" target="_blank">Sure! I would love to!</a></li>' .
	                  '<li><span class="dashicons dashicons-smiley"></span><a href="' . $two_week_review_ignore . '">I have already left a review</a></li>' .
	                  '<li><span class="dashicons dashicons-calendar-alt"></span><a href="' . $two_week_review_temp . '">Maybe later</a></li>' .
	                  '<li><span class="dashicons dashicons-dismiss"></span><a href="' . $two_week_review_ignore . '">Never show again</a></li>',
	        'later_link' => $two_week_review_temp,
	        'int' => 3
	    );

	    $data = get_option('WPTranslator');
        WPTranslator::load_defaults($data);

      $upgrade_tips_ignore = esc_url(add_query_arg(array($this->prefix . '_admin_notice_ignore' => 'upgrade_tips')));
	    $upgrade_tips_temp = esc_url(add_query_arg(array($this->prefix . '_admin_notice_temp_ignore' => 'upgrade_tips', 'gt_int' => 5)));

	    $this->admin_notice($notices);
	}

}
