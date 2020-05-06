<?php include("includes/a_config.php");?>
<!DOCTYPE html>
<html>
<head>
	<?php include("includes/head-tag-contents.php");?>
</head>

<?php include("includes/design-top.php");?>
<?php include("includes/navigation.php");?>

<div class="container" id="main-content">
<center>
	<h2 style="float:center; font-family:'Times New Roman', Times, serif" class="pb-4 pt-3">Fact-Check</h2>

	<?php 
	if (isset($_GET['search'])) {
		include("includes/search.php");
	} else {
		echo '

	<form class="" method="GET">
		<div class="input-group">
			<input class="form-control" type="text" name="search" placeholder="Check Yourself Before You Wreck Yourself! Place your fact here..."></input>
		</div>
		<div class="input-group p-3 ">
			<button type="submit" class="btn btn-outline-dark mx-auto btn-lg">Check it!</button>
		</div>
	</form>
	';
	}
	?>
	<p></p>

</center>
</div>

<br />
<br />
<br />

<?php include("includes/footer.php");?>

</body>
</html>
