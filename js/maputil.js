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
				$('#suburblist').prop('disabled',true);
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
					let mytable = $('#datatable > tbody');
					let myheader = $('#datatable > thead');
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
						let radiobutton = ($('<td>').attr('class','buttons')
							.append($('<input>').attr('type','radio').attr('name','optradio')
								.click(	function(){
									$.ajax({
										url: baseUrl+"/recordings/update-representative/"+recording.id,
										method: 'PUT'
									}).done(function(response){
										console.log(response);
										alert("successful");
									}).fail(function(response){
										console.log(response);
										alert("fail");
									});
								})));

						let row =$('<tr>')
							.append($('<th>').attr('scope','row').text(count))
							.append($('<td>').text(recording.id))
							.append($('<td>').text(recording.filename))
							.append($('<td>').text(recording.uploadTime))
							.append($('<td>').attr('class','buttons')
								.append($('<button>').attr('type','button').attr('class','btn').text('Download')))
							.append(radiobutton);
						mytable.append(row);
						count++;
					}
					}).fail(function(){
						deferred.reject();
						alert("fail to get data");
					});
			}
		});

		function radioButtonClick(recording){
			console.log("recording ID is: "+recording.id);
			$.ajax({
				url: baseUrl+"/recordings/update-representative/"+recording.id,
				method: 'PUT'
			}).done(function(response){
				console.log(response);
				alert("successful");
			}).fail(function(response){
				console.log(response);
				alert("fail");
			});
		}


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


		// let data = {
		//     City:"Auckland"
		// };
		// $.post(url,data).done((data) => {
		//     console.log(data);
		//
		// });

	});

})(OC, jQuery);