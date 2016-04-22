 function detect_run_selectcpt(){
    jQuery('.custom_section_form:not(.hide) select.selectcoursecpt,.cloned_section_form select.selectcoursecpt').each(function(){
        if(jQuery(this).hasClass('select2-hidden-accessible')){
            return;
        }
        var $this = jQuery(this);
        var cpt = $this.attr('data-cpt');
        var placeholder = $this.attr('data-placeholder');
        $this.select2({
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
                                    security: jQuery('#vibe_security').val(),
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
 }
 jQuery(document).ready(function($){
   
    $('#add_custom_section').on('click',function(){
        var cloned = $('.custom_section_form.hide').clone();
        cloned.removeClass('hide');
        $('#course_custom_sections_form').append(cloned);
        // SELECT 2 Migration
        detect_run_selectcpt();
    });
    
    $('.remove_section').click(function(){
        if (confirm(wplms_course_custom_nav_js.delete_section_confirm)) {
            $(this).parent().parent().remove();
        }
    });
    $('body').delegate('#add_section','click',function(){
        var $this=$(this).parent().parent().parent();
        var title = $this.find('.custom_section_title').val();
        var slug = $this.find('.custom_section_slug').val();
        if( typeof title == 'undefined' || title.length == 0){
            alert(wplms_course_custom_nav_js.valid_title);
            return false;
        }
        if( typeof slug == 'undefined' || slug.length == 0){
            alert(wplms_course_custom_nav_js.valid_slug);
            return false;
        }

        $('#message').remove();

        var $courses = [];
        $this.find('.custom_section_courses option:selected').each(function(){
            var course={'id':$(this).val(),'text':$(this).text()};
            $courses.push(course);
        });
        courses=encodeURIComponent(JSON.stringify($courses));
        $('#course_custom_sections').append("<li id='"+$this.find(".custom_section_slug").val()+"'><h4><strong>"+title+"</strong><span>"+$this.find(".custom_section_description").val()+"</span><a class='remove_section dashicons dashicons-no-alt'></a><a class='edit_section dashicons dashicons-edit'></a></h4>"+
            "<input type='hidden' class='section_courses' data-courses='"+courses+"' value='"+$this.find('.custom_section_courses').val()+"'>"+
            "<input type='hidden' value='"+$this.find('.custom_section_slug').val()+"' class='custom_course_section_slug'>"+
            "<input type='hidden' class='section_all_courses' value='"+ $this.find('.custom_section_all_courses:checked').val()+"'>"+
            "<input type='hidden' class='section_visibility' value='"+ $this.find('.custom_section_visibility').val()+"'></li>");
        $('.custom_section_form').hide(200);
        $('.remove_section').click(function(){
            if (confirm(wplms_course_custom_nav_js.delete_section_confirm)) {
               $(this).parent().parent().remove();
            }   
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
        $('#save_custom_sections').text(wplms_course_custom_nav_js.saving);
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
                var message='<div class="notice notice-warning is-dismissible permalinks_notice" id="message"><p>'+wplms_course_custom_nav_js.permalinks_save_notice+'</p></div>';
                $button.next().after(message);
                setTimeout(function(){
                    $button.text(defaultxt);
                    $('.permalinks_notice').hide(2500);
                }, 5000);

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
                var default_val = $(this).find('.post_field').val();
                var type = $(this).find('.post_field').attr('data-type');
                if(type == 'checkbox'){
                    default_val  = $(this).find('.post_field:checked').val();   
                }
                var field ={'field':$(this).attr('id'),'visibility':$(this).find('.course_field_label:checked').val(),'default':default_val};
                fields.push(field);
            });
            section_fields={section: $this.find('h2.section').attr('id'),'visibility': section_visibility,'fields':fields};
            custom_creation_visibility.push(section_fields);
        });
        $('#save_course_creation_settings').text(wplms_course_custom_nav_js.saving);
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
   

}); 


jQuery(document).on('click','#course_custom_sections li .edit_section',function(){
    var li = jQuery(this).parent().parent();
    
    if(jQuery(this).hasClass('cloned')){
        li.find('.cloned_section_form').toggle(200);
        return;
    }

    jQuery(this).addClass('cloned');

    var cloned = jQuery('.custom_section_form.hide').clone().attr('class','cloned_section_form');
    cloned.find('.custom_section_title').val(li.find('h4 strong').text());
    cloned.find('.custom_section_slug').val(li.find('.custom_course_section_slug').val()).attr('disabled','disabled');
    cloned.find('.custom_section_description').val(li.find('h4 span').text());
    if(li.find('.section_visibility').val().length>0){
        cloned.find('.custom_section_visibility').val(li.find('.section_visibility').val());
    }else{
        cloned.find('.custom_section_visibility').val('everyone');
    }
    visibility=li.find('.section_all_courses').val();
    if(visibility){

        cloned.find('.custom_section_all_courses').attr('checked','checked');
    }
    var courses;

    courses=li.find('.section_courses').attr('data-courses');
    courses=jQuery.parseJSON(decodeURIComponent(courses));
    jQuery.each(courses,function(key,item){
        cloned.find('.custom_section_courses').append('<option value="'+item.id+'" selected="selected">'+item.text.split('+').join(' ')+'</option>');
    });
    cloned.find('#add_section').attr('id','').attr('class','save_edit_section button').text('Edit Section');
    li.append(cloned);
    li.find('.cloned_section_form').show(200);
    detect_run_selectcpt();
});

jQuery(document).on('click','.close_section',function(){
    jQuery(this).closest('.custom_section_form').remove();
    jQuery(this).closest('.cloned_section_form').hide();
    jQuery('.save_edit_section').trigger('click');
});

jQuery(document).on('click','.save_edit_section',function(){

    var li = jQuery(this).parent().parent().parent().parent();
    var $courses = [];
        li.find('.custom_section_courses option:selected').each(function(){
            var course={'id':jQuery(this).val(),'text':jQuery(this).text()};
            $courses.push(course);
        });
    courses=encodeURIComponent(JSON.stringify($courses));
    li.find('.section_courses').attr('data-courses',courses);
    li.find('.section_courses').val(li.find('.custom_section_courses').val());
    li.find('h4 strong').text(li.find('.custom_section_title').val());
    li.find('h4 span').text(li.find('.custom_section_description').val());
    li.find('.section_all_courses').val( li.find('.custom_section_all_courses:checked').val());
    li.find('.section_visibility').val( li.find('.custom_section_visibility').val());

});