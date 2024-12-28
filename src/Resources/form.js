$(document).ready(function() {
    $(document).on('submit', 'form.ajax-form[id!=""]', function(event) {
        event.preventDefault();

        const form = $(this),
            formData = form.serialize();

        let urlObj = new URL(window.location.href);

        if ($(this).closest('[data-url]').length > 0) {
            urlObj = new URL(window.location.origin + $(this).closest('[data-url]').attr('data-url'));
        }

        urlObj.searchParams.append('ajax', 'form');
        urlObj.searchParams.append('form', $(this).attr('id'));

        $.ajax({
            url: urlObj.toString(),
            type: form.attr('method') || 'POST',
            data: formData,
            success: (response, status, xhr) => {
                if (typeof response === 'object' && response.type === 'redirect' && response.url) {
                    window.location.href = response.url;
                } else {
                    const currentElement = document.getElementById($(this).attr('id'));

                    if (currentElement) {
                        currentElement.innerHTML = response;
                    }
                }
            },
            error: function(xhr, status, error) {
                if (typeof xhr.responseJSON === 'object' && xhr.responseJSON.type === 'redirect' && xhr.responseJSON.url) {
                    window.location.href = xhr.responseJSON.url;
                }

                console.error('Error:', error);
            }
        });
    });
});