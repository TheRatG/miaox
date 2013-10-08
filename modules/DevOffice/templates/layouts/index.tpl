<?php
$bodyClass = $this->_getValueOf( 'bodyClass', '' );
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Developer Zone</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Developer Zone">
		<meta name="author" content="vpak">

		<!-- Le styles -->
		<link href="static/css/bootstrap.min.css" rel="stylesheet">
		<link href="static/css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="static/css/jquery.pnotify.css" rel="stylesheet">
		<link href="static/css/main.css" rel="stylesheet">

		<!-- This is a special version of jQuery with RequireJS built-in -->
		<script data-main="static/scripts/main"
			src="static/scripts/require-jquery.js"></script>
	</head>
	<body class="<?=$bodyClass?>">
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="btn btn-navbar" data-toggle="collapse"
						data-target=".nav-collapse"> <span class="icon-bar"></span> <span
						class="icon-bar"></span> <span class="icon-bar"></span>
					</a> <a class="brand" href="#">Developer Zone</a>
					<div class="nav-collapse">
						<ul class="nav">
							<li class="active"><a href="#">Home</a></li>
							<li class="dropdown">
								 <a data-toggle="dropdown" class="dropdown-toggle" href="#">Access <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="?_view=Access_Group">Groups</a></li>
									<li><a href="?_view=Access_People">People</a></li>
								</ul>
							</li>
							<li><a href="#contact">Contact</a></li>
						</ul>
					</div>
					<!--/.nav-collapse -->
				</div>
			</div>
		</div>

		<div class="container">
			<?=$this->_includeTemplate( $this->_getViewTemplate() );?>
			<footer>
		    	<p>&copy; Company <?=date('Y')?></p>
			</footer>
		</div>
		<!-- /container -->
    </body>
</html>