<?php
function render_article(string $title, string $content) : string {
  return <<<EOT
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>$title - NOS lite</title>
  <style>html,body{font-size:62.5%;padding:0;margin:0;display:flex;flex-direction:column}body{font-size:1.6em;line-height:1.5;font-family:Helvetica,sans-serif}p,ul{font-size:1em;line-height:1.5;margin:1.5em 0}p:first-child{margin-top:0}h2{font-size:1.125em;line-height:1.3333;margin:1.3333em 0 0 0}footer{padding:10px 20px;border-top:1px solid #ccc;color:#AAA;max-width:600px;display:flex;justify-content:space-between;align-items:center}main{padding:20px;max-width:600px}ul{margin:10px 0}li{padding-bottom:10px}.b{margin-left:1em}</style>
</head>
<body>
  <main>$content</main>
  <footer>$title <a href="/" class="b">Terug</a></footer>
</body>
</html>
EOT;
}