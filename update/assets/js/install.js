$(document).ready(function(){
  $('.kr-sql-check').click(function(){
    checkSQL();
  });

  $('form').submit(function(){
    $('.kr-install-loading').show();
    $('footer').hide();
  });

});

function checkSQL(callback = null){

  $.post('app/src/actions/checkSQL.php', $('.kr-bdd').serialize()).done(function(data){
    let result = jQuery.parseJSON(data);
    if(result.error == 1){
      $('.kr-msg').html(result.msg).addClass('kr-msg-error').show();
    } else {
      $('.kr-msg').html(result.msg).removeClass('kr-msg-error').show();
      $('.kr-next-f').removeAttr('disabled').removeClass('btn-grey').addClass('btn-orange');
    }
    callback(result.error);
  });

}
