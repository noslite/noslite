<?php
function render_article(string $title, string $content) : string {
  return <<<EOT
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>$title - NOS lite</title>
  <style>
    html,body {
      font-size: 62.5%;
      padding: 0;
      margin: 0;
    }
    body {
      font-size: 1.6em;
      line-height: 1.5;
      font-family: Helvetica,sans-serif;
    }
    p, ul {
      font-size: 1em;
      line-height: 1.5;
      margin: 1.5em 0;
    }
    h2 {
      font-size:  1.125em; /* equiv 18px */
      line-height:  1.3333;
      margin:  1.3333em 0 0 0;
    }
    header {
      padding: 10px 20px;
      border-bottom: 1px solid #ccc;
      color: #AAA;
    }
    main {
      padding: 20px;
      max-width: 600px;
    }
    ul {
      list-style-position: inside;
      margin: 10px 0;
    }
    li {
      padding-bottom: 10px;
    }
  </style>
</head>
<body>
  <header><a href="/">Terug</a> | $title</header>
  <main>
    $content
  </main>
</body>
</html>
EOT;
}