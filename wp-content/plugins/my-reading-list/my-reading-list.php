<?php

/**
 * Plugin Name:       My Reading List
 * Description:       Create a list of books to be rendered in a dynamic block.
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            Kai Pfeiffer
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-reading-list
 *
 * @package 		  my-reading-list
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class My_Reading_List_Plugin
{

	/**
	 * Registers the custom post type 'book'.
	 */
	static function register_book_post_type()
	{
		register_post_type(
			'book',
			array(
				'labels'       => array(
					'name'          => 'Books',
					'singular_name' => 'Book',
				),
				'public'       => true,
				'has_archive'  => true,
				'supports'     => array('title', 'editor', 'thumbnail'),
				'show_in_rest' => true
			)
		);
	}


	/**
	 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
	 * Behind the scenes, it also registers all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	static function reading_list_block_init()
	{
		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
		 * based on the registered block metadata.
		 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
		 *
		 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
		 */
		if (function_exists('wp_register_block_types_from_metadata_collection')) {
			/**
			 * Registers the block type(s) from the `blocks-manifest.php` file.
			 * 
			 * Additional arguments for the method "register_block_type" must be
			 * injected by the filter "register_block_type_args"
			 * https://developer.wordpress.org/reference/hooks/register_block_type_args/
			 * 
			 * The template for the key 'render_callback' can be defined in the entry
			 * 'render' => 'file:./render.php', in the block.json file.
			 */
			wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
			return;
		}

		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` file.
		 * Added to WordPress 6.7 to improve the performance of block type registration.
		 *
		 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
		 */
		if (function_exists('wp_register_block_metadata_collection')) {
			wp_register_block_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
		}
		/**
		 * Registers the block type(s) in the `blocks-manifest.php` file.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_block_type/
		 */
		$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
		foreach (array_keys($manifest_data) as $block_type) {
			$slug = str_replace('-', '_', $block_type);
			register_block_type(__DIR__ . "/build/{$block_type}", array('render_callback' => array(__CLASS__, 'render_callback')));
		}
	}


	/**
	 * Render callback for the dynamic block.
	 * 
	 * fallback, if method "wp_register_block_types_from_metadata_collection" is not available
	 * 
	 * @param array $attributes Block attributes.
	 * @param string $param Block parameters.
	 * @param WP_Block_Type $object Block type object.
	 * @return string Rendered block HTML.
	 */
	static function render_callback($attributes, $param, $object)
	{
		$slug = ((explode('/', $object->name ?? '/'))[0]);
		$file_name = __DIR__ . "/build/{$slug}/render.php";

		error_log(__CLASS__ . '->' . __LINE__ . '->' . "Rendering block from file: $file_name");
		if (file_exists($file_name)) {
			ob_start();
			include $file_name;
			return ob_get_clean();
		}
	}

	/**
	 * Add featured image to the book post type
	 */
	static function register_book_featured_image()
	{
		register_rest_field(
			'book',
			'featured_image_src',
			array(
				'get_callback' => array(__CLASS__, 'get_book_featured_image_src'),
				'schema'       => null,
			)
		);
	}

	/**
	 * Get the featured image URL for a book post.
	 *
	 * @param array $object The book post object.
	 * @return string|false The URL of the featured image or false if not set.
	 */
	static function get_book_featured_image_src($object)
	{
		if ($object['featured_media']) {
			$img = wp_get_attachment_image_src($object['featured_media'], 'medium');
			return $img[0];
		}
		return false;
	}

	static function run()
	{
		add_action('init', array(__CLASS__, 'reading_list_block_init'));
		add_action('init', array(__CLASS__, 'register_book_post_type'));
		add_action('rest_api_init', array(__CLASS__, 'register_book_featured_image'));
	}
}
My_Reading_List_Plugin::run();
