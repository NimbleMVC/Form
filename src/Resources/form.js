(function($) {
    $.fn.ajaxform = function(options) {
        const defaults = {
            onSuccess: null,
            onError: null
        };

        const settings = $.extend({}, defaults, options);

        return this.each(function() {
            const form = $(this);

            form.on('submit', function(event) {
                event.preventDefault();

                form.trigger('ajaxform.submit', [form]);

                const formData = form.serialize();
                let urlObj = new URL(window.location.href);

                if (form.closest('[data-url]').length > 0) {
                    urlObj = new URL(window.location.origin + form.closest('[data-url]').attr('data-url'));
                }

                urlObj.searchParams.append('ajax', 'form');
                urlObj.searchParams.append('form', form.attr('id'));

                $.ajax({
                    url: urlObj.toString(),
                    type: form.attr('method') || 'POST',
                    data: formData,
                    success: function(response, status, xhr) {
                        let preventDefault = false;

                        form.trigger('ajaxform.success', [response, form, function() {
                            preventDefault = true;
                        }]);

                        if (!preventDefault) {
                            if (typeof response === 'object' && response.type === 'redirect' && response.url) {
                                window.location.href = response.url;
                            } else {
                                const currentElement = document.getElementById(form.attr('id'));

                                if (currentElement) {
                                    currentElement.innerHTML = response;
                                }
                            }
                        }

                        if (typeof settings.onSuccess === 'function') {
                            settings.onSuccess(response, form);
                        }
                    },
                    error: function(xhr, status, error) {
                        let preventDefault = false;

                        form.trigger('ajaxform.error', [xhr, status, error, form, function() {
                            preventDefault = true;
                        }]);

                        if (!preventDefault) {
                            if (typeof xhr.responseJSON === 'object' && xhr.responseJSON.type === 'redirect' && xhr.responseJSON.url) {
                                window.location.href = xhr.responseJSON.url;
                            }

                            if (typeof settings.onError === 'function') {
                                settings.onError(xhr, status, error, form);
                            }
                        }

                        console.error('Error:', error);
                    }
                });
            });
        });
    };

    $(document).ready(function() {
        $('form.ajax-form').ajaxform();
    });
})(jQuery);
