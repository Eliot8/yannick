$(document).ready(function () {
    $('#file-input').on('change', function () { //on file input change
        if (window.File && window.FileReader && window.FileList && window
            .Blob) //check File API supported browser
        {

            var data = $(this)[0].files; //this file data

            $.each(data, function (index, file) { //loop though each file
                if (/(\.|\/)(gif|jpe?g|png)$/i.test(file
                        .type)) { //check supported file type
                    var fRead = new FileReader(); //new filereader
                    fRead.onload = (function (
                        file) { //trigger function on successful read
                        return function (e) {
                            var span = $(
                                '<span class="thumb-insid"></span>');
                            var img = $('<img />').addClass('thumb').attr(
                                'src', e.target.result);
                            var close = $(
                                '<i class="fas fa-times-circle text-danger close"></i>'
                            );
                            $('#thumb-output').append(span.append(img,
                                close));
                            //create image element
                            // $('#thumb-inside').append(img, close); 
                            //append image to output element
                        };
                    })(file);
                    fRead.readAsDataURL(file); //URL representing the file's data.
                }
            });

        } else {
            alert("Your browser doesn't support File API!"); //if File API is absent
        }
    });

    // $(".remove").on('click', function () {
    //     alert('done');
    //     // e.preventDefault();
    //     // data.splice(0, 1);
    //     // console.log(data);
    //     // $('#thumb-output a').eq(1).remove();
    //     // $(this).parent('.thumb-insid').css('display', 'none');
    //     // $('#thumb-output a').css('display', 'none');
    // });
});
jQuery(function ($) {
    $("span").on("click", ".thumb-insid .close", function () {
        var id = $(this).closest(".thumb-insid").find("img").data("id");
        //to remove image tag
        $(this).parent().find("img").not().remove();
        $(this).parent().find("span").not().remove();
    });
});