(function (OC, $) {

	console.log("maputil script.js entered");
	console.log("OC => " + OC);
	console.log(OC);
	console.log("$ => " + $);
	console.log($);

	$(document).ready( () => { // IMPORTANT NOTICE : $.ready( fn ) DOES NOT WORK, IT MUST BE $(document).ready( fn )

		console.log("document ready");

        // this will be map to 'recording#index', the last bit is the 'url' part of the corresponding route, see routes
        let baseUrl = OC.generateUrl("/apps/maputil/recordings"); // '/recordings' is the last bit

        console.log("maputil baseUrl : " + baseUrl);

        $('#get_one').on('click', () => {



        });

        $('#update_one_with_url_param').on('click', () => {



        });

        $('#update_one_with_ajax_body').on('click', () => {



        });

    });

})(OC, jQuery);


// (function (OC, window, $, undefined) {
// 	'use strict';
//
// 	$(document).ready(function () {
//
//         console.log("maputil script.js entered");
//         console.log("OC => " + OC);
//         console.log(OC);
//         console.log("$ => " + $);
//         console.log($);
//         console.log("wtf is this " + undefined);
//
// 	});
//
// })(OC, window, jQuery);