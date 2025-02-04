<?php
$code_values = htmlspecialchars(
	implode('', array_values($codeSnippet['parsedCode'])),
	ENT_NOQUOTES,
);
$code_keys = json_encode(array_keys($codeSnippet['parsedCode']));
$sid = session_id();
?>

<!doctype html>
<html>

<head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>

<script>
   document.head.innerHTML = ''
   document.title = 'PhpSlides - Uncaught Exception'
</script>

<style type="text/css" media="all">
   <?php echo file_get_contents(__DIR__ . '/src/highlight.min.css'); ?>
</style>

<style type="text/css" media="all">
   * {
      margin: 0;
      padding: 0;
   }

   body {
      color: white;
      background-color: #510303;
   }

   header {
      padding: 25px 10px;
      padding-left: 20px;
      border-radius: 0 0 25px 25px;
      background-color: #a60303;
   }

   header h3 {
      font-size: 18px;
      font-family: Tahoma;
      margin-bottom: 10px;
      text-transform: uppercase;
      font-weight: 700;
   }

   header span {
      font-size: 14px;
      font-weight: 500;
      font-family: monospace;
      word-break: break-word;
      overflow-wrap: break-word;
   }

   .h {
      color: whitesmoke;
      font-size: 18px;
      font-weight: 700;
      margin-left: 5px;
      font-family: Tahoma, Sans-Serif;
   }

   .container {
      padding: 15px;
      margin-top: 10px;
   }

   .code-wrapper {
      padding: 10px;
      font-size: 15px;
      margin-top: 10px;
      border-radius: 10px;
      background-color: #714935;
      font-family: Tahoma, Sans-Serif;
   }

   .code-wrapper>span {
      color: #e0e0e0;
      display: block;
      margin-bottom: 13px;
      word-break: break-word;
      overflow-wrap: break-word;
   }

   .code-wrapper span:last-child {
      margin-bottom: 0;
   }

   .code-wrapper pre code {
      padding: 10px 0;
      font-size: 14px;
      min-height: 20px;
      border-radius: 7px;
      background-color: #b8a9a9;
   }

   .highlighted-line {
      padding-right: 10px;
      background-color: yellow;
   }

   .hljs-line-numbers {
      padding-left: 12px;
      margin-right: 10px;
      padding-right: 10px;
      user-select: none;
      border-right: 1px solid #ccc;
   }
</style>

<body>
   <header>
      <h3>Uncaught Exception</h3>
      <span><?php echo $message; ?></span>
   </header>

   <div class="container">
      <span class="h">Source File »</span>

      <div class="code-wrapper"> 
         <span><b>File: </b><?php echo ltrim(
         	$file ?? 'Anonymous',
         	\PhpSlides\Core\Foundation\Application::$basePath,
         ) .
         	':' .
         	$line; ?></span>
         <pre><code class="language-php"><?php echo $code_values; ?></code></pre>
      </div>
   </div>

   <div class="container" style="margin-top: -5px">
      <span class="h">Call Stack »</span>

      <div class="code-wrapper">
         <?php foreach ($trace as $key => $value) {
         	$key = $key + 1;
         	$_file =
         		ltrim(
         			$value['file'] ?? 'Anonymous',
         			\PhpSlides\Core\Foundation\Application::$basePath,
         		) ?? 'Unknown';
         	$_line = $value['line'] ?? 1;
         	echo "<span>$key. {$_file}:{$_line}</span>";
         } ?>
      </div>
   </div>

   <script>
      <?php echo file_get_contents(__DIR__ . '/src/highlight.min.js'); ?>
   </script>

   <script>
      document.addEventListener('DOMContentLoaded', (event) => {
         document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
            addLineNumbers(block);
            highlightSpecificLines(block, [<?php echo $line; ?>]); // Highlight lines 2 and 3
         });
      });

      function addLineNumbers(block) {
         const codeLines = <?php echo $code_keys . ";\n"; ?>
         const lines = block.innerHTML.split('\n');

         block.innerHTML = lines.map((line, index) => {
            if (codeLines[index] != undefined) {
               return `<span class="hljs-line-numbers">${codeLines[index] + 1}</span>${line}`;
            }
         }).join('\n');
      }

      function highlightSpecificLines(block, linesToHighlight) {
         const codeLines = <?php echo $code_keys . ";\n"; ?>
         const lines = block.innerHTML.split('\n');

         block.innerHTML = lines.map((line, index) => {
            if (linesToHighlight.includes(codeLines[index] + 1)) {
               return `<span class="highlighted-line">${line}</span>`;
            }
            return line;
         }).join('\n');
      }
   </script>

   <?php
   $protocol =
   	(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
   	$_SERVER['SERVER_PORT'] == 443
   		? 'https://'
   		: 'http://';

   $addr =
   	\PhpSlides\Core\Foundation\Application::$REMOTE_ADDR .
   	"/hot-reload-a$sid/worker";
   $phpslides_version =
   	\PhpSlides\Core\Foundation\Application::PHPSLIDES_VERSION;

   if (getenv('HOT_RELOAD') == 'true'): ?>
      <script>
      /**
       * PHPSLIDES HOT RELOAD GENERATED
       *
       * @version <?php echo $phpslides_version; ?>
       * @author Dave Conco < info@dconco.dev>
       * @copyright 2023 - 2024 Dave Conco
            */
         new Worker('<?php echo $addr; ?>').addEventListener('message', (event) => {
            if (event.data === 'reload') {
               window.location.reload();
            }
         })
      </script>
   <?php endif;
   ?>
</body>

</html>