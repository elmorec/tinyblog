<?php
function check_session() {
  if (!isset($_SESSION['tb_m'])) {
    return redirect(ABP . '/m/login');
  }
}

function get_access_log() {
  return ORM::for_table('tb_access') -> find_many();
}

function get_blog_m($title_mf = '') {
  $orm = ORM::for_table('tb_posts');

  if ($title_mf == 'new') {
    return ['title' => '', 'title_mf' => '', 'ts' => '', 'excerpt' => '', 'status' => '', 'author' => '', 'content' => ''];
  }

  return !$title_mf ?
    $orm -> select_many('title', 'title_mf', 'ts', 'excerpt', 'status') -> order_by_desc('ts') -> find_many() :
    $orm -> where('title_mf', str_replace('-', ' ', $title_mf)) -> find_one();
}

// login & logout
map('GET', '/m/login', function() {
  echo phtml('m/login', [], 'm/layout');
});

map('GET', '/m/logout', function() {
  session_destroy();
  return redirect(ABP);
});

map('POST', '/m/login', function() {
  if ($_POST['name'] == config('name')) {
    if ($_POST['passwd'] == config('passwd')) {
      $_SESSION['tb_m'] = true;
      json(['error' => null]);
    } else {
      json(['error' => 'invalid password']);
    }
  } else {
    json(['error' => 'invalid username']);
  }
});

// access log
map('GET', '/m/log', function() {
  check_session();
  echo phtml('m/access-log', [
    'it' => get_access_log(),
    'universal' => ['title' => '']
  ], 'm/layout');
});

// blog list
map('GET', '/m/list', function() {
  check_session();
  echo phtml('m/blog-list', [
    'it' => get_blog_m(),
    'universal' => ['url' => ABP . '/m/e/new', 'title' => 'new']
  ], 'm/layout');
});

// tag
map('POST', '/m/tag', function() {
  check_session();
  $tags = ORM::for_table('tb_tags');
  $name = $_POST['name'];

  // new tag:refer
  if (isset($_POST['refer'])) {
    if ($update = $tags -> where_equal('name', $_POST['refer']) -> find_one()) {
       $update -> set('name', $name) -> save();
       json(['error' => null]);
     } else {
       json(['error' => 'invalid refer']);
     }
  } else {
    if ($tags -> where_equal('name', $name) -> find_one()) {
      json(['error' => 'tag already exist']);
    } else {
      $tags -> create() -> set('name', $name) -> save();
      json(['error' => null, 'tag' => $tags -> where_equal('name', $name) -> find_one() -> as_array()]);
    }
  }
});

// get blog
map('GET', '/m/e/<title>', function($params) {
  check_session();
  $title = $params['title'];
  $blog = get_blog_m($title);

  echo phtml('m/blog', [
    'it' => $blog,
    'tags' => get_blog_tags($blog['tags']),
    'universal' => ['title' => 'save', 'url' => '']
  ], 'm/layout');
});

// update blog
map('POST', '/m/e/<title>', function($params) {
  check_session();
  $_POST['title_mf'] = strtolower($_POST['title_mf']);
  $_POST['tags'] = isset($_POST['tags']) ? join(',', $_POST['tags']) : '';
  $_POST['modified_ts'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

  if (!$_POST['title_mf']) {
    json(['error' => 'please input url']);
  } else {
    $blog = ORM::for_table('tb_posts') -> find_one($_POST['id']);
    if ($blog) {
      $blog -> set($_POST) -> save();
      json(['error' => null, 'redirect' => ABP.'/m/list']);
    } else {
      json(['error' => 'blog does not exist']);
    }
  }

});

// create blog
map('POST', '/m/e', function() {
  check_session();
  $_POST['title_mf'] = strtolower($_POST['title_mf']);
  $_POST['tags'] = isset($_POST['tags']) ? join(',', $_POST['tags']) : '';
  $_POST['ip'] = $_SERVER['REMOTE_ADDR'];

  if (!$_POST['title_mf']) {
    json(['error' => 'please input url']);
  } else {
    $blog = ORM::for_table('tb_posts');
    $blog = ORM::for_table('tb_posts');
    if ($blog -> where_equal('title_mf', $_POST['title_mf']) -> find_one()) {
      json(['error' => 'blog already exist']);
    } else {
      $blog -> create() -> set($_POST) -> save();
      json(['error' => null, 'redirect' => ABP.'/m/list']);
    }
  }
});
