(function (OC, $) {
	$(document).ready( () => { // IMPORTANT NOTICE : $.ready( fn ) DOES NOT WORK, IT MUST BE $(document).ready( fn )
		console.log("document start");

		function Citylist(baseUrl) {
			this._baseUrl = baseUrl;
			this._cities = [];
		}

		Citylist.prototype = {
			loadAll: function () {
				let deferred = $.Deferred();
				let self = this;
				$.get(this._baseUrl+"/cities").done(function (cities) {
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
				    list.append(option);
				}
			}
			

		};

		// this will be map to 'recording#index', the last bit is the 'url' part of the corresponding route, see routes
		let baseUrl = OC.generateUrl("/apps/maputil"); // '/recordings' is the last bit
		console.log("maputil url: "+baseUrl);

		//sent request to controller to get city which contains recordings.
		let cities = new Citylist(baseUrl);
		cities.loadAll().done(function () {
			alert('success');
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