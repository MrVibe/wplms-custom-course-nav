 jQuery(document).ready(function($){
    $('#course_custom_sections').trigger('section_added');
    $('#add_custom_section').on('click',function(){
        $('.custom_section_form').toggle(200);
    });
    $('.remove_section').click(function(){$(this).parent().parent().remove();});
    $('#add_section').on('click',function(){
        var title = $('.custom_section_title').val();

        if( typeof title == 'undefined' || title.length == 0){
            alert('Please enter a valid title');
            return false;
        }

        $('#message').remove();

        $('#course_custom_sections').append('<li><h4><strong>'+title+'</strong><span>'+$('.custom_section_description').val()+'</span><a class="remove_section dashicons dashicons-no-alt"></a><a class="edit_section">EDIT</a></h4>'+
            '<input type="hidden" class="section_courses" value="'+ $('.custom_section_courses').val()+'">'+
            '<input type="hidden" value="'+$('.custom_section_slug').val()+'" class="custom_course_section_slug">'+
            '<input type="hidden" class="section_all_courses" value="'+ $('.custom_section_all_courses:checked').val()+'">'+
            '<input type="hidden" class="section_visibility" value="'+ $('.custom_section_visibility').val()+'"></li>');
        $('.custom_section_form').hide(200);
        $('.remove_section').click(function(){$(this).parent().remove();});
        $('#course_custom_sections').trigger('section_added');
    });


    

    $('#course_custom_sections').on('section_added',function(){

    $('#course_custom_sections li').each(function(){
        var $this = $(this);
            $this.find('.edit_section').click(function(){
                var edit_title=$this.find('h4 strong').text();
                var edit_description=$this.find('h4 span').text();
                var edit_slug=$this.find('input.custom_course_section_slug').val();
                var edit_courses=$this.find('input.section_courses').val();
                var edit_all_courses=$this.find('input.section_all_courses').val();
                var edit_visibility=$this.find('input.section_visibility').val();
                $this.remove();
                
                $('.custom_section_form input.custom_section_title').val(edit_title);
                $('.custom_section_description').val(edit_description);
                $('.custom_section_courses').val(edit_courses);
                $('.custom_section_slug').val(edit_slug);
                if( edit_all_courses)
                    $('.custom_section_all_courses').attr('checked','checked');
                if(edit_visibility.length<0){
                    $('.custom_section_visibility').val('everyone');
                }else{
                    $('.custom_section_visibility').val(edit_visibility);
                }
                if($('ul.section_form_table .cancel_edit').length<=0){
                   $('ul.section_form_table').append('<a class="cancel_edit button-primary">Cancel</a>'); 
                }
                
                $('.custom_section_form').show(350);
                $('#add_section').text('Edit Section');
                
                $('.cancel_edit').on('click',function(){
                    $('#add_section').trigger('click');
                });
                

            });
        });
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
            var data = {title: $this.find('h4 strong').text(),'slug':$this.find('.custom_course_section_slug').val(),'description': $this.find('h4 span').text(), 'courses':$this.find('.section_courses').val(),'all_courses':$this.find('.section_all_courses').val(),'visibility':$this.find('.section_visibility').val()};
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
        var defaultxt = $(this).text();
        var $button = $(this);
        var custom_creation_visibility = [];
        var section_fields=[];
        var section_visibility=[];
        $('div.section_wrapper').each(function(){
            var fields=[];
            var $this = $(this);
            var section_visibility = $this.find('.course_section:checked').val();
            
            $this.find('.section_fields').each(function(){
                var field ={'field':$(this).attr('id'),'visibility':$(this).find('.course_field_label:checked').val()};
                fields.push(field);
            });
            section_fields={section: $this.find('h2.section').attr('id'),'visibility': section_visibility,'fields':fields};
            custom_creation_visibility.push(section_fields);
        });

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'save_custom_course_creation', 
                    security: $('#vibe_security').val(),
                    course_creation:JSON.stringify(custom_creation_visibility),
                  },
            cache: false,
            success: function (html) {
                $button.text(html);
                setTimeout(function(){$button.text(defaultxt);}, 5000);
            }
        });
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