$(function() {

$('a[save]').click(function(ev) {
  ev.preventDefault();
  $('form').trigger('submit');
});

$('#blogForm').on('submit', function(ev) {
  ev.preventDefault();
  $.ajax({
    url: this.action,
    type: 'post',
    dataType: 'json',
    data: $(this).serialize(),
    success: function(data) {
      data && !data.error ? (location = data.redirect) : alert(data.error);
    }
  });
});

$('a[href=tag]').click(function(ev) {
  var $self = $(this),
    input = prompt('new tag:<refer>:\n');

  input && (input = input.split(':'));

  ev.preventDefault();
  input && input[0] && $.ajax({
    url: dir + '/tag',
    type: 'post',
    dataType: 'json',
    data: 'name=' + input[0] + (input[1] ? ('&refer=' + input[1]) : ''),
    success: function(data) {
      if (data && !data.error) {
        input[1] ?
          $self.parent().find('label').each(function() {
            if(this.innerText == input[1]) this.innerHTML = this.innerHTML.replace(input[1], input[0]);
          }) :
          $self.before('<label><input type="checkbox" name="tags[]" value="' + data.tag.id + '">' + data.tag.name + '</label>');
      } else {
        alert(data.error);
      }
    }
  });
});

$('#loginForm').submit(function(ev) {
  ev.preventDefault();
  $.ajax({
    url: this.action,
    type: 'post',
    data: $(this).serialize(),
    success: function(data) {
      data && !data.error ? (location = dir + '/log') : alert(data.error);
    }
  });
});

});
