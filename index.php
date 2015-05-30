<?php
// load dispatch framework
require './include/dispatch.php';

// load config
config(parse_ini_file('config.ini'));
config('templates', './views');

// constant define
define('HOST', 'http://' . $_SERVER['HTTP_HOST']);
define('ABP', 'http://' . $_SERVER['HTTP_HOST'] . config('url'));
define('DIR', dirname(__FILE__));

error_reporting(0);

date_default_timezone_set(config('timezone'));

// load db info and custom functions after configuration
require './include/common.inc.php';

session_start();

// about
map('GET', '/about', function() {
  render('about', ['title' => 'About Me']);
});

// blog
map('GET', '/', function() {
  render('blog-list', [
    'page' => 1,
    'more' => get_blog(true) > config('blog_per_page')
  ], get_blog());
});
map('GET', '/<param>', function($params) {
  if(!preg_match('/^[1-9][0-9]*$/', $params['param'])) {
    $blog = get_blog($params['param']);

    render('blog', [
      'title' => 'title',
      'tags' => get_blog_tags($blog['tags'])
    ], $blog);
  } else {
    render('blog-list', [
      'page' => $params['param'],
      'more' => get_blog(true) > config('blog_per_page') * $params['param']
    ], get_blog($params['param']));
  }
});

// get blog via tag
map('GET', '/tag/<tag>', function($params) {
  render('blog-list', [
    'page' => 1,
    'tag' => $params['tag'],
    'more' => get_blog_by_tag($params['tag'], true) > config('blog_per_page')
  ], get_blog_by_tag($params['tag']));
});
map('GET', '/tag/<tag>/<page>', function($params) {
  render('blog-list', [
    'page' => $params['page'],
    'tag' => $params['tag'],
    'more' => get_blog_by_tag($params['tag'], true) > config('blog_per_page') * $params['page']
  ], get_blog_by_tag($params['tag'], $params['page']));
});

// error page
map(404, function() {
  render('404', ['title' => '404 Not Find']);
});

// admin
if (config('admin'))
  include 'admin.php';

dispatch();
footprints();
