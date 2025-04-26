jQuery(document).ready(function($) {
  $('#fsdb-form').on('submit', function(e) {
    e.preventDefault();
    let data = {
      nome: $('input[name="nome"]').val(),
      email: $('input[name="email"]').val(),
      tel: $('input[name="tel"]').val(),
      info: $('[name="info"]').val(),
      messaggio: $('textarea[name="messaggio"]').val()
    };

    $.ajax({
      url: fsdb_ajax.url,
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: res => {
        $('#fsdb-response').text(res.message).css('color', 'green');
        $('#fsdb-form')[0].reset();
      },
      error: res => {
        $('#fsdb-response').text(res.responseJSON.message).css('color', 'red');
      }
    });
  });
});
