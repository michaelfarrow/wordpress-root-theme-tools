<?php

class Root_Theme_Tools{

	static public $except = [];

	static public function load( $except = [] ){
		static::$except = $except;

		static::loadExtension('utils');            // Utility functions
		static::loadExtension('init');             // Initial theme setup and constants
		static::loadExtension('wrapper');          // Theme wrapper class
		static::loadExtension('admin');            // Admin
		static::loadExtension('security');         // Security
		static::loadExtension('sidebar');          // Sidebar class
		static::loadExtension('config');           // Configuration
		static::loadExtension('titles');           // Page titles
		static::loadExtension('cleanup');          // Cleanup
		static::loadExtension('nav');              // Custom nav modifications
		static::loadExtension('gallery');          // Custom [gallery] modifications
		static::loadExtension('relative-urls');    // Root relative URLs
		static::loadExtension('widgets');          // Sidebars and widgets
		static::loadExtension('git');              // Git functions
		static::loadExtension('scripts');          // Scripts and stylesheets
		static::loadExtension('fields');           // Advanced Custom Fields
		static::loadExtension('custom');           // Custom functions
		static::loadExtension('custom-post-type'); // Custom post type
		static::loadExtension('image');            // Image functions
		static::loadExtension('map');              // Map functions
	}

	static public function loadExtension( $name ){
		if( ! in_array( $name, static::$except ) ){
			require_once __DIR__ . '/includes/' . $name . '.php';
		}
	}

}
