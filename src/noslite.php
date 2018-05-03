<?php
require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Amsterdam');

const URL = 'http://feeds.nos.nl/nosnieuwsalgemeen';

final class Unavailable extends Exception {}

function path(string $path) : string {
  return __DIR__ . '/..' . $path;
}

function strip_id(string $str) : string {
  // Removes https://nos.nl/l/
  $id = substr($str, 17);

  if (empty($id) || !is_numeric($id)) {
    throw new Unavailable('Something wrong with the id');
  }

  return $id;
}

function internal_link(string $id) : string {
  return '/l/' . $id . '.html';
}

function safe_get_contents(string $url) : string {
  $contents = file_get_contents($url);

  if ($contents === false) {
    throw new Unavailable($url);
  }

  return $contents;
}

function safe_xml_element(string $string) : SimpleXMLElement {
  libxml_use_internal_errors(true);
  try {
    return new SimpleXMLElement($string);
  } catch(Exception $e) {
    libxml_clear_errors();
    throw new Unavailable($e->getMessage());
  }
}

function parse_feed(SimpleXMLElement $rss) : array {
  $as_array = [];

  foreach($rss->channel->item as $item) {
    $id = strip_id($item->link);
     
    $as_array[] = [
      'id' => $id,
      'internal_link' => internal_link($id),
      'external_link' => $item->link,
      'title' => trim($item->title),
      'content' => $item->description
    ];
  }

  return $as_array;
}

function persist(string $path, string $data) {
  file_put_contents($path, $data);
  file_put_contents($path . '.gz', gzencode($data, 9));
}

$loader = new Twig_Loader_Filesystem(path('/templates'));
$twig = new Twig_Environment($loader, [
  'cache' => path('/cache/compiled_templates'),
]);
$twig->addExtension(new \nochso\HtmlCompressTwig\Extension());

$contents = safe_get_contents(URL);
$items = parse_feed(safe_xml_element($contents));

persist(
  path('/site/index.html'),
  $twig->render('index.html', ['items' => $items])
);

foreach ($items as $item) {
  persist(
    path('/site' . $item['internal_link']),
    $twig->render('article.html', $item)
  );
}
