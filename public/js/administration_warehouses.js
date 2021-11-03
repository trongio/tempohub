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
/******/ 	return __webpack_require__(__webpack_require__.s = 4);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/administration_warehouses.js":
/*!***************************************************!*\
  !*** ./resources/js/administration_warehouses.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

deleteWarehouse();
editWarehouse();

function deleteWarehouse() {
  $('.confirm-delete-warehouse').on('click', function () {
    var warehouseid = $(this).attr('data-warehouse');

    if (warehouseid) {
      $.ajax({
        type: 'GET',
        url: "/administration/delete-warehouse/" + warehouseid,
        data: {
          id: warehouseid
        },
        success: function success(data) {
          if (data.success == 'ok') {
            var table = $('#warehouse-table');
            table.find('tr[data-warehouse-id="' + warehouseid + '"]').hide();
            $('#delete_warehouse_' + warehouseid).modal('hide');
            showMessage(data.alertType, data.message);
          }
        },
        error: function error(jqXHR, exception) {
          showMessage(data.alertType, data.message);
        }
      });
    }
  });
}

function editWarehouse() {
  $('.confirm-edit-warehouse').on('click', function () {
    var warehouseid = $(this).attr('data-warehouse');
    var firmaid = $('#edit_warehouse_' + warehouseid).find('input[name="firmaid"]').val();

    var _token = $('#edit_warehouse_' + warehouseid).find('input[name="_token"]').val();

    var name = $('#edit_warehouse_' + warehouseid).find('input[name="name"]').val();
    var isactive = $('#edit_warehouse_' + warehouseid).find('select[name="isactive"]').val();

    if (1 > 0) {
      $.ajax({
        type: 'POST',
        url: "/administration/edit-warehouse",
        data: {
          id: warehouseid,
          _token: _token,
          firmaid: firmaid,
          name: name,
          isactive: isactive
        },
        success: function success(data) {
          if (data.success == 'ok') {
            var table = $('#warehouse-table');
            table.find('tr[data-warehouse-id="' + data.warehouse.id + '"]').empty();
            table.find('tr[data-warehouse-id="' + data.warehouse.id + '"]').append(data.html);
            $('#edit_warehouse_' + warehouseid).modal('hide');
            $('.modal-backdrop ').hide();
            deleteWarehouse();
            editWarehouse();
            showMessage(data.alertType, data.message);
          }
        },
        error: function error(jqXHR, exception) {
          showMessage(data.alertType, data.message);
        }
      });
    }
  });
}

$('#submit_add_warehouse_button').on('click', function () {
  var firmaid = $('#add_warehouse').find('input[name="firmaid"]').val();

  var _token = $('#add_warehouse').find('input[name="_token"]').val();

  var name = $('#add_warehouse').find('input[name="name"]').val();
  var isactive = $('#add_warehouse').find('select[name="isactive"]').val();

  if (1 > 0) {
    $.ajax({
      type: 'POST',
      url: "/administration/add-new-warehouse",
      data: {
        _token: _token,
        firmaid: firmaid,
        name: name,
        isactive: isactive
      },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function success(data) {
        if (data.success == 'ok') {
          $('#add_warehouse').modal('hide');
          var table = $('#warehouse-table');
          table.append(data.html);
          deleteWarehouse();
          editWarehouse();
          showMessage(data.alertType, data.message);
        }
      },
      error: function error(jqXHR, exception) {
        showMessage(data.alertType, data.message);
      }
    });
  }
});

function showMessage(alertType, message) {
  var messageBox = $('.alert');

  if (alertType == 1) {
    messageBox.removeClass('alert-success');
    messageBox.addClass('alert-success');
    messageBox.empty();
    messageBox.append(message);
    messageBox.show();
  } else {
    messageBox.removeClass('alert-success');
    messageBox.addClass('alert-danger');
    messageBox.empty();
    messageBox.append('დაფიქსირდა შეცდომა, გთხოვთ ცადოთ მოგვიანებით');
    messageBox.show();
  }
}

/***/ }),

/***/ 4:
/*!*********************************************************!*\
  !*** multi ./resources/js/administration_warehouses.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\xampp\htdocs\tempohub\resources\js\administration_warehouses.js */"./resources/js/administration_warehouses.js");


/***/ })

/******/ });