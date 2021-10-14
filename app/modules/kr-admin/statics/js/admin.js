/**
 * Init admin interface
 */
function initAdmin(){

  // Enable admin navigation menu
  $('.kr-admin-nav').find('li').off('click').click(function(){
    changeView($(this).attr('kr-module'), $(this).attr('kr-view'));
  });

  // Enable coin list pagination
  $('.kr-admin-pagination-coins').find('li').off('click').click(function(){
    changeView('admin', 'coins', {page:$(this).attr('kr-page')});
  });

  $('[kr-step-identity-type-s="true"]').off('change').change(function(){
    if($(this).val() == "form"){
      $('[kr-step-h="form"]').hide();
      $('[kr-step-show="form"]').css('display', 'flex');
    } else {
      $('[kr-step-h="form"]').show();
      $('[kr-step-show="form"]').hide();
    }
  });


  // Enable post with JS
  $('.kr-adm-post-evs').off('submit').submit(function(e){

    if($(this).hasClass('kr-adm-post-evs-confirm')){
      swal({
        title: 'Please comfirm',
        text: "Are you sure do to this action ?",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: "Yes, i'm sure"
      }).then((result) => {
        if (result.value) {
          $.post($(this).attr('action'), $(this).serialize()).done(function(data){

            // Decode result in JSON
            let resp = jQuery.parseJSON(data);

            // Check if result was an error
            if(resp.error == 1) showAlert('Oops', resp.msg, 'error');
            else showAlert(resp.title, resp.msg);

            // Reload view
            changeView('admin', $('.kr-admin-nav-selected').attr('kr-view'));

          }).fail(function(){ // If fail to post (505, 404), show error message
            showAlert('Oops', 'Error : Fail to save (check php error log)', 'error');
          });
        }
      })
    } else {
      var form = $(this)[0];
      var data = new FormData(form);
      let actionPath = $(this).attr('action');
      $.ajax({
          type: "POST",
          enctype: 'multipart/form-data',
          url: actionPath,
          data: data,
          processData: false,
          contentType: false,
          cache: false,
          timeout: 600000,
          success: function (data) {

            // Decode result in JSON
            let resp = jQuery.parseJSON(data);

            // Check if result was an error
            if(resp.error == 1) showAlert('Oops', resp.msg, 'error');
            else showAlert(resp.title, resp.msg);

            // Reload view
            changeView('admin', $('.kr-admin-nav-selected').attr('kr-view'));

          },
          error: function (e) {
              showAlert('Oops', 'Error : Fail to save (check php error log)', 'error');
          }
      });
      // $.ajax({
      //     type: "POST",
      //     url: $(this).attr('action'),
      //     enctype: 'multipart/form-data',
      //     data: $(this).serialize(),
      //     success: function (data) {

      //     }
      // });
      // $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      //
      //   // Decode result in JSON
      //   let resp = jQuery.parseJSON(data);
      //
      //   // Check if result was an error
      //   if(resp.error == 1) showAlert('Oops', resp.msg, 'error');
      //   else showAlert(resp.title, resp.msg);
      //
      //   // Reload view
      //   changeView('admin', $('.kr-admin-nav-selected').attr('kr-view'));
      //
      // }).fail(function(){ // If fail to post (505, 404), show error message
      //   showAlert('Oops', 'Error : Fail to save (check php error log)', 'error');
      // });
    }





    e.preventDefault();
    return false;
  });

  $('.kr-admin-tggle-coin-status').off('submit').submit(function(e){

    let cs = $(this).parent().parent().find('.kr-admin-lst-c-status').html();
    $(this).parent().parent().find('.kr-admin-lst-c-status').html($(this).find('input[type="submit"]').attr('alt-st'));
    $(this).parent().parent().find('.kr-admin-lst-c-status').toggleClass('kr-admin-lst-tag-red');
    $(this).find('input[type="submit"]').attr('alt-st', cs);

    let css = $(this).find('input[type="submit"]').val();
    $(this).find('input[type="submit"]').val($(this).find('input[type="submit"]').attr('alt'));
    $(this).find('input[type="submit"]').attr('alt', css);

    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let resp = jQuery.parseJSON(data);
      if(resp.error == 1){
        showAlert('Oops', resp.msg, 'error');
      }
    });
    e.preventDefault();
    return false;
  });



  $('.btn-adm-user-c').off('click').click(function(e){
    showAccountView({adm_acc_user:$(this).attr('idu')});
    e.preventDefault();
    return false;
  });

  $('.kr-admin-boxthird').sortable({
    update: function(event, ui){
      let exchangeOrder = [];
      $('.kr-admin-boxthird > div').each(function(){
        exchangeOrder.push($(this).attr('kr-exchangename'));
      });

      $.post($('body').attr('hrefapp') + '/app/modules/kr-admin/src/actions/updateTradingExchangeOrder.php', {exchange:JSON.stringify(exchangeOrder)}).done(function(data){
      
      }).fail(function(){
        showAlert('Oops', 'Fail to update exchange order', 'error');
      });
    }
  });

}
