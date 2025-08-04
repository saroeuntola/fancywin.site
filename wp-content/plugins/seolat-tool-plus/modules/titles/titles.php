<?php
/**
 * Title Tag Rewriter Module
 * 
 * @since 0.1
 */

if (class_exists('SL_Module')) {

function sl_titles_export_filter($all_settings) {
	unset($all_settings['titles']['taxonomy_titles']);
	return $all_settings;
}
add_filter('sl_settings_export_array', 'sl_titles_export_filter');

class SL_Titles extends SL_Module {
	
	static function get_module_title() { return __('Title Tag Rewriter', 'seolat-tool-plus'); }
	static function get_menu_title() { return __('Title Tag Rewriter', 'seolat-tool-plus'); }
	
	function init() {
		
		switch ($this->get_setting('rewrite_method', 'ob')) {
			case 'filter':
				add_filter('wp_title', array(&$this, 'get_title'));
				break;
			case 'ob':
			default:
				add_action('template_redirect', array(&$this, 'before_header'), 0);
				add_action('wp_head', array(&$this, 'after_header'), 1000);
				break;
		}
		
		add_filter('sl_postmeta_help', array(&$this, 'postmeta_help'), 10);
	}
	
	function get_admin_page_tabs() {
		return array_merge(
			  array(
				  array('title' => __('Default Formats', 'seolat-tool-plus'), 'id' => 'sl-default-formats', 'callback' => 'formats_tab')
				, array('title' => __('Settings', 'seolat-tool-plus'), 'id' => 'sl-settings', 'callback' => 'settings_tab')
				)
			, $this->get_meta_edit_tabs(array(
				  'type' => 'textbox'
				, 'name' => 'title'
				, 'term_settings_key' => 'taxonomy_titles'
				, 'label' => __('Title Tag', 'seolat-tool-plus')
			))
		);
	}
	
	function formats_tab() {
		//echo "<table class='form-table'>\n";
		$this->textboxes($this->get_supported_settings(), $this->get_default_settings());
		//echo "</table>";
	}
	
	function settings_tab() {
		$this->admin_form_table_start();
		$this->checkbox('terms_ucwords', __('Convert lowercase category/tag names to title case when used in title tags.', 'seolat-tool-plus'), __('Title Tag Variables', 'seolat-tool-plus'));
		$this->radiobuttons('rewrite_method', array(
			  'ob' => __('Use output buffering &mdash; no configuration required, but slower (default)', 'seolat-tool-plus')
			, 'filter' => __('Use filtering &mdash; faster, but configuration required (see the &#8220;Settings Tab&#8221 section of the &#8220;Help&#8221; dropdown for details)', 'seolat-tool-plus')
		), __('Rewrite Method', 'seolat-tool-plus'));
		$this->admin_form_table_end();
	}
	
	function get_default_settings() {
		
		//We internationalize even non-text formats (like "{post}") to allow RTL languages to switch the order of the variables
		return array(
			  'title_home' => __('{blog}', 'seolat-tool-plus')
			, 'title_single' => __('{post}', 'seolat-tool-plus')
			, 'title_page' => __('{page}', 'seolat-tool-plus')
			, 'title_category' => __('{category}', 'seolat-tool-plus')
			, 'title_tag' => __('{tag}', 'seolat-tool-plus')
			, 'title_day' => __('Archives for {month} {day}, {year}', 'seolat-tool-plus')
			, 'title_month' => __('Archives for {month} {year}', 'seolat-tool-plus')
			, 'title_year' => __('Archives for {year}', 'seolat-tool-plus')
			, 'title_author' => __('Posts by {author}', 'seolat-tool-plus')
			, 'title_search' => __('Search Results for {query}', 'seolat-tool-plus')
			, 'title_404' => __('404 Not Found', 'seolat-tool-plus')
			, 'title_paged' => __('{title} - Page {num}', 'seolat-tool-plus')
			, 'terms_ucwords' => true
			, 'rewrite_method' => 'ob'
		);
	}
	
	function get_supported_settings() {
		return array(
			  'title_home' => __('Blog Homepage Title', 'seolat-tool-plus')
			, 'title_single' => __('Post Title Format', 'seolat-tool-plus')
			, 'title_page' => __('Page Title Format', 'seolat-tool-plus')
			, 'title_category' => __('Category Title Format', 'seolat-tool-plus')
			, 'title_tag' => __('Tag Title Format', 'seolat-tool-plus')
			, 'title_day' => __('Day Archive Title Format', 'seolat-tool-plus')
			, 'title_month' => __('Month Archive Title Format', 'seolat-tool-plus')
			, 'title_year' => __('Year Archive Title Format', 'seolat-tool-plus')
			, 'title_author' => __('Author Archive Title Format', 'seolat-tool-plus')
			, 'title_search' => __('Search Title Format', 'seolat-tool-plus')
			, 'title_404' => __('404 Title Format', 'seolat-tool-plus')
			, 'title_paged' => __('Pagination Title Format', 'seolat-tool-plus')
		);
	}
	
	function get_title_format() {
		if ($key = $this->get_current_page_type())
			return $this->get_setting("title_$key");
		
		return false;
	}
	
	function get_current_page_type() {
		$pagetypes = $this->get_supported_settings();
		unset($pagetypes['title_paged']);
		
		foreach ($pagetypes as $key => $title) {
			$key = str_replace('title_', '', $key);
			if (call_user_func("is_$key")) return $key;
		}
		
		return false;
	}
	
	function should_rewrite_title() {
		return (!is_admin() && !is_feed());
	}
	
	function before_header() {
		if ($this->should_rewrite_title()) ob_start(array(&$this, 'change_title_tag'));
	}

	function after_header() {
		if ($this->should_rewrite_title()) {
			
			$handlers = ob_list_handlers();
			if (count($handlers) > 0 && strcasecmp($handlers[count($handlers)-1], 'SL_Titles::change_title_tag') == 0)
				ob_end_flush();
			else
				sl_debug_log(__FILE__, __CLASS__, __FUNCTION__, __LINE__, "Other ob_list_handlers found:\n".print_r($handlers, true));
		}
	}
	
	function change_title_tag($head) {
		
		$title = $this->get_title();
		if (!$title) return $head;
		// Pre-parse the title replacement text to escape the $ ($n backreferences) when followed by a number 0-99 because of preg_replace issue
		$title = preg_replace('/\$(\d)/', '\\\$$1', $title);
		//Replace the old title with the new and return
		return preg_replace('/<title>[^<]*<\/title>/i', '<title>'.$title.'</title>', $head);
	}
	
	function get_title() {
		
		global $wp_query, $wp_locale;
		
		//Custom post/page title?
		if ($post_title = $this->get_postmeta('title'))
			return htmlspecialchars($this->get_title_paged($post_title));
		
		//Custom taxonomy title?
		if (lat_wp::is_tax()) {
			$tax_titles = $this->get_setting('taxonomy_titles');
			if ($tax_title = $tax_titles[$wp_query->get_queried_object_id()])
				return htmlspecialchars($this->get_title_paged($tax_title));
		}
		
		//Get format
		if (!$this->should_rewrite_title()) return '';
		if (!($format = $this->get_title_format())) return '';
		
		//Load post/page titles
		$post_id = 0;
		$post_title = '';
		$parent_title = '';
		if (is_singular()) {
			$post = $wp_query->get_queried_object();
			$post_title = strip_tags( apply_filters( 'single_post_title', $post->post_title, $post ) );
			$post_id = $post->ID;
			
			if ($parent = $post->post_parent) {
				$parent = get_post($parent);
				$parent_title = strip_tags( apply_filters( 'single_post_title', $parent->post_title, $post ) );
			}
		}
		
		//Load date-based archive titles
		if ($m = get_query_var('m')) {
			$year = substr($m, 0, 4);
			$monthnum = intval(substr($m, 4, 2));
			$daynum = intval(substr($m, 6, 2));
		} else {
			$year = get_query_var('year');
			$monthnum = get_query_var('monthnum');
			$daynum = get_query_var('day');
		}
		$month = $wp_locale->get_month($monthnum);
		$monthnum = zeroise($monthnum, 2);
		$day = date('jS', mktime(12,0,0,$monthnum,$daynum,$year));
		$daynum = zeroise($daynum, 2);
		
		//Load category titles
		$cat_title = $cat_titles = $cat_desc = '';
		if (is_category()) {
			$cat_title = single_cat_title('', false);
			$cat_desc = category_description();
		} elseif (count($categories = get_the_category())) {
			$cat_titles = sl_lang_implode($categories, 'name');
			usort($categories, '_usort_terms_by_ID');
			$cat_title = $categories[0]->name;
			$cat_desc = category_description($categories[0]->term_id);
		}
		if (strlen($cat_title) && $this->get_setting('terms_ucwords', true))
			$cat_title = lat_string::tclcwords($cat_title);
		
		//Load tag titles
		$tag_title = $tag_desc = '';
		if (is_tag()) {
			$tag_title = single_tag_title('', false);
			$tag_desc = tag_description();
			
			if ($this->get_setting('terms_ucwords', true))
				$tag_title = lat_string::tclcwords($tag_title);
		}
		
		//Load author titles
		if (is_author()) {
			$author_obj = $wp_query->get_queried_object();
		} elseif (is_singular()) {
			global $authordata;
			$author_obj = $authordata;
		} else {
			$author_obj = null;
		}
		if ($author_obj)
			$author = array(
				  'username' => $author_obj->user_login
				, 'name' => $author_obj->display_name
				, 'firstname' => get_the_author_meta('first_name', $author_obj->ID)
				, 'lastname' => get_the_author_meta('last_name',  $author_obj->ID)
				, 'nickname' => get_the_author_meta('nickname',   $author_obj->ID)
			);
		else
			$author = array(
				  'username' => ''
				, 'name' => ''
				, 'firstname' => ''
				, 'lastname' => ''
				, 'nickname' => ''
			);
		
		$variables = array(
			  '{blog}' => get_bloginfo('name')
			, '{tagline}' => get_bloginfo('description')
			, '{post}' => $post_title
			, '{page}' => $post_title
			, '{page_parent}' => $parent_title
			, '{category}' => $cat_title
			, '{categories}' => $cat_titles
			, '{category_description}' => $cat_desc
			, '{tag}' => $tag_title
			, '{tag_description}' => $tag_desc
			, '{tags}' => sl_lang_implode(get_the_tags($post_id), 'name', true)
			, '{daynum}' => $daynum
			, '{day}' => $day
			, '{monthnum}' => $monthnum
			, '{month}' => $month
			, '{year}' => $year
			, '{author}' => $author['name']
			, '{author_name}' => $author['name']
			, '{author_username}' => $author['username']
			, '{author_firstname}' => $author['firstname']
			, '{author_lastname}' => $author['lastname']
			, '{author_nickname}' => $author['nickname']
			, '{query}' => sl_esc_attr(get_search_query())
			, '{ucquery}' => sl_esc_attr(ucwords(get_search_query()))
			, '{url_words}' => $this->get_url_words($_SERVER['REQUEST_URI'])
		);
		
		$title = str_replace(array_keys($variables), array_values($variables), htmlspecialchars($format));
		
		return $this->get_title_paged($title);
	}
	
	function get_title_paged($title) {
		
		global $wp_query, $numpages;
		
		if (is_paged() || get_query_var('page')) {
			
			if (is_paged()) {
				$num = absint(get_query_var('paged'));
				$max = absint($wp_query->max_num_pages);
			} else {
				$num = absint(get_query_var('page'));
				
				if (is_singular()) {
					$post = $wp_query->get_queried_object();
					$max = count(explode('<!--nextpage-->', $post->post_content));
				} else
					$max = '';
			}
			
			return str_replace(
				array('{title}', '{num}', '{max}'),
				array( $title, $num, $max ),
				$this->get_setting('title_paged'));
		} else
			return $title;
	}
	
	function get_url_words($url) {
		
		//Remove any extensions (.html, .php, etc)
		$url = preg_replace('|\\.[a-zA-Z]{1,4}$|', ' ', $url);
		
		//Turn slashes to >>
		$url = str_replace('/', ' &raquo; ', $url);
		
		//Remove word separators
		$url = str_replace(array('.', '/', '-'), ' ', $url);
		
		//Capitalize the first letter of every word
		$url = explode(' ', $url);
		$url = array_map('trim', $url);
		$url = array_map('ucwords', $url);
		$url = implode(' ', $url);
		$url = trim($url);
		
		return $url;
	}
	
	function postmeta_fields($fields, $screen) {								
		$id = "_sl_keyword";
		$value = sl_esc_attr($this->get_postmeta('keyword'));
		$fields['serp'][9]['keyword'] =
			"<div class='form-group sl textbox'>\n<label class='col-sm-4 col-md-4 control-label' for='$id'>".__('Key word:', 'seolat-tool-plus')."</label>\n<div class='col-sm-4 col-md-4'><input name='$id' id='$id' type='text' value='$value' class='form-control input-sm regular-text' tabindex='2'"
			. " onkeyup=\"javascript:document.getElementById('sl_keyword_wordcount').innerHTML = document.getElementById('_sl_keyword').value.trim().split(/[\.,\/ -!;?]/).filter(Boolean).length\" />"
			. "</div>\n<div class='col-sm-4 col-md-4 help-text'>".sprintf(__('You&#8217;ve Entered %s words.', 'seolat-tool-plus'), "<strong id='sl_keyword_wordcount'>".count(preg_split("/[\.,\/ -!;?]/", trim($value), -1, PREG_SPLIT_NO_EMPTY))."</strong>")
			. "</div>\n<div class='col-sm-12 col-md-12'><div id='sl_check_keyword_result'></div></div></div>\n";

		$id = "_sl_title";
		$value = sl_esc_attr($this->get_postmeta('title'));
		$fields['serp'][10]['title'] =
			"<div class='form-group sl textbox'>\n<label class='col-sm-4 col-md-4 control-label' for='$id'>".__('Title Tag:', 'seolat-tool-plus')."</label>\n<div class='col-sm-4 col-md-4'><input name='$id' id='$id' type='text' value='$value' class='form-control input-sm regular-text' tabindex='2'"
			. " onkeyup=\"javascript:document.getElementById('sl_title_charcount').innerHTML = document.getElementById('_sl_title').value.length\" />"
			. "</div>\n<div class='col-sm-4 col-md-4 help-text'>".sprintf(__('You&#8217;ve Entered %s Characters. Most Search Engines Use Up To 70.', 'seolat-tool-plus'), "<strong id='sl_title_charcount'>".strlen($value)."</strong>")
			. "</div>\n</div>\n";
		
		
		return $fields;
	}
	
	function postmeta_help($help) {
		$help[] = __('<strong>Title Tag</strong> &mdash; The exact contents of the &lt;title&gt; tag. The title appears in visitors&#8217; title bars and in search engine result titles. If this box is left blank, then the <a href="admin.php?page=sl-titles" target="_blank">default post/page titles</a> are used.', 'seolat-tool-plus');
		return $help;
	}
	
	function add_help_tabs($screen) {
		
		$screen->add_help_tab(array(
			  'id' => 'sl-titles-overview'
			, 'title' => __('Overview', 'seolat-tool-plus')
			, 'content' => __("
<ul>
	<li><strong>What it does:</strong> Title Tag Rewriter helps you customize the contents of your website&#8217;s <code>&lt;title&gt;</code> tags. The tag contents are displayed in web browser title bars and in search engine result pages.</li>
	<li><strong>Why it helps:</strong> Proper title rewriting ensures that the keywords in your post/Page titles have greater prominence for search engine spiders and users. This is an important foundation for WordPress SEO.</li>
	<li><strong>How to use it:</strong> Title Tag Rewriter enables recommended settings automatically, so you shouldn&#8217;t need to change anything. If you do wish to edit the rewriting formats, you can do so using the textboxes below (the &#8220;Formats & Variables&#8221; help tab includes additional information on this). You also have the option of overriding the <code>&lt;title&gt;</code> tag of an individual post/page/category/tag/etc. using the appropriate tabs below, or by using the &#8220;Title Tag&#8221; textbox that Title Tag Rewriter adds to the post/page editors.</li>
</ul>
", 'seolat-tool-plus')));
	
	$screen->add_help_tab(array(
			  'id' => 'sl-titles-vars'
			, 'title' => __('Default Formats Tab', 'seolat-tool-plus')
			, 'content' => __("
<p>Various variables, surrounded in {curly brackets}, are provided for use in the title formats. All settings support the {blog} variable, which is replaced with the name of the blog, and the {tagline} variable, which is replaced with the blog tagline as set under <a href='options-general.php'>Settings &rArr; General</a>.</p>

<p>Here&#8217;s information on each of the settings and its supported variables:</p>

<ul>
	<li><strong>Blog Homepage Title</strong> &mdash; Displays on the main blog posts page.</li>
	<li>
		<p><strong>Post Title Format</strong> &mdash; Displays on single-post pages. Supports these variables:</p>
		<ul>
			<li>{post} &mdash; The post&#8217;s title.</li>
			<li>{category} &mdash; The title of the post category with the lowest ID number.</li>
			<li>{categories} &mdash; A natural-language list of the post&#8217;s categories (e.g. &#8220;Category A, Category B, and Category C&#8221;).</li>
			<li>{tags} &mdash; A natural-language list of the post's tags (e.g. &#8220;Tag A, Tag B, and Tag C&#8221;).</li>
			<li>{author} &mdash; The Display Name of the post's author.</li>
			<li>{author_username}, {author_firstname}, {author_lastname}, {author_nickname} &mdash; The username, first name, last name, and nickname of the post&#8217;s author, respectively, as set in his or her profile.</li>
		</ul>
	</li>
	<li>
		<p><strong>Page Title Format</strong> &mdash; Displays on WordPress Pages. Supports these variables:
		<ul>
			<li>{page} &mdash; The page&#8217;s title.</li>
			<li>{page_parent} &mdash; The title of the page&#8217;s parent page.</li>
			<li>{author} &mdash; The Display Name of the page&#8217;s author.</li>
			<li>{author_username}, {author_firstname}, {author_lastname}, {author_nickname} &mdash; The username, first name, last name, and nickname of the page&#8217;s author, respectively, as set in his or her profile.</li>
		</ul>
	</li>
	<li><strong>Category Title Format</strong> &mdash; Displays on category archives. The {category} variable is replaced with the name of the category, and {category_description} is replaced with its description.</li>
	<li><strong>Tag Title Format</strong> &mdash; Displays on tag archives. The {tag} variable is replaced with the name of the tag, and {tag_description} is replaced with its description.</li>
	<li>
		<p><strong>Day Archive Title Format</strong> &mdash; Displays on day archives. Supports these variables:</p>
		<ul>
			<li>{day} &mdash; The day number, with ordinal suffix, e.g. 23rd</li>
			<li>{daynum} &mdash; The two-digit day number, e.g. 23</li>
			<li>{month} &mdash; The name of the month, e.g. April</li>
			<li>{monthnum} &mdash; The two-digit number of the month, e.g. 04</li>
			<li>{year} &mdash; The year, e.g. 2009</li>
		</ul>
	</li>
	<li><strong>Month Archive Title Format</strong> &mdash; Displays on month archives. Supports {month}, {monthnum}, and {year}.</li>
	<li><strong>Year Archive Title Format</strong> &mdash; Displays on year archives. Supports the {year} variable.</li>
	<li><strong>Author Archive Title Format</strong> &mdash; Displays on author archives. Supports the same author variables as the Post Title Format box, i.e. {author}, {author_username}, {author_firstname}, {author_lastname}, and {author_nickname}.</li>
	<li><strong>Search Title Format</strong> &mdash; Displays on the result pages for WordPress&#8217;s blog search function. The {query} variable is replaced with the search query as-is. The {ucwords} variable returns the search query with the first letter of each word capitalized.</li>
	<li>
		<p><strong>404 Title Format</strong> &mdash; Displays whenever a URL doesn&#8217;t go anywhere. Supports this variable:</p>
		<ul>
			<li>{url_words} &mdash; The words used in the error-generating URL. The first letter of each word will be capitalized.</li>
		</ul>
	</li>
	<li>
		<p><strong>Pagination Title Format</strong> &mdash; Displays whenever the visitor is on a subpage (page 2, page 3, etc.) of the homepage or of an archive. Supports these variables:</p>
		<ul>
			<li>{title} &mdash; The title that would normally be displayed on page 1</li>
			<li>{num} &mdash; The current page number (2, 3, etc.)</li>
			<li>{max} &mdash; The total number of subpages available. Would usually be used like this: Page {num} of {max}</li>
		</ul>
	</li>
</ul>
", 'seolat-tool-plus')));
	
	$screen->add_help_tab(array(
			  'id' => 'sl-titles-settings'
			, 'title' => __('Settings Tab', 'seolat-tool-plus')
			, 'content' => __("
<p>Here&#8217;s documentation for the options on the &#8220;Settings&#8221; tab.</p>
<ul>
	<li><strong>Convert lowercase category/tag names to title case when used in title tags</strong> &mdash; If your Tag Title Format is set to <code>{tag}</code> and you have a tag called &#8220;blue widgets,&#8221; your title tag would be <code>blue widgets | My WordPress Blog</code>. Enabling this setting would capitalize the words in &#8220;blue widgets&#8221; so that the title tag would be <code>Blue Widgets | My WordPress Blog</code> instead.</li>
	<li>
		<p><strong>Rewrite Method</strong> &mdash; This setting controls the method by which Title Tag Rewriter edits your site&#8217;s <code>&lt;title&gt;</code> tags.</p>
		<ul>
			<li><strong>Use output buffering</strong> &mdash; This is the &#8220;traditional&#8221; method that most SEO plugins use.
				With this method, SEOLAT Tool Plus will intercept your site&#8217;s <code>&lt;head&gt;</code> tag section as it&#8217;s being outputted, 
				locate the <code>&lt;title&gt;</code> tag, edit its value, and then output the edited <code>&lt;head&gt;</code> data. 
				The good thing about this method is that you don&#8217;t have to edit your theme in any way, as SEOLAT Tool Plus will overwrite 
				whatever your theme puts in your <code>&lt;title&gt;</code> tag. The bad thing is that this output interception takes a few extra 
				milliseconds to complete. If you are concerned about performance, are comfortable editing your theme&#8217;s header.php file, 
				and will remember to edit the header.php file of any new themes you activate, you may want to try the filtering rewrite method.</li>
			<li>
				<p><strong>Use filtering</strong> &mdash; With this method, SEOLAT Tool Plus will register itself with WordPress and will replace 
				WordPress&#8217;s <code>&lt;title&gt;</code> tag output with its own. This method can only edit the text that WordPress itself 
				generates for the <code>&lt;title&gt;</code> tag; the filtering method can&#8217;t edit anything extra your theme may add. 
				For this reason, you need to edit your theme to make sure it&#8217;s only pulling <code>&lt;title&gt;</code> tag data from WordPress 
				and is not adding anything else.</p>
				<p>Here&#8217;s how to set up filtering:</p>
				<ol>
					<li>Go to <a href='theme-editor.php'>Appearance &rArr; Editor</a> (if you get a permissions error, you may be on a WordPress multi-site environment and may not be able to use the filtering rewrite method)</li>
					<li>Click &#8220;Header (header.php)&#8221;</li>
					<li>Look for the <code>&lt;title&gt;</code> start tag and the <code>&lt;/title&gt;</code> end tag</li>
					<li>Edit the text in between those tags so that it looks like this: <code>&lt;title&gt;&lt;?php wp_title(''); ?&gt;&lt;/title&gt;</code></li>
					<li>Click &#8220;Update File&#8221;</li>
					<li>Return to the &#8220;Settings&#8221; tab of Title Tag Rewriter, select &#8220;Use filtering,&#8221; and click &#8220;Save Changes&#8221;</li>
				</ol>
			</li>
		</ul>
	</li>
</ul>
", 'seolat-tool-plus')));
		
		$screen->add_help_tab(array(
			  'id' => 'sl-titles-faq'
			, 'title' => __('FAQ', 'seolat-tool-plus')
			, 'content' => __("
<ul>
	<li><strong>Does the Title Tag Rewriter edit my post/page titles?</strong><br />No. The Title Tag Rewriter edits the <code>&lt;title&gt;</code> tags of your site, not your post/page titles.</li>
	<li><strong>Will rewriting the title tags of my posts change their permalinks/URLs?</strong><br />No.</li>
	<li><strong>What&#8217;s the difference between the &#8220;title&#8221; and the &#8220;title tag&#8221; of a post/page?</strong><br />The &#8220;title&#8221; is the title of your post or page that&#8217;s used in your site&#8217;s theme, in your site&#8217;s admin, in your site&#8217;s RSS feeds, and in your site&#8217;s <code>&lt;title&gt;</code> tags. A <code>&lt;title&gt;</code> tag is the title of a specific webpage, and it appears in your browser&#8217;s title bar and in search result listings. Title Tag Rewriter lets you edit your post&#8217;s <code>&lt;title&gt;</code> tags without editing their actual titles. This means you can edit a post&#8217;s title as it appears in search results, but not as it appears on your site.</li>
</ul>
", 'seolat-tool-plus')));
	
		$screen->add_help_tab(array(
			  'id' => 'sl-titles-troubleshooting'
			, 'title' => __('Troubleshooting', 'seolat-tool-plus')
			, 'content' => __("
<ul>
	<li><strong>Why isn&#8217;t Title Tag Rewriter changing my <code>&lt;title&gt;</code> tags?</strong><br />Try disabling other SEO plugins, as they may be conflicting with SEO LAT+. If you&#8217;re using the default &#8220;output buffering&#8221; rewrite method, check to make sure your theme is <a href='http://johnlamansky.com/wordpress/theme-plugin-hooks/' target='_blank'>plugin-friendly</a>. If you're using the &#8220;filtering&#8221; rewrite method, check your theme&#8217;s <code>header.php</code> file and make sure the <code>&lt;title&gt;</code> tag looks like this: <code>&lt;title&gt;&lt;?php wp_title(''); ?&gt;&lt;/title&gt;</code>.</li>
</ul>
", 'seolat-tool-plus')));
	}
}

}
?>