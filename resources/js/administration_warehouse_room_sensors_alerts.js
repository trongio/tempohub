
  deleteAlert();
  editAlert();
  fillDevicesAndSensors();

  function fillDevicesAndSensors() {
    $('#warehouse_list').on('change', function () {
      if ($(this).val() != 0) {
        $.ajax({
          type: 'GET',
          url: "/administration/get-controllers",
          data: {
            warehouseid: $(this).val()
          },
          success: function success(data) {
            if (data.success == 'ok') {
              $('#device_list').empty();
              $('#device_list').attr('disabled', false);
              var firstOption = '<option value="0"></option>';
              $('#device_list').append(firstOption);
              $.each(data.controllers, function (key, val) {
                var option = '<option value="' + val.imei + '">' + val.controllername + '</option>';
                $('#device_list').append(option);
              });
            }
          },
          error: function error(jqXHR, exception) {}
        });
      } else {
        $('#device_list').find('option').remove();
        $('#device_list').attr('disabled', true);
        $('#sensor_list').find('option').remove();
        $('#sensor_list').attr('disabled', true);
      }
    });
    $('.alerts_warehouse').on('change', function () {
      var alertid = $(this).attr('data-alert-id');
      alert(alertid);

      if ($(this).val() != 0) {
        $.ajax({
          type: 'GET',
          url: "/administration/get-controllers",
          data: {
            warehouseid: $(this).val()
          },
          success: function success(data) {
            if (data.success == 'ok') {
              $('#device_list_' + alertid).empty();
              $('#device_list_' + alertid).attr('disabled', false);
              var firstOption = '<option value="0"></option>';
              $('#device_list_' + alertid).append(firstOption);
              $.each(data.controllers, function (key, val) {
                var option = '<option value="' + val.imei + '">' + val.controllername + '</option>';
                $('#device_list_' + alertid).append(option);
              });
            }
          },
          error: function error(jqXHR, exception) {}
        });
      } else {
        $('#device_list_' + alertid).find('option').remove();
        $('#device_list_' + alertid).attr('disabled', true);
        $('#sensor_list_' + alertid).find('option').remove();
        $('#sensor_list_' + alertid).attr('disabled', true);
      }
    });
    $('#device_list').on('change', function () {
      if ($(this).val() != 0) {
        $.ajax({
          type: 'GET',
          url: "/administration/get-sensors",
          data: {
            imei: $(this).val()
          },
          success: function success(data) {
            if (data.success == 'ok') {
              $('#sensor_list').empty();
              $('#sensor_list').attr('disabled', false);
              var firstOption = '<option value="0"></option>';
              $('#sensor_list').append(firstOption);
              $.each(data.sensors, function (key, val) {
                var option = '<option value="' + val.index + '">' + val.name + '</option>';
                $('#sensor_list').append(option);
              });
            }
          },
          error: function error(jqXHR, exception) {}
        });
      } else {
        $('#sensor_list').find('option').remove();
        $('#sensor_list').attr('disabled', true);
      }
    });
    $('.alerts_device').on('change', function () {
      var alertid = $(this).attr('data-alert-id');

      if ($(this).val() != 0) {
        $.ajax({
          type: 'GET',
          url: "/administration/get-sensors",
          data: {
            imei: $(this).val()
          },
          success: function success(data) {
            if (data.success == 'ok') {
              $('#sensor_list_' + alertid).empty();
              $('#sensor_list_' + alertid).attr('disabled', false);
              var firstOption = '<option value="0"></option>';
              $('#sensor_list_' + alertid).append(firstOption);
              $.each(data.sensors, function (key, val) {
                var option = '<option value="' + val.index + '">' + val.name + '</option>';
                $('#sensor_list_' + alertid).append(option);
              });
            }
          },
          error: function error(jqXHR, exception) {}
        });
      } else {
        $('#sensor_list_' + alertid).find('option').remove();
        $('#sensor_list_' + alertid).attr('disabled', true);
      }
    });
  }

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

  function editAlert() {
    $('.edit_warehouse_room_sensor_alert_form').on('submit', function () {
      var formData = new FormData($(this)[0]);
      $.ajax({
        type: 'POST',
        url: "/administration/edit_alert",
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
            var target = $('tr[data-alert-id="' + data.alertid + '"]');
            $('#edit_alert').modal('hide');
            $('.modal-backdrop ').hide();
            target.empty();
            target.append(data.html);
            deleteAlert();
            editAlert();
            createSmartWizardEdit();
            fillDevicesAndSensors();
            showMessage(data.alertType, data.message);
          }
        },
        error: function error(err) {}
      });
      return false;
    });
  }

  $('#add_warehouse_room_sensor_alert_form').on('submit', function () {
    var formData = new FormData($(this)[0]);
    $.ajax({
      type: 'POST',
      url: "/administration/add_alert",
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
          $('#add_alert').modal('hide');
          $('.modal-backdrop ').hide();
          $('#allerts_table').append(data.html);
          deleteAlert();
          editAlert();
          createSmartWizardEdit();
          fillDevicesAndSensors();
          showMessage(data.alertType, data.message);
        }
      },
      error: function error(err) {
        console.log(err);
      }
    });
    return false;
  });

  function deleteAlert() {
    $('.confirm-delete-alert').on('click', function () {
      var alertid = $(this).attr('data-alert');

      if (alertid) {
        $.ajax({
          type: 'GET',
          url: "/administration/delete-alert/" + alertid,
          data: {
            id: alertid
          },
          success: function success(data) {
            if (data.success == 'ok') {
              var table = $('#allerts_table');
              table.find('tr[data-alert-id="' + alertid + '"]').hide();
              $('#delete_alert_' + alertid).modal('hide'); // showMessage(data.alertType, data.message);
            }
          },
          error: function error(jqXHR, exception) {// showMessage(data.alertType, data.message);
          }
        });
      }
    });
  }

  $("#edit_user_check_all").click(function () {
    $(".edit-user").prop('checked', $(this).prop('checked'));
  });
  createSmartWizardEdit();

  function createSmartWizardEdit() {
    var btnFinishEdit = $('<div class="form-group"><button type="submit" class="btn btn-primary sw-btn-group-extra d-none">დამატება</button></div>');
    $('.smartwizardedit').smartWizard({
      selected: 0,
      theme: 'default',
      autoAdjustHeight: true,
      transitionEffect: 'fade',
      showStepURLhash: false,
      enableFinishButton: true,
      // makes finish button enabled always,
      contentCache: false,
      labelFinish: 'დამატება',
      // label for Finish button     
      lang: {
        // Language variables for button
        next: 'შემდეგი',
        previous: 'წინა'
      },
      toolbarSettings: {
        toolbarPosition: 'bottom',
        // none, top, bottom, both
        toolbarButtonPosition: 'right',
        // left, right, center
        showNextButton: true,
        // show/hide a Next button
        showPreviousButton: true,
        // show/hide a Previous button
        toolbarExtraButtons: [btnFinishEdit] // Extra buttons to show on toolbar, array of jQuery input/buttons elements

      }
    });
    $(".smartwizardedit").on("leaveStep", function (e, anchorObject, currentStepIndex, nextStepIndex, stepDirection) {
      if (anchorObject.prevObject.length - 2 == currentStepIndex) {
        $('.sw-btn-group-extra').removeClass('d-none');
      } else {
        if (!$('.sw-btn-group-extra').hasClass('d-none')) {
          $('.sw-btn-group-extra').addClass('d-none');
        }
      }
    });
  }