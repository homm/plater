This module allow use blocks like in Django.

In your layout template define blocks:

	<html>
	<head>
		<title>
			<?php $this->block('title') ?>
				Layout title
			<?php $this->endblock('title') ?>
		</title>
	</head>
	<body>
		<?php $this->block('content') ?>
			Layout content
		<?php $this->endblock('content') ?>
	</body>
	</html>

And add in child view this:

	<?php $this->extend('layout') ?>

	<?php $this->block('content') ?>
		Child content 
	<?php $this->endblock('content') ?>

Now you can simple render child view:

	$this->request->response = View::factory('child');

Blocks can be nested:

	<?php $this->extend('layout') ?>

	<?php $this->block('content') ?>

		<p>Content</p>
	
		<?php $this->block('left') ?>
			<p>Left</p>
		<?php $this->endblock('left') ?>
	
		<?php $this->block('right') ?>
			<p>Right</p>
		<?php $this->endblock('right') ?>
	
	<?php $this->endblock('content') ?>

You can use any vars to discover which template to extend.

	<?php $this->extend('layouts/'.$extends) ?>

Block name in endblock() method is optional. If it passed, module also check right order of 
blocks.
