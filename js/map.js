
//Get the modal

mapboxgl.accessToken = 'pk.eyJ1Ijoic2VyZzEyMyIsImEiOiJjamNrY3VwanUzdWY4MnpvM295aGt2bDBiIn0.P_ibesEShm7j2J4pe-yb1Q';
var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/light-v9',
    center: [-103.59179687498357, 40.66995747013945],
    zoom: 3
});
var bounds = new mapboxgl.LngLatBounds();
var price = {min:0,max:0};
var priceArray = [];
var info = document.getElementById("info")
//initial filters state
var filters =  {
	rating: [0,5],
	partner: "all"
}
var compare = [];

map.on('load', function() {
	
  	map.addSource('pin', {
        'type': 'geojson',
        'data': {
          'type': 'FeatureCollection',
          'features': [],
        },
      });

    map.addLayer({
        id: "area_layer",
        type: "circle",
        source: "pin",
        paint: {
            "circle-color": "red",
            "circle-radius": 5,
            "circle-stroke-width": 2,
            "circle-stroke-color": "white",
            
        }
    });
      
	d3.csv("geoDataN.csv", function(err, data) {
		
    	 console.log("DAta",data)  
    	 
    	 pins = data.map(function(pin,key) {
	            bounds.extend([pin.lng, pin.lat]);
	            priceArray.push(+pin['Total Actual Value']);
	            return turf.point([+pin.lng, +pin.lat], {
                    id: key,
                    title: pin['Document No 2'],
                    addr: pin['Property Address'],
                    descr: pin['Legal Description'],
                    //price: +pin['Total Actual Value'],
                    price: +pin['Total Actual Value'],
                    lat: pin.lat,
                    lng: pin.lng
	            });
	      })
          var collection = turf.featureCollection(pins);
          map.getSource('pin').setData(collection);
          
          price.min = Math.min.apply(Math, priceArray);
          price.max = Math.max.apply(Math, priceArray);
          createSlider(price);
   	});
   
      
     map.on('mouseenter', 'area_layer', function() {
        map.getCanvas().style.cursor = 'pointer';
      });
      map.on('mouseleave', 'area_layer', function() {
        map.getCanvas().style.cursor = '';
      });
      
      map.on('click', 'area_layer', function(e) {
      	
        console.log(e, e.features["0"].properties);
		var prop = e.features["0"].properties;
		var whereToLookLatLng = {lng: +prop.lng, lat: +prop.lat};

		console.log(whereToLookLatLng);

		var sv = new google.maps.StreetViewService();
		sv.getPanorama({location: whereToLookLatLng, radius: 50}, function(data){
			
                try {
                    console.log(data)
                    var ManLatLng = data.location.latLng;
                    console.log(ManLatLng)
                    var heading = google.maps.geometry.spherical.computeHeading(ManLatLng, new google.maps.LatLng(whereToLookLatLng.lat, whereToLookLatLng.lng));
                    
                    console.log(heading)
                    var img = 'https://maps.googleapis.com/maps/api/streetview?size=400x400&location='+ManLatLng.lat()+','+ManLatLng.lng()+'&heading='+heading+'&key=AIzaSyABI531IMktJH-Nq24irDSdENZt7LKEHsc';
                    var text = '<img src="'+img+'">';
                    console.log(img)
                    
                    info.innerHTML = text + "<h2>Descr:</h2>" +  JSON.stringify(prop, null, 4)
                } catch (error) {
                    console.log(error)
                    info.innerHTML = "<h2>No data from streetview:</h2>" +  JSON.stringify(prop, null, 4)
                }

                var popup = new mapboxgl.Popup()
                    .setLngLat(e.lngLat)
                    .setHTML('<button class="compare" pinId="'+e.features["0"].properties.id+'">Add to complare</button>')
                    .addTo(map);
				
		});
	})
});

$( document ).ready(function() {

    $(document).on("click",".compare",function() {
        var id = +$(this).attr("pinId");
        console.log(id)
        if(compare.indexOf(id) === -1){
            compare.push(id); 
        }
    });

    var compareData = document.getElementById("compareData");
    $(document).on("click","#showCompare",function() {
        var text = "";
        $("#modal").css("display","block");
        if(compare.length>0){
            compare.map(function(id){
                var item = pins[id].properties
                text += '<div style="float:left;width:200px;height:300px;padding:5px;border:1px solid red">'
                text += '<h3>'+item.addr+'</h3>'
                text += '<p>Price:'+item.price+'</p>'
                text += '<p>Descr:'+item.descr+'</p>'
                text += "</div>"
            })
        }
        compareData.innerHTML = text
    });
    $(document).on("click","#closeCompare",function() {
        $("#modal").css("display","none");
    });
});

function setFilters() {
	
	var part = (filters.partner === 'true')
	var filterRating =  [["<=", 'price', parseFloat(filters.rating[1])],[">=", 'price', parseFloat(filters.rating[0])]];
	var filterPartner = ['==', 'pro_partner', part];
	
	console.log(filterRating)
	
	if(filters.partner !== "all" ){
		console.log("Pro or Non-Pro")
		console.log(filters.partner)
		map.setFilter('area_layer', ['all', filterRating[0], filterRating[1], filterPartner]);
		console.log(map.getFilter('company_layer'));
	}else{
		console.log("all")
		console.log(filters.partner)
		map.setFilter('area_layer',  ['all',filterRating[0],filterRating[1]]);	
	}

}


//SLIDER

function createSlider(price){
	var range = document.getElementById('range');
	range.style.height = '400px';
	range.style.margin = '0 auto 30px';
	noUiSlider.create(range, {
		start: [ price.min,price.max ], // 4 handles, starting at...
		connect: true, // Display a colored bar between the handles
		direction: 'rtl', // Put '0' at the bottom of the slider
		orientation: 'vertical', // Orient the slider vertically
		step: 1000,
		tooltips: [wNumb({ decimals: 0 }), wNumb({ decimals: 0 })],
		range: {
			'min': price.min,
			'max': price.max
		},
	});
	range.noUiSlider.on('set', function( values, handle ) {
		console.log(values, parseFloat(values[0]),+values[0])
		filters.rating = [parseFloat(values[0]),parseFloat(values[1])];
		setFilters()
		console.log(filters.rating)
	});
}