<html>
<head>
<title>Magic Mirror</title>

<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/knockout/knockout-3.0.0.js"></script>
<script src="js/moment.min.js"></script>

<script type="text/javascript">
	var gitHash = '<?php echo trim(`git rev-parse HEAD`) ?>';
</script>

</head>
<body>

	<div class="top left"><div class="small dimmed" data-bind="text: date"></div><div data-bind="html: times"></div></div>
	
	<div class="top right">
		<div class="small dimmed">
		<span class="wi wi-strong-wind xdimmed"></span>
		<span data-bind="text: windSpeed"></span>
		<span data-bind="css: suns"></span>
		<span data-bind="text: sunTime"></span>
		</div>
		<div>
			<span data-bind="css: iconClass" class="icon dimmed wi"></span>
			<span class="temp" data-bind="html: temps">
		</div>
	</div>

	<div class="bottom left small" data-bind="foreach: buses">
	<p data-bind="html: $data.timeTable">.</p>
	</div>
<script>

function AppViewModel() {
    self = this;
	
	this.times  = ko.observable("");
	this.date = ko.observable("");
	this.weatherData = ko.observable(null);	
	this.sunTime = ko.observable(); 
	self.buses = ko.observableArray();	

//Checking sensor
	this.sensorCheck = ko.observable("OFF");
	lastTime = moment().unix();

	this.checkPIR = function()
	{
		var nowTime = moment().unix();
		$.get("Sensor.txt", function(data){
			self.sensorCheck(data);
		});
		if(self.sensorCheck() == "ON" && nowTime-lastTime>= 30 ){
		
			self.updateBus();
			lastTime = moment().unix();
		}
	};
	setInterval(this.checkPIR, 1000);
	
//End Checking sensor
	

	var tripQuestion = 'getBus.php';
	
	this.updateBus = function()
	{
	$.getJSON( tripQuestion,function(result) {
		
		self.buses.removeAll();
		var upDate = moment().format("DD-MMM HH:mm:ss");
		self.buses.push({timeTable: '<font color="grey">'
			+ upDate
			+ "</font>"});
		
        $.each(result.ResponseData.Metros, function(i, data) {
	
		        self.buses.push({timeTable:  '<font color="white">' 
		        + data.Destination + " avgår "
		        + data.DisplayTime
		        + "</font>"});
	
	
        });
        
    });
	
	};
	
	setInterval(this.updateBus, 3600000);
	
	this.windSpeed = ko.computed(function()
	{		
		if(self.weatherData() == null){
		return 'N/a';
		}
		else{
		return Math.round(self.weatherData().wind.speed);
		}
	});

	this.iconClass = ko.computed(function()
	{		
		if(self.weatherData() == null){
		return "wi-day-sunny";
		}
		else{
		return iconTable[self.weatherData().weather[0].icon];
		}
	});
	
	this.temps = ko.computed(function()
		{
			if(self.weatherData() == null){
				return 0 + '&deg;';
			}else{
				return Math.round(self.weatherData().main.temp*10)/10 +'&deg;';
			}
		});
	
	this.suns = ko.computed(function()
	{
		var now = new Date();
		
		if(self.weatherData() == null){
		return "wi-day-sunny";
		}
		else{
			if (self.weatherData().sys.sunrise*1000 < now && self.weatherData().sys.sunset*1000 > now) {
				self.sunTime(new Date(self.weatherData().sys.sunset*1000).toTimeString().substring(0,5));
				return "wi wi-sunset xdimmed";

			}
			else{
				self.sunTime(new Date(self.weatherData().sys.sunrise*1000).toTimeString().substring(0,5));
				return "wi wi-sunrise xdimmed";

			}
		}		
	});
 	
	this.updateClock = function(){
	
	var now = moment();
	var dates = now.format('LLLL').split(' ',4);
	
	self.date(dates[0] + ' ' + dates[1] + ' ' + dates[2] + ' ' + dates[3]);
	var times = now.format('HH') + ':' + now.format('mm') + '<span class="sec">'+now.format('ss')+'</span>';
	self.times(times);
	
	};

	setInterval(this.updateClock,999);
	
	//GET Weather
	var weatherParams = {
    'q':'Stockholm',
    'units':'metric','APPID':'74c81ec9b5ecd02d92f244cf23235856',
    };
	
	var iconTable = {
			'01d':'wi-day-sunny',
			'02d':'wi-day-cloudy',
			'03d':'wi-cloudy',
			'04d':'wi-cloudy-windy',
			'09d':'wi-showers',
			'10d':'wi-rain',
			'11d':'wi-thunderstorm',
			'13d':'wi-snow',
			'50d':'wi-fog',
			'01n':'wi-night-clear',
			'02n':'wi-night-cloudy',
			'03n':'wi-night-cloudy',
			'04n':'wi-night-cloudy',
			'09n':'wi-night-showers',
			'10n':'wi-night-rain',
			'11n':'wi-night-thunderstorm',
			'13n':'wi-night-snow',
			'50n':'wi-night-alt-cloudy-windy'
		};
		
	this.updateCurrentWeather = function()
	{
		$.getJSON('http://api.openweathermap.org/data/2.5/weather', weatherParams, function(data){
			self.weatherData(data);
		});

	};

	this.updateCurrentWeather();
	setInterval(this.updateCurrentWeather, 60000);
	//END of Weather
	
	//SELFUPDATER
	this.checkVersion = function()
	{
		$.getJSON('githash.php', {}, function(json, textStatus) {
			if (json) {
				if (json.gitHash != gitHash) {
					window.location.reload();
					window.location.href=window.location.href;
				}
			}
		});
		
	};
	setInterval(this.checkVersion, 5000);
	
}
ko.applyBindings(new AppViewModel());
</script>



</body>
</html>

