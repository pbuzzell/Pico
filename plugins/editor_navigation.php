<?php

/**
 * navigation plugin which generates a better configurable navigation with endless children navigations in the pico editor
 *
 */
class Editor_Navigation {	
	##
	# VARS
	##
	private $settings = array();
	private $navigation = array();
	
	##
	# HOOKS
	##
	
	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page)
	{
		$navigation = array();
		
		foreach ($pages as $page)
		{
			if (!$this->editor_exclude($page))
			{
				$_split = explode('/', substr($page['url'], strlen($this->settings['base_url'])+1));
				$navigation = array_merge_recursive($navigation, $this->editor_recursive($_split, $page, $current_page));
			}
		}
		
		array_multisort($navigation);
		$this->navigation = $navigation;
	}
	
	public function config_loaded(&$settings)
	{
		$this->settings = $settings;
		
		// default id
		if (!isset($this->settings['editor_navigation']['id'])) { $this->settings['editor_navigation']['id'] = 'at-navigation'; }
		
		// default class
		if (!isset($this->settings['editor_navigation']['class'])) { $this->settings['editor_navigation']['class'] = 'at-navigation'; }
		
		// default excludes
		$this->settings['editor_navigation']['exclude'] = array_merge_recursive(
			array('single' => array(), 'folder' => array()),
			isset($this->settings['editor_navigation']['exclude']) ? $this->settings['editor_navigation']['exclude'] : array()
		);
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
		$twig_vars['editor_navigation']['navigation'] = $this->editor_build_navigation($this->navigation, true);
	}

	##
	# HELPER
	##
	
	private function editor_build_navigation($navigation = array(), $start = false)
	{
		$id = $start ? $this->settings['editor_navigation']['id'] : '';
		$class = $start ? $this->settings['editor_navigation']['class'] : '';
		$child = '';
		$ul = $start ? '<ul class="nav">%s</ul>' : '<ul class="child">%s</ul>';
		
		if (isset($navigation['_child']))
		{
			$_child = $navigation['_child'];
			array_multisort($_child);
			
			foreach ($_child as $c)
			{
				$child .= $this->editor_build_navigation($c);
			}
			
			$child = $start ? sprintf($ul,$child) : sprintf($ul, $child);
		}
		if ( isset ( $navigation [ 'title' ] ) )
		{
			$retVal = '<li class="' . $navigation['class'] . '"><a href="#" data-url="' . $navigation['url'] . '" class="post"><span data-icon="3" aria-hidden="true">' . $navigation['title'] . '</a><a href="' . $navigation['url'] . '" target="_blank" class="view" title="View">5</a><a href="#" data-url="' . $navigation['url'] . '" class="delete" title="Delete">4</a>' . $child. '</li>';
		}
		else
		{
			$retVal = $child;
		}
		
		return html_entity_decode($retVal);
	}
	
	private function editor_exclude($page)
	{
		$exclude = $this->settings['editor_navigation']['exclude'];
		$url = substr($page['url'], strlen($this->settings['base_url'])+1);
		$url = (substr($url, -1) == '/') ? $url : $url.'/';
		
		foreach ($exclude['single'] as $s)
		{	
			$s = (substr($s, -1*strlen('index')) == 'index') ? substr($s, 0, -1*strlen('index')) : $s;
			$s = (substr($s, -1) == '/') ? $s : $s.'/';
			
			if ($url == $s)
			{
				return true;
			}
		}
		
		foreach ($exclude['folder'] as $f)
		{
			$f = (substr($f, -1) == '/') ? $f : $f.'/';
			$is_index = ($f == '' || $f == '/') ? true : false;
			
			if (substr($url, 0, strlen($f)) == $f || $is_index)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function editor_recursive($split = array(), $page = array(), $current_page = array())
	{
		$activeClass = (isset($this->settings['editor_navigation']['activeClass'])) ? $this->settings['editor_navigation']['activeClass'] : 'is-active';
		if (count($split) == 1)
		{			
			$is_index = ($split[0] == '') ? true : false;
			$ret = array(
				'title'			=> $page['title'],
				'url'			=> $page['url'],
				'class'			=> ($page['url'] == $current_page['url']) ? $activeClass : ''
			);
			
			$split0 = ($split[0] == '') ? '_index' : $split[0];
			return array('_child' => array($split0 => $ret));
			return $is_index ? $ret : array('_child' => array($split[0] => $ret));
		}
		else
		{
			if ($split[1] == '')
			{
				array_pop($split);
				return $this->editor_recursive($split, $page, $current_page);
			}
			
			$first = array_shift($split);
			return array('_child' => array($first => $this->editor_recursive($split, $page, $current_page)));
		}
	}
}
?>