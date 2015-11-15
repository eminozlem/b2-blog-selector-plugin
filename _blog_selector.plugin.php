<?php
/**
 *
 * This file implements the Latest posts widget for {@link http://b2evolution.net/}.
 *
 * @copyright (c)2008 by Emin Özlem  - {@link http://eminozlem.com/}.
 *
 * @license GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *
 * @package plugins
 *
 * @author Emin Özlem
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

class blog_selector_plugin extends Plugin
{
	/**
	 * Variables below MUST be overriden by plugin implementations,
	 * either in the subclass declaration or in the subclass constructor.
	 */
	var $name = 'Blog Selector';
	/**
	 * Code, if this is a renderer or pingback plugin.
	 */
	var $code = 'blog_selector';
	var $priority = 30;
	var $version = '0.1';
	var $author = 'Emin Özlem';
	var $author_url = 'http://eminozlem.com';
	var $help_url = 'http://forums.b2evolution.net/blog-selector-plugin';
	var $apply_rendering = 'never';
	var $number_of_installs = 1;

	/**
	 * Init
	 *
	 * This gets called after a plugin has been registered/instantiated.
	 */
	function PluginInit( & $params )
	{
		$this->short_desc = $this->T_('Display a dropdown to select and go to a blog');
		$this->long_desc = $this->T_('Display a dropdown to select and go to a blog');
	}
	
	/**
	* Get definitions for widget specific editable params
	*
	* @see Plugin::GetDefaultSettings()
	* @param local params like 'for_editing' => true
	*/
	function get_widget_param_definitions( $params )
	{
		return  array(
				'title' => array(
					'label' => T_( 'Title' ),
					'size' => 40,
					'note' => T_( 'This is the title to display, $icon$ will be replaced by the feed icon' ),
					'defaultvalue' => T_('Select a blog'),
				),
				'order_by' => array(
					'label' => T_('Order by'),
					'note' => T_('How to sort the blogs'),
					'type' => 'select',
					'options' => get_coll_sort_options(),
					'defaultvalue' => 'order',
				),
				'order_dir' => array(
					'label' => T_('Direction'),
					'note' => T_('How to sort the blogs'),
					'type' => 'radio',
					'options' => array( array( 'ASC', T_('Ascending') ),
										array( 'DESC', T_('Descending') ) ),
					'defaultvalue' => 'ASC',
				),
			);
	}
	
	
	function SkinBeginHtmlHead()
	{	
		global $plugins_url;
		require_css( $plugins_url.'blog_selector_plugin/rsc/chosen.min.css', true );
		require_css( $plugins_url.'blog_selector_plugin/rsc/blogselector.css', true );
		require_js( $plugins_url.'blog_selector_plugin/rsc/chosen.jquery.min.js', true );
		add_js_headline("
			$.noConflict();
			jQuery(document).ready(function($){
			  $('select.blog_sel_w').chosen({disable_search_threshold: 6});
			  $('select.blog_sel_w').bind('change', function () {
				  var url = $(this).find(':selected').data('url')
				  if (url) { // require a URL
					  window.location = url; // redirect
				  }
				  return false;
			  });
			});
		");
		//require_js( $plugins_url.'blog_selector_plugin/rsc/blog.select.init.js', true );
	}
	
	/**
	 * Event handler: SkinTag (widget)
	 *
	 * @param array Associative array of parameters.
	 * @return boolean did we display?
	 *
	 */
	function SkinTag( $params )
	{
		/**
		 * Default params:
		 */
		if(!isset($params['block_start'])) $params['block_start'] = '<div class="bSideItem widget_plugin_'.$this->code.'" style="margin: 0.1em 0">';
		if(!isset($params['block_end'])) $params['block_end'] = "</div>\n";
		if(!isset($params['block_title_start'])) $params['block_title_start'] = '';
		if(!isset($params['block_title_end'])) $params['block_title_end'] = '';
		if(!isset($params['filter'])) $params['filter'] = 'public';
		
		// Display our widget
		$this->disp_coll_select( $params );
	}
	
	
	/**
	 * List of collections/blogs
	 *
	 * @param array MUST contain at least the basic display params
	 */
	function disp_coll_select( $params )	{
		/**
		 * @var Blog
		 */
		global $Blog, $baseurl;

		echo $params['block_start'];
		$filter = $params['filter'];
		$order_by = $params['order_by'];
		$order_dir = $params['order_dir'];

		/**
		 * @var BlogCache
		 */
		$BlogCache = & get_BlogCache();

		if( $filter == 'owner' )
		{	// Load blogs of same owner
			$blog_array = $BlogCache->load_owner_blogs( $Blog->owner_user_ID, $order_by, $order_dir );
		}
		else
		{	// Load all public blogs
			$blog_array = $BlogCache->load_public( $order_by, $order_dir );
		}

		
		$select_options = '';
		foreach( $blog_array as $l_blog_ID )
		{	// Loop through all public blogs:
			$l_Blog = & $BlogCache->get_by_ID( $l_blog_ID );

			// Add item select list:
			$select_options .= '<option data-url="'.$l_Blog->gen_blogurl().'" value="'.$l_blog_ID.'" title="'.$l_Blog->dget( 'tagline', 'formvalue' ).'"';
			$select_options .= ( $Blog && $l_blog_ID == $Blog->ID ) ? ' selected="selected"' : '';
			$select_options .= '>'.$l_Blog->dget( 'shortname', 'formvalue' ).'</option>'."\n";
		}

		if( !empty($select_options) )
		{
			echo '<form class="" action="'.$baseurl.'" method="get">';
			echo '<div class="form-group col-xs-12">';
			echo '<div class="selwrap">';
			echo '<label for="blog" style="display: inline-block;
    font-weight: normal;
    margin-bottom: 5px;
    max-width: 100%;
    padding: 0.2em;" class="control-label">' . $params['title'] . '</label>';
			echo '<select name="blog" class="blog_sel_w chosen" data-placeholder="-Select a blog-">'.$select_options .'</select>';
			echo '</div>';
			echo '</div>';
			echo '<noscript><input type="submit" value="'.T_('Go').'" /></noscript></form>';
		}

		
		echo $params['block_end'];
	}
}

?>