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
/******/ 	return __webpack_require__(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/reports.js":
/*!*********************************!*\
  !*** ./resources/js/reports.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

var timeinterval = 60;
$('#reports_warehouse_select').change(function () {
  if ($(this).val() != 0) {
    $.ajax({
      type: 'GET',
      url: "/ajaxes/warehouse-rooms",
      data: {
        warehouseid: $(this).val()
      },
      success: function success(data) {
        if (data.success == 'ok') {
          $('#reports_room_select').find('option').remove();
          $.each(data.rooms, function (key, val) {
            var option = '<option value="' + val.id + '">' + val.name + '</option>';
            $('#reports_room_select').attr('disabled', false);
            $('#reports_room_select').append(option);
          });
          getSensorsByRoom();
        }
      },
      error: function error(err) {}
    });
  } else {
    $('#reports_room_select').find('option').remove();
    $('#reports_room_select').attr('disabled', true);
  }
});

function getSensorsByRoom(inheritedroomid) {
  var roomid = inheritedroomid > 0 ? inheritedroomid : $('#reports_room_select').children("option:selected").val();

  if (roomid != 0) {
    $.ajax({
      type: 'GET',
      url: "/ajaxes/warehouse-room-sensors",
      data: {
        roomid: roomid
      },
      success: function success(data) {
        if (data.success == 'ok') {
          $('#reports_sensor_select').find('option').remove();
          $.each(data.sensors, function (key, val) {
            var option = '<option value="' + val.id + '" data-device-imei="' + val.imei + '" data-device-index="' + val.index + '">' + val.name + '</option>';
            $('#reports_sensor_select').attr('disabled', false);
            $('#reports_sensor_select').append(option);
          });
        }
      },
      error: function error(err) {}
    });
  } else {
    $('#reports_sensor_select').find('option').remove();
    $('#reports_sensor_select').attr('disabled', true);
  }
}

$('#reports_room_select').change(function () {
  var roomid = $('#reports_room_select option:selected').val();

  if (roomid != 0) {
    getSensorsByRoom(roomid);
  }
});
$('#generate_report_submit_btn').on('click', function () {
  var target = $('#report-body');
  target.find('tr').remove();
  getReport();
});
//datepickers();

function datepickers() {
  var startdate = $('#startdate');
  var enddate = $('#enddate');
  var today = moment().format('YYYY-MM-DD');
  $(enddate).val(today);
  $(enddate).attr('max', today);
  startdate.change(function () {
    var startdate_val = moment(startdate.val());
    var enddate_val = moment(enddate.val());
    var today = moment();
    var duration = today.diff(startdate_val, 'days');

    if (duration > 30) {
      enddate_val = moment(startdate_val).add(30, 'D');
      enddate_val = moment(enddate_val).format('YYYY-MM-DD');
      enddate.val(enddate_val);
    }
  });
  enddate.change(function () {
    var startdate_val = moment(startdate.val());
    var enddate_val = moment(enddate.val());
    var today = moment();
    var duration = enddate_val.diff(startdate_val, 'days');

    if (duration > 30) {
      startdate_val = moment(enddate_val).subtract(30, 'D');
      startdate_val = moment(startdate_val).format('YYYY-MM-DD');
      startdate.val(startdate_val);
    }
  });
}

$('#open_range_filtr').on('click', function () {
  $('#range-filter-div').toggleClass('opened');
});
changeTableByReportType();
$('input[name="reporttype"]').on('click', function () {
  changeTableByReportType();
});

function changeTableByReportType() {
  reporttype = $('input[name="reporttype"]:checked').val();

  if (reporttype == 'temperature') {
    $('#report_table_div').removeClass('hidden');
    $('#range_report_table_div').addClass('hidden');
  } else if (reporttype == 'temp_range') {
    $('#report_table_div').addClass('hidden');
    $('#range_report_table_div').removeClass('hidden');
  }
}

function getReport() {
  if ($('#reports_warehouse_select').val() != 0 && $('#reports_room_select').val() != 0 && $('#reports_sensor_select option:selected').attr('data-device-imei') != 0) {
    $('#loading-image').show();
    reporttype = $('input[name="reporttype"]:checked').val();
    $.ajax({
      type: 'GET',
      url: "/reports/",
      data: {
        warehouseid: $('#reports_warehouse_select').val(),
        roomid: $('#reports_room_select').val(),
        sensorimei: $('#reports_sensor_select option:selected').attr('data-device-imei'),
        sensorindex: $('#reports_sensor_select option:selected').attr('data-device-index'),
        startdate: $('#startdate').val(),
        enddate: $('#enddate').val(),
        timeinterval: $('#time-interval').val(),
        reporttype: $('input[name="reporttype"]:checked').val(),
        start1: $('#start1').val(),
        end1: $('#end1').val(),
        start2: $('#start2').val(),
        end2: $('#end2').val(),
        start3: $('#start3').val(),
        end3: $('#end3').val(),
        start4: $('#start4').val(),
        end4: $('#end4').val(),
        start5: $('#start5').val(),
        end5: $('#end5').val()
      },
      success: function success(data) {
        if (data.success == 'ok') {
          table.clear().draw();
          table_range.clear().draw();

          if (reporttype == 'temperature') {
            $.each(data.data_array, function (key, val) {
              $(table.column(0).header()).text('თარიღი');
              $(table.column(1).header()).text('საწყობი');
              $(table.column(2).header()).text('ოთახი');
              $(table.column(3).header()).text('სენსორი');
              $(table.column(4).header()).text('ტემპერატურა');
              $(table.column(5).header()).text('ტენიანობა');
              $(table.column(6).header()).text('ელემენტი');
              table.row.add([val.datestamploc, val.warehousename, val.roomname, val.sensorname, val.tempo, val.humidity, val.battery_proc]).draw(false);
            });
          } else if (reporttype == 'temp_range') {
            $.each(data.data_array, function (key, val) {
              $(table_range.column(0).header()).text('მინ.');
              $(table_range.column(1).header()).text('საშუალო.');
              $(table_range.column(2).header()).text('მაქს.');
              $(table_range.column(3).header()).text($('#start1').val() + '°' + '↔' + $('#end1').val() + '°');
              $(table_range.column(4).header()).text($('#start2').val() + '°' + '↔' + $('#end2').val() + '°');
              $(table_range.column(5).header()).text($('#start3').val() + '°' + '↔' + $('#end3').val() + '°');
              $(table_range.column(6).header()).text($('#start4').val() + '°' + '↔' + $('#end4').val() + '°');
              $(table_range.column(7).header()).text($('#start5').val() + '°' + '↔' + $('#end5').val() + '°');
              $(table_range.column(8).header()).text('ჯამში');
              $(table_range.column(9).header()).text($('#start1').val() + '°' + '↔' + $('#end1').val() + '°');
              $(table_range.column(10).header()).text($('#start2').val() + '°' + '↔' + $('#end2').val() + '°');
              $(table_range.column(11).header()).text($('#start3').val() + '°' + '↔' + $('#end3').val() + '°');
              $(table_range.column(12).header()).text($('#start4').val() + '°' + '↔' + $('#end4').val() + '°');
              $(table_range.column(13).header()).text($('#start5').val() + '°' + '↔' + $('#end5').val() + '°');
              $(table_range.column(14).header()).text('ჯამში');
              $(table_range.column(15).header()).text($('#start1').val() + '°' + '↔' + $('#end1').val() + '°');
              $(table_range.column(16).header()).text($('#start2').val() + '°' + '↔' + $('#end2').val() + '°');
              $(table_range.column(17).header()).text($('#start3').val() + '°' + '↔' + $('#end3').val() + '°');
              $(table_range.column(18).header()).text($('#start4').val() + '°' + '↔' + $('#end4').val() + '°');
              $(table_range.column(19).header()).text($('#start5').val() + '°' + '↔' + $('#end5').val() + '°');
              $(table_range.column(20).header()).text('ჯამში');
              table_range.row.add([val.min_tempo, Math.round((val.avg_tempo + Number.EPSILON) * 100) / 100, val.max_tempo, val.rangetime1readable, val.rangetime2readable, val.rangetime3readable, val.rangetime4readable, val.rangetime5readable, val.fulltime, val.range1, val.range2, val.range3, val.range4, val.range5, val.full_count, Math.round((val.range1percent + Number.EPSILON) * 100) / 100 + '%', Math.round((val.range2percent + Number.EPSILON) * 100) / 100 + '%', Math.round((val.range3percent + Number.EPSILON) * 100) / 100 + '%', Math.round((val.range4percent + Number.EPSILON) * 100) / 100 + '%', Math.round((val.range5percent + Number.EPSILON) * 100) / 100 + '%', Math.round((val.fullpercent + Number.EPSILON) * 100) / 100 + '%']).draw(false);
            });
          }
        }
      },
      complete: function complete() {
        $('#loading-image').hide();
      },
      error: function error(err) {}
    });
  } else {
    $('#reports_room_select').find('option').remove();
    $('#reports_room_select').attr('disabled', true);
  }
}

var d = new Date();
var month = d.getMonth() + 1;
var day = d.getDate();
var output = d.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day;
var table = $('#report-table-for-export').DataTable({
  dataSrc: "",
  autoWidth: false,
  autoHeight: false,
  pageLength: 25,
  pagingType: "full_numbers",
  scrollCollapse: true,
  scrollY: false,
  scrollX: false,
  dom: '<"top"pi<"clear">>rt<"bottom"pi<"clear">>',
  buttons: [{
    extend: 'pdfHtml5',
    title: output + '_Report_in_pdf',
    text: '<i class="fas fa-file-pdf" style="color: #EF5F5F"></i> PDF',
    titleAttr: 'Export Excel',
    oSelectorOpts: {
      filter: 'applied',
      order: 'current'
    },
    exportOptions: {
      modifier: {
        page: 'all'
      }
    }
  }, {
    extend: 'excelHtml5',
    title: output + '_Report_in_excel - TempoHub',
    text: '<i class="fas fa-file-excel" style="color: #15BB66"></i> EXCEL',
    titleAttr: 'Export Excel',
    oSelectorOpts: {
      filter: 'applied',
      order: 'current'
    },
    exportOptions: {
      modifier: {
        page: 'all'
      }
    }
  }],
  language: {
    lengthMenu: "Display _MENU_ records per page",
    zeroRecords: "სამწუხაროდ ჩანაწერი არ მოიძებნა.",
    info: "ნაჩვენებია _PAGE_ გვერდი _PAGES_-დან",
    infoEmpty: "ჩანაწერების ჩვენება შეუძლებელია",
    infoFiltered: "(გაფილტვრით მიღებულია _MAX_ შედეგიდან)",
    sSearch: "",
    paginate: {
      first: "პირველი",
      last: "ბოლო",
      next: "შემდეგი",
      previous: "წინა"
    }
  }
});
var table_range = $('#report-table-for-export_range').DataTable({
  dataSrc: "",
  autoWidth: false,
  autoHeight: false,
  pageLength: 25,
  pagingType: "full_numbers",
  scrollCollapse: true,
  scrollY: false,
  scrollX: false,
  dom: 'Bfrtip',
  buttons: [{
    extend: 'pdfHtml5',
    title: output + '_Report_in_pdf',
    text: '<i class="fas fa-file-pdf" style="color: #EF5F5F"></i> PDF',
    titleAttr: 'Export Excel',
    oSelectorOpts: {
      filter: 'applied',
      order: 'current'
    },
    exportOptions: {
      modifier: {
        page: 'all'
      }
    }
  }, {
    extend: 'excelHtml5',
    title: output + '_Report_in_excel - TempoHub',
    text: '<i class="fas fa-file-excel" style="color: #15BB66"></i> EXCEL',
    titleAttr: 'Export Excel',
    oSelectorOpts: {
      filter: 'applied',
      order: 'current'
    },
    exportOptions: {
      modifier: {
        page: 'all'
      }
    }
  }],
  language: {
    lengthMenu: "Display _MENU_ records per page",
    zeroRecords: "სამწუხაროდ ჩანაწერი არ მოიძებნა.",
    info: "ნაჩვენებია _PAGE_ გვერდი _PAGES_-დან",
    infoEmpty: "ჩანაწერების ჩვენება შეუძლებელია",
    infoFiltered: "(გაფილტვრით მიღებულია _MAX_ შედეგიდან)",
    sSearch: "",
    paginate: {
      first: "პირველი",
      last: "ბოლო",
      next: "შემდეგი",
      previous: "წინა"
    }
  }
}); // $('.export_pdf').click(function () {
//   $('#report-body-export').find('tr').remove();
//   exportPdfReport();
// });
// $('.export_excel').click(function () {
//   $('#report-body-export').find('tr').remove();
//   exportExcelReport();
// });
// function makeButtonLoading(buttonId) {
//   var button = $('.' + buttonId);
//   button.addClass('active');
// }
// function makeButtonSuccess(buttonId) {
//   var button = $('.' + buttonId);
//   button.removeClass('active');
// }
// function exportPdfReport() {
//   var warehouseid = $('#reports_warehouse_select').val();
//   var roomid = $('#reports_room_select').val();
//   var startdate = $('#startdate').val();
//   var enddate = $('#enddate').val();
//   timeinterval = $('#time-interval').val();
//   if (warehouseid != 0 && roomid != 0) {
//     target = $('#report-body-export');
//     var file_name = '';
//     makeButtonLoading('export_pdf');
//     $.ajax({
//       type: 'GET',
//       url: "/reports/export",
//       data: {
//         warehouseid: warehouseid,
//         roomid: roomid,
//         startdate: startdate,
//         enddate: enddate,
//         timeinterval: timeinterval
//       },
//       success: function success(data) {
//         if (data.success == 'ok') {
//           var html = '';
//           file_name = data.data_array[0].warehousename + '_' + data.data_array[0].roomname + '_' + startdate + '_' + enddate;
//           $.each(data.data_array, function (key, val) {
//             html += '<tr>';
//             html += '<td>' + val.warehousename + '</td>';
//             html += '<td>' + val.roomname + '</td>';
//             html += '<td>' + val.sensorname + '</td>';
//             html += '<td>' + val.tempo + '</td>';
//             html += '<td>' + val.humidity + '</td>';
//             html += '<td>' + val.battery_proc + '</td>';
//             html += '<td>' + val.datestamploc + '</td>';
//             html += '</tr>';
//           });
//           target.append(html);
//         }
//       },
//       complete: function complete() {
//         var element = document.getElementById("data_table_for_export");
//         exportPdf(file_name, element);
//         makeButtonSuccess('export_pdf');
//       },
//       error: function error(err) {}
//     });
//   }
// }
// function exportExcelReport() {
//   var warehouseid = $('#reports_warehouse_select').val();
//   var roomid = $('#reports_room_select').val();
//   var startdate = $('#startdate').val();
//   var enddate = $('#enddate').val();
//   timeinterval = $('#time-interval').val();
//   if (warehouseid != 0 && roomid != 0) {
//     target = $('#report-body-export');
//     var file_name = '';
//     makeButtonLoading('export_excel');
//     $.ajax({
//       type: 'GET',
//       url: "/reports/export",
//       data: {
//         warehouseid: warehouseid,
//         roomid: roomid,
//         startdate: startdate,
//         enddate: enddate,
//         timeinterval: timeinterval
//       },
//       success: function success(data) {
//         if (data.success == 'ok') {
//           var html = '';
//           file_name = data.data_array[0].warehousename + '_' + data.data_array[0].roomname + '_' + startdate + '_' + enddate;
//           $.each(data.data_array, function (key, val) {
//             html += '<tr>';
//             html += '<td>' + val.warehousename + '</td>';
//             html += '<td>' + val.roomname + '</td>';
//             html += '<td>' + val.sensorname + '</td>';
//             html += '<td>' + val.tempo + '</td>';
//             html += '<td>' + val.humidity + '</td>';
//             html += '<td>' + val.battery_proc + '</td>';
//             html += '<td>' + val.datestamploc + '</td>';
//             html += '</tr>';
//           });
//           target.append(html);
//         }
//       },
//       complete: function complete() {
//         var element = document.getElementById("data_table_for_export");
//         exportExcel('data_table_for_export', file_name);
//         makeButtonSuccess('export_excel');
//       },
//       error: function error(err) {}
//     });
//   }
// }
// function exportPdf(file_name, element) {
//   var opt = {
//     margin: 0.5,
//     filename: file_name,
//     image: {
//       type: 'jpeg',
//       quality: 1
//     },
//     html2canvas: {
//       scale: 1
//     },
//     jsPDF: {
//       unit: 'in',
//       format: 'A4',
//       orientation: 'landscape'
//     }
//   };
//   html2pdf().set(opt).from(element).save();
// }
// var exportExcel = function exportExcel(tableID) {
//   var filename = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
//   var downloadLink;
//   var dataType = 'application/vnd.ms-excel';
//   var tableSelect = document.getElementById(tableID);
//   var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20'); // Specify file name
//   filename = filename ? filename + '.xls' : 'excel_data.xls'; // Create download link element
//   downloadLink = document.createElement("a");
//   document.body.appendChild(downloadLink);
//   if (navigator.msSaveOrOpenBlob) {
//     var blob = new Blob(["\uFEFF", tableHTML], {
//       type: dataType
//     });
//     navigator.msSaveOrOpenBlob(blob, filename);
//   } else {
//     // Create a link to the file
//     downloadLink.href = 'data:' + dataType + ', ' + tableHTML; // Setting the file name
//     downloadLink.download = filename; //triggering the function
//     downloadLink.click();
//   }
// };

/***/ }),

/***/ 3:
/*!***************************************!*\
  !*** multi ./resources/js/reports.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\xampp\htdocs\tempohub\resources\js\reports.js */"./resources/js/reports.js");


/***/ })

/******/ });