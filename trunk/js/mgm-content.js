/**
 * STF2015 Conent provider and content_form_renderer
 *
 * @author: M. Käser
 * @date: 23.12.2014
 **/
(function($) {
  var provider = {
    _html : '<div class="row">' +
              '<label for="gizmo_content_paragraph" class="col col_12_12">Timetable</label>' +
              '<div class="row">' +
                '<select class="col col_12_12" name="gizmo_content_place"></select>' +
              '</div>' +
              '<div class="row">' +
                '<label class="col col_4_12" for="gizmo_content_pagesize">Einträge pro Seite</label>' +
                '<input class="col col_2_12" type="number" name="gizmo_content_pagesize" step="1" min="1" max="15" value="5" />' +
              '</div>' +
            '</div>',

    html : function() {
      var options = '';
      var places = makae_gm_stf.settings.places;
      for(var i in places)
        options += '<option value="' + places[i]['key'] + '">' + places[i]['value'] + '</option>';

      var $html = $(this._html);
      $html.find('select[name="gizmo_content_place"]').append($(options));

      return $html;
    },

    render : function(gizmo, callback) {
      $html = this.html();
      if(typeof gizmo.content_data == 'undefined') {
        gizmo.content_data = {};
        callback($html);
        return;
      }

      if(gizmo.content_data.place)
        $html.find('[name="gizmo_content_place"]').val(gizmo.content_data.place);

      if(gizmo.content_data.pagesize)
        $html.find('[name="gizmo_content_pagesize"]').val(gizmo.content_data.pagesize);

      callback($html);
    },

    update : function(gizmo, form) {
      gizmo.content_data = this.form_data(gizmo, form);
    },

    form_data : function(gizmo, form) {
      return {
        'place' : $(form).find('[name="gizmo_content_place"]').val(),
        'pagesize' : $(form).find('[name="gizmo_content_pagesize"]').val()
      }
    },

    save : function(gizmo, form) {
      this.update();
    },

    get_data : function(gizmo, callback) {
      var data = {
        place : gizmo.content_data.place
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