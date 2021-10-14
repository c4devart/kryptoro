(function() {
  var init, setupShepherd;

  init = function() {
    return setupShepherd();
  };

  setupShepherd = function() {
    var shepherd;



    if($('body').attr('sintro') == "0") return false;

    $.get($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/getIntroList.php').done(function(data){
      let jsonRes = jQuery.parseJSON(data);
      if(jsonRes.error == 1){
        showAlert('Oops', jsonRes.msg, 'error');
      } else {
        if(jsonRes.show){
          shepherd = new Shepherd.Tour({
            defaults: {
              classes: 'shepherd-element shepherd-open shepherd-theme-arrows',
              showCancelLink: true
            }
          });

          $.each(jsonRes.steps, function(k, step){
            if(k == 0){
              shepherd.addStep('including', {
                title: step.title,
                text: step.text,
                attachTo: step.attach,
                buttons: [
                  {
                    text: 'Next',
                    action: shepherd.next
                  }
                ]
              });
            } else {
              shepherd.addStep('including', {
                title: step.title,
                text: step.text,
                attachTo: step.attach,
                buttons: [
                  {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: shepherd.back
                  }, {
                    text: 'Next',
                    action: shepherd.next
                  }
                ]
              });
            }

          });

          return shepherd.start();
        }


      }
    });

  };

  $(init);

}).call(this);
