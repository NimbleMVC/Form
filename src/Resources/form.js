$(document).ready(function() {
    $('form.ajax-form[id!=""]').on('submit', function(event) {
        event.preventDefault();

        const form = $(this),
            formData = form.serialize();

        let urlObj = new URL(window.location.href);
        urlObj.searchParams.append('ajax', 'form');
        urlObj.searchParams.append('form', $(this).attr('id'));

        $.ajax({
            url: urlObj.toString(),
            type: form.attr('method') || 'POST',
            data: formData,
            success: (response, status, xhr) => {
                console.log(typeof response);
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
                console.error('Błąd:', error);
            }
        });
    });
});