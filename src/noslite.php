<?php

require_once(__DIR__ . '/index_template.php');
require_once(__DIR__ . '/article_template.php');

function strip_id(string $id) : string {
  // Remove [http://nos.nl/l/]2193163
  return substr($id, 16);
}

$string = file_get_contents('http://feeds.nos.nl/nosnieuwsalgemeen');
$rss = new SimpleXMLElement($string);

$list_items = '<strong>Algemeen nieuws</strong><ul>';
foreach($rss->channel->item as $item) {
  $id = strip_id($item->link);
  $list_items .= '<li><a href="l/' . $id. '.html">' . $item->title . '</a></li>';

  file_put_contents(__DIR__ . '/../site/l/' . $id . '.html', render_article($item->title, $item->description, $item->link));
}
$list_items .= '</ul>';

$footer = 'Laatste update: ' . date('H:i') . '. Bron: <a href="https://nos.nl/">nos.nl</a>';

file_put_contents(__DIR__ . '/../site/index.html', render_index($footer, $list_items));

