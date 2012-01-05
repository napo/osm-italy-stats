//Global variables
	var map;
	var start_lat=44;
	var start_lon=10;
	var start_zoom=7;


	function setExtent(minLon, minLat, maxLon, maxLat){
		var bounds = new OpenLayers.Bounds(minLon, minLat, maxLon, maxLat); //.transform(new OpenLayers.Projection("EPSG:4326"),map.getProjectionObject()); 
		map.zoomToExtent(bounds);
	}

   function init(){
         map = new OpenLayers.Map( 'map' );

			var layer = new OpenLayers.Layer.WMS( "VMap0", 
                  "/osm/tilecache/tilecache.cgi?", {layers: 'osm', 
format: 'image/png' },
               {
					maxExtent: new OpenLayers.Bounds(735521.375,4330996,2139247.25,5958113.5),
					resolutions: [8819.4396799999995, 4409.7198399999997, 2204.8599199999999, 1102.4299599999999, 551.21497999999997, 275.60748999999998, 137.80374499999999, 68.901872499999996, 34.450936249999998, 17.225468124999999, 8.6127340624999995, 4.3063670312499998, 2.1531835156249999, 1.0765917578124999, 0.53829587890624997, 0.26914793945312498, 0.13457396972656249, 0.067286984863281246, 0.033643492431640623, 0.016821746215820312],                	
               	units: 'm',
               	projection:new OpenLayers.Projection("EPSG:900913"),                	
               	transitionEffect:'resize'
				} 


			 );
         map.addLayer(layer);


         map.addLayers([layer]);

			if (!map.getCenter()) map.zoomToMaxExtent(); 

         map.addControl(new OpenLayers.Control.Scale('scale'));
         map.addControl(new OpenLayers.Control.Permalink('permalink'));
         map.addControl(new OpenLayers.Control.MousePosition());

			//Create an instance of the wkt reader/writer			
			//wkt = new OpenLayers.Format.WKT();
      }

		
	/* OpenLayers.Control.Click: create a OL controls to deal with click
	OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {                
	    defaultHandlerOptions: {
	        'single': true,
	        'double': false,
	        'pixelTolerance': 0,
	        'stopSingle': false,
	        'stopDouble': false
	    },

	    initialize: function(options) {
	        this.handlerOptions = OpenLayers.Util.extend(
	            {}, this.defaultHandlerOptions
	        );
	        OpenLayers.Control.prototype.initialize.apply(
	            this, arguments
	        ); 
	        this.handler = new OpenLayers.Handler.Click(
	            this, {
	                'click': this.triggerSingleClick
	            }, this.handlerOptions
	        );
	    }, 

		//trigger fuctions, connected to single click
	    triggerSingleClick: function(e) 
	    	{
				//Call the function startEndspire
				var lonlat = map.getLonLatFromViewPortPx(e.xy);	
				
				map.setCenter(lonlat,15);

				lonlat = lonlat.transform(map.getProjectionObject(),new OpenLayers.Projection("EPSG:4326"));
		
				//custom_function(lonlat.lon,lonlat.lat);
	    	}

	});
*/
