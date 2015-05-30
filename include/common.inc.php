<?php
require 'idiorm.php';

// database config
ORM::configure(array(
  'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'),
  'connection_string' => 'mysql:host=' . config('db_host') . ';port=' . config('db_port') . ';dbname=' . config('db_name'),
  'username' => config('db_user'),
  'password' => config('db_passwd'),

  'id_column_overrides' => array('tb_access' => 'ip')
));

// common functions
function render($template, $meta = [], $data = '') {
  if ($data === '') {
    echo phtml($template, ['meta' => $meta]);
  } else if (($data !== false && $data !== [])) {
     echo phtml($template, ['it' => $data, 'meta' => $meta]);
   } else {
     error(404);
  }
}

function date_pretty($timestring) {
  return date_format(date_create($timestring), 'M j, Y');
}

function parse_md($md) {
  require_once DIR . '/include/Michelf/MarkdownExtra.inc.php';

  return \Michelf\MarkdownExtra::defaultTransform($md);
}

// get blog
function get_blog($param = 1) {
  $orm = ORM::for_table('tb_posts');
  $ppp = config('blog_per_page');

  if ($param === true) {
    if (!isset($_SESSION['tb_blog_count'])) {
      $_SESSION['tb_blog_count'] = $orm -> where('status', 1) -> count();
    }

    return $_SESSION['tb_blog_count'];
  }

  return is_numeric($param) ?
    $orm
      -> select_many('title', 'title_mf', 'ts', 'excerpt')
      -> where('status', 1)
      -> order_by_desc('ts') -> limit($ppp) -> offset(($param - 1) * $ppp)
      -> find_many() :
    $orm
      -> where('title_mf', str_replace('-', ' ', $param))
      -> where_gt('status', 0)
      -> find_one();
}

function get_blog_by_tag($tag, $page = 1) {
  $ppp = config('blog_per_page');

  $blog = ORM::for_table('tb_posts')
    -> select_many('title', 'title_mf', 'ts')
    -> where('status', 1)
    -> where_like('tags', '%' . array_keys(get_tags(), $tag)[0] . '%');

  if ($page === true) {
    $tag_count = 'tb_blog_' . $tag . '_count';
    if (!isset($_SESSION[$tag_count])) {
      $_SESSION[$tag_count] = $blog -> count();
    }

    return $_SESSION[$tag_count];
  } else {
    return $blog ->
      order_by_desc('ts') ->
      limit($ppp) -> offset(($page - 1) * $ppp)
      -> find_many();
  }
}

// tag
function get_tags() {
  if (!isset($_SESSION['tb_blog_tags'])) {
    $tags = [];
    foreach (ORM::for_table('tb_tags') -> find_many() as $tag) {
      $tags[ $tag['id'] ] = $tag['name'];
    }

    $_SESSION['tb_blog_tags'] = $tags;
  }

  return $_SESSION['tb_blog_tags'];
}

function get_blog_tags($tag_ids) {
  if (!$tag_ids) return [''];

  $all_tags = get_tags();

  $tags = [];

  foreach (explode(',', $tag_ids) as $tag) {
    array_push($tags, $all_tags[$tag]);
  }

  return $tags;
}

// record access info
function footprints() {
  if (isset($_SERVER['HTTP_REFERER'])) return;

  $ip = $_SERVER['REMOTE_ADDR'];
  $ts = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
  $ua = $_SERVER['HTTP_USER_AGENT'];

  $tk = ORM::for_table('tb_access');
  $ct = $tk -> where('ip', $ip) -> find_one();

  !$ct ?
    $tk -> create() -> set(['ip' => $ip, 'first_ts' => $ts, 'last_ts' => $ts, 'ua' => $ua]) -> save() :
    $tk -> where('ip', $ip) -> find_one() -> set(['last_ts' => $ts, 'freq' => ($ct -> freq) + 1]) -> save();
}
