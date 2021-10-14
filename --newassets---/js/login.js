$(document).ready(function(){

  startAutooverview();

  if($('body.kr-login').hasClass('kr-ac-pwdr')) showLoginView('resetPassword', {token:$('body').attr('kr-pwdr')});
  else showLoginView('login');

  $('div.kr_lang_select').off('click').click(function(){
    $(this).find('ul').css('display', 'block');
  });

  $(document).mouseup(function(e)
  {
      var container = $('div.kr_lang_select');
      if (!container.is(e.target) && container.has(e.target).length === 0) $('div.kr_lang_select').find('ul').css('display', 'none');
  });

  $('.close-kr-page').click(function(){
    $('.kr-page-view').hide();
    $('.kr-page-view > section > section').html('');
  });

  $('[kr-page]').click(function(){
    loadTermsPage($(this).attr('kr-page'));
  });

});

function loadTermsPage(pageName){
  $('.kr-page-view').css('display', 'flex');
  $('.kr-page-view > section > section').load('app/views/pages/' + pageName + '.tpl', function( response, status, xhr ) {
    if ( status == "error" ) {
      showAlert('Oops', 'Fail to load : ' + pageName, 'error');
    }
  });
}

let overviewSlide = null;
function startAutooverview(){
  clearTimeout(overviewSlide); overviewSlide = null;
  overviewSlide = setTimeout(function(){
    nextOverview();
    startAutooverview();
  }, 5000);
}

function nextOverview(){
  let currentSlide = $('.kr-app-overview').attr('nov');
  let nextSlide = parseInt(currentSlide) + 1;
  if(nextSlide > $('.kr-app-overview').find('section').length) nextSlide = 1;

  let slideNew = $('.kr-app-overview').find('section:nth-child(' + (nextSlide + 1) + ')');

  $.when($('.kr-app-ovrview-infos').find('h2').fadeOut()).then(function(){
    $('.kr-app-overview').find('section').css('transform', 'translateX(-' + ((nextSlide - 1) * 500) + 'px)');
    $('.kr-app-ovrview-infos').find('h2').html(slideNew.attr('kr-ov-title')).fadeIn();
    $('.kr-app-ovrview-infos').find('.kr-app-ovrview-selected').removeClass('kr-app-ovrview-selected');
    $('.kr-app-ovrview-infos').find('ul').find('li:nth-child(' + (nextSlide) + ')').addClass('kr-app-ovrview-selected');
    $('.kr-app-overview').attr('nov', nextSlide);
  });

}

function showLoginView(view = 'login', args = {}){

  $('.kr-login-view').html('<div> <div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div> </div>');
  $.get($('body').attr('hrefapp') + "/app/views/login/" + view + ".php", args).done(function(data){
    $('.kr-login-view').attr('class', 'kr-login-view kr-' + view);
    $.when($('.kr-login-view').html(data)).then(function(){
      $('body.kr-login > form').attr('action', $('.kr-login-act').attr('act'));
      $('body.kr-login > form').attr('class', 'kr-lgs-v-' + view);
      iniLoginViewControllers();
    });


  }).fail(function(){
    showAlert('Ooops', 'Fail to load ' + view, 'error');
  });

}

function showLoadingForm(){
  $('.kr-loading-fnc').css('display', 'flex');
}

function hideLoadingForm(){
  $('.kr-loading-fnc').css('display', 'none');
}

function iniLoginViewControllers(){
  $('.kr-resetpassword-view').click(function(){
    showLoginView('resetPassword');
  });

  $('.kr-login-signup-ctrl').click(function(){
    showLoginView('signup');
  });

  $('.kr-gologin-view').click(function(){
    showLoginView();
  });

  $('form').off('submit');

  $('.kr-lgs-v-login').off('submit').submit(function(e){
    showLoadingForm();
    $('.kr-login-field').find('input[type="text"], input[type="password"]').removeClass('kr-inp-error');
    $('.kr-login-field').find('div').find('span').html('');
    $('input[type="submit"]').attr('disabled', 'true').addClass('btn-grey');
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let respond = jQuery.parseJSON(data);
      if(respond.error == 0){
        $('section.kr-login-loading-full').css('display', 'flex');
        window.location.replace(respond.href);
      } else if(respond.error == 1){
        showAlert('Ooops', respond.msg, 'error');
      } else if(respond.error == 2){
        $.each(respond.fields, function(k, v){
          $('.kr-i-msg-f-' + k).find('span').html(v);
          $('input[name="' + k + '"]').addClass('kr-inp-error');
        });
      } else if(respond.error == 3){
        $.post($('body').attr('hrefapp') + '/app/views/login/googleAuthentificator.php', {user:respond.user, pwd:respond.pwd}).done(function(data){
          $.when($('body').prepend(data)).then(function(){
            initGoogleAuthenticator();
          });
        }).fail(function(){
          showAlert('Oops', 'Fail to load google Authenticator view', 'error');
        });
      }
      $('input[type="submit"]').removeAttr('disabled').removeClass('btn-grey');
      hideLoadingForm();
    }).fail(function(){
      showAlert('Ooops', 'Fail to login', 'error');
    });
    e.preventDefault();
    return false;
  });

  $('.kr-lgs-v-resetPassword').off('submit').submit(function(e){
    showLoadingForm();
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      hideLoadingForm();
      let respond = jQuery.parseJSON(data);
      if(respond.error == 0){
        showAlert('Succes !', respond.msg, 'success');
        showLoginView('login');
      } else if(respond.error == 1){
        showAlert('Ooops', respond.msg, 'error');
      } else if(respond.error == 2){
        $('.kr-i-msg-f-kr_usr_pwdr_rep').find('span').html(respond.msg);
        $('input[name="kr_usr_pwdr_rep"]').addClass('kr-inp-error');
        $('input[name="kr_usr_pwdr"]').addClass('kr-inp-error');
      }
    }).fail(function(){
      showAlert('Ooops', 'Fail to load reset password', 'error');
    });
    e.preventDefault();
    return false;
  });


  $('.kr-lgs-v-signup').off('submit').submit(function(e){

    showLoadingForm();

    $('.kr-inp-error').removeClass('kr-inp-error');
    $('.kr-login-field').find('span').html('');

    $.post($('.kr-lgs-v-signup').attr('action'), $('.kr-lgs-v-signup').serialize()).done(function(data){

      if($('.g-recaptcha').length > 0)grecaptcha.reset();

      let respond = jQuery.parseJSON(data);

      if(respond.error != 1) hideLoadingForm();

      if(respond.error == 0){
        $('section.kr-login-loading-full').css('display', 'flex');
        window.location.replace(respond.href);
      } else if(respond.error == 1){
        showAlert('Ooops', respond.msg, 'error');
        showLoginView('login');
      } else if(respond.error == 2){
        $.each(respond.fields, function(k, v){
          $('.kr-i-msg-f-' + k).find('span').html(v);
          $('input[name="' + k + '"]').addClass('kr-inp-error');
        });
      } else if(respond.error == 3){
        showAlert('Success', respond.msg, 'success');
      }

    }).fail(function(){
      showAlert('Ooops', 'Fail to access to signup page', 'error');
    });

    e.preventDefault();
    return false;
  });


}

function kryptoSignup(){
  $('.kr-lgs-v-signup').submit();
}

function kryptoLogin(){
  $('.kr-lgs-v-login').submit();
}

function initGoogleAuthenticator(){

  showLoadingForm();

  $('#google_tfs_inpt').focus();

  $('.kr-login-authentificator-act').on('submit', function(e){

    $('.kr-login-tfs > section > *').hide();
    $('.kr-login-tfs > section > .sk-folding-cube').show();

    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
        let respond = jQuery.parseJSON(data);
        console.log(respond);
        if(respond.error == 0){
          $('section.kr-login-loading-full').css('display', 'flex');
          $('.kr-login-tfs').remove();
          window.location.replace(respond.href);
        } else if(respond.error == 4){
          $('.kr-login-tfs > section > *').show();
          $('.kr-login-tfs > section > .sk-folding-cube').hide();
          $('.kr-login-tfs').find('input[type="text"]').addClass('kr-inp-error').val('').focus();
        } else {
          showAlert('Ooops', 'Args missing', 'error');
        }

    }).fail(function(){
        showAlert('Ooops', 'Fail to login', 'error');
    });

    e.preventDefault();
    return false;

  });

}
