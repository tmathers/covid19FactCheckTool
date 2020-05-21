<?php include("includes/a_config.php");?>
<!DOCTYPE html>
<html>
<head>
	<?php include("includes/head-tag-contents.php");?>
</head>
<body>


<?php //include("includes/navigation.php");?>

<div class="cover-container d-flex w-100 h-100 mx-auto flex-column" >
<?php include("includes/design-top.php");?>	

	

		<?php 
		if (isset($_GET['search'])) {

			$search = filter_input(INPUT_POST | INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
			echo '
				<div class="container  pb-4 pt-5">
					<form class="form-inline pb-4" method="GET">
						<div class="input-group col pl-0 pr-0">
							<input class="form-control mr-3" type="text" name="search" value="'.$search.'"></input>
							<button type="submit" class="btn btn-primary">Search</button>
						</div>
					</form>';

			include("includes/search.php");
		} else {
			echo '

		<div class="container">
			<div class="cover inner" role="main">

				<div class="mx-auto" style="width: 400px; text-align:center">
					<h1 class="display-3">Infodemic</h1>
					<h1 class="lead" style="font-size:1.6rem;">Fact Check</h1>
				</div>

				<br />

				<form class="" method="GET">
					<div class="input-group">
						<input class="form-control" type="text" name="search" placeholder="Fact-check a URL or question..."></input>
					</div>
					<div class="input-group p-3 ">
						<button type="submit" class="btn btn-primary mx-auto">Search</button>
					</div>
				</form>
			</div>

		';
		}
		?>
	</div>

<?php include("includes/footer.php");?>

</div>

</body>
</html>