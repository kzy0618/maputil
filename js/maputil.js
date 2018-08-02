(function (OC, $) {
	$(document).ready( () => { // IMPORTANT NOTICE : $.ready( fn ) DOES NOT WORK, IT MUST BE $(document).ready( fn )
		console.log("document start");

		let citychoosen = "default";
		let suburbchoosen = "default";
		function Citylist(baseUrl) {
			this._baseUrl = baseUrl;
			this._cities = [];
		}

		Citylist.prototype = {
			loadAll: function () {
				let deferred = $.Deferred();
				let self = this;
				$.get(self._baseUrl+"/cities").done(function (cities) {
					self._cities = cities;
					self.display(cities);
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			display: function(cities){
				let list = $('#citylist');
				for(let i = 0; i < cities.length; i++){
				    let option = document.createElement("option");
				    option.text = cities[i];
				    option.value = cities[i];
				    list.append(option);
				}
			}
		};

		$('#citylist').change(function() {
			let deferred = $.Deferred();
			let city = $('#citylist').val();
			citychoosen = city;
			if (city != "default") {
				$('#suburblist').prop('disabled',false);
				$.get(baseUrl + "/suburbsAt/" + city).done(function (suburbs) {
					let list = $('#suburblist');
					let defaultPption = document.createElement("option");
					defaultPption.text = "Choose...";
					list.empty().append(defaultPption);
					for (let i = 0; i < suburbs.length; i++) {
						let option = document.createElement("option");
						option.text = suburbs[i];
						list.append(option);
					}
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
					alert("fail to get suburb lists");
				});
			}else if(city == "default"){
				let defaultPption = document.createElement("option");
				defaultPption.text = "Choose...";
				$('#suburblist').empty().append(defaultPption).prop('disabled',true);
			}
			else{
				alert("choose a city");
			}
			return deferred.promise();
		});

		$('#suburblist').change(function(data){
			let deferred = $.Deferred();
			let suburb = $('#suburblist').val();
			suburbchoosen = suburb;
			if (suburb != "default") {
				$.get(baseUrl+"/recordings/"+citychoosen+"/"+suburbchoosen).done(function(recordings){
					console.log(recordings);

					//Dynamically inserting representative table items
					let mytable = $('table#representative > tbody');
					let myheader = $('table#representative > thead');
					mytable.html('');
					myheader.html('');
					let header =$('<tr>')
						.append($('<th>').attr('scope','col').text('#'))
						.append($('<th>').attr('scope','col').text('ID'))
						.append($('<th>').attr('scope','col').text('Filename'))
						.append($('<th>').attr('scope','col').text('Upload Date'))
						.append($('<th>').attr('scope','col').text('Download').attr('class','buttons'))
						.append($('<th>').attr('scope','col').text('Choose').attr('class','buttons'));
					myheader.append(header);
					let count = 1;
					for(let i = recordings.length-1; i>=0; i--){
						let recording = recordings[i];
						let radiobutton = $('<input>').attr('type','radio').attr('name','optradio')
								.on('click', () => {
									$.ajax(baseUrl+"/recordings/update-representative/"+recording.id,{
										method: 'PUT',
										contentType: 'application/json'
									}).done(function(response){
										console.log(response);
										alert("table is updated");
									}).fail(function(response){
										console.log(response);
										alert("fail");
									});
								});
						if(recording.isRepresentative == 1){
							console.log("chekced id: "+ recording.id);
							radiobutton.attr('checked', true);
						}

						let downloadButton = $('<button>').attr('type','button').addClass('btn').html('Download')
							.on('click', function() {
								$.get(baseUrl+"/download/"+recording.id).done(function(response){
										alert("success");
										console.log(response);
									}).fail();
							});

						let row =$('<tr>')
							.append($('<th>').attr('scope','row').text(count))
							.append($('<td>').text(recording.id))
							.append($('<td>').text(recording.filename))
							.append($('<td>').text(recording.uploadTime))
							.append($('<td>').attr('class','buttons')
								.append(downloadButton))
							.append($('<td>').attr('class','buttons').append(radiobutton));
						mytable.append(row);
						count++;
					}

					//Dynamically inserting standalong table items
					mytable = $('table#stand-along > tbody');
					myheader = $('table#stand-along > thead');
					mytable.html('');
					myheader.html('');
					header =$('<tr>')
						.append($('<th>').attr('scope','col').text('#'))
						.append($('<th>').attr('scope','col').text('ID'))
						.append($('<th>').attr('scope','col').text('Filename'))
						.append($('<th>').attr('scope','col').text('Upload Date'))
						.append($('<th>').attr('scope','col').text('Download').attr('class','buttons'))
						.append($('<th>').attr('scope','col').text('Check').attr('class','buttons'));
					myheader.append(header);

					count = 1;
					for(let i = recordings.length-1; i>=0; i--){
						let recording = recordings[i];
						let checkbox = $('<input>').attr('type','checkbox').attr('name','optcheck')
								.on('click', () => {
									$.ajax(baseUrl+"/recordings/update-standalone/"+recording.id,{
										method: 'PUT',
										contentType: 'application/json'
									}).done(function(response){
										console.log(response);
										alert("table is updated");
									}).fail(function(response){
										console.log(response);
										alert("fail");
									});
								});
						if(recording.isStandalone == 1){
							checkbox.prop('checked',true);
						}

						let row =$('<tr>')
							.append($('<th>').attr('scope','row').text(count))
							.append($('<td>').text(recording.id))
							.append($('<td>').text(recording.filename))
							.append($('<td>').text(recording.uploadTime))
							.append($('<td>').attr('class','buttons')
								.append($('<button>').attr('type','button').attr('class','btn').text('Download')))
							.append(checkbox)
							.append(($('<td>').attr('class','buttons').append(checkbox)));
						mytable.append(row);
						count++;
					}

					}).fail(function(){
						deferred.reject();
						alert("fail to get data");
					});


			}
		});

		let tablinks = $('.tablinks');
		let representativeButton = $('#representativeButton');
		let standalongButton = $('#standalongButton');

		standalongButton.on('click', () => {
			let tabcontent, tablinks;
			tabcontent = $('.tabcontent');
			for (let j = 0; j < tabcontent.length; j++) {
				tabcontent[j].style.display = "none";
			}
			tablinks = $('.tablinks');
			for (let j = 0; j < tablinks.length; j++) {
				tablinks[j].className = tablinks[j].className.replace(" active", "");
			}
			$('div#stand-along').show();
			$('#standalongButton').addClass("active");
		});

		representativeButton.on('click', () => {
			let tabcontent, tablinks;
			tabcontent = $('.tabcontent');
			for (let j = 0; j < tabcontent.length; j++) {
				tabcontent[j].style.display = "none";
			}
			tablinks = $('.tablinks');
			for (let j = 0; j < tablinks.length; j++) {
				tablinks[j].className = tablinks[j].className.replace(" active", "");
			}
			$('div#representative').show();
			$('#representativeButton').addClass("active");
		});



		// this will be map to 'recording#index', the last bit is the 'url' part of the corresponding route, see routes
		let baseUrl = OC.generateUrl("/apps/maputil"); // '/recordings' is the last bit
		console.log("maputil url: "+baseUrl);

		//sent request to controller to get city which contains recordings.
		let cities = new Citylist(baseUrl);
		cities.loadAll().done(function () {
			console.log('data retrieve success');
		}).fail(function () {
			alert('Could not load notes');
		});

	});

})(OC, jQuery);