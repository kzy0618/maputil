<!--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>-->
<!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>-->
<!--script sheet cannot be used in main.php, has to be in the js file-->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

<div class="container">
	<div class="filter-bar bg-light">
		<div id="filter">
			<h1>Filter</h1>
		</div>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6 city">
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text" for="citylist">@City</label>
					</div>
					<select class="custom-select" id="citylist">
						<option selected value = "default">Choose...</option>
					</select>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6 suburb">
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text" for="suburblist">@Suburb</label>
					</div>
					<select class="custom-select" id="suburblist" disabled>
						<option selected value = "default">Choose...</option>
					</select>
				</div>
			</div>
		</div>
		<div class="row" >
			<div class="col-lg-6 col-md-6 col-sm-6 city">
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text" for="typeList">@Type</label>
					</div>
					<select class="custom-select" id="citylist" disabled>
						<option selected value = "default">Choose...</option>
						<option selected value = "word">word</option>
						<option selected value = "sentence">sentence</option>
						<option selected value = "list_word">list_word</option>
						<option selected value = "short_sentence">short_sentence</option>
						<option selected value = "unclassified">unclassified</option>
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="data-content">
		<div class="tab">
			<button class="tablinks active" id="representativeButton">Representative</button>
			<button class="tablinks" id="standalongButton">Standalong</button>
		</div>

			<div class="tab-content tabcontent" id="representative">
				<div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
					<div class="table-responsive">
						<table class="table table-hover" id="representative">
							<thead>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="tabcontent tab-content" id="stand-along">
				<div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
					<div class="table-responsive">
						<table class="table table-hover" id="stand-along">
							<thead>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>
	</div>
</div>