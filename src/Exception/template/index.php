<!doctype html>
<html>

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Exception Error</title>
</head>

<style type="text/css" media="all">
	<?php include_once '../template/src/highlight.min.css' ?>
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
		font-size: 16px;
		min-height: 20px;
		border-radius: 7px;
		background-color: #ba9582;
	}

	.highlighted-line {
		padding-right: 10px;
		background-color: #ff00006f;
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
		<h3>Parse Error</h3>
		<span>Syntax error, unexpected T_STRING</span>
	</header>

	<div class="container">
		<span class="h">Source File »</span>

		<div class="code-wrapper">
			<span><b>File: </b>../components/style/App.style</span>

			<pre><code class="language-php">
&lt;?php
	echo 'Hello, world!';
	echo 'This is an error line!';
	echo 'Another line of code.';
?&gt;
				</code></pre>
		</div>
	</div>

	<div class="container" style="margin-top: -5px">
		<span class="h">Call Stack »</span>

		<div class="code-wrapper">
			<span>1. ../components/style/App.style</span>
			<span>2. ../components/style/App.style</span>
		</div>
	</div>

	<script>
		<?php include_once '../template/src/highlight.min.js' ?>
	</script>

	<script>
		document.addEventListener('DOMContentLoaded', event => {
			document.querySelectorAll('pre code').forEach(block => {
				hljs.highlightBlock(block)
				addLineNumbers(block)
				highlightSpecificLines(block, [4])
			})
		})

		function addLineNumbers(block) {
			const lines = block.innerHTML.split('\n')
			block.innerHTML = lines
				.map((line, index) => {
					return `<span class="hljs-line-numbers">${index + 1
						}</span>${line}`
				})
				.join('\n')
		}

		function highlightSpecificLines(block, linesToHighlight) {
			const lines = block.innerHTML.split('\n')
			block.innerHTML = lines
				.map((line, index) => {
					if (linesToHighlight.includes(index + 1)) {
						return `<span class="highlighted-line">${line}</span>`
					}
					return line
				})
				.join('\n')
		}
	</script>
</body>

</html>