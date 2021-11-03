
  unlinkSensor();
  linkSensor2point();
  deletePoint();
  fillSensorsByController();
  getRoomPins();
  $('#add_warehouse_room_point_form').on('submit', function () {
    var formData = new FormData($(this)[0]);
    $.ajax({
      type: 'POST',
      url: "/administration/add-new-warehouse-room-sensor",
      dataType: 'JSON',
      cache: false,
      contentType: false,
      processData: false,
      data: formData,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function success(data) {
        if (data.success == 'ok') {
          $('#add_warehouse_room_point_form').find('input[name="sensornewname"]').val('');
          $('#add_sensors').find('option').remove();
          $('#add_sensors').attr('disabled', true);
          $('#add_controller').find('option:first').attr('selected', true);
          $('#add_warehouse_room_point').modal('hide');
          var table = $('#warehouse-room-sensors-table');
          table.append(data.html);
          unlinkSensor();
          linkSensor2point();
          deletePoint();
          fillSensorsByController();
          getRoomPins();
          showMessage(data.alertType, data.message);
        }
      },
      error: function error(err) {
        showMessage(data.alertType, data.message);
      }
    });
    return false;
  });

  function linkSensor2point() {
    $('.link-sensor-2-point-btn').on('click', function () {
      var formid = 'link-sensor-2-point-form_' + $(this).attr('data-point-id');
      var formData = new FormData($('#' + formid)[0]);
      $.ajax({
        type: 'POST',
        url: "/administration/add-sensor-to-point",
        dataType: 'JSON',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function success(data) {
          if (data.success == 'ok') {
            var table = $('#warehouse-room-sensors-table');
            table.find('tr[data-pin-checked="' + data.roomsensor.id + '"]').empty();
            table.find('tr[data-pin-checked="' + data.roomsensor.id + '"]').append(data.html);
            $('#link_sensor_' + data.roomsensor.id).modal('hide');
            $('.modal-backdrop ').hide();
            unlinkSensor();
            deletePoint();
            fillSensorsByController();
            getRoomPins();
            showMessage(data.alertType, data.message);
          }
        },
        error: function error(err) {
          showMessage(data.alertType, data.message);
        }
      });
      return false;
    });
  }

  function deletePoint() {
    $('.confirm-delete-point').on('click', function () {
      var roomsensorid = $(this).attr('data-point-id');

      if (roomsensorid) {
        $.ajax({
          type: 'GET',
          url: "/administration/delete-warehouse-room-sensor/" + roomsensorid,
          data: {
            id: roomsensorid
          },
          success: function success(data) {
            if (data.success == 'ok') {
              var table = $('#warehouse-room-sensors-table');
              table.find('tr[data-pin-checked="' + roomsensorid + '"]').hide();
              $('#delete_point_' + roomsensorid).modal('hide');
              getRoomPins();
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

  function unlinkSensor() {
    $('.confirm-unlink-sensor').on('click', function () {
      var sensorid = $(this).attr('data-point-id');

      if (sensorid) {
        $.ajax({
          type: 'GET',
          url: "/administration/unlink-warehouse-room-sensor/" + sensorid,
          data: {
            id: sensorid
          },
          success: function success(data) {
            if (data.success == 'ok') {
              var table = $('#warehouse-room-sensors-table');
              table.find('tr[data-pin-checked="' + data.roomsensorid + '"]').empty();
              table.find('tr[data-pin-checked="' + data.roomsensorid + '"]').append(data.html);
              $('#unlink_sensor_' + sensorid).modal('hide');
              $('.modal-backdrop ').hide();
              linkSensor2point();
              deletePoint();
              fillSensorsByController();
              getRoomPins();
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

  $('.point-row').hover(function () {
    var pinid = $(this).attr('data-pin-checked');
    var pin = $('.pin[data-pin-id="' + pinid + '"]');
    $('.pin').css('border', 'none');
    $('.pin').css('width', '30px');
    $('.pin').css('height', '30px');
    pin.css('border', '2px solid blue');
    pin.css('width', '50px');
    pin.css('height', '50px');
  });
  $('.room_plan_drawer_container_add').bind('click', function (e) {
    var _this = $(this);

    var $div = $(e.target);
    var offset = $div.offset();
    var x = e.clientX - offset.left - 15;
    var y = e.clientY - offset.top - 15;

    var propX = x / _this.width();

    var propY = y / _this.height();

    var coordinate_x = _this.width() * propX;
    var coordinate_y = _this.height() * propY;
    var pin = '<div class="add_pin" style="top:' + coordinate_y + 'px; left:' + coordinate_x + 'px; height:30px; width: 30px;"></div>';
    $('.add_pin').remove();

    _this.append(pin);

    $('#map_x').val(propX);
    $('#map_y').val(propY);
  });

  function fillSensorsByController() {
    $('.add_controller').change(function () {
      $('.add_sensors').find('option').remove();
      $('.add_sensors').attr('disabled', true);

      if ($(this).val() != 0) {
        $.ajax({
          type: 'GET',
          url: "/administration/controller-sensors",
          data: {
            controllerid: $(this).val()
          },
          success: function success(data) {
            if (data.success == 'ok') {
              $('.add_sensors').empty();
              $('.add_sensors').append("<option></option>");
              $('.add_sensors').attr('disabled', false);
              $.each(data.sensors, function (key, val) {
                getSensorsByController(val.id, val.name);
              });
            }
          },
          error: function error(jqXHR, exception) {}
        });
      } else {
        $('.add_sensors').find('option').remove();
        $('.add_sensors').attr('disabled', true);
      }
    });
  }

  getSensorsByController = function getSensorsByController(id, name) {
    var option = "<option value='" + id + "'>" + name + "</option>";
    $('.add_sensors').append(option);
  };

  function getRoomPins() {
    var roomid = $('input[name="roomid"]').val();

    if (roomid) {
      $.ajax({
        type: 'GET',
        url: "/administration/room-points",
        data: {
          roomid: roomid
        },
        dataType: 'JSON',
        cache: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function success(data) {
          if (data.success == 'ok') {
            $('.warehouse_room_container_in_sensors_page').find('.pin').remove();
            $('.room_plan_drawer_container_add').find('.pin').remove();
            $('.room_plan_drawer_container_edit').find('.pin').remove();
            $.each(data.points, function (key, val) {
              fillMap(val, true, 'warehouse_room_container_in_sensors_page', 0);
              fillMap(val, false, 'room_plan_drawer_container_add', 0);
              fillMap(val, false, 'room_plan_drawer_container_edit', val.id);
            });
          }
        },
        error: function error(jqXHR, exception) {}
      });
    }
  }

  $('#open_sensor_add_modal').on('click', function () {
    $('.room_plan_drawer_container_add').find('.add_pin').remove();
    getRoomPins();
  });
  $('.link-sensor-btn').on('click', function () {
    setTimeout(function () {
      getRoomPins();
    }, 200);
  });

  function fillMap(point, withparams, target) {
    var mapid = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;

    if (mapid > 0) {
      var map = $('div[data-map-id="' + mapid + '"]');
    } else {
      var map = $('.' + target);
    }

    var coord_x = 0;
    var coord_y = 0;
    var color = '';
    var pin = '';
    var params = '';
    coord_x = map.width() * point.map_x;
    coord_y = map.height() * point.map_y;

    if (point.isactive) {
      color = 'green';
    } else {
      color = 'gray';
    }

    if (withparams) {
      params = '<i class="fas fa-times-circle delete-pin-icon" id="pin_remove_btn" data-toggle="modal" data-target="#delete_point_' + point.id + '"></i>';
    }

    pin = '<div class="pin" style="top:' + coord_y + 'px; left:' + coord_x + 'px; background-color:' + color + '" id="pin_' + point.roomid + '_' + point.index + '" data-sensor-id="' + point.sensorid + '" data-room-id="' + point.roomid + '" data-pin-id="' + point.id + '">' + params + point.index + '</div>';
    map.append(pin);
  }

  $(window).resize(function () {
    getRoomPins();
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