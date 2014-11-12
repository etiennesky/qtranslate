<?php // encoding: utf-8

/*  Copyright 2008  Qian Qin  (email : mail@qianqin.de)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* qTranslate Widget */

class qTranslateWidget extends WP_Widget {
	function qTranslateWidget() {
		$widget_ops = array('classname' => 'widget_qtranslate', 'description' => __('Allows your visitors to choose a Language.','qtranslate') );
		$this->WP_Widget('qtranslate', __('qTranslate Language Chooser','qtranslate'), $widget_ops);
	}
	
	function widget($args, $instance) {
		extract($args);
		
		echo $before_widget;
		$title = empty($instance['title']) ? __('Language', 'qtranslate') : apply_filters('widget_title', $instance['title']);
		$hide_title = empty($instance['hide-title']) ? false : 'on';
		$show_inline = empty($instance['show-inline']) ? false : 'on';
		$type = $instance['type'];
		if($type!='text'&&$type!='image'&&$type!='both'&&$type!='dropdown') $type='text';

		if($hide_title!='on') { echo $before_title . $title . $after_title; };
		echo qtrans_generateLanguageSelectCode($type, $this->id, $show_inline);
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		//if(isset($new_instance['hide-title'])) $instance['hide-title'] = $new_instance['hide-title'];
		$instance['hide-title'] = $new_instance['hide-title'];
		$instance['show-inline'] = $new_instance['show-inline'];
		$instance['type'] = $new_instance['type'];

		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'hide-title' => false, 'show-inline' => false, 'type' => 'text' ) );
		$title = $instance['title'];
		$hide_title = $instance['hide-title'];
		$show_inline = $instance['show-inline'];
		$type = $instance['type'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'qtranslate'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('hide-title'); ?>"><?php _e('Hide Title:', 'qtranslate'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('hide-title'); ?>" name="<?php echo $this->get_field_name('hide-title'); ?>" <?php echo ($hide_title=='on')?'checked="checked"':''; ?>/></label></p>
		<p><?php _e('Display:', 'qtranslate'); ?></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>1"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>1" value="text"<?php echo ($type=='text')?' checked="checked"':'' ?>/> <?php _e('Text only', 'qtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>2"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>2" value="image"<?php echo ($type=='image')?' checked="checked"':'' ?>/> <?php _e('Image only', 'qtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>3"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>3" value="both"<?php echo ($type=='both')?' checked="checked"':'' ?>/> <?php _e('Text and Image', 'qtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('type'); ?>4"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>4" value="dropdown"<?php echo ($type=='dropdown')?' checked="checked"':'' ?>/> <?php _e('Dropdown Box', 'qtranslate'); ?></label></p>
		<p><label for="<?php echo $this->get_field_id('show-inline'); ?>"><?php _e('Show Text/Image Inline:', 'qtranslate'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('show-inline'); ?>" name="<?php echo $this->get_field_name('show-inline'); ?>" <?php echo ($show_inline=='on')?'checked="checked"':''; ?>/></label></p>
<?php
	}
}

function qtrans_convertURL2($url='', $lang='') {
	global $q_config;
	if(strpos($url,'?')===false) {
		$url .= '?';
	} else {
		$url .= '&';
	}
	$url .= "lang=".$lang."&newlang=1";
	return $url;
}

// Language Select Code for non-Widget users
function qtrans_generateLanguageSelectCode($style='', $id='', $show_inline=false) {
	global $q_config;
	$html = '';
	if($style=='') $style='text';
	if(is_bool($style)&&$style) $style='image';
	if(is_404()) $url = get_option('home'); else $url = '';
	if($id=='') $id = 'qtranslate';
	$id .= '-chooser';
	switch($style) {
		case 'image':
		case 'text':
		case 'dropdown':
			if ( ! $show_inline=='on') $html .= '<ul class="qtrans_language_chooser" id="'.$id.'">';
			else $html .= '<div class="qtrans_language_chooser" id="'.$id.'">';
			foreach(qtrans_getSortedLanguages() as $language) {
				$classes = array('lang-'.$language);
				if($language == $q_config['language'])
					$classes[] = 'active';
				if ( ! $show_inline=='on') $html .= '<li class="'. implode(' ', $classes) .'">';
				else $html .= '<div style="display: block; width: auto; float: left; padding-right: 20px;" class="'. implode(' ', $classes) .'">';
				$html .= '<a href="'.qtrans_convertURL2($url, $language).'"';
				// set hreflang
				$html .= ' hreflang="'.$language.'" title="'.$q_config['language_name'][$language].'"';
				if($style=='image')
					$html .= ' class="qtrans_flag qtrans_flag_'.$language.'"';
				$html .= '><span';
				if($style=='image')
					$html .= ' style="display:none"';
				$html .= '>'.$q_config['language_name'][$language].'</span></a>';
				if ( ! $show_inline=='on') $html .= '</li>';
				else $html .= '</div>';
			}
			if ( ! $show_inline=='on') $html .= "</ul>";
			$html .= "<div class=\"qtrans_widget_end\"></div>";
			if($style=='dropdown') {
				$html .= "<script type=\"text/javascript\">\n// <![CDATA[\r\n";
				$html .= "var lc = document.getElementById('".$id."');\n";
				$html .= "var s = document.createElement('select');\n";
				$html .= "s.id = 'qtrans_select_".$id."';\n";
				$html .= "lc.parentNode.insertBefore(s,lc);";
				// create dropdown fields for each language
				foreach(qtrans_getSortedLanguages() as $language) {
					$html .= qtrans_insertDropDownElement($language, qtrans_convertURL2($url, $language), $id);
				}
				// hide html language chooser text
				$html .= "s.onchange = function() { document.location.href = this.value;}\n";
				$html .= "lc.style.display='none';\n";
				$html .= "// ]]>\n</script>\n";
			}
			break;
		case 'both':
			$html .= '<ul class="qtrans_language_chooser" id="'.$id.'">';
			foreach(qtrans_getSortedLanguages() as $language) {
				$html .= '<li';
				if($language == $q_config['language'])
					$html .= ' class="active"';
				$html .= '><a href="'.qtrans_convertURL2($url, $language).'"';
				$html .= ' class="qtrans_flag_'.$language.' qtrans_flag_and_text" title="'.$q_config['language_name'][$language].'"';
				$html .= '><span>'.$q_config['language_name'][$language].'</span></a></li>';
			}
			$html .= "</ul><div class=\"qtrans_widget_end\"></div>";
			break;
	}

	return $html;
}

function qtrans_widget_init() {
	register_widget('qTranslateWidget');
}

?>
