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