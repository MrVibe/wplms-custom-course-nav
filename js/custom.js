 jQuery(document).ready(function($){
    $('#add_custom_section').on('click',function(){
        $('.custom_section_form').toggle(200);
    });
    $('.remove_section').click(function(){$(this).parent().remove();});
    $('#add_section').on('click',function(){
        var title = $('.custom_section_title').val();

        if( typeof title == 'undefined' || title.length == 0){
            alert('Please enter a valid title');
            return false;
        }

        $('#message').remove();

        $('#course_custom_sections').append('<li><h4><strong>'+title+'</strong><span>'+$('.custom_section_description').val()+'</span><a class="remove_section dashicons dashicons-no-alt"></a></h4>'+
            '<input type="hidden" class="section_courses" value="'+ $('.custom_section_courses').val()+'">'+
            '<input type="hidden" class="section_all_courses" value="'+ $('.custom_section_all_courses:checked').val()+'">'+
            '<input type="hidden" class="section_visibility" value="'+ $('.custom_section_visibility').val()+'"></li>');
        $('.remove_section').click(function(){$(this).parent().remove();});
    });

    $(function() {
        $( "#course_custom_sections" ).sortable();
    });

    $('#save_custom_sections').on('click',function(){
        var defaultxt = $(this).text();
        var $button = $(this);
        var custom_sections = [];
        $('#course_custom_sections li').each(function(){
            var $this = $(this);
            var data = {title: $this.find('h4 strong').text(),'description': $this.find('h4 span').text(), 'courses':$this.find('.section_courses').val(),'all_courses':$this.find('.section_all_courses').val(),'visbility':$this.find('.section_visibility').val()};
            custom_sections.push(data);
        });

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'save_custom_course_sections', 
                    security: $('#vibe_security').val(),
                    sections:JSON.stringify(custom_sections),
                  },
            cache: false,
            success: function (html) {
                $button.text(html);
                setTimeout(function(){$button.text(defaultxt);}, 5000);
            }
        });
    });
    //Front end settings
    $('#save_course_creation_settings').on('click',function(){
        
    });
   // SELECT 2 Migration
    $('.selectcpt').each(function(){
        var $this = $(this);
        var cpt = $(this).attr('data-cpt');
        var placeholder = $(this).attr('data-placeholder');
        $(this).select2({
            minimumInputLength: 4,
            placeholder: placeholder,
            closeOnSelect: true,
            allowClear: true,
            ajax: {
                url: ajaxurl,
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(term){ 
                        return  {   action: 'get_admin_select_cpt', 
                                    security: $('#vibe_security').val(),
                                    cpt: cpt,
                                    id:$this.attr('id'),
                                    q: term,
                                }
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },       
                cache:true  
            },
        });
    });


}); 