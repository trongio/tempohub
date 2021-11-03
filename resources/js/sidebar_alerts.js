var alerts_ids = [];
var audioElement = document.createElement('audio'); 
audioElement.setAttribute('src', 'sounds/tejat.mp3');

$(document).ready(function () {
  getAlerts();
  setInterval(function () {
    getAlerts();
  }, 1000 * 100);
});

getAlerts = function getAlerts() {
  $.ajax({
    type: 'GET',
    url: "/recived-alerts",
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function success(data) {
      if (data.success == 'ok') {
        $('.alerts_notifications_container').empty();
        $('.alerts_container').empty();
        $.each(data.alerts, function(key, alert) {
          $.each(alert.get_non_readed_and_today_allerts, function(key, alertdata) {
            var html = '';
            html += '<div class="alert_notification_container alert_data_' + alertdata.id + '">';
            html += '<table class="table">';
            html += '<tr>';
            if(!alertdata.readed) {
              html += '<td><i class="fas fa-circle"></i></td>';
            } else {
              html += '<td><i class="fas fa-circle readed"></i></td>';
            }
            html += '<td><i class="far fa-clock"></i> ' + alertdata.datestamp + '</td>';
            html += '<td><i class="fas fa-times close-alert" data-alert-id="' + alertdata.id + '"></i></td>';
            html += '</tr>';
            html += '<tr><td colspan="3">' + alert.name + '</td></tr>';
            html += '<tr><td colspan="3">' + alertdata.sensor.controller.warehouse.name + ' -> ' + alertdata.sensor.room_to_sensor.room.name + ' -> ' +  alertdata.sensor.name + '</td></tr>';
            if(alertdata.value1 == 1) {
              var difference = Math.round((alertdata.value3 - alertdata.value2) * 100) / 100;
              html += '<tr><td colspan="3">ტემპერატურამ მოიმატა ' + difference +'°C</td></tr>';
            } else {
              var difference = Math.round((alertdata.value2 - alertdata.value3) * 100) / 100;
              html += '<tr><td colspan="3">ტემპერატურამ დაიკლო ' + difference +'°C</td></tr>';
            }
            
            html += '<tr><td colspan="3">დასაშვები ზღვარი: ' + alertdata.value2 +'°C</td></tr>';
            html += '</table></div>';
            if(!alertdata.readed) {
              alarmCount++;
              $('.alerts_notifications_container').append(html);
            }
            $('.alerts_container').append(html);
          })      
        })

        if($('.alerts_notification_container').length > 0) {
          $('.alerts_notification_container').css('display', 'block');
          var currentAlarmCount = $('.alerts_notification_container').length;
          console.log($('.alerts_notification_container').length);
          if(alarmSignal || alarmCount < currentAlarmCount) {
            switchAlarm();
            alarmCount = 0;
          }
        }
      }
    }
  });
};

$('.alert_nav').click(function() {
  $(this).toggleClass('active');
  $('.alerts_sidebar_container').toggleClass('active');
})

var alarmSignal = true;
var alarmCount = 0;
function switchAlarm() {
  var timer, sound;
  sound = new Howl({
    src: 'sounds/tejat.mp3',
  });
  sound.play();
  alarmSignal = false;
}

$(document).on('click', '.close-alert', function() {
  var alertid = $(this).attr('data-alert-id');
  alertReadStatusChange(alertid);
});
function alertReadStatusChange(alertid) {
  console.log(alertid);
  $.ajax({
    type: 'GET',
    url: "/alert-read-status-change",
    data: {
      alertid: alertid,
    },
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function success(data) {
      if (data.success == 'ok') {
        $('.alerts_notifications_container .alert_data_' + alertid).remove();
      }
    }
  });
}