<?php

/**
 * navigation plugin which generates a better configurable navigation with endless children navigations
 *
 * @author Ahmet Topal
 * @link http://ahmet-topal.com
 * @license http://opensource.org/licenses/MIT
 */
class AT_Navigation {	
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
			if (!$this->at_exclude($page))
			{
				$_split = explode('/', substr($page['url'], strlen($this->settings['base_url'])+1));
				$navigation = array_merge_recursive($navigation, $this->at_recursive($_split, $page, $current_page));
			}
		}
		
		array_multisort($navigation);
		$this->navigation = $navigation;
	}
	
	public function config_loaded(&$settings)
	{
		$this->settings = $settings;
		
		// default id
		if (!isset($this->settings['at_navigation']['id'])) { $this->settings['at_navigation']['id'] = 'at-navigation'; }
		
		// default class
		if (!isset($this->settings['at_navigation']['class'])) { $this->settings['at_navigation']['class'] = 'at-navigation'; }
		
		// default excludes
		$this->settings['at_navigation']['exclude'] = array_merge_recursive(
			array('single' => array(), 'folder' => array()),
			isset($this->settings['at_navigation']['exclude']) ? $this->settings['at_navigation']['exclude'] : array()
		);
	}
	
	public function before_render(&$twig_vars, &$twig)
	{
		$twig_vars['at_navigation']['navigation'] = $this->at_build_navigation($this->navigation, true);
	}

	##
	# HELPER
	##
	
	private function at_build_navigation ( $navigation = array (), $start = false )
	{
		$id = $start ? $this->settings['at_navigation']['id'] : '';
		$class = $start ? $this->settings['at_navigation']['class'] : '';
		$child = '';
		$ul = $start ? '<ul id="%s" class="%s">%s</ul>' : '<ul class="dropdown-menu">%s</ul>';
		
		if ( isset ( $navigation [ '_child' ] ) )
		{
			$_child = $navigation[ '_child' ];
			array_multisort ( $_child );
			
			foreach ( $_child as $c )
			{
				$ul = $start ? '<ul id="%s" class="%s">%s</ul>' : '<ul class="dropdown-menu">%s</ul>';
				$child .= $this -> at_build_navigation ( $c );
			}
			
			$child = $start ? sprintf($ul, $id, $class, $child) : sprintf($ul, $child);
		}
		if ( !isset ( $navigation [ '_child' ] ) && isset ( $navigation [ 'title' ] ) )
		{
			$li = '<li class="' . $navigation [ 'class' ] . '"><a href="' . $navigation['url'] . '" class="' . $navigation [ 'class' ] . '" title="' . $navigation [ 'title' ] . '">' . $navigation [ 'title' ] . '</a>' . $child . '</li>';
		}
		else if ( isset ( $navigation [ '_child' ] ) && isset ( $navigation [ 'title' ] ) )
		{
			$li = '<li class="dropdown"><a data-toggle="dropdown" href="#" class="dropdown-toggle" title="' . $navigation [ 'title' ] . '">' . $navigation [ 'title' ] . '<b class="caret"></b></a>' . $child . '</li>';
		}
		else {
			$li = $child;
		}
		return $li;
	}
	
	private function at_exclude($page)
	{
		$exclude = $this->settings['at_navigation']['exclude'];
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
	
	private function at_recursive($split = array(), $page = array(), $current_page = array())
	{
		$activeClass = (isset($this->settings['at_navigation']['activeClass'])) ? $this->settings['at_navigation']['activeClass'] : 'is-active';
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
				return $this->at_recursive($split, $page, $current_page);
			}
			
			$first = array_shift($split);
			return array('_child' => array($first => $this->at_recursive($split, $page, $current_page)));
		}
	}
}
?>