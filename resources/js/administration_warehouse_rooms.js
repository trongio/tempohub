editWarehouseRoom();
deleteWarehouseRoom();
$('#add_warehouse_room_form').on('submit', function () {
  var formData = new FormData($(this)[0]);
  $.ajax({
    type: 'POST',
    url: "/administration/add-new-warehouse-room",
    dataType: "JSON",
    cache: false,
    contentType: false,
    processData: false,
    data: formData,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function success(data) {
      if (data.success == 'ok') {
        $('#add_warehouse_room_form').find('input[name="name"]').val('');
        $('#add_warehouse_room_form').find('input[name="image"]').val('');
        $('#add_warehouse_room').modal('hide');
        var table = $('#warehouse-room-table');
        table.append("<tr>" + data.html + "</tr>");
        deleteWarehouseRoom();
        editWarehouseRoom();
        showMessage(data.alertType, data.message);
      }
    },
    error: function error(err) {
      showMessage(data.alertType, data.message);
    }
  });
  return false;
});

function editWarehouseRoom() {
  $('.submit_edit_warehouse_room_button').on('click', function () {
    var formid = 'edit_warehouse_room_form_' + $(this).attr('data-warehouse-room');
    var formData = new FormData($('#' + formid)[0]);
    $.ajax({
      type: 'POST',
      url: "/administration/edit-warehouse-room",
      dataType: "JSON",
      cache: false,
      contentType: false,
      processData: false,
      data: formData,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function success(data) {
        if (data.success == 'ok') {
          var table = $('#warehouse-room-table');
          table.find('tr[data-warehouse-room-id="' + data.warehouse_room.id + '"]').empty();
          table.find('tr[data-warehouse-room-id="' + data.warehouse_room.id + '"]').append(data.html);
          $('#edit_warehouse_room_' + data.warehouse_room.id).modal('hide');
          $('.modal-backdrop ').hide();
          deleteWarehouseRoom();
          editWarehouseRoom();
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

function deleteWarehouseRoom() {
  $('.confirm-delete-warehouse').on('click', function () {
    var roomid = $(this).attr('data-warehouse-room-id');

    if (roomid) {
      $.ajax({
        type: 'GET',
        url: "/administration/delete-warehouse-room/" + roomid,
        data: {
          id: roomid
        },
        success: function success(data) {
          if (data.success == 'ok') {
            var table = $('#warehouse-room-table');
            table.find('tr[data-warehouse-room-id="' + roomid + '"]').hide();
            $('#delete_room_' + roomid).modal('hide');
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