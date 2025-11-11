$(function () {
    "use strict";

    let originalButtonText;

    $("#customizationForm").on("submit", function (e) {
        e.preventDefault();

        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.closest('form').attr('action'),
            formObject: form,
        };

        ajaxRequest(formArray);
    });

    function disableSubmitButton(form) {
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...');
    }

    function enableSubmitButton(form) {
        form.find('button[type="submit"]')
            .prop('disabled', false)
            .html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
    }

    function afterCallAjaxResponse(formObject) {
        enableSubmitButton(formObject);
    }

    function afterSuccessAjax(formObject) {
        iziToast.success({
            title: 'Success',
            layout: 2,
            message: 'Customization saved successfully',
            timeout: 10000
        });

        setTimeout(() => {
            location.reload();
        }, 5000);
    }

    function ajaxRequest(formArray) {
        var formData = new FormData(document.getElementById(formArray.formId));

        $.ajax({
            type: 'POST',
            url: formArray.url,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': formArray.csrf
            },
            beforeSend: function () {
                if (typeof beforeCallAjaxRequest === 'function') {
                    beforeCallAjaxRequest(formArray.formObject);
                }
            },
            success: function (data) {
                if (typeof afterSuccessAjax === 'function') {
                    afterSuccessAjax(formArray.formObject);
                }
            },
            error: function (response) {
                var message = response.responseJSON.message || 'Something went wrong.';
                iziToast.error({
                    title: 'Error',
                    layout: 2,
                    message: message
                });
            },
            complete: function () {
                if (typeof afterCallAjaxResponse === 'function') {
                    afterCallAjaxResponse(formArray.formObject);
                }
            }
        });
    }

    // Handle image preview and filename display for all three images
    function handleImagePreview(inputId, previewId, filenameId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const filename = document.getElementById(filenameId);

        if (input && preview && filename) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                    filename.textContent = this.files[0].name;
                }
            });
        }
    }

    // Handle image deletion
    function handleImageDeletion() {
        $('.delete-image').on('click', function() {
            const imageKey = $(this).data('image-key');
            const imageName = $(this).data('image-name');
            
            iziToast.question({
                timeout: 20000,
                close: false,
                overlay: true,
                displayMode: 'once',
                id: 'question',
                zindex: 999,
                title: 'Confirm Deletion',
                message: 'Are you sure you want to delete this image?',
                position: 'center',
                buttons: [
                    ['<button><b>YES</b></button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        
                        $.ajax({
                            type: 'DELETE',
                            url: '/customize/image/delete',
                            data: {
                                image_key: imageKey,
                                image_name: imageName,
                                _token: $('input[name="_token"]').val()
                            },
                            dataType: 'json',
                            success: function (data) {
                                if (data.success) {
                                    iziToast.success({
                                        title: 'Success',
                                        layout: 2,
                                        message: data.message,
                                        timeout: 5000
                                    });
                                    
                                    // Reload the page after successful deletion
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1000);
                                } else {
                                    iziToast.error({
                                        title: 'Error',
                                        layout: 2,
                                        message: data.message
                                    });
                                }
                            },
                            error: function (response) {
                                var message = response.responseJSON.message || 'Something went wrong while deleting the image.';
                                iziToast.error({
                                    title: 'Error',
                                    layout: 2,
                                    message: message
                                });
                            }
                        });
                        
                    }, true],
                    ['<button>NO</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    }],
                ]
            });
        });
    }

    // Initialize for all three images when DOM is ready
    $(document).ready(function() {
        handleImagePreview('image_1', 'image_1_preview', 'image_1_filename');
        handleImagePreview('image_2', 'image_2_preview', 'image_2_filename');
        handleImagePreview('image_3', 'image_3_preview', 'image_3_filename');
        handleImageDeletion();
    });
});