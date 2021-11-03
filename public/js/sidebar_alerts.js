/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/sidebar_alerts.js":
/*!****************************************!*\
  !*** ./resources/js/sidebar_alerts.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

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
        $.each(data.alerts, function (key, alert) {
          $.each(alert.get_non_readed_and_today_allerts, function (key, alertdata) {
            var html = '';
            html += '<div class="alert_notification_container alert_data_' + alertdata.id + '">';
            html += '<table class="table">';
            html += '<tr>';

            if (!alertdata.readed) {
              html += '<td><i class="fas fa-circle"></i></td>';
            } else {
              html += '<td><i class="fas fa-circle readed"></i></td>';
            }

            html += '<td><i class="far fa-clock"></i> ' + alertdata.datestamp + '</td>';
            html += '<td><i class="fas fa-times close-alert" data-alert-id="' + alertdata.id + '"></i></td>';
            html += '</tr>';
            html += '<tr><td colspan="3">' + alert.name + '</td></tr>';
            html += '<tr><td colspan="3">' + alertdata.sensor.controller.warehouse.name + ' -> ' + alertdata.sensor.room_to_sensor.room.name + ' -> ' + alertdata.sensor.name + '</td></tr>';

            if (alertdata.value1 == 1) {
              var difference = Math.round((alertdata.value3 - alertdata.value2) * 100) / 100;
              html += '<tr><td colspan="3">ტემპერატურამ მოიმატა ' + difference + '°C</td></tr>';
            } else {
              var difference = Math.round((alertdata.value2 - alertdata.value3) * 100) / 100;
              html += '<tr><td colspan="3">ტემპერატურამ დაიკლო ' + difference + '°C</td></tr>';
            }

            html += '<tr><td colspan="3">დასაშვები ზღვარი: ' + alertdata.value2 + '°C</td></tr>';
            html += '</table></div>';

            if (!alertdata.readed) {
              alarmCount++;
              $('.alerts_notifications_container').append(html);
            }

            $('.alerts_container').prepend(html);
          });
        });


        if ($('.alerts_notification_container').length > 0) {
          $('.alerts_notification_container').css('display', 'block');
          var currentAlarmCount = $('.alerts_notification_container').length;
          console.log($('.alerts_notification_container').length);

          if (alarmSignal || alarmCount < currentAlarmCount) {
            switchAlarm();
            alarmCount = 0;
          }
        }
      }
    }
  });
};

$('.alert_nav').click(function () {
  $(this).toggleClass('active');
  $('.alerts_sidebar_container').toggleClass('active');
});
var alarmSignal = true;
var alarmCount = 0;

function switchAlarm() {
  var timer, sound;
  sound = new Howl({
    src: 'sounds/tejat.mp3'
  });
  sound.play();
  alarmSignal = false;
}

$(document).on('click','.close-alert', function () {
  var alertid = $(this).attr('data-alert-id');
  alertReadStatusChange(alertid);
});

function alertReadStatusChange(alertid) {
  console.log(alertid);
  $.ajax({
    type: 'GET',
    url: "/alert-read-status-change",
    data: {
      alertid: alertid
    },
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function success(data) {
      if (data.success == 'ok') {
        $('.alert_data_' + alertid).remove();
      }
    }
  });
}

/***/ }),

/***/ "./resources/sass/app.scss":
/*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/login.scss":
/*!***********************************!*\
  !*** ./resources/sass/login.scss ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/sass/main.scss":
/*!**********************************!*\
  !*** ./resources/sass/main.scss ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*******************************************************************************************************************************!*\
  !*** multi ./resources/js/sidebar_alerts.js ./resources/sass/app.scss ./resources/sass/login.scss ./resources/sass/main.scss ***!
  \*******************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! C:\xampp\htdocs\tempohub\resources\js\sidebar_alerts.js */"./resources/js/sidebar_alerts.js");
__webpack_require__(/*! C:\xampp\htdocs\tempohub\resources\sass\app.scss */"./resources/sass/app.scss");
__webpack_require__(/*! C:\xampp\htdocs\tempohub\resources\sass\login.scss */"./resources/sass/login.scss");
module.exports = __webpack_require__(/*! C:\xampp\htdocs\tempohub\resources\sass\main.scss */"./resources/sass/main.scss");


/***/ })

/******/ });