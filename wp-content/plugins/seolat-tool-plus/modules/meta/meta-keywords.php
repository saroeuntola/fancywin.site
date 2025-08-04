<?php
/**
 * Meta Keywords Editor Module
 * 
 * @since 4.0
 */

if (class_exists('SL_Module')) {

function sl_meta_keywords_export_filter($all_settings) {
	unset($all_settings['meta']['taxonomy_keywords']);
	return $all_settings;
}
add_filter('sl_settings_export_array', 'sl_meta_keywords_export_filter');

class SL_MetaKeywords extends SL_Module {
	
	static function get_module_title() { return __('Meta Keywords Editor', 'seolat-tool-plus'); }
	static function get_menu_title()   { return __('Meta Keywords', 'seolat-tool-plus'); }
	function get_settings_key() { return 'meta'; }
	function get_default_status() { return SL_MODULE_DISABLED; }
	
	function init() {
		add_action('sl_head', array(&$this, 'head_tag_output'));
	}
	
	function get_default_settings() {
		return array(
			  'auto_keywords_posttype_post_words_value' => 3
			, 'auto_keywords_posttype_page_words_value' => 3
			, 'auto_keywords_posttype_attachment_words_value' => 3
		);
	}
	
	function get_admin_page_tabs() {
		return array_merge(
			  array(
				  array('title' => __('Sitewide Values', 'seolat-tool-plus'), 'id' => 'sl-sitewide-values', 'callback' => 'global_tab')
				, array('title' => __('Default Values', 'seolat-tool-plus'), 'id' => 'sl-default-values', 'callback' => 'defaults_tab')
				, array('title' => __('Blog Homepage', 'seolat-tool-plus'), 'id' => 'sl-blog-homepage', 'callback' => 'home_tab')
				)
			, $this->get_meta_edit_tabs(array(
				  'type' => 'textbox'
				, 'name' => 'keywords'
				, 'term_settings_key' => 'taxonomy_keywords'
				, 'label' => __('Meta Keywords', 'seolat-tool-plus')
			))
		);
	}
	
	function global_tab() {
		$this->admin_form_table_start();
		$this->textarea('global_keywords', __('Sitewide Keywords', 'seolat-tool-plus') . '<br /><small><em>' . __('(Separate with commas)', 'seolat-tool-plus') . '</em></small>');
		$this->admin_form_table_end();
	}
	
	function defaults_tab() {
		$this->admin_form_table_start();
		
		$posttypenames = get_post_types(array('public' => true), 'names');
		foreach ($posttypenames as $posttypename) {
			$posttype = get_post_type_object($posttypename);
			$posttypelabel = $posttype->labels->name;
			
			$checkboxes = array();
			
			if (post_type_supports($posttypename, 'editor'))
				$checkboxes["auto_keywords_posttype_{$posttypename}_words"] = __('The %d most commonly-used words', 'seolat-tool-plus');
			
			$taxnames = get_object_taxonomies($posttypename);
			
			foreach ($taxnames as $taxname) {
				$taxonomy = get_taxonomy($taxname);
				$checkboxes["auto_keywords_posttype_{$posttypename}_tax_{$taxname}"] = $taxonomy->labels->name;
			}
			
			if ($checkboxes)
				$this->checkboxes($checkboxes, $posttypelabel);
		}
		
		$this->admin_form_table_end();
	}
	
	function home_tab() {
		$this->admin_form_table_start();
		$this->textarea('home_keywords', __('Blog Homepage Meta Keywords', 'seolat-tool-plus'), 3);
		$this->admin_form_table_end();
	}
	
	function head_tag_output() {
		global $post;
		
		$kw = false;
		
		//If we're viewing the homepage, look for homepage meta data.
		if (is_home()) {
			$kw = $this->get_setting('home_keywords');
		
		//If we're viewing a post or page...
		} elseif (is_singular()) {
			
			//...look for its meta data
			$kw = $this->get_postmeta('keywords');	
			
			//...and add default values
			if ($posttypename = get_post_type()) {
				$taxnames = get_object_taxonomies($posttypename);
				
				foreach ($taxnames as $taxname) {
					if ($this->get_setting("auto_keywords_posttype_{$posttypename}_tax_{$taxname}", false)) {
						$terms = get_the_terms(0, $taxname);
						$terms = lat_array::flatten_values($terms, 'name');
						$terms = implode(',', $terms);
						$kw .= ',' . $terms;
					}
				}
				
				if ($this->get_setting("auto_keywords_posttype_{$posttypename}_words", false)) {
					$words = preg_split("/[\s+]/", strip_tags($post->post_content), null, PREG_SPLIT_NO_EMPTY);
					$words = array_count_values($words);
					arsort($words);
					$words = array_filter($words, array(&$this, 'filter_word_counts'));
					$words = array_keys($words);
					$stopwords = lat_array::explode_lines($this->get_setting('words_to_remove', array(), 'slugs'));
					$stopwords = array_map(array('lat_string', 'tolower'), $stopwords);
					$words     = array_map(array('lat_string', 'tolower'), $words);
					$words = array_diff($words, $stopwords);
					$words = array_slice($words, 0, $this->get_setting("auto_keywords_posttype_{$posttypename}_words_value"));
					$words = implode(',', $words);
					$kw .= ',' . $words;
				}
			}
			
		//If we're viewing a term, look for its meta data.
		} elseif (lat_wp::is_tax()) {
			global $wp_query;
			$tax_keywords = $this->get_setting('taxonomy_keywords');
			
			$term_id = $wp_query->get_queried_object_id();
			if (isset($tax_keywords[$term_id]))
				$kw = $tax_keywords[$term_id];
			else
				$kw = '';
		}
		
		if ($globals = $this->get_setting('global_keywords')) {
			if (strlen($kw)) $kw .= ',';
			$kw .= $globals;
		}
		
		$kw = str_replace(array("\r\n", "\n"), ',', $kw);
		$kw = explode(',', $kw);
		$kw = array_map('trim', $kw); //Remove extra spaces from beginning/end of keywords
		$kw = array_filter($kw); //Remove blank keywords
		$kw = lat_array::array_unique_i($kw); //Remove duplicate keywords
		$kw = implode(',', $kw);
		
		//Do we have keywords? If so, output them.
		if ($kw) {
			$kw = sl_esc_attr($kw);
			echo "\t<meta name=\"keywords\" content=\"$kw\" />\n";
		}
	}
	
	function filter_word_counts($count) {
		return $count > 1;
	}

	function postmeta_fields($fields, $screen) {	
		$fields['serp'][21]['keywords'] = $this->get_postmeta_textbox('keywords', __('Meta Keywords:<br /><em>(separate with commas)</em>', 'seolat-tool-plus'));
		return $fields;
	}

	function add_help_tabs($screen) {
		
		$screen->add_help_tab(array(
			  'id' => 'sl-meta-keywords-overview'
			, 'title' => __('Overview', 'seolat-tool-plus')
			, 'content' => __("
<p>Meta Keywords Editor lets you tell search engines what keywords are associated with the various pages on your site. Modern search engines don&#8217;t give meta keywords much weight, if any at all, but the option is there if you want to use it.</p>
", 'seolat-tool-plus')));
		
		$screen->add_help_tab(array(
				  'id' => 'sl-meta-keywords-global'
				, 'title' => __('Sitewide Settings Tab', 'seolat-tool-plus')
				, 'content' => __("
<ul>
	<li><strong>Sitewide Keywords</strong> &mdash; Here you can enter keywords that describe the overall subject matter of your entire blog. Use commas to separate keywords. These keywords will be put in the <code>&gt;meta name=&quot;keywords&quot; /&gt;</code> tags of all webpages on the site (homepage, posts, pages, archives, etc.).</li>
</ul>
", 'seolat-tool-plus')));
		
		$screen->add_help_tab(array(
			  'id' => 'sl-meta-keywords-home'
			, 'title' => __('Blog Homepage Tab', 'seolat-tool-plus')
			, 'content' => __("
<ul>
	<li><strong>Blog Homepage Meta Keywords</strong> &mdash; These keywords will be applied only to the <em>blog</em> homepage. Note that if you&#8217;ve specified a &#8220;front page&#8221; under <a href='options-reading.php'>Settings &rArr; Reading</a>, you&#8217;ll need to edit your frontpage and set your frontpage keywords there.</li>
</ul>
", 'seolat-tool-plus')));
		
		$screen->add_help_tab(array(
			  'id' => 'sl-meta-keywords-faq'
			, 'title' => __('FAQ', 'seolat-tool-plus')
			, 'content' => __("
<ul>
	<li><strong>How do I edit the meta keywords of my homepage?</strong><br />If you have configured your <a href='options-reading.php'>Settings &rArr; Reading</a> section to use a &#8220;front page&#8221; and/or a &#8220;posts page,&#8221; just edit those pages&#8217;s meta keywords on the &#8220;Pages&#8221; tab. Otherwise, just use the Blog Homepage field.</li>
	<li><strong>What happens if I add a global keyword that I previously assigned to individual posts or pages?</strong><br />Don&#8217;t worry; Meta Keywords Editor will remove duplicate keywords automatically.</li>
</ul>
", 'seolat-tool-plus')));
		
		$screen->add_help_tab(array(
			  'id' => 'sl-meta-keywords-troubleshooting'
			, 'title' => __('Troubleshooting', 'seolat-tool-plus')
			, 'content' => __("
<ul>
	<li>
		<p><strong>What do I do if my site has multiple meta tags?</strong><br />First, try removing your theme&#8217;s built-in meta tags if it has them. Go to <a href='theme-editor.php' target='_blank'>Appearance &rArr; Editor</a> and edit <code>header.php</code>. Delete or comment-out any <code>&lt;meta&gt;</code> tags.</p>
		<p>If the problem persists, try disabling other SEO plugins that may be generating meta tags.</p>
		<p>Troubleshooting tip: Go to <a href='options-general.php?page=seo-lat'>Settings &rArr; SEO LAT+</a> and enable the &#8220;Insert comments around HTML code insertions&#8221; option. This will mark SEOLAT Tool Plus meta tags with comments, allowing you to see which meta tags are generated by SEOLAT Tool Plus and which aren&#8217;t.</p>
	</li>
</ul>
", 'seolat-tool-plus')));
	}
}

}
?>