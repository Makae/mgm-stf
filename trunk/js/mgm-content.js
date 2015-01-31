/**
 * Section for providing the default providers
 *
 * @author: M. Käser
 * @date: 23.12.2014
 **/
(function($) {
  var provider = {
    _html : '<div class="row">' +
              '<label for="gizmo_content_paragraph" class="col col_12_12">Timetable</label>' +
              '<textarea name="gizmo_content_paragraph" class="col col_12_12"></textarea>' +
            '</div>',

    html : function() {
      return this._html;
    },

    render : function(gizmo, callback) {
      $html = $(this.html());
      $html.find('textarea[name="gizmo_content_paragraph"]').val(gizmo.content_data);
      callback($html);
    },

    update : function(gizmo, form) {
      gizmo.content_data = this.form_data(gizmo, form);
    },

    form_data : function(gizmo, form) {
      return $(form).find('textarea[name="gizmo_content_paragraph"]').val();
    },

    save : function(gizmo, form) {
      this.update();
    },

    get_data : function(gizmo, callback) {
      var data = {
        place : 'test'//gizmo.content_data.place
      };
      data = $.extend(makae_gm_stf.ajax_params, data);

      $.ajax({
        url : makae_gm_stf.ajax_url,
        data : data,
        dataType: 'json',
        success : function(data) {
          callback(data.content);
        }
      });
    }
  };

  // Register the content provider to mgm
  if(typeof mgm.content_form_provider != 'undefined')
    mgm.content_form_provider.setProvider('timetable', provider);
  mgm.content_manager.setProvider('timetable', provider.get_data);
  console.log("MGM STF Content providers initialized");
})(jQuery);