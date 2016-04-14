 jQuery(document).ready(function($){
   // $('#course_custom_sections').trigger('section_added');
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

        $('#course_custom_sections').append('<li id="'+$('.custom_section_slug').val()+'"><h4><strong>'+title+'</strong><span>'+$('.custom_section_description').val()+'</span><a class="remove_section dashicons dashicons-no-alt"></a><a class="edit_section dashicons dashicons-edit"></a></h4>'+
            '<input type="hidden" class="section_courses" value="'+ $('.custom_section_courses').val()+'">'+
            '<input type="hidden" value="'+$('.custom_section_slug').val()+'" class="custom_course_section_slug">'+
            '<input type="hidden" class="section_all_courses" value="'+ $('.custom_section_all_courses:checked').val()+'">'+
            '<input type="hidden" class="section_visibility" value="'+ $('.custom_section_visibility').val()+'"><div class="edit_box"></div></li>');
        $('.custom_section_form').hide(200);
        $('.remove_section').click(function(){$(this).parent().remove();});
        //$('#course_custom_sections').trigger('section_added');
    });

    $('#course_custom_sections li').each(function(){
        var $this = $(this);
        var new_edit_slug='';
        var edit_slug=$this.find('input.custom_course_section_slug').val();
            $('li#'+edit_slug+' .edit_section').click(function(){
               
                edit_slug=$this.find('input.custom_course_section_slug').val();
                var edit_title=$this.find('h4 strong').text();
                var edit_description=$this.find('h4 span').text();
                
                var edit_courses=$this.find('input.section_courses').val();
                var edit_all_courses=$this.find('input.section_all_courses').val();
                var edit_visibility=$this.find('input.section_visibility').val();
                
                //$('.custom_section_form').clone().appendTo( 'li#'+edit_slug+' .edit_box' );
                
                $('.edit_box').addClass('active');
                if($('.edit_box').hasClass('active')){
                    $('#course_custom_sections_form').prop('disabled','true');
                }
                $('li#'+edit_slug+' .edit_box input.custom_section_title_edit').val(edit_title);
                $('li#'+edit_slug+' .edit_box .custom_section_description_edit').val(edit_description);
                $('li#'+edit_slug+' .edit_box .custom_section_courses_edit').val(edit_courses);
                $('li#'+edit_slug+' .edit_box .custom_section_slug_edit').val(edit_slug);
                if( edit_all_courses==1){
                    $('li#'+edit_slug+' .edit_box .custom_section_all_courses_edit').attr('checked','checked');
                }
                if(edit_visibility.length<0){
                    $('li#'+edit_slug+' .edit_box .custom_section_visibility_edit').val('everyone');
                }else{
                    $('li#'+edit_slug+' .edit_box .custom_section_visibility_edit').val(edit_visibility);
                }
                if($('li#'+edit_slug+' .edit_box ul.section_form_table .cancel_edit').length<=0){
                   $('li#'+edit_slug+' .edit_box ul.section_form_table').append('<a class="cancel_edit button-primary">Close</a>'); 
                }
                
                $('li#'+edit_slug+' .edit_box .custom_section_form_edit').toggle(350);
                $('li#'+edit_slug+' .edit_box #edit_section_li').on('click',function(){
                    new_edit_slug=$('li#'+edit_slug+' .edit_box .custom_section_slug_edit').val();
                    $this.find('input.custom_course_section_slug').val($('li#'+edit_slug+' .edit_box .custom_section_slug_edit').val());
                    $this.find('h4 strong').text($('li#'+edit_slug+' .edit_box input.custom_section_title_edit').val());
                    $this.find('h4 span').text($('li#'+edit_slug+' .edit_box .custom_section_description_edit').val());
                    
                    $this.find('input.section_courses').val($('li#'+edit_slug+' .edit_box .custom_section_courses_edit').val());
                    $this.find('input.section_all_courses').val($('li#'+edit_slug+' .edit_box .custom_section_all_courses_edit:checked').val());
                    $this.find('input.section_visibility').val($('li#'+edit_slug+' .edit_box .custom_section_visibility_edit').val());
                    $this.attr('id',$('li#'+edit_slug+' .edit_box .custom_section_slug_edit').val());
                   console.log(new_edit_slug.length);
                    if(new_edit_slug.length>0){
                        edit_slug= new_edit_slug;
                    }
                   // $('#course_custom_sections').trigger('section_edit');
                });
                $('li#'+edit_slug+' .cancel_edit').on('click',function(){
                    $('li#'+edit_slug+' .edit_box .custom_section_form_edit').hide(350);
                });
                 //$('#course_custom_sections').trigger('edit_trigger');
            });
            
        }); 



    $(function() {
        $( "#course_custom_sections" ).sortable();
    });

    $('#save_custom_sections').on('click',function(){
        var defaultxt = $(this).text();
        var $button = $(this);
        var custom_sections = [];
        $('#course_custom_sections>li').each(function(){
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

 jQuery(document).on('section_added','#course_custom_sections',function(){
      
    });