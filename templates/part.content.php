<!--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>-->
<!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>-->
<!--script sheet cannot be used in main.php, has to be in the js file-->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

<div class="container">
	<div class="filter-bar">
		<div id="filter">
			<h1>Filter</h1>
		</div>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6 city">
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text" for="citylist">City</label>
					</div>
					<select class="custom-select" id="citylist">
						<option selected value = "default">Choose...</option>
					</select>
				</div>
			</div>

			<div class="col-lg-6 col-md-6 col-sm-6 suburb">
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text" for="suburblist">Suburb</label>
					</div>
					<select class="custom-select" id="suburblist" disabled>
						<option selected value = "default">Choose...</option>
					</select>
				</div>
			</div>
		</div>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th scope="col">#</th>
					<th scope="col">File name</th>
					<th scope="col">Date</th>
					<th scope="col" class="buttons">Download</th>
					<th scope="col" class="buttons">choose</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<th scope="row">1</th>
					<td>http://asfdqfasfqw</td>
					<td>2017/04/08</td>
					<td class="buttons"><button type="button" class="btn">Download</td>
					<td class="buttons"><input type="radio" name="optradio"></td>
				</tr>
				<tr>
					<th scope="row">2</th>
					<td>http://asfdqfasfqw</td>
					<td>2017/04/08</td>
					<td class="buttons"><button type="button" class="btn">Download</td>
					<td class="buttons"><input type="radio" name="optradio"></td>
				</tr>
				</tbody>
			</table>
		</div>


	</div>
	<!--		<div class="data-panel">datapanel</div>-->
</div>