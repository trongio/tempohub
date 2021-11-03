$(document).ready(function () {
  drawMonitoringPage();

  function drawMonitoringPage() {
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var headerHeight = $('.navbar').height();
    var mainDiv = $('.monitoring-div');

    if (windowWidth < 768) {
      mainDiv.find('.list-div').css('height', 'auto');
      mainDiv.find('.graphycs-div').css('height', 'auto');
    } else {
      mainDiv.find('.list-div').css('height', windowHeight - headerHeight);
      mainDiv.find('.graphycs-div').css('height', windowHeight - headerHeight);
    }
  }

  $(window).resize(function () {
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var mainDiv = $('.monitoring-div');
    var headerHeight = $('.navbar').height();

    if (windowWidth < 768) {
      mainDiv.find('.list-div').css('height', 'auto');
    } else {
      mainDiv.find('.list-div').css('height', windowHeight - headerHeight);
    }
  });
  $('#warehouses_multiselect').multiselect({
    includeSelectAllOption: true,
    selectAllText: 'ყველა',
    allSelectedText: 'ყველა',
    nonSelectedText: 'არცერთი',
    buttonContainer: '<div class="btn-group w-100" />',
    enableFiltering: true,
    templates: {
      button: '<button type="button" class="multiselect dropdown-toggle btn btn-sm btn-block btn-default ladda-button" data-toggle="dropdown" title="" data-original-title="" data-style="slide-down" aria-expanded="false"><span class="ladda-label"><span class="multiselect-selected-text">ყველა</span> <b class="caret"></b></span><span class="ladda-spinner"></span></button>',
      filter: '<div class="multiselect-filter d-flex align-items-center"><input type="search" class="multiselect-search form-control" placeholder="ძებნა" /></div>'
    },
    onSelectAll: function onSelectAll() {
      $('#monitoring_room_list_accordion').find('.warehouse-div').addClass('warehouse-div-on');
    },
    onDeselectAll: function onDeselectAll() {
      $('#monitoring_room_list_accordion').find('.warehouse-div').removeClass('warehouse-div-on');
    },
    onChange: function onChange(option) {
      var warehouseid = $(option).val();
      $('#monitoring_room_list_accordion').find('div[id="warehouse_' + warehouseid + '"]').toggleClass('warehouse-div-on');
    }
  });
  $("#warehouses_multiselect").multiselect('selectAll', true);
  $('.multiselect-search').attr('placeholder', 'ძებნა');
});
$('.warehouse-panel-heading').on('click', function () {
  if ($(this).find('.arrow-icon').hasClass('rotated')) {
    $('.warehouse-panel-heading').find('.arrow-icon').removeClass('rotated');
  } else {
    $('.warehouse-panel-heading').find('.arrow-icon').removeClass('rotated');
    $(this).find('.arrow-icon').addClass('rotated');
  }
});
$('.room-panel-heading').on('click', function () {
  if ($(this).find('.arrow-icon').hasClass('rotated')) {
    $('.room-panel-heading').find('.arrow-icon').removeClass('rotated');
  } else {
    $('.room-panel-heading').find('.arrow-icon').removeClass('rotated');
    $(this).find('.arrow-icon').addClass('rotated');
  }
});
$('.get-sensor-graphycs').on('click', function () {
  var sensorid = $(this).attr('data-sensor-id');
  getLiveDataForGraphycs(sensorid);
  setInterval(function () {
    getLiveDataForGraphycs(sensorid);
  }, 1000 * 60 * 5);
});

function showNonActiveSensorGraphyc() {
  $('#monitoring_graphycs_div').empty();
  $('#monitoring_graphycs_div').append('<h4>სენსორი არ არის აქტიური</h4>');
}

$('.room-panel-heading').on('click', function () {
  $(this).toggleClass('opened-room');
  var roomid = $(this).attr('data-room-id');
  getSensorsDataForMap(roomid);
});
$('#mapDivTab').on('click', function () {
  setTimeout(function () {
    fillRoomMap('from tab');
  }, 500);
});
$(window).resize(function () {
  setTimeout(function () {
    fillRoomMap('from resize');
  }, 500);
});
var pointCoordinates = [];

function getSensorsDataForMap(roomid) {
  if (roomid > 0) {
    $.ajax({
      type: 'GET',
      url: "/get-room-map-data",
      data: {
        roomid: roomid
      },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function success(data) {
        if (data.success == 'ok') {
          var mapimg = '<img src="/images/rooms/' + data.sensors[0].roomid + '/' + data.sensors[0].image + '" />';
          $('.room-map-div').css('min-height', $('.monitoring-div').find('.graphycs-div').height() - 130);
          $('.room-map-div').css('max-height', $('.monitoring-div').find('.graphycs-div').height() - 130);
          $('.room-map-div').empty();
          $('.room-map-div').append(mapimg);
          pointCoordinates = [];
          pointCoordinates.push(data.sensors);
          setTimeout(function () {
            fillRoomMap('from ajax');
          }, 500);
        }
      },
      error: function error(jqXHR, exception) {
        console.log(jqXHR);
        console.log(exception);
      }
    });
  }
}

function fillRoomMap(message) {
  var data = pointCoordinates[0];
  $('.monitoring-pin').remove();
  if(data) {
    for (var i = 0; i < data.length; i++) {
      var targetDiv = $('.room-map-div');
      var targetImg = $('.room-map-div > img');
      var coordinate_x = targetImg.width() * data[i].map_x;
      var coordinate_y = targetImg.height() * data[i].map_y;
      var color = '';
      var tempo = '';
      var humidity = '';
  
      if (data[i].isactive == 1) {
        color = '#15BB66';
  
        if (parseFloat(data[i].temperaturedata.tempo) < data[i].mintemp || parseFloat(data[i].temperaturedata.tempo) > data[i].maxtemp || parseFloat(data[i].temperaturedata.humidity) < data[i].minhum || parseFloat(data[i].temperaturedata.humidity) > data[i].maxhum) {
          color = '#EF5F5F';
        }
  
        if (data[i].temperaturedata.tempo) {
          tempo = '<i class="fas fa-thermometer-half"> ' + parseFloat(data[i].temperaturedata.tempo).toFixed(1) + ' °C';
        }
  
        if (data[i].temperaturedata.humidity) {
          humidity = ' / <i class="fas fa-tint"> ' + parseFloat(data[i].temperaturedata.humidity).toFixed(1) + '%';
        }
      } else {
        color = '#888888';
      }
  
      var pin = '';
      pin += '<div class="monitoring-pin" data-sensor-id="' + data[i].sensorid + '" style="top:' + coordinate_y + 'px; left:' + coordinate_x + 'px; background-color:' + color + '">';
      pin += '<div class="tooltip-box" style="background-color:' + color + '">';
      pin += '<p>' + data[i].sensorname + '</p>';
      pin += '<p class="point-data">' + tempo + humidity + '</p>';
      pin += '</div>';
      pin += '</div>';
      targetDiv.append(pin);

      reverseTooltip(data[i].sensorid);
    }
    selectSensorFromMap();
  }
}

function reverseTooltip(pinId) {
  var windowWidth = $(window).width();
  var objectOffset = $('.monitoring-pin[data-sensor-id="' + pinId + '"]').find('.tooltip-box').offset();
  if(windowWidth - objectOffset.left < 150) {
    $('.monitoring-pin[data-sensor-id="' + pinId + '"]').find('.tooltip-box').css('left', '-125px');
  }
}

function selectSensorFromMap() {
  $('.monitoring-pin').on('click', function () {
    $('.sensor-div').css('background-color', '#ffffff'); // $('.sensors-div').find('#sensor_' + $(this).attr('data-sensor-id')).css('background-color', 'red');
  });
}

function getSensorsLiveData() {
  $.ajax({
    type: 'GET',
    url: "/",
    data: {
      json: true
    },
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function success(data) {
      if (data.success == 'ok') {
        $.each(data.warehouses, function (key, val) {
          $.each(val.warehouse_rooms, function (key, val) {
            for (var i = 0; i < val.sensors.length; i++) {
              if (val.sensors[i].isactive) {
                for (var j = 0; j < val.sensors[i].transmission.temperature_data_backs.length; j++) {
                  if (val.sensors[i].index == val.sensors[i].transmission.temperature_data_backs[j].index) {
                    var sensor = val.sensors[i];
                    var currentDate = sensor.transmission.temperature_data_backs[j];
                    var temperaturaIconColor = currentDate.tempo <= sensor.mintemp || currentDate.tempo >= sensor.maxtemp ? '#EF5F5F' : '#15BB66 ';
                    var humidityIconBg = currentDate.humidity <= sensor.minhum || currentDate.humidity >= sensor.maxhum ? '#EF5F5F' : '#15BB66 ';
                    var batteryIconColor = currentDate.battery_proc < 5 ? '#EF5F5F' : '#15BB66 ';
                    var tempo = '';
                    var humidity = '';
                    var color = '#15BB66';

                    if (currentDate.tempo) {
                      $('.sensors-div').find('#sensor_' + sensor.id).find('.temperature-td').html('<i class="fas fa-thermometer-half" style="color: ' + temperaturaIconColor + '"></i> ' + Math.round(currentDate.tempo * 10) / 10 + '°C');
                      tempo = '<i class="fas fa-thermometer-half"> ' + parseFloat(currentDate.tempo).toFixed(1) + ' °C';
                    } else {
                      $('.sensors-div').find('#sensor_' + sensor.id).find('.temperature-td').html('');
                    }

                    if (currentDate.humidity) {
                      $('.sensors-div').find('#sensor_' + sensor.id).find('.humidity-td').html('<i class="fas fa-tint" style="color: ' + humidityIconBg + '"></i> ' + Math.round(currentDate.humidity * 10) / 10 + '%');
                      humidity = ' / <i class="fas fa-tint"> ' + parseFloat(currentDate.humidity).toFixed(1) + '%';
                    } else {
                      $('.sensors-div').find('#sensor_' + sensor.id).find('.humidity-td').html('');
                    }

                    if (currentDate.battery_proc) {
                      $('.sensors-div').find('#sensor_' + sensor.id).find('.battery-td').html('<i class="fas fa-battery-half" style="color: ' + batteryIconColor + '"></i> ' + currentDate.battery_proc + '%');
                    } else {
                      $('.sensors-div').find('#sensor_' + sensor.id).find('.battery-td').html('');
                    }

                    var targetPin = $('.monitoring-pin[data-sensor-id="' + sensor.id + '"]');
                    if(currentDate.tempo < sensor.mintemp || currentDate.tempo > sensor.maxtemp || currentDate.humidity < sensor.minhum || currentDate.humidity > sensor.maxhum) {
                      color = '#EF5F5F';
                    }
                    targetPin.css('background-color', color);
                    targetPin.find('.tooltip-box').css('background-color', color);
                    targetPin.find('.point-data').empty();
                    targetPin.find('.point-data').append(tempo + humidity);
                  }
                }
              } else {
                var sensor = val.sensors[i];
                var noSignalColor = '#888888';
                $('.sensors-div').find('#sensor_' + sensor.id).find('.temperature-td').html('<i class="fas fa-thermometer-half" style="color: ' + noSignalColor + '"></i>');
                $('.sensors-div').find('#sensor_' + sensor.id).find('.humidity-td').html('<td style="width: 15%" class="humidity-td"><i class="fas fa-tint" style="color: ' + noSignalColor + '"></i></td>');
                $('.sensors-div').find('#sensor_' + sensor.id).find('.battery-td').html('<td style="width: 15%" class="battery-td"><i class="fas fa-battery-half" style="color: ' + noSignalColor + '"></i></td>');
                var targetPin = $('.monitoring-pin[data-sensor-id="' + sensor.id + '"]');
                targetPin.css('background-color', '#888888');
                targetPin.find('.tooltip-box').css('background-color', '#888888');
                targetPin.find('.point-data').empty();
              }
            }
          });
        });
      }
    },
    error: function error(jqXHR, exception) {
      console.log(jqXHR);
      console.log(exception);
    }
  });
}

function getLiveDataForGraphycs(sensorid) {
  if (sensorid > 0) {
    $.ajax({
      type: 'GET',
      url: "/get-sensor-graphycs-data",
      data: {
        sensorid: sensorid
      },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function success(data) {
        if (data.success == 'ok') {
          if (data.sensor.isactive == true) {
            $('#monitoring_graphycs_div').empty();
            fillChart(data);
          } else {
            showNonActiveSensorGraphyc();
          }
        }
      },
      error: function error(jqXHR, exception) {
        console.log(jqXHR);
        console.log(exception);
      }
    });
  }
}

function fillChart(sensorData) {
  var graphycsheight = $(window).height() - $('.navbar').height() - 130;
  Highcharts.setOptions({
    colors: ['#469AFF', '#6AE6F9', '#000000', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
  });
  Highcharts.chart('monitoring_graphycs_div', {
    chart: {
      type: 'spline',
      zoomType: 'xy',
      animation: Highcharts.svg,
      // don't animate in old IE
      width: null,
      height: graphycsheight
    },
    rangeSelector: {
      enabled: true,
      verticalAlign: 'top',
      x: 0,
      y: 0,
      allButtonsEnabled: true,
      selected: 0,
      buttons: [{
        type: 'day',
        count: 1,
        text: sensorData.language_resource.today,
        title: 'View Today'
      }, {
        type: 'day',
        count: 2,
        text: sensorData.language_resource.yesterday,
        title: 'View Yesterday'
      }, {
        type: 'week',
        count: 1,
        text: sensorData.language_resource.lastweek,
        title: 'View Last Week'
      }],
      buttonTheme: {
        width: 80
      }
    },
    time: {
      useUTC: false
    },
    title: {
      text: ''
    },
    accessibility: {
      announceNewData: {
        enabled: true,
        minAnnounceInterval: 15000,
        announcementFormatter: function announcementFormatter(allSeries, newSeries, newPoint) {
          if (newPoint) {
            return 'New point added. Value: ' + newPoint.y;
          }

          return false;
        }
      }
    },
    xAxis: {
      type: 'datetime',
      tickPixelInterval: 150,
      gridLineWidth: 1,
      labels: {
        align: 'left',
        x: 3,
        y: 20
      },
      crosshair: true
    },
    yAxis: [{
      labels: {
        format: '{value}°C',
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      title: {
        text: sensorData.language_resource.temperature,
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      opposite: false,
      plotLines: [{
        value: sensorData.sensor.mintemp,
        width: 2,
        dashStyle : 'shortdash',
        color: Highcharts.getOptions().colors[0],
        label: {
          text: sensorData.language_resource.minallowedtemp + ' ' + sensorData.sensor.mintemp + ' °C',
          style: {
            color: Highcharts.getOptions().colors[0]
          },
          id: 'plotlinemax',
          x: 30
        }
      },{
        value: sensorData.sensor.maxtemp,
        width: 2,
        color: 'rgba(204,0,0,0.75)',
        dashStyle : 'shortdash',
        label: {
          text: sensorData.language_resource.maxallowedtemp + ' ' + sensorData.sensor.maxtemp + ' °C',
          style: {
            color: 'red'
          },
          id: 'plotlinemax',
          x: 30
        }
      }]
    }, {
      gridLineWidth: 1,
      labels: {
        format: '{value}%',
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      title: {
        text: sensorData.language_resource.humidity,
        style: {
          color: Highcharts.getOptions().colors[2]
        }
      },
      opposite: true,
      plotLines: [{
        value: sensorData.sensor.minhum,
        width: 2,
        dashStyle : 'shortdash',
        color: Highcharts.getOptions().colors[0],
        label: {
          text: sensorData.language_resource.minallowehum + ' ' + sensorData.sensor.minhum + ' °C',
          style: {
            color: Highcharts.getOptions().colors[0]
          },
          id: 'plotlinemax',
          align: 'right',
          x: -30
        }
      },{
        value: sensorData.sensor.maxhum,
        width: 2,
        color: 'rgba(204,0,0,0.75)',
        dashStyle : 'shortdash',
        label: {
          text: sensorData.language_resource.maxallowedhum + ' ' + sensorData.sensor.maxhum + ' °C',
          style: {
            color: 'red'
          },
          id: 'plotlinemax',
          align: 'right',
          x: -30
        }
      }]
    }],
    tooltip: {
      backgroundColor: 'rgba(0,0,0,0.75)',
      shared: true,
      crosshair: true,
      useHTML: true,
      borderWidth: 0,
      shadow: false,
      style: {
        color: '#fff',
        border: 'none',
        fontSize: '14px',
        fontFamily: 'BPG Arial Caps',
      },
      formatter: function() {
        var date = new Date(this.x),
            yy = date.getFullYear(),
            mm = date.getMonth(),
            dd = date.getDay(),
            hh = date.getHours(),
            mn = date.getMinutes(),
            ss = date.getSeconds();
        mm = (mm < 10) ? '0' + mm : mm;
        dd = (dd < 10) ? '0' + dd : dd;
        hh = (hh < 10) ? '0' + hh : hh;
        mn = (mn < 10) ? '0' + mn : mn;
        ss = (ss < 10) ? '0' + ss : ss;
        var pointDate = yy + '-' + mm + '-' + dd + ' ' + hh + ':' + mn + ':' + ss;
        var outputHtml = `<small>${pointDate}</small><br/>`;
        if(this.points[0]) {
          outputHtml += `<small><i class="fas fa-thermometer-half" style="color:` + Highcharts.getOptions().colors[0] + `"></i> ` + sensorData.language_resource.temperature + ` ${this.points[0].y}°C</small><br/>`;
        }
        if(this.points[1]) {
          outputHtml += `<small><i class="fas fa-tint" style="color:` + Highcharts.getOptions().colors[1] + `"></i> ` + sensorData.language_resource.humidity + ` ${this.points[1].y}%</small>`;
        }
        return outputHtml;
      }
    },
    legend: {
      align: 'center',
      verticalAlign: 'bottom',
      borderWidth: 0
    },
    series: [{
      name: sensorData.language_resource.temperature,
      type: 'spline',
      tooltip: {
        valueSuffix: ' °C'
      },
      data: function () {
        // generate an array of random data
        var data = [];
        $.each(sensorData.data, function(key, val) {
          var date = key;
          var value = val.tempo;
          data.push({
            x: Date.parse(date),
            y: Math.round(parseFloat(value * 10)) / 10
          });
        })
        return data;
      }()
    }, {
      name: sensorData.language_resource.humidity,
      type: 'spline',
      yAxis: 1,
      tooltip: {
        valueSuffix: ' %'
      },
      data: function () {
        // generate an array of random data
        var data = [];
        $.each(sensorData.data, function(key, val) {
          var date = key;
          var value = val.humidity;
          data.push({
            x: Date.parse(date),
            y: Math.round(parseFloat(value * 10)) / 10
          });
        })
        return data;
      }()
    }],
    responsive: {
      rules: [{
        condition: {
          maxWidth: 500
        },
        chartOptions: {
          legend: {
            floating: false,
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom',
            x: 0,
            y: 0
          },
          yAxis: [{
            labels: {
              align: 'right',
              x: 0,
              y: -6
            },
            showLastLabel: false
          }, {
            labels: {
              align: 'left',
              x: 0,
              y: -6
            },
            showLastLabel: false
          }, {
            visible: false
          }],
          rangeSelector: {
            enabled: true,
            verticalAlign: 'bottom',
            x: 0,
            y: 0,
            allButtonsEnabled: true,
            selected: 0,
            buttons: [{
              type: 'day',
              count: 1,
              text: 'Today',
              title: 'View Today'
            }, {
              type: 'day',
              count: 2,
              text: 'Yesterday',
              title: 'View Yesterday'
            }, {
              type: 'week',
              count: 1,
              text: 'Last Week',
              title: 'View Last Week'
            }],
            buttonTheme: {
              width: 60
            }
          }
        }
      }]
    }
  });
}

setInterval(function(){
  getSensorsLiveData();
}, 1000 * 10);